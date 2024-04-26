<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *  Sharing Cart
 *
 * @package    block_sharing_cart
 * @copyright  2017 (C) VERSION2, INC.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_sharing_cart;

use backup;
use backup_controller;
use block_sharing_cart\event\backup_activity_created;
use block_sharing_cart\event\backup_activity_started;
use block_sharing_cart\event\restore_activity_created;
use block_sharing_cart\event\restore_activity_started;
use block_sharing_cart\event\section_backedup;
use block_sharing_cart\event\section_deleted;
use block_sharing_cart\event\section_restored;
use block_sharing_cart\event\sharing_cart_item_deleted;
use block_sharing_cart\exceptions\no_backup_support_exception;
use block_sharing_cart\repositories\backup_options;
use block_sharing_cart\repositories\backup_repository;
use block_sharing_cart\repositories\course_module_repository;
use block_sharing_cart\repositories\course_repository;
use block_sharing_cart\repositories\task_repository;
use block_sharing_cart\task\async_restore_course_module;
use cache_helper;
use cm_info;
use coding_exception;
use context_course;
use context_module;
use context_user;
use core\event\course_module_created;
use core_text;
use dml_exception;
use file_exception;
use moodle_database;
use moodle_exception;
use require_login_exception;
use restore_activity_task;
use restore_controller;
use restore_fix_missings_helper;
use stdClass;
use base_setting;

use stored_file_creation_exception;

use textlib;

use function check_dir_exists;
use function clean_filename;
use function confirm_sesskey;
use function format_module_intro;
use function fulldelete;
use function get_config;
use function get_coursemodule_from_id;
use function get_file_packer;
use function has_capability;
use function moveto_module;
use function rebuild_course_cache;
use function require_capability;
use function require_login;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../../../course/lib.php';

/**
 *  Sharing Cart action controller
 */
class controller {
    /** @const int  The maximum length of a backup file name */
    protected const MAX_FILENAME = 20;

    /** @var string	The prefix to add to the file to let the user know this is a Sharing Cart file */
    protected const PREFIX_FILENAME = 'Sharingcart';

	/**
	 *  Constructor
	 *
	 * @throws coding_exception
	 * @throws moodle_exception
	 * @throws require_login_exception
	 */
    public function __construct() {
        require_login(null, false, null, false, true);
    }

    /**
     *  Render an item tree
     *
     * @param int|null $userid = $USER->id
     * @return string HTML
     * @throws coding_exception
     * @throws dml_exception
     * @global moodle_database $DB
     * @global object $USER
     */
    public function render_tree(int $userid = null): string {
        global $DB, $USER;

        require_once __DIR__ . '/renderer.php';

        // build an item tree from flat records
        $records = $DB->get_records('block_sharing_cart', array('userid' => $USER->id));

        $course_repo = new course_repository($DB);
        // Get all course full name from course ids in the records
        $course_fullnames = $course_repo->get_course_fullnames_by_sharing_carts($records);

        $records = array_values($records);
        $records = $this->attach_uninstall_attribute($records);

        $tree = [];
        foreach ($records as $record) {
            $record->coursefullname = $course_fullnames[(int)$record->course] ?? '';
            $components = explode('/', trim($record->tree, '/'));
            $node_ptr = &$tree;
            do {
                $dir = (string) array_shift($components);
                isset($node_ptr[$dir]) || $node_ptr[$dir] = [];
                $node_ptr = &$node_ptr[$dir];
            } while ($dir !== '');
            $node_ptr[] = $record;
        }

        // sort tree nodes and leaves
        $sort_node = static function(array &$node) use (&$sort_node) {
            uksort($node, static function($lhs, $rhs) {
                // items follow directory
                if ($lhs === '') {
                    return +1;
                }
                if ($rhs === '') {
                    return -1;
                }
                return strnatcasecmp($lhs, $rhs);
            });
            foreach ($node as $name => &$leaf) {
                if ($name !== '') {
                    $sort_node($leaf);
                } else {
                    usort($leaf, static function($lhs, $rhs) {
                        if ($lhs->weight < $rhs->weight) {
                            return -1;
                        }
                        if ($lhs->weight > $rhs->weight) {
                            return +1;
                        }
                        return strnatcasecmp($lhs->modtext, $rhs->modtext);
                    });
                }
            }
        };
        $sort_node($tree);

        return renderer::render_tree($tree);
    }

