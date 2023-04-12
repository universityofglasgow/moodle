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
 * Class for backupcontroller persistence.
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_template\models;

use local_template\collections\persistentcollection;
use local_template\local\notifications;
use local_template\controllers;
use core_user;
use backup;

global $CFG;
require_once $CFG->dirroot . '/backup/util/includes/restore_includes.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Class for loading/storing template backupcontrollers from the DB.
 *
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class backupcontroller extends \core\persistent {

    const TABLE = 'backup_controllers';

    public static $backupcontrollersperpage = 20;

    private $template;
    private $course;
    private $connect_role;
    private $file;


    /**
     * @var notifications notifications
     */
    public $notifications = null;

    private $createuser = null;


    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    public static function define_properties() {
        return [
            'backupid' => [
                'type' => PARAM_RAW,
                'description' => 'Unique id of the backup',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
            ],
            'operation' => [
                'type' => PARAM_RAW,
                'description' => 'Type of operation (backup/restore)',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'choices' => [backup::OPERATION_BACKUP, backup::OPERATION_RESTORE],
                'default' => backup::OPERATION_BACKUP,
            ],
            'type' => [
                'type' => PARAM_RAW,
                'description' => 'Type of the backup (activity/section/course)',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'choices' => [backup::TYPE_1ACTIVITY, backup::TYPE_1SECTION, backup::TYPE_1COURSE],
                'default' => backup::TYPE_1COURSE,
            ],
            'itemid' => [
                'type' => PARAM_INT,
                'description' => 'Foreign key id of the module/section/activity being backup',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
            ],
            'format' => [
                'type' => PARAM_RAW,
                'description' => 'format of the backup (moodle/imscc...)',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'choices' => [backup::FORMAT_MOODLE, backup::FORMAT_MOODLE1, backup::FORMAT_IMSCC1, backup::FORMAT_IMSCC11, backup::FORMAT_UNKNOWN],
                'default' => backup::FORMAT_UNKNOWN,
            ],
            'interactive' => [
                'type' => PARAM_INT,
                'description' => 'is the backup interactive (1-yes/0-no)',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'choices' => [backup::INTERACTIVE_YES, backup::INTERACTIVE_NO],
                'default' => backup::INTERACTIVE_NO,
            ],
            'purpose' => [
                'type' => PARAM_INT,
                'description' => 'purpose (target) of the backup (general, import, hub...)',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'choices' => [backup::MODE_GENERAL, backup::MODE_IMPORT, backup::MODE_HUB, backup::MODE_SAMESITE, backup::MODE_AUTOMATED, backup::MODE_CONVERTED, backup::MODE_ASYNC, backup::MODE_COPY],
                'default' => backup::MODE_GENERAL,
            ],
            'userid' => [
                'type' => PARAM_INT,
                'description' => 'user that owns/performs the backup',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'default' => function () {
                    global $USER;
                    return $USER->id;
                },
            ],
            'status' => [
                'type' => PARAM_INT,
                'description' => 'current status of the backup (configured, ui, running...)',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'choices' => [backup::STATUS_CREATED, backup::STATUS_REQUIRE_CONV, backup::STATUS_PLANNED, backup::STATUS_CONFIGURED, backup::STATUS_SETTING_UI,
                              backup::STATUS_NEED_PRECHECK, backup::STATUS_AWAITING, backup::STATUS_EXECUTING, backup::STATUS_FINISHED_ERR, backup::STATUS_FINISHED_OK],
                'default' => backup::STATUS_CREATED,
            ],
            'execution' => [
                'type' => PARAM_INT,
                'description' => 'type of execution (immediate/delayed)',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'choices' => [backup::EXECUTION_INMEDIATE, backup::EXECUTION_DELAYED],
                'default' => backup::EXECUTION_INMEDIATE,
            ],
            'executiontime' => [
                'type' => PARAM_INT,
                'description' => 'epoch secs when the backup should be executed (for delayed backups only)',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,
            ],
            'checksum' => [
                'type' => PARAM_RAW,
                'description' => 'checksum of the backup_controller object',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'default' => '',
            ],
            'timemodified' => [
                'type' => PARAM_INT,
                'description' => 'last time the controller was modified',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,
            ],
            'progress' => [
                'type' => PARAM_FLOAT,
                'description' => 'The backup or restore progress as a floating point number',
                'optional' => false,
                'null' => NULL_NOT_ALLOWED,
                'default' => 0,
            ],
            'controller' => [
                'type' => PARAM_RAW,
                'description' => 'serialised backup_controller object',
                'null' => NULL_NOT_ALLOWED,
                'default' => ''
            ],
            // base64_encode(serialize($controller));
            // $controller = unserialize(base64_decode($controllerrec->controller));
            // check checksum
        ];
    }

    public static function collection($parentid = 0, $view = 'table', $displayheadings = true, $params = null, $sort = 'timemodified', $order = 'DESC') {
        $backupcontrollerspage = template_get_paging('backupcontrollerspage', $parentid);

        $backupcontrollersperpage = self::$backupcontrollersperpage;
        if (!empty(get_config('local_template', 'backupcontrollersperpage'))) {
            $backupcontrollersperpage = get_config('local_template', 'backupcontrollersperpage');
        }

        $select = '';

        global $DB;
        $ids = [];

        $conditions = null;
        if (!empty($parentid)) {
            $conditions = ['id' => $parentid];
        }
        $templates = $DB->get_records('local_template', $conditions, null, 'id, copybackupid, copyrestoreid, importbackupid, importrestoreid');
        foreach ($templates as $template) {
            if (!empty($template->copybackupid)) $ids[] = $template->copybackupid;
            if (!empty($template->copyrestoreid)) $ids[] = $template->copyrestoreid;
            if (!empty($template->importbackupid)) $ids[] = $template->importbackupid;
            if (!empty($template->importrestoreid)) $ids[] = $template->importrestoreid;
        }

        if (count($ids) > 0) {
            list($templateselect, $templateparams) = $DB->get_in_or_equal($ids);
            if (!is_array($params)) {
                $params = [];
            }
            $params = array_merge($params, $templateparams);
            $select = 'backupid ' . $templateselect;
        } else {
            if (!empty($parentid)) {
                $select = '1 = 0';
            }

        }

        if (!is_template_admin()) {
            global $USER;
            // Only show records for current user, and not hidden records.
            $params['userid'] = $USER->id;
        }

        // 'local_template\\models\\backupcontroller'
        return new persistentcollection(get_called_class(), $parentid, $view, $displayheadings, $select, $params, $sort, $order, $backupcontrollerspage, $backupcontrollersperpage);
    }

    public static function title() {
        return get_string('backupcontrollers', 'local_template');
    }

    public static function collection_properties($parentid = 0) {
        global $OUTPUT;
        $properties = [
            'timemodified' => [
                'label' => get_string('timemodified', 'local_template'),
                'alignment' => 'left',
            ],
            'operation' => [
                'label' => get_string('operation', 'local_template'),
                'alignment' => 'left',
            ],
            'type' => [
                'label' => get_string('type', 'local_template'),
                'alignment' => 'left',
            ],
            'purpose' => [
                'label' => get_string('purpose', 'local_template'),
                'alignment' => 'left',
            ],
            'status' => [
                'label' => get_string('status', 'local_template'),
                'alignment' => 'left',
            ],
            'progress' => [
                'label' => get_string('progress', 'local_template'),
                'alignment' => 'left',
            ],
        ];

        if (is_template_admin()) {
            $properties['userid'] = [
                'label' => get_string('username', 'local_template'),
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

        $this->notifications = new notifications();

        if (!empty($id)) {
            $this->get_template();
            $this->get_course();
        }
    }

    protected function get_timemodified() {
        $timemodified = userdate($this->raw_get('timemodified'), get_string('strftimedatetimeshort', 'core_langconfig'));
        if (empty($timemodified)) {
            $timemodified = get_string('missingbackupcontrollerdate','local_template');
        }
        return $timemodified;
    }

    public function get_name($path = null) {
        $operation = $this->get_operation();
        $type = $this->get_status();
        $itemid = $this->raw_get('itemid');
        $status = $this->get_status();
        $name = $operation . ' of ' . $type . '(' . $itemid . ') - ' . $status;
        if (!empty($path)) {
            global $OUTPUT;
            $name .= $OUTPUT->spacer() . template_icon_link('edit', $path, ['action' => 'editbackupcontroller', 'backupcontrollerid' => $this->raw_get('id')]);
        }

        return $name;
    }

    protected function get_operation() {
        $operation = self::get_operation_string($this->raw_get('operation'));
        return format_string($operation);
    }

    public static function get_operation_choices() {
        return [
            backup::OPERATION_BACKUP => get_string('backupcontrolleroperationbackup', 'local_template'),
            backup::OPERATION_RESTORE => get_string('backupcontrolleroperationrestore', 'local_template'),
        ];
    }

    public static function get_operation_string($operation) {
        $choices = self::get_operation_choices();
        return $choices[$operation];
    }

    protected function get_type() {
        $type = self::get_type_string($this->raw_get('type'));
        return format_string($type);
    }

    public static function get_type_choices() {
        return [
            '' => get_string('backupcontrollertypeunknown', 'local_template'),
            backup::TYPE_1ACTIVITY => get_string('backupcontrollertypeactivity', 'local_template'),
            backup::TYPE_1SECTION => get_string('backupcontrollertypescetion', 'local_template'),
            backup::TYPE_1COURSE => get_string('backupcontrollertypecourse', 'local_template'),
        ];
    }

    public static function get_type_string($type) {
        $choices = self::get_type_choices();
        return $choices[$type];
    }

    protected function get_purpose() {
        $purpose = self::get_purpose_string($this->raw_get('purpose'));
        return format_string($purpose);
    }

    public static function get_purpose_choices() {
        return [
            backup::MODE_GENERAL => get_string('backupcontrollerpurposegeneral', 'local_template'),
            backup::MODE_IMPORT => get_string('backupcontrollerpurposeimport', 'local_template'),
            backup::MODE_HUB => get_string('backupcontrollerpurposehub', 'local_template'),
            backup::MODE_SAMESITE => get_string('backupcontrollerpurposesamesite', 'local_template'),
            backup::MODE_AUTOMATED => get_string('backupcontrollerpurposeautomated', 'local_template'),
            backup::MODE_CONVERTED => get_string('backupcontrollerpurposeconverted', 'local_template'),
            backup::MODE_ASYNC => get_string('backupcontrollerpurposeasync', 'local_template'),
            backup::MODE_COPY => get_string('backupcontrollerpurposecopy', 'local_template'),

        ];
    }

    public static function get_purpose_string($purpose) {
        $choices = self::get_purpose_choices();
        return $choices[$purpose];
    }

    protected function get_format() {
        $format = self::get_format_string($this->raw_get('format'));
        return format_string($format);
    }

    public static function get_format_choices() {
        return [

            backup::FORMAT_MOODLE => get_string('backupformatmoodle2', 'backup'),
            backup::FORMAT_MOODLE1 => get_string('backupformatmoodle1', 'backup'),
            backup::FORMAT_IMSCC1 => get_string('backupformatimscc1', 'backup'),
            backup::FORMAT_IMSCC11 => get_string('backupformatimscc11', 'backup'),
            backup::FORMAT_UNKNOWN => get_string('backupformatunknown', 'backup'),
        ];
    }

    public static function get_format_string($format) {
        $choices = self::get_format_choices();
        return $choices[$format];
    }

    public static function get_interactive_choices() {
        return [
            backup::INTERACTIVE_NO => get_string('no'),
            backup::INTERACTIVE_YES => get_string('yes'),
        ];
    }

    protected function get_status() {
        $status = self::get_status_string($this->raw_get('status'));
        return format_string($status);
    }

    public static function get_status_choices() {
        return [
            backup::STATUS_CREATED => get_string('backupcontrollerstatuscreated', 'local_template'),
            backup::STATUS_REQUIRE_CONV => get_string('backupcontrollerstatusrequireconv', 'local_template'),
            backup::STATUS_PLANNED => get_string('backupcontrollerstatusplanned', 'local_template'),
            backup::STATUS_CONFIGURED => get_string('backupcontrollerstatusconfigured', 'local_template'),
            backup::STATUS_SETTING_UI => get_string('backupcontrollerstatussettingui', 'local_template'),
            backup::STATUS_NEED_PRECHECK => get_string('backupcontrollerstatusneedprecheck', 'local_template'),
            backup::STATUS_AWAITING => get_string('backupcontrollerstatusawaiting', 'local_template'),
            backup::STATUS_EXECUTING => get_string('backupcontrollerstatusexecuting', 'local_template'),
            backup::STATUS_FINISHED_ERR => get_string('backupcontrollerstatusfinishederr', 'local_template'),
            backup::STATUS_FINISHED_OK => get_string('backupcontrollerstatusfinishedok', 'local_template'),
        ];
    }

    public static function get_status_string($status) {
        $choices = self::get_status_choices();
        return $choices[$status];
    }

    public static function get_execution_choices() {
        return [
            backup::EXECUTION_INMEDIATE => get_string('backupcontrollerexecutionimmediate', 'local_template'),
            backup::EXECUTION_DELAYED => get_string('backupcontrollerexecutiondelayed', 'local_template'),
        ];
    }

    protected function get_progress() {
        return $this->progress();
    }

    protected function get_exportfileid() {
        $exportfile = '';
        $file = $this->get_file();
        if (empty($file)) {
            $exportfile = get_string('missingfilename','local_template');
        } else {
            $exportfile = template_icon_link('download', $this->get_file_url());
        }
        return $exportfile;
    }

    protected function get_userid() {
        if ($this->createuser) {
            return fullname($this->createuser);
        } else {
            $this->createuser = $this->read_user($this->raw_get('userid'));
            return fullname($this->createuser);
        }
    }

    public static function add_new_icon($parentid = 0) {
        return template_icon_link('add', controllers\template::path(), ['id' => '0', 'action' => 'createbackupcontroller', 'templateid' => $parentid]);
    }

    public static function add_new($parentid) {
        global $OUTPUT;
        $label = get_string('addbackupcontroller', 'local_template');
        return \html_writer::link(new \moodle_url(controllers\backupcontroller::path(), ['id' => '0', 'action' => 'createbackupcontroller', 'templateid' => $parentid]),
            $label . ' ' . $OUTPUT->pix_icon('t/add', $label), ['title' => $label]);
        if ($button) {
            $label = get_string('addbackupcontroller', 'local_template');
            return \html_writer::link(new \moodle_url(controllers\backupcontroller::path(), ['id' => '0', 'action' => 'createbackupcontroller', 'templateid' => $parentid]),
                $label . ' ' . $OUTPUT->pix_icon('t/add', $label), ['title' => $label]);
            return $OUTPUT->single_button(
                new \moodle_url(controllers\backupcontrollergrade::path(), ['id' => '0', 'action' => 'createbackupcontroller', 'templateid' => $parentid]),
                get_string('addbackupcontroller', 'local_template')
            );
        } else {
            return $OUTPUT->spacer() . get_string('addbackupcontroller', 'local_template') . self::add_new_icon($parentid);
        }
    }

    public static function no_records($parentid) {
        global $OUTPUT;
        return $OUTPUT->notification(get_string('nobackupcontrollersdefined', 'local_template') . $OUTPUT->spacer() . self::add_new($parentid));
    }


    public function get_actions($count = 0) {
        $path = controllers\backupcontroller::path();
        $actions = '';

        // preview, add, edit, hide, show, moveup, movedown, delete
        $actions .= template_icon_link('preview', $path, ['action' => 'viewbackupcontroller', 'backupcontrollerid' => $this->raw_get('id')]);

        $actions .= template_icon_link('edit', $path, ['action' => 'editbackupcontroller', 'backupcontrollerid' => $this->raw_get('id')]);

        $actions .= template_icon_link('delete', $path, ['action' => 'deletebackupcontroller', 'backupcontrollerid' => $this->raw_get('id'), 'sesskey' => sesskey()]);

        $actions .= template_icon_link('go', $path, ['action' => 'runbackupcontroller', 'backupcontrollerid' => $this->raw_get('id')]);

        return $actions;
    }

    public static function get_context() {
        return \context_system::instance();
    }

    public static function get_exportfileoptions() {
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

    public function has_errors() {
        return $this->notifications->has_errors();
    }

    public function get_notifications_string() {
        return (string)($this->notifications);
    }



    public function get_identifier() {
        return $this->raw_get('backupid');
    }

    public function get_helpers() {
        if ($this->helpers) {
            return $this->helpers;
        } else {
            $this->helpers = $this->read_helpers();
            return $this->helpers;
        }
    }

    /**
     *
     * populates $this->>helpers to prevent multiple reads of the same data for backupcontrollerrows
     *
     * ```php
     *     $helpers = [
     *         'connect_course' => [
     *             '178' => [],
     *             '1104' => [],
     *             '2209' => []
     *         ]
     *     ];
     * ```
     * @return array $helpers helper collection read from database.
     */
    private function read_helpers() {
        global $DB;

        $helpers = [];

        $courseid = $this->raw_get('courseid');

        // connect_course. Multiple records are possible as courseid (mid) is not solely a candidate key for this table.
        $helpers['connect_course']  = $DB->get_records('connect_course', ['mid' => $courseid]);
        if (empty($helpers['connect_course'])) {
            $this->notifications->add('Could not retrieve connect course record for this course.', notifications::WARNING);
        }

        // connect_course_exts. courseid (coursemid) is a candidate key for this table.
        $helpers['connect_course_exts']  = $DB->get_record('connect_course_exts', ['coursemid' => $courseid]);
        if (empty($helpers['connect_course_exts'])) {
            $this->notifications->add('Could not retrieve connect course extension record for this course.', notifications::WARNING);
        }

        // course_sections
        if (!empty($this->raw_get('validatecoursesections'))) {
            $helpers['course_sections'] = $DB->get_records('course_sections', ['course' => $courseid]);
        }

        // Initiate modulecodes.
        $helpers['modulecodes'] = [];

        // Initiate course code validation.
        $helpers['coursecode_validation'] = [
            // e.g. 'COMP5190' => true,
        ];

        // Initiate year validation.
        $helpers['year_validation'] = [
            // e.g. '2020' => true,
        ];

        // Initiate connect_campus.
        $helpers['connect_campus'] = [
            'campusid' => [],
            'campuscode' => [],
        ];

        return $helpers;
    }

    protected function validatecourseid() {

        // Course courseid must be present, and be a course.
        if (empty($this->raw_get('courseid'))) {
            $this->notifications->add('backupcontroller is missing course ID.', notifications::ERROR);
            return false;
        } else {
            global $DB;
            $course = $DB->get_record('course', ['id' => $this->raw_get('courseid')]);
            if (empty($course)) {
                $this->notifications->add('backupcontroller course does not exist.', notifications::ERROR);
                return false;
            }
        }

        return true;
    }

    public function get_template() {
        if ($this->template) {
            return $this->template;
        } else {
            $this->template = $this->read_template();
            return $this->template;
        }
    }

    private function read_template() {
        global $DB;
        $backupid = $this->get('id');
        $where = 'copybackupid = :copybackupid OR copyrestoreid = :copyrestoreid OR importbackupid = :importbackupid OR importrestoreid = :importrestoreid';
        $params = [
            'copybackupid' => $backupid,
            'copyrestoreid' => $backupid,
            'importbackupid' => $backupid,
            'importrestoreid' => $backupid
        ];
        $templates = $DB->get_records_select('local_template', $where , $params);
        if (count($templates) == 0) {
            // throw new \coding_exception('Backup controller has no template');
        }
        if (count($templates) > 1) {
            // throw new \coding_exception('Backup controller has too many templates');
        }

        if (!empty($templateid)) {
            $template = new template($templateid, $templates[0]);
            return $template;
        }
        return null;
    }

    public function get_course() {
        if ($this->course) {
            return $this->course;
        } else {
            $this->course = $this->read_course();
            return $this->course;
        }
    }

    private function read_course() {
        $itemid = $this->raw_get('itemid');
        if (!empty($courseid)) {
            global $DB;
            $course = $DB->get_record('course', ['id' => $itemid]);
            return $course;
        }
    }

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
            return get_string('exportfilemissing','local_template');
        }

        $url = $this->get_file_url();
        if (empty($url)) {
            return get_string('exportfilemissing','local_template');
        }

        $filename = $file->get_filename();
        return \html_writer::link($url, $filename, ['title' => $filename]);
    }

    /**
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_filename() {
        $file = $this->get_file();
        if (!empty($file)) {
            return $file->get_filename();
        } else {
            return get_string('exportfilemissing', 'local_template');
        }
    }


    /**
     * @return \stored_file
     * @throws \coding_exception
     */
    public function get_file() {
        if ($this->file) {
            return $this->file;
        } else {
            $this->file = $this->read_file();
            return $this->file;
        }
    }

    /**
     * @return bool|\stored_file
     * @throws \coding_exception
     */
    private function read_file() {
        $id = $this->raw_get('exportfileid');
        if (!empty($id)) {
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($id);
            return $file;
        }
        return false;
    }

    private function deletefile() {
        if (!empty($this->raw_get('exportfileid'))) {
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($this->raw_get('exportfileid'));
            if (!empty($file)) {
                if (!$file->delete()) {
                    return false;
                }
            }
            $this->set('exportfileid', null);
            $this->save();
        }
        return true;
    }

    private function read_user() {
        $userid = $this->raw_get('userid');
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
        return userdate($this->raw_get('timecreated'), get_string('strftimedatetimeshort', 'langconfig'));
    }

    public function progress() {

        $progress = $this->raw_get('progress');

        $content = '';
        $content .= \html_writer::start_tag('div', ['class' => 'container']);
        $content .= \html_writer::start_tag('div', ['class' => 'progress']);
        $content .= $this->progresslevel($progress, 'info');
        //$content .= $this->progresslevel($this->raw_get('recordsinfo'), $numrecords, 'info');
        //$content .= $this->progresslevel($this->raw_get('recordssuccess'), $numrecords, 'success');
        //$content .= $this->progresslevel($this->raw_get('recordswarning'), $numrecords, 'warning');
        //$content .= $this->progresslevel($this->raw_get('recordserror'), $numrecords, 'danger');
        $content .= \html_writer::end_tag('div');

        $content .= \html_writer::end_tag('div');
        return $content;
    }

    private static function progresslevel($progress, $class) {

        $percentage = round($progress * 100);

        return \html_writer::tag('div', '<span>' . $percentage . '</span>', [
            'class' => 'progress-bar bg-' . $class . ' position-relative',
            'role' => 'progressbar',
            'style' => 'width:' . $percentage .'%',
        ]);
    }
}