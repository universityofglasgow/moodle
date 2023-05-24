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
 * Local template
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_template\forms;
use core\notification;
use local_template\models;
use local_template\controllers;
use local_template\utils;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/lib/classes/form/persistent.php';

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

/**
 * Class template
 *
 * @copyright  2023 David Aylmer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template extends \core\form\persistent {

    /** @var string Persistent class name. */
    protected static $persistentclass = 'local_template\\models\\template';

    /** @var array Fields to remove from the persistent validation. */
    protected static $foreignfields = ['action'];


    public const STEPPER_HEADER = 1;
    public const STEPPER_SELECTTEMPLATE = 2;
    public const STEPPER_COURSE_START = 3;
    public const STEPPER_COURSE_END = 4;
    public const STEPPER_DESCRIPTION_START = 5;
    public const STEPPER_DESCRIPTION_END = 6;
    public const STEPPER_ENROLMENT_START = 7;
    public const STEPPER_ENROLMENT_END = 8;
    public const STEPPER_IMPORT_START = 9;
    public const STEPPER_IMPORT_END = 10;
    public const STEPPER_PROCESS_START = 11;
    public const STEPPER_PROCESS_END = 12;
    public const STEPPER_FOOTER = 13;
    public const STEPPER_JAVASCRIPT = 14;

    /**
     * Define the form.
     */
    public function definition() {
        global $CFG, $OUTPUT, $USER;

        $mform = $this->_form;

        global $PAGE;
        $renderer = $PAGE->get_renderer('local_template');

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'action', $this->_customdata['action']);
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->setConstant('action', $this->_customdata['action']);

        $record = $this->get_persistent()->to_record();
        if (utils::is_admin()) {
            if (!empty($record->id)) {
                //notification::success('Edit Mode');
            } else {
                //notification::success('Add Mode');
            }
        }

        //$course = $this->_customdata['course'];
        //$coursecontext = \context_course::instance($course->id);
        $courseconfig = get_config('moodlecourse');
        //$returnto = $this->_customdata['returnto'];
        //$returnurl = $this->_customdata['returnurl'];

        //if (empty($course->category)) {
        //    $course->category = $course->categoryid;
        //}

        // Course ID.
        //$mform->addElement('hidden', 'courseid', $course->id);
        //$mform->setType('courseid', PARAM_INT);

        if (empty($this->_customdata['admin'])) {

            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_HEADER, []));

            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_SELECTTEMPLATE, []));

            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_COURSE_START, []));
        } else {
            $mform->addElement('header', 'coursedetails', get_string('coursedetails', 'local_template'));
        }

        global $DB;

        // Get plugin config setting list of template course categories.
        $templatecategorysettings = get_config('local_template', 'categories');

        $categories = [];
        if (!empty($templatecategorysettings)) {
            // Reduce set of template categories based on user capability in each category.
            $templatecategories = explode(',', $templatecategorysettings);
            if (!empty($templatecategories)) {
                foreach ($templatecategories as $categoryid) {
                    if (has_capability('local/template:usetemplate', \context_coursecat::instance($categoryid))) {
                        $categories[] = $categoryid;
                    }
                }
            }
        }

        if (empty($categories)) {
            $templatecourses = [0 => ''];
        } else {
            list($insql, $params) = $DB->get_in_or_equal($categories);
            $templatecourses = $DB->get_records_sql_menu('SELECT c.id, c.fullname FROM {course} c WHERE c.category ' . $insql, $params);
            $templatecourses = [0 => ''] + $templatecourses;
        }
        $mform->addElement('autocomplete', 'templatecourseid', get_string('templatecourse', 'local_template'), $templatecourses);
        $mform->addRule('templatecourseid', null, 'required', null, 'client');
        $mform->setDefault('templatecourseid', 0);
        //$mform->addHelpButton('category', 'coursecategory');


        // Course category.
        $importcategories = \core_course_category::make_categories_list(\core_course\management\helper::get_course_copy_capabilities());

        //$importcategories = get_config('local_template', 'categories');


        // Return to type.
        //$mform->addElement('hidden', 'returnto', null);
        //$mform->setType('returnto', PARAM_ALPHANUM);
        //$mform->setConstant('returnto', $returnto);

        // Notifications of current copies.
        //$copies = \backup\util\helper\copy_helper::get_copies($USER->id, $course->id);
        //if (!empty($copies)) {
        //    $progresslink = new \moodle_url('/backup/copyprogress.php?', array('id' => $course->id));
        //    $notificationmsg = get_string('copiesinprogress', 'backup', $progresslink->out());
        //    $notification = $OUTPUT->notification($notificationmsg, 'notifymessage');
        //    $mform->addElement('html', $notification);
        //}

        // Return to URL.
        //$mform->addElement('hidden', 'returnurl', null);
        //$mform->setType('returnurl', PARAM_LOCALURL);
        //$mform->setConstant('returnurl', $returnurl);

        // Form heading.
        //$mform->addElement('html', \html_writer::div(get_string('copycoursedesc', 'backup'), 'form-description mb-3'));

        // Course fullname.
        $mform->addElement('text', 'fullname', get_string('fullnamecourse'), 'maxlength="254" size="50"');
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_TEXT);

        // Course shortname.
        $mform->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="20"');
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_TEXT);

        $defaultcategory = [];
        $defaultcategoryid = 0;
        if (!empty($this->_customdata['category'])) {
            $defaultcategoryid = $this->_customdata['category'];
            if (isset($importcategories[$defaultcategoryid])) {
                $defaultcategory[$defaultcategoryid] = $importcategories[$defaultcategoryid];
            }
        }
        $autocomplete = $mform->addElement('autocomplete', 'category', get_string('coursecategory'), $defaultcategory + $importcategories, ['multiple' => false]);
        $mform->setDefault('category', $defaultcategoryid);
        $autocomplete->setValue($defaultcategoryid);
        $mform->addRule('category', null, 'required', null, 'client');
        $mform->addHelpButton('category', 'coursecategory');

        // Course visibility.
        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $mform->addElement('select', 'visible', get_string('coursevisibility'), $choices);
        $mform->addHelpButton('visible', 'coursevisibility');
        $mform->setDefault('visible', $courseconfig->visible);
        //if (!has_capability('moodle/course:visibility', $coursecontext)) {
        //    $mform->hardFreeze('visible');
        //    $mform->setConstant('visible', $course->visible);
        //}


        // Course start date.
        $mform->addElement('date_time_selector', 'startdate', get_string('startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $date = (new \DateTime())->setTimestamp(usergetmidnight(time()));
        $date->modify('+1 day');
        $mform->setDefault('startdate', $date->getTimestamp());

        // Course enddate.
        $mform->addElement('date_time_selector', 'enddate', get_string('enddate'), array('optional' => true));
        $mform->addHelpButton('enddate', 'enddate');

        if (!empty($CFG->enablecourserelativedates)) {
            $attributes = [
                'aria-describedby' => 'relativedatesmode_warning'
            ];
            if (!empty($course->id)) {
                $attributes['disabled'] = true;
            }
            $relativeoptions = [
                0 => get_string('no'),
                1 => get_string('yes'),
            ];
            $relativedatesmodegroup = [];
            $relativedatesmodegroup[] = $mform->createElement('select', 'relativedatesmode', get_string('relativedatesmode'),
                $relativeoptions, $attributes);
            $relativedatesmodegroup[] = $mform->createElement('html', \html_writer::span(get_string('relativedatesmode_warning'),
                '', ['id' => 'relativedatesmode_warning']));
            $mform->addGroup($relativedatesmodegroup, 'relativedatesmodegroup', get_string('relativedatesmode'), null, false);
            $mform->addHelpButton('relativedatesmodegroup', 'relativedatesmode');
        }

        // Course ID number (default to the current course ID number; blank for users who can't change ID numbers).
        $mform->addElement('text', 'idnumber', get_string('idnumbercourse'), 'maxlength="100"  size="10"');
        //$mform->setDefault('idnumber', $course->idnumber);
        $mform->addHelpButton('idnumber', 'idnumbercourse');
        $mform->setType('idnumber', PARAM_RAW);
        //if (!has_capability('moodle/course:changeidnumber', $coursecontext)) {
        //    $mform->hardFreeze('idnumber');
        //    $mform->setConstant('idnumber', '');
        //}



        // /*
        // Keep source course user data.
        //$mform->addElement('select', 'userdata', get_string('userdata', 'backup'),
        //    [0 => get_string('no'), 1 => get_string('yes')]);
        //$mform->setDefault('userdata', 0);
        //$mform->addHelpButton('userdata', 'userdata', 'backup');

        //$requiredcapabilities = array(
        //    'moodle/restore:createuser', 'moodle/backup:userinfo', 'moodle/restore:userinfo'
        //);
        //if (!has_all_capabilities($requiredcapabilities, $coursecontext)) {
        //    $mform->hardFreeze('userdata');
        //    $mform->setConstant('userdata', 0);
        //}
        // */

        // Keep manual enrolments.
        // Only get roles actually used in this course.
        //$roles = role_fix_names(get_roles_used_in_context($coursecontext, false), $coursecontext);

        // Only add the option if there are roles in this course.
        //if (!empty($roles) && has_capability('moodle/restore:createuser', $coursecontext)) {
        //    $rolearray = array();
        //    foreach ($roles as $role) {
        //        $roleid = 'role_' . $role->id;
        //        $rolearray[] = $mform->createElement('advcheckbox', $roleid,
        //            $role->localname, '', array('group' => 2), array(0, $role->id));
        //    }
        //
        //    $mform->addGroup($rolearray, 'rolearray', get_string('keptroles', 'backup'), ' ', false);
        //    $mform->addHelpButton('rolearray', 'keptroles', 'backup');
        //    $this->add_checkbox_controller(2);
        //}


        /*
        // TODO: Prepare course and the editor
        // Prepare course and the editor.
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true);
        $overviewfilesoptions = course_overviewfiles_options($course);
        if (!empty($course)) {
            // Add context for editor.
            $editoroptions['context'] = $coursecontext;
            $editoroptions['subdirs'] = file_area_contains_subdirs($coursecontext, 'course', 'summary', 0);
            $course = file_prepare_standard_editor($course, 'summary', $editoroptions, $coursecontext, 'course', 'summary', 0);
            if ($overviewfilesoptions) {
                file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, $coursecontext, 'course', 'overviewfiles', 0);
            }

            // Inject current aliases.
            $aliases = $DB->get_records('role_names', array('contextid'=>$coursecontext->id));
            foreach($aliases as $alias) {
                $course->{'role_'.$alias->roleid} = $alias->name;
            }

            // Populate course tags.
            $course->tags = core_tag_tag::get_item_tags_array('core', 'course', $course->id);

        } else {
            // Editor should respect category context if course context is not set.
            $editoroptions['context'] = $catcontext;
            $editoroptions['subdirs'] = 0;
            $course = file_prepare_standard_editor($course, 'summary', $editoroptions, null, 'course', 'summary', null);
            if ($overviewfilesoptions) {
                file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, null, 'course', 'overviewfiles', 0);
            }
        }
        */

        if (empty($this->_customdata['admin'])) {
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_COURSE_END, []));

            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_DESCRIPTION_START, []));
        } else {
            $mform->addElement('header', 'description', get_string('description', 'local_template'));
        }



        // TODO: summary_editor
        $mform->addElement('editor', 'summary_editor', get_string('summary'), ['rows' => 6, 'cols' => 100], ['autosave' => false]);
        $mform->addHelpButton('summary_editor', 'coursesummary');
        $mform->setType('summary_editor', PARAM_RAW);
        // $mform->addRule('description', get_string('required'), 'required', null, 'client');


        // TODO: overviewfiles_filemanager
        $course = null;
        if ($overviewfilesoptions = course_overviewfiles_options($course)) {
            $mform->addElement('filemanager', 'overviewfiles_filemanager', get_string('courseoverviewfiles'), null, $overviewfilesoptions);
            $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
        }

        if (empty($this->_customdata['admin'])) {
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_DESCRIPTION_END, []));
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_ENROLMENT_START, []));
        } else {
            $mform->addElement('header', 'enrolment', get_string('enrolment', 'local_template'));
        }

        // Boolean flag for whether a gudatabase enrolment method should be added to the course.
        $mform->addElement('advcheckbox', 'gudbenrolment', get_string('gudbenrolment', 'local_template'), get_string('gudbaddenrolment', 'local_template'), null, [0, 1]);
        $mform->addHelpButton('gudbenrolment', 'gudbaddenrolment', 'local_template');


        // TODO: include enrol
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
            ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'gudbstatus', get_string('status', 'enrol_gudatabase'), $options);
        $mform->addHelpButton('gudbstatus', 'status', 'enrol_gudatabase');
        //$mform->setDefault('gudbstatus', $this->get_config('status'));

        $yesno = array(
            0 => get_string('no'),
            1 => get_string('yes'),
        );
        $mform->addElement('select', 'gudbsettingscodes', get_string('settingscodes', 'enrol_gudatabase'), $yesno);
        $mform->addHelpButton('gudbsettingscodes', 'settingscodes', 'enrol_gudatabase');
        $mform->setDefault('gudbsettingscodes', 0);

        $mform->addElement('select', 'gudballowhidden', get_string('allowhidden', 'enrol_gudatabase'), $yesno);
        $mform->addHelpButton('gudballowhidden', 'allowhidden', 'enrol_gudatabase');
        $mform->setDefault('gudballowhidden', 0);



        /*

        // TODO: include enrol/gudatabase/lib.php (edit_instance_form), get_coursedescriptions
        // Automatic enrolment (codes) settings.
        $mform->addElement('header', 'codesettings', get_string('codesettings', 'enrol_gudatabase'));

        $codes = $this->get_codes($course, $instance);
        if (empty($instance->customint3)) {
            $instance->customint3 = 0;
        }

        $mform->addElement('html', $output->print_codes($course->id, $codes, $instance->customint3, $this->enrolment_possible($course, $instance)));
*/


        $mform->addElement('textarea', 'gudbcodelist', get_string('codelist', 'enrol_gudatabase'),
            'rows="15" cols="25" style="height: auto; width:auto;"');
        $mform->addHelpButton('gudbcodelist', 'codelist', 'enrol_gudatabase');
        $mform->setType('gudbcodelist', PARAM_TEXT);