	/**
	 *  Get whether a module is userdata copyable and the logged-in user has enough capabilities
	 *
	 * @param int $cmid
	 * @return boolean
	 * @throws coding_exception
	 * @throws dml_exception
	 */
    public function is_userdata_copyable(int $cmid): bool {
        $cm = get_coursemodule_from_id(null, $cmid, 0, false, MUST_EXIST);
        $modtypes = get_config('block_sharing_cart', 'userdata_copyable_modtypes');
        $context = context_module::instance($cm->id);
        return in_array($cm->modname, explode(',', $modtypes))
                && has_capability('moodle/backup:userinfo', $context)
                && has_capability('moodle/backup:anonymise', $context)
                && has_capability('moodle/restore:userinfo', $context);
    }

	/**
	 *  Get whether any module in section is userdata copyable and the logged-in user has enough capabilities
	 *
	 * @param int $sectionid
	 * @return boolean
	 * @throws coding_exception
	 * @throws dml_exception
	 */
    public function is_userdata_copyable_section(int $sectionid): bool {
        GLOBAL $DB;

        $modules = $DB->get_records('course_modules', array('section' => $sectionid), '', 'id');

        foreach ($modules as $module) {
            if ($this->is_userdata_copyable((int)$module->id)) {
                return true;
            }
        }

        return false;
    }

    public function backup_async(
        int $cm_id,
        int $course_id,
        bool $include_userdata = false,
        bool $include_badges = false
    ): void
    {
        global $USER;

        $options = new backup_options();
        $options->set_include_user_data($include_userdata, context_user::instance($USER->id))
            ->set_include_badge($include_badges);

        $repo = backup_repository::create();
        $repo->backup_async(
            $USER->id,
            $cm_id,
            $course_id,
            $USER->id,
            $options
        );
    }

    public function backup_section_async(
        int $section_id,
        int $course_id,
        ?string $section_name = null,
        bool $include_userdata = false,
        bool $include_badges = false
    ): void
    {
        global $USER;

        $options = new backup_options();
        $options->set_include_user_data($include_userdata, context_user::instance($USER->id))
            ->set_include_badge($include_badges);

        $repo = backup_repository::create();
        $repo->backup_section_async(
            $USER->id,
            $course_id,
            $section_id,
            $section_name,
            $options
        );
    }

    /**
     *  Backup a module into Sharing Cart
     *
     * @param int $cmid
     * @param boolean $has_userdata
     * @param int $course
     * @param int $section
     * @return int
     * @throws moodle_exception
     * @global object $CFG
     * @global moodle_database $DB
     * @global object $USER
     */
    public function backup(
        int $cmid,
        bool $has_userdata,
        int $course,
        int $section = 0,
        bool $include_badges = false,
        ?int $user_id = null
    ): int {

        global $USER, $CFG; //$CFG IS USED, DO NOT REMOVE IT

        if (module::has_backup($cmid, $course) === false) {
            throw new no_backup_support_exception('No backup in module',
                    'Module not implementing: https://docs.moodle.org/dev/Backup_API');
        }

        // THIS FILE REQUIRES $CFG, DO NOT REMOVE IT
        require_once __DIR__ . '/../../../backup/util/includes/backup_includes.php';

        $mod_ifo = get_fast_modinfo($course);
        $cm = $mod_ifo->get_cm($cmid);
        $user_id ??= $USER->id;

        backup_activity_started::create_by_course_module_id(
            $course,
            $cm->id
        )->trigger();

        // validate parameters and capabilities
        $context = $cm->context;
        require_capability('moodle/backup:backupactivity', $context);
        if ($has_userdata) {
            require_capability('moodle/backup:userinfo', $context);
        }

        // generate a filename from the module info
        $modtext = $cm->modname === 'label' ? self::get_cm_intro($cm) : $cm->name;
        $filename = backup_repository::create_backup_filename($cm);

        // backup the module into the predefined area
        //    - user/backup ... if userdata not included
        //    - backup/activity ... if userdata included
        $settings = [
                'role_assignments' => false,
                'activities' => true,
                'blocks' => false,
                'filters' => false,
                'comments' => false,
                'calendarevents' => false,
                'userscompletion' => false,
                'logs' => false,
                'grade_histories' => false,
                'users' => false,
                'anonymize' => false,
                'badges' => $include_badges
        ];
        if ($has_userdata && has_capability('moodle/backup:userinfo', $context)) {
            $settings['users'] = true;
        }
        $controller = new backup_controller(
                backup::TYPE_1ACTIVITY,
                $cm->id,
                backup::FORMAT_MOODLE,
                backup::INTERACTIVE_NO,
                backup::MODE_GENERAL,
                $user_id,
                backup::RELEASESESSION_YES
        );
        $plan = $controller->get_plan();
        foreach ($settings as $name => $value) {
            if ($plan->setting_exists($name)) {
                $current_setting = $plan->get_setting($name);
                // If locked
                if (base_setting::NOT_LOCKED !== $current_setting->get_status()) {
                    continue;
                }
                $current_setting->set_value($value);
            }
        }
        $plan->get_setting('filename')->set_value($filename);

        set_time_limit(0);
        $controller->set_status(backup::STATUS_AWAITING);
        $controller->execute_plan();

        // move the backup file to user/backup area if it is not in there
        $results = $controller->get_results();
        /** @var \stored_file $file */
        $file = $results['backup_destination'];
        $backup_file = $file;

        // insert an item record
        $record = new record([
            'userid' => $user_id,
            'modname' => $cm->modname,
            'modicon' => $cm->icon,
            'modtext' => $modtext,
            'filename' => $filename,
            'course' => $course,
            'section' => $section,
            'fileid' => 0
        ]);
        $id = $record->insert();

        $storage = new storage($user_id);
        $new_backup_file = $storage->copy_stored_file($backup_file, [
            'itemid' => $id
        ]);

        $record->filename = $new_backup_file->get_filename();
        $record->fileid = $new_backup_file->get_id();
        $record->update();

        $backup_file->delete();
        $controller->destroy();

        backup_activity_created::create_by_course_module_id(
            $course,
            $cmid,
            $id
        )->trigger();

        return $id;
    }

