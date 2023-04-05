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
 * Class for template persistence.
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_template\models;
use core_user;
use local_template\collections\persistentcollection;
use local_template\controllers;
use local_template\controllers\backupcontroller;
use local_template\local\notifications;
use moodle_database;
use renderable;
use renderer_base;
use stdClass;
use templatable;

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Class for loading/storing template from the DB.
 *
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class template extends \core\persistent implements renderable, templatable {

    const TABLE = 'local_template';

    public static $templateperpage = 10;

    private $backupcontrollers = null;

    private $createuser = null;

    /**
     * @var \local_tempalte\local\notifications notifications
     */
    public $notifications = null;

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'templatecourseid' => [
                'type' => PARAM_INT,
                'description' => 'Foreign key link to the course table for the template course id',
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,
            ],
            'importcourseid' => [
                'type' => PARAM_INT,
                'description' => 'Foreign key link to the course table for the import course id',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ],
            'createdcourseid' => [
                'type' => PARAM_INT,
                'description' => 'Foreign key link to the course table for the created course id',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ],
            'copybackupid' => [
                'type' => PARAM_RAW,
                'description' => 'Foreign key link to the backup_controllers table on candidate key backupid for the course copy backup object',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ],
            'copyrestoreid' => [
                'type' => PARAM_RAW,
                'description' => 'Foreign key link to the backup_controllers table on candidate key backupid for the course copy restore object',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ],
            'importbackupid' => [
                'type' => PARAM_RAW,
                'description' => 'Foreign key link to the backup_controllers table on candidate key backupid for the course import backup object',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ],
            'importrestoreid' => [
                'type' => PARAM_RAW,
                'description' => 'Foreign key link to the backup_controllers table on candidate key backupid for the course import restore object',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ],
            'category' => [
                'type' => PARAM_INT,
                'description' => 'Foreign key link to destination course category',
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,
            ],
            'fullname' => [
                'type' => PARAM_TEXT,
                'description' => 'Destination Course full name',
                'null' => NULL_NOT_ALLOWED,
                'default' => '',
            ],
            'shortname' => [
                'type' => PARAM_TEXT,
                'description' => 'Destination Course short name',
                'null' => NULL_NOT_ALLOWED,
                'default' => '',
            ],
            'idnumber' => [
                'type' => PARAM_RAW,
                'description' => 'Destination Course idnumber',
                'null' => NULL_NOT_ALLOWED,
                'optional' => true,
                'default' => '',
            ],
            'summary' => [
                'type' => PARAM_RAW,
                'description' => 'Destination Course summary',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => '',
            ],
            'summaryformat' => [
                'type' => PARAM_INT,
                'description' => 'Destination Course summary format',
                'null' => NULL_NOT_ALLOWED,
                'choices' => [FORMAT_HTML, FORMAT_MOODLE, FORMAT_PLAIN, FORMAT_MARKDOWN],
                'default' => FORMAT_HTML,
            ],
            'startdate' => [
                'type' => PARAM_INT,
                'description' => 'Destination Course start date',
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,
            ],
            'enddate' => [
                'type' => PARAM_INT,
                'description' => 'Destination Course end date',
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,
            ],
            'visible' => [
                'type' => PARAM_BOOL,
                'description' => 'Destination Course visible',
                'null' => NULL_NOT_ALLOWED,
                'default' => true,
            ],

            'gudbstatus' => [
                'type' => PARAM_INT,
                'description' => 'gudatabase status 0..9 are system constants, 0 means active enrolment, see ENROL_STATUS_* constants, plugins may define own status greater than 10',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ],
            'gudbsettingscodes' => [
                'type' => PARAM_INT,
                'description' => 'Enable codes in course settings - When enabled also process any valid codes found in this courses shortname or idnumber fields. Only one enrolment method per course should have this set',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ],
            'gudballowhidden' => [
                'type' => PARAM_INT,
                'description' => 'Allow hidden course - If enabled, automatic enrolment will function even if the course is hidden. By default hidden courses are ignored',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ],
            'gudbcodelist' => [
                'type' => PARAM_TEXT,
                'description' => 'More codes (one per line)',
                'null' => NULL_ALLOWED,
                'optional' => true,
                'default' => null,
            ],

            'timecreated' => [
                'type' => PARAM_INT,
                'description' => 'Destination Course time created',
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,
            ],
            'timemodified' => [
                'type' => PARAM_INT,
                'description' => 'Destination Course time modified',
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,
            ],
            'usercreated' => [
                'default' => function () {
                    global $USER;
                    return $USER->id;
                },
                'description' => 'User that created the record.',
                'type' => PARAM_INT,
            ]
        ];
    }

    public static function collection($parentid = 0, $view = 'table', $displayheadings = true, $params = null, $sort = 'timemodified', $order = 'DESC') {

        $templatepage = template_get_paging('templatepage');

        $templateperpage = self::$templateperpage;
        if (!empty(get_config('local_template', 'templateperpage'))) {
            $templateperpage = get_config('local_template', 'templateperpage');
        }

        if (!is_template_admin()) {
            global $USER;
            // Only show records for current user, and not hidden records.
            $params['usercreated'] = $USER->id;
        }

        return new persistentcollection(get_called_class(), $parentid, $view, $displayheadings, $select = '', $params, $sort, $order, $templatepage, $templateperpage);
    }

    public static function title() {
        return get_string('template', 'local_template');
    }

    public static function collection_properties($parentid = 0) {
        global $OUTPUT;
        $properties = [
            'timemodified' => [
                'label' => get_string('timemodified', 'local_template'),
                'alignment' => 'left',
            ],
            'fullname' => [
                'label' => get_string('fullname', 'local_template'),
                'alignment' => 'left',
            ],
            'importcourseid' => [
                'label' => get_string('importcourse', 'local_template'),
                'alignment' => 'left',
            ],
            'createdcourseid' => [
                'label' => get_string('importcourse', 'local_template'),
                'alignment' => 'left',
            ],
            'backupcontrollercollection' => [
                'label' => get_string('backupcontrollers', 'local_template'),
                'alignment' => 'left',
                'callback' => "get_backupcontrollercollection",
            ],
        ];
        if (is_template_admin()) {
            $properties['usercreated'] = [
                'label' => get_string('username', 'local_template'),
                'alignment' => 'left',
                'alignment' => 'left',
            ];
        }

        $properties['edit'] = [
            'label' => get_string('edit', 'local_template') . $OUTPUT->spacer() . self::add_new_icon($parentid),
            'alignment' => 'left',
            'callback' => "get_actions",
        ];

        return $properties;
    }

    public function __construct($id = 0, \stdClass $record = null) {
        parent::__construct($id, $record);

        /*
        if (!empty($id)) {
            if (!empty($this->raw_get('importfileid'))) {
                $this->read_file();
            }
        }
        */
    }

    protected function get_timemodified() {
        $timemodified = userdate($this->raw_get('timemodified'), get_string('strftimedatefullshort', 'core_langconfig'));
        if (empty($timemodified)) {
            $timemodified = get_string('missingtemplatedate','local_template');
        }
        return $timemodified;
    }

    protected function get_fullname($path = null) {
        $name = $this->raw_get('fullname');
        if (empty($name)) {
            $name = get_string('missingtemplatefullname','local_template');
        }
        $name = format_string($name);
        if (!empty($path)) {
            global $OUTPUT;
            $name .= $OUTPUT->spacer() . template_icon_link('edit', $path, ['action' => 'edittemplate', 'templateid' => $this->get('id')]);
        }
        return $name;
    }

    protected function get_usercreated() {
        if ($this->createuser) {
            return fullname($this->createuser);
        } else {
            $this->createuser = $this->read_user($this->raw_get('usercreated'));
            return fullname($this->createuser);
        }
    }

    /*
    // TODO: getcourseid
    protected function get_courseid() {
        $importfile = '';
        if (!empty($this->raw_get('courses'))) {
            $importfile .= $this->raw_get('courses') . '<br>';
        }
        if (!empty($this->raw_get('assessments'))) {
            $importfile .= $this->raw_get('assessments') . '<br>';
        }

        $file = $this->get_file();
        if (empty($file)) {
            $importfile .= get_string('missingfilename','local_template');
        } else {
            $importfile .= template_icon_link('download', $this->get_file_url());
        }

        return $importfile;
    }
    */

    public function get_backupcontrollercollection() {
        global $PAGE, $SESSION;
        $view = 'table';
        $show = true;
        if (object_property_exists($SESSION, 'local_template_view')) {
            $view = $SESSION->local_template_view;
            if ($view == 'header') {
                $view = 'table';
                $show = true;
            }
        }

        return '';
        // TODO: uncomment when backup controllers are working
        //$backupcontrollers = \local_template\models\backupcontroller::collection($this->raw_get('id'), $view, $show, ['templateid' => $this->get('id')]);
        //// $backupcontrollers = new backupcontrollercollection('backupcontroller Rows', controllers\backupcontroller::path(), $view, $show, ['templateid' => $this->get('id')]);
        //return $backupcontrollers->render();
    }

    public function get_identifier() {
        return $this->get_timemodified() . ' ' . $this->get('fullname');
    }

    public static function add_new_icon($parentid) {
        global $OUTPUT;
        return template_icon_link('add', controllers\template::path(), ['action' => 'createtemplate', 'id' => '0']);
    }

    public static function add_new($parentid) {
        global $OUTPUT;
        $label = get_string('addtemplate', 'local_template');
        return \html_writer::link(new \moodle_url(controllers\template::path(), ['action' => 'createtemplate', 'id' => '0']),
            $label . ' ' . $OUTPUT->pix_icon('t/add', $label), ['title' => $label]);

        if ($button) {

            $label = get_string('addtemplate', 'local_template');
            return \html_writer::link(new \moodle_url(controllers\template::path(), ['action' => 'createtemplate', 'id' => '0']),
                $label . ' ' . $OUTPUT->pix_icon('t/add', $label), ['title' => $label]);

            /*
                        return $OUTPUT->single_button(
                            new \moodle_url(controllers\template::path(), ['action' => 'createtemplate', 'id' => '0']),
                            get_string('addtemplate', 'local_template')
                        );
            */
        } else {
            return $OUTPUT->spacer() . get_string('addtemplate', 'local_template') . self::add_new_icon($parentid);
        }
    }

    public static function no_records($parentid) {
        global $OUTPUT;
        return $OUTPUT->notification(get_string('notemplatedefined', 'local_template') . $OUTPUT->spacer() . self::add_new($parentid));
    }

    public function get_actions($count = 0) {
        $path = controllers\template::path();
        $actions = '';
        // preview, add, edit, hide, show, moveup, movedown, delete
        $actions .= template_icon_link('preview', $path, ['action' => 'viewtemplate', 'templateid' => $this->raw_get('id')]);

        $actions .= template_icon_link('edit', $path, ['action' => 'edittemplate', 'templateid' => $this->raw_get('id')]);

        if (is_template_admin()) {
            $actions .= template_icon_link('delete', $path, ['action' => 'deletetemplate', 'templateid' => $this->raw_get('id'), 'sesskey' => sesskey()]);
        } else {
            $actions .= template_icon_link('delete', $path, ['action' => 'hidetemplate', 'templateid' => $this->raw_get('id')]);
        }

        $actions .= template_icon_link('go', $path, ['action' => 'runtemplate', 'templateid' => $this->raw_get('id')]);
        return $actions;
    }

    public static function get_context() {
        return \context_system::instance();
    }

    /*
    public static function get_importfileoptions() {
        global $CFG;
        $context = \context_system::instance();
        $maxbytes = get_user_max_upload_file_size($context, $CFG->maxbytes);

        $_10megabytes = 10485760;
        $areamaxbytes = $_10megabytes;
        if (!empty(get_config('local_template', 'areamaxbytes'))) {
            $areamaxbytes = get_config('local_template', 'areamaxbytes');
        }

        return [
            'subdirs' => 0, 'maxbytes' => $maxbytes, 'areamaxbytes' => $areamaxbytes, 'maxfiles' => 1
        ];
    }
    */
    /*
        public function get_file_url() {
            $file = $this->get_file();
            if (!empty($file)) {
                return \moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
            } else {
                return '';
            }
        }

        public function get_file_link() {
            $file = $this->get_file();
            if (empty($file)) {
                return get_string('importfilemissing','local_template');
            }

            $url = $this->get_file_url();
            if (empty($url)) {
                return get_string('importfilemissing','local_template');
            }

            $filename = $file->get_filename();
            return \html_writer::link($url, $filename, ['title' => $filename]);
        }
    */
    public function cascadedelete() {

        // 1. Delete file from mdl_file using file API
        // 2. Delete all backupcontrollers
        // 3. Delete self

        $fs = get_file_storage();
        $file = $fs->get_file_by_id($this->raw_get('importfileid'));
        if (!$file) {
            // No file to delete.
        } else {
            if (!$file->delete()) {
                return false;
            }
        }

        $backupcontrollers = $this->get_backupcontrollers();
        foreach ($backupcontrollers as $backupcontroller) {
            if (!$backupcontroller->cascadedelete()) {
                return false;
            }
        }

        // Finally delete self.
        if (!parent::delete()) {
            return false;
        }

        return true;
    }

    public function get_backupcontrollers() {
        if ($this->backupcontrollers) {
            return $this->backupcontrollers;
        } else {
            $this->backupcontrollers = $this->read_backupcontrollers();
            return $this->backupcontrollers;
        }
    }

    private function read_backupcontrollers() {
        $id = $this->get('id');
        if (!empty($id)) {
            $backupcontrollers = \local_template\models\backupcontroller::get_records(['templateid' => $id], 'timemodified', 'DESC');
            return $backupcontrollers;
        }
    }

    public function get_backupcontrollersnames($seperator=', ') {

        $backupcontrollers = $this->get_backupcontrollers();

        $names = '';
        foreach ($backupcontrollers as $backupcontroller) {

            $names .= $backupcontroller->get_formattedtimecreated();

            if (next($backupcontrollers) === false) {
            } else {
                $names .= $seperator;
            }

        }
        return $names;
    }

    private function read_user() {
        $userid = $this->raw_get('usercreated');
        if (!empty($userid)) {

            if (!core_user::is_real_user($userid)) {
                $this->userstatus = get_string('invaliduser', 'error');
                return false;
            }

            $user = core_user::get_user($userid);

            if (!$user) {
                $this->userstatus = get_string('invaliduser', 'error');
                return false;
            }

            if ($user->deleted) {
                $this->userstatus = get_string('userdeleted', 'moodle'); // error
                return $user;
            }

            if (empty($user->confirmed)) {
                $this->userstatus = get_string('usernotconfirmed', 'moodle', $user->username);
                return $user;
            }

            if (isguestuser($user)) {
                $this->userstatus = get_string('guestsarenotallowed', 'error');
                return $user;
            }

            if ($user->suspended) {
                $this->userstatus = get_string('suspended', 'auth');
                return $user;
            }

            if ($user->auth == 'nologin') {
                $this->userstatus = get_string('suspended', 'auth');
                return $user;
            }

            $this->userstatus = '';

            return $user;
        }
    }

    public function get_formattedtimecreated() {
        return userdate($this->raw_get('timecreated'), get_string('strftimedatefullshort', 'langconfig'));
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        global $SESSION, $OUTPUT, $USER;
        $view = 'table';
        if (object_property_exists($SESSION, 'local_template_view')) {
            $view = $SESSION->local_template_view;
        }

        $path = controllers\template::path();

        $headings = [];
        $headings[] = [
            'columnindex' => 0,
            'lastcol' => '',
            'alignment' => 'left',
            'text' => get_string('timemodified', 'local_template')
        ];
        $headings[] = [
            'columnindex' => 1,
            'lastcol' => '',
            'alignment' => 'left',
            'text' => get_string('fullname', 'local_template')
        ];
        $headings[] = [
            'columnindex' => 2,
            'lastcol' => '',
            'alignment' => 'left',
            'text' => get_string('course', 'local_template')
        ];

        $norecordslangstring = 'notemplatedefined';
        $addrecordlangstring = 'addtemplate';
        $addnewiconlink = template_icon_link('add', $path, ['action' => 'createtemplate', 'id' => '0']);
        $containsactions = true;

        if ($view == 'table') {
            $headings[] = [
                'columnindex' => 3,
                'lastcol' => '',
                'alignment' => 'left',
                'text' => get_string('backupcontrollers', 'local_template')
            ];
        }

        $filters = [];
        if (is_template_admin()) {
            // Add usercreated column
            $headings[] = [
                'columnindex' => 4,
                'lastcol' => '',
                'alignment' => 'left',
                'text' => get_string('user', 'local_template')
            ];
        } else {
            // Only show records for current user
            $filters['usercreated'] = $USER->id;
        }

        $records = [];
        $templatecollection = self::get_records($filters, 'timemodified', 'DESC');

        foreach ($templatecollection as $template) {
            $record = [];

            $templatedate = userdate($template->get('timemodified'), get_string('strftimedatefullshort', 'core_langconfig'));
            if (empty($templatedate)) {
                $templatedate = get_string('missingtemplatedate','local_template');
            }
            $record[] = format_string($templatedate);

            $templatename = $template->get('fullname');
            if (empty($templatename)) {
                $templatename = get_string('missingtemplatename','local_template');
            }
            $templatename = format_string($templatename);
            $templatename .= $OUTPUT->spacer() . template_icon_link('edit', $path, ['action' => 'edittemplate', 'templateid' => $template->get('id')]);

            $record[] = $templatename;

            $importfile = '';
            /*
            if (!empty($template->get('courses'))) {
                $importfile .= $template->get('courses') . '<br>';
            }
            if (!empty($template->get('assessments'))) {
                $importfile .= $template->get('assessments') . '<br>';
            }

            $file = $template->get_file();
            if (empty($file)) {
                $importfile .= get_string('missingfilename','local_template');
            } else {
                $importfile .= template_icon_link('download', $template->get_file_url());
            }
            $record[] = $importfile;
            */
            if ($view == 'table') {
                $record[] = backupcontroller::renderbackupcontrollers($template->get('id'), false);
            }

            if (is_template_admin()) {
                // Show user
                $record[] = format_string(fullname($template->get_createuser()));
            }

            $actions = '';
            // add, edit, hide, show, moveup, movedown, delete
            $actions .= template_icon_link('edit',$path, ['action' => 'edittemplate', 'templateid' => $template->get('id')]);

            if (is_template_admin()) {
                $actions .= template_icon_link('delete', $path, ['action' => 'deletetemplate', 'templateid' => $template->get('id'), 'sesskey' => sesskey()]);
            } else {
                $actions .= template_icon_link('delete', $path, ['action' => 'hidetemplate', 'templateid' => $template->get('id')]);
            }

            $record[] = $actions;

            // Append this backupcontroller to records for output
            $records[] = $record;
        }

        /*
        if ($view == 'table') {
            $output = create_action_table($records, $headings, $headingalignment, $norecordslangstring, $addrecordlangstring, $addnewiconlink, $containsactions);
        } else {
            if ($view == 'list') {
                $output = create_action_list($records, $headings, $headingalignment, $norecordslangstring, $addrecordlangstring, $addnewiconlink, $containsactions);
            } else {
                $output = create_action_collapse($records, $headings, $headingalignment, $norecordslangstring, $addrecordlangstring, $addnewiconlink, $containsactions);
            }
        }
        */

        // $output .= backupcontroller::renderbackupcontrollers($template->get('id'));

        //return $output;

        $data = new stdClass();
        $data->displayheadings = true; // $displayheadings;
        $data->headings = $headings;
        $data->rows = $records;


        return $data;
    }


    /**
     * @return bool success or failure.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function process() {

        $notifications = new \local_template\local\notifications();
        $this->save_controllers();
        $this->process_controllers();
        $this->process_enrolment();
    }

    private function save_controllers() {
        global $USER;

        // Prevent duplicate values on fullname and shortname
        list($fullname, $shortname) = \restore_dbops::calculate_course_names(0, $this->get('fullname'), $this->get('shortname'));
        $this->set('fullname', $fullname);
        $this->set('shortname', $shortname);
        $createdcourseid = $this->create_new_course();
        $this->set('createdcourseid', $createdcourseid);

        // Copy backup controller.
        $copybc = new \backup_controller(\backup::TYPE_1COURSE, $this->get('templatecourseid'), \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO, \backup::MODE_GENERAL, $USER->id, \backup::RELEASESESSION_YES);
        $test = $copybc->get_backupid();
        $this->set('copybackupid', $test);
        //$copybc->set_status(\backup::STATUS_AWAITING);
        //$copybc->get_status();
        //$copybc->save_controller();
        //$copybc->destroy();

        $copybc->execute_plan();
        $results = $copybc->get_results();

/*
        $copybcprogress = new \core\progress\display();
        $copybcprogress->set_display_names();
        $copybc = \backup_controller::load_controller($this->get('copybackupid'));
        $copybc->set_progress($copybcprogress);
        $copybc->execute_plan();
        $results = $copybc->get_results();
        $copybc->destroy();
        */

        global $CFG;
        // Get the backup file.
        $coursecontext = \context_course::instance($this->get('templatecourseid'));
        $fs = get_file_storage();
        $files = $fs->get_area_files($coursecontext->id, 'backup', 'course', false, 'id ASC');
        $backupfile = reset($files);

        $backupdir = "restore_" . uniqid();
        $path = $CFG->tempdir . DIRECTORY_SEPARATOR . "backup" . DIRECTORY_SEPARATOR . $backupdir;

        $fp = get_file_packer('application/vnd.moodle.backup');
        $fp->extract_to_pathname($backupfile, $path);


        // Copy restore controller.
        $copyrc = new \restore_controller($backupdir, $createdcourseid, \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL, $USER->id, \backup::TARGET_NEW_COURSE, null,
            \backup::RELEASESESSION_NO, $this->get_copydata());
        $this->set('copyrestoreid', $copyrc->get_restoreid());
        //$copyrc->save_controller();
        // $copyrc->destroy();

        $copyrc->execute_precheck();
        $copyrc->execute_plan();

        /*
        $copyrcprogress = new \core\progress\display();
        $copyrcprogress->set_display_names();
        $copyrc = \restore_controller::load_controller($this->get('copyrestoreid'));
        $copyrc->set_progress($copyrcprogress);
        $copyrc->execute_precheck();
        $copyrc->execute_plan();
        $results = $copyrc->get_results();
        $copyrc->destroy();
        */

        // Import backup controller.
        $importbc = new \backup_controller(\backup::TYPE_1COURSE, $this->get('importcourseid'), \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO, \backup::MODE_GENERAL, $USER->id, \backup::RELEASESESSION_YES);
        $this->set('importbackupid', $importbc->get_backupid());
        $importbc->save_controller();
        //$importbc->destroy();

        // Import restore controller.
        $importrc = new \restore_controller($this->get('copybackupid'), $createdcourseid, \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT, $USER->id, \backup::TARGET_EXISTING_ADDING, null,
            \backup::RELEASESESSION_NO, $this->get_copydata());
        $this->set('importrestoreid', $importrc->get_restoreid());
        $importrc->save_controller();
        // $importrc->destroy();

        $this->save();
    }

    private function process_controllers() {


        $importbcprogress = new \core\progress\display();
        $importbcprogress->set_display_names();
        $importbc = \backup_controller::load_controller($this->get('importbackupid'));
        $importbc->set_progress($importbcprogress);
        $importbc->execute_plan();
        $results = $importbc->get_results();
        $importbc->destroy();

        $importrcprogress = new \core\progress\display();
        $importrcprogress->set_display_names();
        $importrc = \restore_controller::load_controller($this->get('importrestoreid'));
        $importrc->set_progress($importrcprogress);
        $importrc->execute_precheck();
        $importrc->execute_plan();
        $results = $importrc->get_results();
        $importrc->destroy();
    }

    private function process_enrolment() {
        $pluginname = 'gudatabase';

        // Get the enrol plugin.
        $plugin = enrol_get_plugin($pluginname);

        $fields = [
            'status' => $this->get('gudbstatus'), // status - enable existing enrolments. bigint10
            'customint3' => $this->get('gudbsettingscodes'), // settingscodes - enable codes in course settings.  bigint10
            'customint6' => $this->get('gudballowhidden'), // allowhidden allow hidden course customint6
            'customtext1' => $this->get('gudbcodelist'), // codelist more codes customtext1 text
        ];

        // Enable this enrol plugin for the course.
        $plugin->add_instance($this->get_course(), $fields);
    }

    private function get_copydata() {
        return (object)[
            'courseid' =>  $this->get('templatecourseid'),
            'fullname' => $this->get('fullname'),
            'shortname' => $this->get('shortname'),
            'category' => $this->get('category'),
            'visible' => $this->get('visible'),
            'startdate' => $this->get('startdate'),
            'enddate' => $this->get('enddate'),
            'idnumber' => $this->get('idnumber'),
            'userdata' => 0,
        ];

    }

    private function get_course() {
        $time = time();

        return (object)[
            'id' => $this->get('createdcourseid'),
            'fullname' => $this->get('fullname'),
            'shortname' => $this->get('shortname'),
            'category' => $this->get('category'),
            'summary' => $this->get('summary'),
            'summaryformat' => $this->get('summaryformat'),
            'visible' => $this->get('visible'),
            'startdate' => $this->get('startdate'),
            'enddate' => $this->get('enddate'),
            'idnumber' => $this->get('idnumber'),
            'sortorder' => 0,
            'timecreated'  => $time,
            'timemodified' => $time
        ];
    }

    /**
     * Creates a skeleton record within the database using the passed parameters
     * and returns the new course id.
     *
     * @global moodle_database $DB
     * @return int The new course id
     */
    private function create_new_course() {
        global $DB;
        $category = $DB->get_record('course_categories', ['id' => $this->get('category')], '*', MUST_EXIST);
        $courseid = $DB->insert_record('course', $this->get_course());
        $category->coursecount++;
        $DB->update_record('course_categories', $category);
        return $courseid;
    }
}