/*
        // Automatic groups settings.
        $mform->addElement('header', 'groupsettings', get_string('groupsettings', 'enrol_gudatabase'));

        if ($coursedescriptions) {
            $mform->addElement('html', '<div class="alert alert-info">' .
                get_string('groupsinstruction', 'enrol_gudatabase') . '</div>');
        } else {
            $mform->addElement('html', '<div class="alert alert-warning">' .
                get_string('nolegacycodes', 'enrol_gudatabase') . '</div>');
        }

        if ($coursedescriptions) {
            $mform->addElement('advcheckbox', 'coursegroups', get_string('coursegroups', 'enrol_gudatabase'), '');
            $mform->setDefault('coursegroups', $instance->customint2);
            $mform->addHelpButton('coursegroups', 'coursegroups', 'enrol_gudatabase');
        }

        foreach ($codeclasses as $code => $classes) {
            $description = $coursedescriptions[$code];
            $mform->addElement('html', "<h3>$code ($description)</h3>");
            foreach ($classes as $class) {
                $classnospace = str_replace(' ', '_', $class);
                $selector = "{$code}_{$classnospace}";
                $mform->addElement('advcheckbox', $selector, $class, '');
                $mform->setDefault($selector, !empty($groups[$code][$class]));
            }
        }
        */

        //echo $renderer->render_stepper(self::STEPPER_IMPORT, []);

        if (empty($this->_customdata['admin'])) {
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_ENROLMENT_END, []));
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_IMPORT_START, []));
        } else {
            $mform->addElement('header', 'import', get_string('import', 'local_template'));
        }


        list($insql, $params) = $DB->get_in_or_equal($importcategories, SQL_PARAMS_QM, 'param', false);
        $importcourses = $DB->get_records_sql_menu('SELECT c.id, c.fullname FROM {course} c WHERE c.category ' . $insql, $params);
        $importcourses = [0 => ''] + $importcourses;

        $mform->addElement('autocomplete', 'importcourseid', get_string('importcourse', 'local_template'), $importcourses);
        //$mform->addRule('importcourseid', null, 'required', null, 'client');
        //$mform->addHelpButton('category', 'coursecategory');
        $mform->setDefault('importcourseid', 0);


        //$buttonarray = array();
        // $buttonarray[] = $mform->createElement('submit', 'submitreturn', get_string('copyreturn', 'backup'));
        //$buttonarray[] = $mform->createElement('submit', 'submitdisplay', get_string('copyview', 'backup'));
        //$buttonarray[] = $mform->createElement('cancel');
        //$mform->addGroup($buttonarray, 'buttonar', '', ' ', false);



        $mform->addElement('html', $renderer->render_stepper(self::STEPPER_IMPORT_END, []));
        $mform->addElement('html', $renderer->render_stepper(self::STEPPER_PROCESS_START, []));

        $mform->addElement('html', '<br><hr>');

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'createandredirect', get_string('createandredirect', 'local_template'));
        $buttonarray[] = &$mform->createElement('submit', 'createcourse', get_string('createcourse', 'local_template'));
        $buttonarray[] = &$mform->createElement('submit', 'savetemplate', get_string('savetemplate', 'local_template'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        //$this->add_action_buttons(true, 'Create Course');

        if (empty($this->_customdata['admin'])) {


            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_PROCESS_END, []));
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_FOOTER, []));
        } else {
            $mform->addElement('header', 'createdcourse', get_string('createdcourse', 'local_template'));
            $createdcoursename = get_string('missingcreatedcourse', 'local_template');
            if (!empty($record->createdcourseid)) {
                $createdcoursename = $DB->get_field('course', 'fullname', ['id' => $record->createdcourseid]);
            }
            $mform->addElement('static', 'createdcoursename', get_string('createdcourse', 'local_template'), $createdcoursename);
            //$mform->addElement('autocomplete', 'createdcourseid', get_string('createdcourse', 'local_template'), $importcourses);
            //$mform->setDefault('createdcourseid', 0);

            //$mform->addElement('header', 'controllers', get_string('controllers', 'local_template'));
            //$controllers = $DB->get_records_menu('backup_controllers', null, 'timemodified DESC', 'id, backupid');

            //$mform->addElement('text', 'copybackupid', get_string('copybackup', 'local_template'), $controllers);
            //$mform->addElement('text', 'copyrestoreid', get_string('copyrestore', 'local_template'), $controllers);
            //$mform->addElement('text', 'importbackupid', get_string('importbackup', 'local_template'), $controllers);
            //$mform->addElement('text', 'importrestoreid', get_string('importrestore', 'local_template'), $controllers);

            if (!empty($record->id)) {
                $mform->addElement('header', 'backupcontrollers', get_string('backupcontrollers', 'local_template'));
                $mform->addElement('html', $OUTPUT->box(controllers\backupcontroller::renderbackupcontrollers($record->id, true)));
            }
        }

        //$mform->addElement('header', 'log', get_string('log', 'local_template'));
        //$mform->addElement('html', $OUTPUT->box(controllers\template::rendertemplates($record->id, true)));




        /*


        global $OUTPUT;

        $mform = $this->_form;

        $mform->addElement('header', 'template', get_string('template', 'local_template'));

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'action', $this->_customdata['action']);
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->setConstant('action', $this->_customdata['action']);

        $mform->addElement('text', 'name', get_string('templatename', 'local_template'), 'maxlength="64" size="64"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'templatename', 'local_template');
        $mform->addRule('name', get_string('required'), 'required', null, 'client');


        //// $mform->addElement('filepicker', 'importfile', get_string('templatefile', 'local_template'));
        //self::$persistentclass
        //$draftitemid = file_get_submitted_draft_itemid('importfile');
        //$context = \context_system::instance();
        //$maxbytes = get_user_max_upload_file_size($context, $CFG->maxbytes);
        //file_prepare_draft_area($draftitemid, $context = \context_system::instance(), 'local_template', 'import', $template->id,
        //    [
        //        'subdirs' => 0, 'maxbytes' => $maxbytes, 'areamaxbytes' => 10485760, 'maxfiles' => 1
        //    ]
        //);

        $record = $this->get_persistent()->to_record();
        $addfile = true;
        if (!empty($record->id)) {
            //notification::success('Edit Mode');
            if (is_template_admin()) {
                $addfile = true;
            } else {
                $addfile = false;
            }
        } else {
            //notification::success('Add Mode');
            $addfile = true;
        }


        if ($addfile) {
            // If in add mode, or in edit mode and user is template admin, use a filemanager for upload of a new file.

            /*
            //file_prepare_standard_filemanager(
            //    $record,
            //    'importfile',
            //    models\template::get_importfileoptions(),
            //    models\template::get_context(),
            //    models\template::TABLE,
            //    models\template::FILEAREA_IMPORT,
            //    $record->id
            //);
            //$mform->addElement('filemanager', 'importfile_filemanager', get_string('templatefile', 'local_template'), null, models\template::get_importfileoptions());

            // $mform->addRule('importfile_filemanager', get_string('required'), 'required', null, 'client');
        } else {
            // If not template admin, don't allow to replace imported file. Keep it as static.

            $mform->addElement('hidden', 'importfile', 1);
            $mform->setType('importfile', PARAM_INT);
            $mform->addElement('hidden', 'importfile_filemanager', $record->importfileid);
            $mform->setType('importfile_filemanager', PARAM_INT);

            $template = new models\template($record->id);
            $mform->addElement('static', 'importfile1', get_string('templatefile', 'local_template'), $template->get_file_link());
        }

        // errormessages.
        if (is_template_admin()) {
            $mform->addElement('textarea', 'errormessages', get_string('errormessages', 'local_template'), array('rows' => 6, 'cols' => 80, 'class' => 'smalltext'));
            $mform->setType('errormessages', PARAM_RAW);
        } else {
            $mform->addElement('html', $OUTPUT->box($record->errormessages));
        }

        if (!empty($record->id)) {
            $mform->addElement('header', 'backupcontrollers', get_string('backupcontrollers', 'local_template'));
            $mform->addElement('html', $OUTPUT->box(controllers\backupcontroller::renderbackupcontrollerss($record->id, true)));
        }

        $this->add_action_buttons(true);
        */

    }

    private function get_import() {

        global $PAGE;

        // Prepare the backup renderer
        $renderer = $PAGE->get_renderer('core','backup');

        // Check if we already have a import course id
        if ($importcourseid === false || $searchcourses) {
            // Obviously not... show the selector so one can be chosen
            $url = new moodle_url('/backup/import.php', array('id'=>$courseid));
            $search = new import_course_search(array('url'=>$url));

            // show the course selector
            echo $OUTPUT->header();
            $backup = new import_ui(false, array());
            echo $renderer->progress_bar($backup->get_progress_bar());
            $html = $renderer->import_course_selector($url, $search);
            echo $html;
            echo $OUTPUT->footer();
            die();
        }
    }

    function definition_after_data() {
        $mform = $this->_form;

        if (!empty($this->_customdata['id'])) {
            $mform->addElement('hidden', 'id', $this->_customdata['id']);
            $mform->setType('id', PARAM_INT);
        }

    }

   /**
    * Define extra validation mechanims.
    *
    * The data here:
    * - does not include {@link self::$fieldstoremove}.
    * - does include {@link self::$foreignfields}.
    * - was converted to map persistent-like data, e.g. array $description to string $description + int $descriptionformat.
    *
    * You can modify the $errors parameter in order to remove some validation errors should you
    * need to. However, the best practice is to return new or overriden errors. Only modify the
    * errors passed by reference when you have no other option.
    *
    * Do not add any logic here, it is only intended to be used by child classes.
    *
    * @param  \stdClass $data Data to validate.
    * @param  array $files Array of files.
    * @param  array $errors Currently reported errors.
    * @return array of additional errors, or overridden errors.
    */
    public function extra_validation($data, $files, array &$errors) {
        global $DB;

        $validateshortname = true;
        $record = $this->get_persistent()->to_record();
        if (!empty($record->id)) {
            if (!empty($record->createdcourseid)) {
                $validateshortname = false;
            }
        }
        // If the template record is already processed and destination course already created, allow resaving of duplicate shortname/idnumber
        if ($validateshortname) {
            // Add field validation check for duplicate shortname.
            $courseshortname = $DB->get_record('course', array('shortname' => $data->shortname), 'fullname', IGNORE_MULTIPLE);
            if ($courseshortname) {
                $errors['shortname'] = get_string('shortnametaken', '', $courseshortname->fullname);
            }

            // Add field validation check for duplicate idnumber.
            if (!empty($data->idnumber)) {
                $courseidnumber = $DB->get_record('course', array('idnumber' => $data->idnumber), 'idnumber', IGNORE_MULTIPLE);
                if ($courseidnumber) {
                    $errors['idnumber'] = get_string('courseidnumbertaken', 'error', $courseidnumber->fullname);
                }
            }
        }

        // Validate the dates (make sure end isn't greater than start).
        if ($errorcode = course_validate_dates((array)$data)) {
            $errors['enddate'] = get_string($errorcode, 'error');
        }

        return $errors;
    }

}