    /**
     * Backup an empty section
     *
     * @param int $courseid
     * @param int $sectionid
     * @return int New item ID
     * @throws dml_exception
     */
    public function backup_emptysection(int $courseid, int $sectionid): int {
        global $DB, $USER;
        $newitem = new stdClass();
        $newitem->id = 0;
        $newitem->userid = $USER->id;
        $newitem->modname = '';
        $newitem->modicon = '';
        $newitem->modtext = '';
        $newitem->ctime = time();
        $newitem->filename = '';
        $newitem->tree = '';
        $newitem->weight = 0;
        $newitem->course = $courseid;
        $newitem->section = $sectionid;
        return $DB->insert_record('block_sharing_cart', $newitem);
    }

    /**
     * Backup a section into Sharing Cart
     *
     * @param int $sectionid
     * @param string|null $sectionname
     * @param bool $userdata
     * @param int $course
     * @throws moodle_exception
     */
    public function backup_section(int $sectionid, ?string $sectionname, bool $userdata, int $course): void {
        global $DB, $USER;

        $itemids = array();

        try {
            // Save section data
            $section = $DB->get_record('course_sections', array('id' => $sectionid));
            $sharing_cart_section = new stdClass();
            $sharing_cart_section->id = 0;
            $sharing_cart_section->name = get_section_name($section->course, $section->section);
            $sharing_cart_section->summary = $section->summary;
            $sharing_cart_section->summaryformat = $section->summaryformat;
            $sharing_cart_section->availability = $section->availability;
            $sc_section_id = $DB->insert_record('block_sharing_cart_sections', $sharing_cart_section);
            $sc_section_id = $sc_section_id ?: 0;

            // Save section files
            if ($sc_section_id > 0) {
                $course_context = context_course::instance($course);
                $user_context = context_user::instance($USER->id);
                $fs = get_file_storage();

                $files = $fs->get_area_files($course_context->id, 'course', 'section', $sectionid);
                foreach ($files as $file) {
                    if ($file->get_filename() !== '.') {
                        $filerecord = array(
                                'contextid' => $user_context->id,
                                'component' => 'user',
                                'filearea' => 'sharing_cart_section',
                                'itemid' => $sc_section_id,
                                'filepath' => $file->get_filepath()
                        );

                        $fs->create_file_from_storedfile($filerecord, $file);
                    }
                }
            }

            // Backup all
            $modulesequence = explode(',', $section->sequence);

            $modulecount = $DB->count_records_sql('SELECT COUNT(*) FROM {course_modules} as cm
                                                         INNER JOIN {modules} as m ON m.id = cm.module
                                                         WHERE m.visible = 1 AND cm.section = :sectionid AND cm.deletioninprogress = 0', [
                        'sectionid' => $sectionid
            ]);

            if (count($modulesequence) != $modulecount) {
                $modules = $DB->get_records_sql('SELECT cm.* FROM {course_modules} as cm
                                                       INNER JOIN {modules} as m ON m.id = cm.module
                                                       WHERE m.visible = 1 AND cm.section = :sectionid AND cm.deletioninprogress = 0', [
                        'sectionid' => $sectionid
                ]);
            } else {
                $modules = [];
                foreach ($modulesequence as $modid) {
                    $modules[] = $DB->get_record_sql('SELECT cm.* FROM {course_modules} as cm
                                                       INNER JOIN {modules} as m ON m.id = cm.module
                                                       WHERE m.visible = 1 AND cm.id = :moduleid AND cm.deletioninprogress = 0', [
                        'moduleid' => $modid
                    ]);
                }
            }

            // Fixed ISSUE-12 - https://github.com/donhinkelman/moodle-block_sharing_cart/issues/12
            // Issue-83 (solution) copying empty section: create an empty module in cart to make the folder path to be visible in cart
            //    so an empty folder can be rendered.
            if (count($modules)) {
                foreach ($modules as $module) {
                    if ((isset($module->deletioninprogress)
                            && $module->deletioninprogress) === 1
                            || module::has_backup((int)$module->id) === false) {
                        continue;
                    }

                    $itemids[] = $this->backup(
                        (int)$module->id,
                        $userdata && $this->is_userdata_copyable((int)$module->id),
                        $course,
                        $sc_section_id
                    );
                }
            } else {
                $itemids[] = $this->backup_emptysection($course, $sc_section_id);
            }

            // Check empty folder name
            $foldername = str_replace("/", "-", $sectionname);

            if ($DB->record_exists("block_sharing_cart", array("tree" => $foldername, 'userid' => $USER->id))) {
                // Get other folder that contain increment number
                $folder_like = $DB->sql_like_escape($foldername);
                $params = ['userid' => $USER->id, 'tree' => $folder_like . ' (%)'];
                $folders = $DB->get_fieldset_select(record::TABLE, 'tree', 'userid = :userid AND tree LIKE :tree', $params);

                // Increase folder number
                $folder_number = empty($folders) ? 1 : count($folders) + 1;
                $foldername .= " ({$folder_number})";
            }

            // Move backup files to folder
            foreach ($itemids as $itemid) {
                $this->movedir($itemid, $foldername);
            }

            // Trigger event
            $event = section_backedup::create([
                'context' => context_course::instance($course),
                'objectid' => $sc_section_id,
                'other' => $sectionid
            ]);
            $event->trigger();
        } catch (moodle_exception $ex) {
            if ($ex->errorcode == "storedfilenotcreated") {
                foreach ($itemids as $itemid) {
                    $this->delete($itemid);
                }
            }

            throw $ex;
        }
    }

    /**
     * Multibyte safe get_string_length() function, uses mbstring or iconv for UTF-8, falls back to typo3.
     *
     * @param string $text input string
     * @return int number of characters
     */
    private function get_string_length(string $text): int {
        $textlength = 0;
        if (method_exists('textlib', 'strlen')) {
            $textlength = textlib::strlen($text);
        } else if (method_exists('core_text', 'strlen')) {
            $textlength = core_text::strlen($text);
        }
        return $textlength;
    }

    /**
     * Multibyte safe get_sub_string() function, uses mbstring or iconv for UTF-8, falls back to typo3.
     *
     * @param string $text string to truncate
     * @param int $start negative value means from end
     * @param int $length maximum length of characters beginning from start
     * @return string portion of string specified by the $start and $len
     */
    private function get_sub_string($text, $start, $length) {
        $result = 0;
        if (method_exists('textlib', 'substr')) {
            $result = textlib::substr($text, $start, $length);
        } else if (method_exists('core_text', 'substr')) {
            $result = core_text::substr($text, $start, $length);
        }
        return $result;
    }

    public function restore_async(
        int $sharing_cart_id,
        int $course_id,
        int $section_number,
        ?int $user_id = null
    ): void
    {
        async_restore_course_module::add_to_queue(
            $sharing_cart_id,
            $course_id,
            $section_number,
            $user_id
        );
        task_repository::create()->set_restore_in_progress(
            $sharing_cart_id,
            $course_id,
            $section_number,
            $user_id
        );
    }

    /**
     *  Restore an item into a course section
     *
     * @param int $id
     * @param int $courseid
     * @param int $sectionnumber
     * @throws moodle_exception
     * @global moodle_database $DB
     * @global object $USER
     * @global object $CFG
     */
    public function restore(int $id, int $courseid, int $sectionnumber, ?int $user_id = null): void {
        global $CFG, $DB, $USER;

        require_once __DIR__ . '/../../../backup/util/includes/restore_includes.php';
        require_once __DIR__ . '/../backup/util/helper/restore_fix_missings_helper.php';

        restore_activity_started::create_by_sharing_cart_backup_id($id)->trigger();

        $user_id ??= $USER->id;

        // validate parameters and capabilities
        $record = record::from_id($id);
        if ($record->userid != $user_id) {
            throw exception::from_forbidden();
        }
        if ($record->fileid < 1) {
            throw exception::from_backup_not_found();
        }

        // cleanup temporary files when we exit this scope
        $tempfiles = array();
        $scope = new scoped(function() use (&$tempfiles) {
            foreach ($tempfiles as $tempfile) {
                fulldelete($tempfile);
            }
        });

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $section = $DB->get_record('course_sections',
                array('course' => $course->id, 'section' => $sectionnumber), '*', MUST_EXIST);
        require_capability('moodle/restore:restorecourse',
                context_course::instance($course->id)
        );

        // prepare the temporary directory and generate a temporary name
        $tempdir = self::get_tempdir();
        $tempname = restore_controller::get_tempdir_name($course->id, $user_id);

        // copy the backup archive into the temporary directory
        $file = get_file_storage()->get_file_by_id($record->fileid);
        $file->copy_content_to("$tempdir/$tempname.mbz");
        $tempfiles[] = "$tempdir/$tempname.mbz";

        // extract the archive in the temporary directory
        $packer = get_file_packer('application/vnd.moodle.backup');
        $packer->extract_to_pathname("$tempdir/$tempname.mbz", "$tempdir/$tempname");
        $tempfiles[] = "$tempdir/$tempname";

        // restore a module from the extracted files
        $controller = new restore_controller(
            $tempname,
            $course->id,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $user_id,
            backup::TARGET_EXISTING_ADDING,
            null,
            backup::RELEASESESSION_YES
        );
        foreach ($controller->get_plan()->get_tasks() as $task) {
            if ($task->setting_exists('overwrite_conf')) {
                $task->get_setting('overwrite_conf')->set_value(false);
            }
            if ($task->setting_exists('userscompletion')) {
                $has_user_data = false;
                if ($task->setting_exists('user')) {
                    $has_user_data = (bool)$task->get_setting('userscompletion')->get_value();
                }
                if (!$has_user_data) {
                    $task->get_setting('userscompletion')->set_value(false);
                }
            }
        }
        if (get_config('block_sharing_cart', 'workaround_qtypes')) {
            restore_fix_missings_helper::fix_plan($controller->get_plan());
        }
        $controller->set_status(backup::STATUS_AWAITING);
        $controller->execute_plan();

        // move the restored module to desired section
        foreach ($controller->get_plan()->get_tasks() as $task) {
            if ($task instanceof restore_activity_task) {
                $cmid = $task->get_moduleid();
                $cm = get_coursemodule_from_id(null, $cmid, 0, false, MUST_EXIST);
                moveto_module($cm, $section);
                // Fire event.
                $event = course_module_created::create_from_cm($cm);
                $event->trigger();

                restore_activity_created::create_by_course_module_id(
                    $course->id,
                    $cmid,
                    $id
                )->trigger();
            }
        }
        rebuild_course_cache($course->id);

        $controller->destroy();
    }

    /**
     * Resotre a directory into a course section
     *
     * @param string $path
     * @param int $courseid
     * @param int $sectionnumber
     * @param int $overwritesectionid
     * @param bool $is_async
     * @param int|null $user_id
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws moodle_exception
     * @throws stored_file_creation_exception
     */
    public function restore_directory(
        string $path,
        int $courseid,
        int $sectionnumber,
        int $overwritesectionid,
        bool $is_async = false,
        ?int $user_id = null
    ): void {
        global $DB, $USER;

        $user_id ??= $USER->id;
        $cart_items = $DB->get_records('block_sharing_cart', ['tree' => $path, 'userid' => $user_id], 'weight ASC');
        if ($is_async) {
            foreach ($cart_items as $cart_item) {
                if (!$cart_item->fileid) { // issue-83 skip restoring empty item
                    continue;
                }
                $this->restore_async($cart_item->id, $courseid, $sectionnumber, $user_id);
            }
        }
        else {
            foreach ($cart_items as $cart_item) {
                if (!$cart_item->fileid) { // issue-83 skip restoring empty item
                    continue;
                }
                $this->restore($cart_item->id, $courseid, $sectionnumber, $user_id);
            }
        }

        $course_context = context_course::instance($courseid);

        $restored_section = $DB->get_record('course_sections', array('course' => $courseid, 'section' => $sectionnumber));

        if ($overwritesectionid > 0) {
            $overwrite_section = $DB->get_record('block_sharing_cart_sections', array('id' => $overwritesectionid));

            $original_restored_section = clone($restored_section);

            if ($overwrite_section && $restored_section) {
                $restored_section->name = $overwrite_section->name;
                $restored_section->summary = $overwrite_section->summary;
                $restored_section->summaryformat = $overwrite_section->summaryformat;
                $restored_section->availability = $overwrite_section->availability;

                cache_helper::purge_by_event('changesincourse');
                course_update_section($courseid, $original_restored_section, $restored_section);
            }

            // Copy section files
            $user_context = context_user::instance($user_id);
            $fs = get_file_storage();
            $files = $fs->get_area_files($user_context->id, 'user', 'sharing_cart_section', $overwritesectionid);
            foreach ($files as $file) {
                if ($file->get_filename() !== '.') {
                    $filerecord = array(
                            'contextid' => $course_context->id,
                            'component' => 'course',
                            'filearea' => 'section',
                            'itemid' => $restored_section->id,
                            'filepath' => $file->get_filepath()
                    );

                    $fs->create_file_from_storedfile($filerecord, $file);
                }
            }
        }

        // Trigger event
        $event = section_restored::create([
            'context' => $course_context,
            'objectid' => $overwritesectionid,
            'other' => [
                'restored_section_id' => $restored_section->id,
                'overwrite_section_settings' => $overwritesectionid > 0
            ]
        ]);
        $event->trigger();
    }

    /**
     *  Move a shared item into a directory
     *
     * @param int $id
     * @param string $path
     * @throws exception
     * @global object $USER
     */
    public function movedir($id, $path): void {
        global $USER;

        $record = record::from_id($id);
        if ($record->userid != $USER->id) {
            throw exception::from_forbidden();
        }
        self::validate_sesskey();

        $components = array_filter(explode('/', $path), 'strlen');
        $path = implode('/', $components);
        if (strcmp($record->tree, $path) != 0) {
            $record->tree = $path;
            $record->weight = record::WEIGHT_BOTTOM;
            $record->update();
        }
    }

    /**
     *  Move a shared item to a position of another item
     *
     * @param int $id The record ID to move
     * @param int $to The record ID of the desired position or zero for move to bottom
     * @throws dml_exception
     * @throws exception
     * @global moodle_database $DB
     * @global object $USER
     */
    public function move($id, $to): void {
        global $DB, $USER;

        $record = record::from_id($id);
        if ($record->userid != $USER->id) {
            throw exception::from_forbidden();
        }
        self::validate_sesskey();

        // get the weight of desired position
        $record->weight = $to != 0
                ? record::from_id($to)->weight
                : record::WEIGHT_BOTTOM;

        // shift existing items under the desired position
        $DB->execute(
                'UPDATE {' . record::TABLE . '} SET weight = weight + 1
			 WHERE userid = ? AND tree = ? AND weight >= ?',
                array($USER->id, $record->tree, $record->weight)
        );

        $record->update();
    }

    /**
     *  Delete a shared item by record ID
     *
     * @param int $id
     * @throws moodle_exception
     * @global object $USER
     */
    public function delete($id): void {
        global $USER;

        $record = record::from_id($id);
        if ($record->userid != $USER->id) {
            throw exception::from_forbidden();
        }
        self::validate_sesskey();

        $storage = new storage();
        $storage->delete($record->filename);
        $record->delete();

        sharing_cart_item_deleted::create_by_sharing_cart_item_id($id, $record->course)->trigger();
    }

    /**
     * Delete a directory
     *
     * @param $path
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function delete_directory($path): void {
        global $DB, $USER;

        if ($path[0] == '/') {
            $path = substr($path, 1);
        }

        $cart_items = $DB->get_records('block_sharing_cart', ['tree' => $path, 'userid' => $USER->id], '', 'id');
        foreach ($cart_items as $cart_item) {
            $this->delete($cart_item->id);
        }

        $this->delete_unused_sections();

        // Delete unused file
        $fs = get_file_storage();
        $user_context = context_user::instance($USER->id);
        $files = $fs->get_area_files($user_context->id, 'user', 'sharing_cart_section');
        foreach ($files as $file) {
            $sectionid = $file->get_itemid();
            if (!$DB->record_exists('block_sharing_cart_sections', array('id' => $sectionid))) {
                $file->delete();
            }
        }
    }

    /**
    * Delete sections without activities since they are not used anymore
    *
    * @param int $course_id
    *
    * @return void
    * @throws dml_exception
    */
    public function delete_unused_sections(int $course_id = 0) : void {

        global $DB, $USER;

        $sql_params = [];

        $sql = /** @lang mysql */'
        SELECT DISTINCT s.id
        FROM {block_sharing_cart_sections} s
        LEFT JOIN {block_sharing_cart} sc ON s.id = sc.section
        ';

        if (!empty($course_id)) {
            $sql .= 'WHERE sc.course = :course_id';
            $sql_params['course_id'] = $course_id;
        }

        $sections = $DB->get_records_sql($sql, $sql_params);

        foreach ($sections as $section) {
            if ((int)$DB->count_records('block_sharing_cart', ['section' => $section->id]) === 0) {
                $DB->delete_records('block_sharing_cart_sections', ['id' => $section->id]);

                // Trigger event
                $event = section_deleted::create([
                    'context' => context_user::instance($USER->id),
                    'objectid' => $section->id
                ]);
                $event->trigger();
            }
        }
    }

    /**
     * Get sections in specified path
     *
     * @param string $path
     * @return array
     * @throws dml_exception
     */
    public function get_path_sections(string $path): array {
        global $DB, $USER;

        $section_ids = array();
        $items = $DB->get_records('block_sharing_cart', array('tree' => $path, 'userid' => $USER->id));
        foreach ($items as $item) {
            if ($item->section) {
                $section_ids[] = $item->section;
            }
        }

        $section_ids = array_unique($section_ids);
        return $DB->get_records_list('block_sharing_cart_sections', 'id', $section_ids);
    }

    /**
     *  Get the path to the temporary directory for backup
     *
     * @return string
     * @throws exception
     * @global object $CFG
     */
    public static function get_tempdir(): string {
        global $CFG;
        $tempdir = $CFG->backuptempdir;
        if (!check_dir_exists($tempdir, true, true)) {
            throw exception::from_unexpected_error();
        }
        return $tempdir;
    }

    /**
     *  Check if the given session key is valid
     *
     * @param string|null $sesskey = \required_param('sesskey', PARAM_RAW)
     * @throws exception
     */
    public static function validate_sesskey(string $sesskey = null): void {
        try {
            if (confirm_sesskey($sesskey)) {
                return;
            }
        } catch (moodle_exception $ex) {
            unset($ex);
        }
        throw exception::from_invalid_operation();
    }

    /**
     *  Get the intro HTML of the course module
     *
     * @param cm_info $cm
     * @return string
     */
    public static function get_cm_intro(cm_info $cm): string {
        return course_module_repository::create()->get_title($cm);
    }

    /**
     * @param int $cmid
     * @param int $courseid
     * @return false|string
     * @throws moodle_exception
     */
    public function ensure_backup_in_module(int $cmid, int $courseid) {
        return json_encode(array(
                'http_response' => 200,
                'message' => '',
                'data' => array(
                        'has_backup_routine' => module::has_backup($cmid, $courseid)
                ),
        ));
    }

    /**
     * @param stdClass[] $records
     * @return stdClass[]
     * @throws dml_exception
     */
    public function attach_uninstall_attribute(array $records): array {
        global $DB;

        foreach ($records as $record) {
            $record->uninstalled_plugin = true;

            if ($DB->get_field('modules', 'id', ['name' => $record->modname])) {
                $record->uninstalled_plugin = false;
            }
        }

        return $records;
    }
}
