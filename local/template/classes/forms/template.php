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
        global $CFG, $DB, $PAGE, $OUTPUT;

        $mform = $this->_form;
        $renderer = $PAGE->get_renderer('local_template');

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'action', $this->_customdata['action']);
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->setConstant('action', $this->_customdata['action']);

        $record = $this->get_persistent()->to_record();

        // Prepare summary and overview files.
        $summaryeditoroptions = models\template::get_summary_editor_options($record->id);
        $courseoverviewfilesoptions = models\template::get_course_overviewfiles_options();
        $context = models\template::get_context();
        $record = file_prepare_standard_editor($record, 'summary', $summaryeditoroptions, $context, models\template::TABLE, models\template::FILEAREA_SUMMARY, $record->id);
        $record = file_prepare_standard_filemanager($record, 'overviewfiles', $courseoverviewfilesoptions, $context, models\template::TABLE, models\template::FILEAREA_OVERVIEWFILES, $record->id);
        $this->set_data($record);

        // Stepper section: Course.

        if (empty($this->_customdata['admin'])) {
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_HEADER, []));
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_SELECTTEMPLATE, []));
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_COURSE_START, []));
        } else {
            $mform->addElement('header', 'coursedetails', get_string('coursedetails', 'local_template'));
        }

        // Course category.
        $importcategories = \core_course_category::make_categories_list(\core_course\management\helper::get_course_copy_capabilities());

        // Get plugin config setting list of template course categories.
        $templatecategorysettings = get_config('local_template', 'categories');

        $categories = [];
        if (!empty($templatecategorysettings)) {
            // Reduce set of template categories based on user capability in each category.
            $templatecategories = explode(',', $templatecategorysettings);
            if (!empty($templatecategories)) {
                foreach ($templatecategories as $categoryid) {
                    $categorycontext = \context_coursecat::instance($categoryid);
                    if (has_capability('local/template:usetemplate', $categorycontext)) {
                        $category = \core_course_category::get($categoryid, MUST_EXIST, true);
                        if ($category->visible || has_capability('moodle/category:viewhiddencategories', $categorycontext)) {
                            $categories[] = $categoryid;
                        }
                    }
                    // Remove template category from categories listing.
                    unset($importcategories[$categoryid]);
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

        // Course templatecourseid.
        $mform->addElement('autocomplete', 'templatecourseid', get_string('templatecourse', 'local_template'), $templatecourses);
        $mform->addHelpButton('templatecourseid', 'templatecourse', 'local_template');
        $mform->addRule('templatecourseid', get_string('missingtemplate', 'local_template'), 'required', null, 'client');
        $mform->setDefault('templatecourseid', 0);

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

        // Course category.
        $defaultcategory = [];
        $defaultcategoryid = 0;
        if (!empty($this->_customdata['category'])) {
            $defaultcategoryid = $this->_customdata['category'];
            if (isset($importcategories[$defaultcategoryid])) {
                $defaultcategory[$defaultcategoryid] = $importcategories[$defaultcategoryid];
            }
        }
        $autocomplete = $mform->addElement('autocomplete', 'category', get_string('coursecategory'), $defaultcategory + $importcategories, ['multiple' => false]);
        $mform->addHelpButton('category', 'coursecategory');
        $mform->addRule('category', null, 'required', null, 'client');
        $mform->setDefault('category', $defaultcategoryid);
        $autocomplete->setValue($defaultcategoryid);

        // Course visible.
        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $courseconfig = get_config('moodlecourse');
        $mform->addElement('select', 'visible', get_string('coursevisibility'), $choices);
        $mform->addHelpButton('visible', 'coursevisibility');
        $mform->setDefault('visible', $courseconfig->visible);

        // Course startdate.
        $mform->addElement('date_time_selector', 'startdate', get_string('startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $date = (new \DateTime())->setTimestamp(usergetmidnight(time()));
        $date->modify('+1 day');
        $mform->setDefault('startdate', $date->getTimestamp());

        // Course enddate.
        $mform->addElement('date_time_selector', 'enddate', get_string('enddate'), array('optional' => true));
        $mform->addHelpButton('enddate', 'enddate');

        // Course idnumber.
        $mform->addElement('text', 'idnumber', get_string('idnumbercourse'), 'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumbercourse');
        $mform->setType('idnumber', PARAM_RAW);

        // Stepper section: Description.

        if (empty($this->_customdata['admin'])) {
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_COURSE_END, []));
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_DESCRIPTION_START, []));
        } else {
            $mform->addElement('header', 'description', get_string('description', 'local_template'));
        }

        // Course summary.
        $mform->addElement('editor', 'summary_editor', get_string('summary'), ['rows' => 10, 'cols' => 100], course_overviewfiles_options(null));
        $mform->addHelpButton('summary_editor', 'coursesummary');
        $mform->setType('summary_editor', PARAM_RAW);

        // Course overviewfiles.
        $mform->addElement('filemanager', 'overviewfiles_filemanager', get_string('courseoverviewfiles'), null, course_overviewfiles_options(null));
        $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');

        // Stepper section: Enrolment.

        if (empty($this->_customdata['admin'])) {
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_DESCRIPTION_END, []));
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_ENROLMENT_START, []));
        } else {
            $mform->addElement('header', 'enrolment', get_string('enrolment', 'local_template'));
        }

        // Enrolment gudbenrolment.
        // Boolean flag for whether a gudatabase enrolment method should be added to the course.
        $mform->addElement('advcheckbox', 'gudbenrolment', get_string('gudbenrolment', 'local_template'), get_string('gudbaddenrolment', 'local_template'), null, [0, 1]);
        $mform->addHelpButton('gudbenrolment', 'gudbaddenrolment', 'local_template');

        // Enrolment gudbstatus.
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
            ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'gudbstatus', get_string('status', 'enrol_gudatabase'), $options);
        $mform->addHelpButton('gudbstatus', 'status', 'enrol_gudatabase');

        // Enrolment gudbsettingscodes.
        $yesno = array(
            0 => get_string('no'),
            1 => get_string('yes'),
        );
        $mform->addElement('select', 'gudbsettingscodes', get_string('settingscodes', 'enrol_gudatabase'), $yesno);
        $mform->addHelpButton('gudbsettingscodes', 'settingscodes', 'enrol_gudatabase');
        $mform->setDefault('gudbsettingscodes', 0);

        // Enrolment gudballowhidden.
        $mform->addElement('select', 'gudballowhidden', get_string('allowhidden', 'enrol_gudatabase'), $yesno);
        $mform->addHelpButton('gudballowhidden', 'allowhidden', 'enrol_gudatabase');
        $mform->setDefault('gudballowhidden', 0);

        // Enrolment gudbcodelist.
        $mform->addElement('textarea', 'gudbcodelist', get_string('codelist', 'enrol_gudatabase'),
            'rows="5" cols="25" style="height: auto; width:auto;"');
        $mform->addHelpButton('gudbcodelist', 'codelist', 'enrol_gudatabase');
        $mform->setType('gudbcodelist', PARAM_TEXT);

        // Stepper section: Import.

        if (empty($this->_customdata['admin'])) {
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_ENROLMENT_END, []));
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_IMPORT_START, []));
        } else {
            $mform->addElement('header', 'import', get_string('import', 'local_template'));
        }

        if (empty($importcategories)) {
            $importcourses = [0 => ''];
        } else {
            list($insql, $params) = $DB->get_in_or_equal(array_keys($importcategories), SQL_PARAMS_NAMED);
            $importcoursesql = '
                SELECT c.id, c.fullname, cc.name, cc.path
                FROM {course} c
                INNER JOIN {course_categories} cc ON cc.id = c.category
                INNER JOIN {context} ctx ON ctx.contextlevel = :contextcategory AND ctx.instanceid = cc.id
                WHERE c.timecreated > :twoyearsagosecs
                AND c.category ';
            $params['contextcategory'] = CONTEXT_COURSECAT;
            $params['twoyearsagosecs'] = time() - YEARSECS * 2;
            $importcourses = [];
            $importcoursesrecords = $DB->get_records_sql($importcoursesql . $insql, $params);
            foreach ($importcoursesrecords as $importcourserecord) {
                list($insql, $params) = $DB->get_in_or_equal(explode('/', $importcourserecord->path));
                $categorylist = $DB->get_records_sql('SELECT name FROM {course_categories} WHERE id ' . $insql . ' ORDER BY sortorder', $params);
                $coursename = '';
                foreach ($categorylist as $category) {
                    $coursename .= $category->name . ' / ';
                }
                $coursename .= $importcourserecord->fullname;
                $importcourses[$importcourserecord->id] = $coursename;
            }
            $importcourses = [0 => ''] + $importcourses;
        }

        // Enrolment importcourseid.
        $mform->addElement('static', 'createdcoursename', '',  get_string('importcourse_desc', 'local_template'));
        $mform->addElement('autocomplete', 'importcourseid', get_string('importcourse', 'local_template'), $importcourses);
        //$mform->addRule('importcourseid', null, 'required', null, 'client');
        //$mform->addHelpButton('category', 'coursecategory');
        $mform->setDefault('importcourseid', 0);

        if (empty($this->_customdata['admin'])) {
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_IMPORT_END, []));
            $mform->addElement('html', $renderer->render_stepper(self::STEPPER_PROCESS_START, []));
        } else {
            $mform->addElement('header', 'process', get_string('process', 'local_template'));
        }

        $mform->addElement('html', '<br><hr>');

        // Action buttons.

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'createandredirect', get_string('createandredirect', 'local_template'));
        $buttonarray[] = &$mform->createElement('submit', 'createcourse', get_string('createcourse', 'local_template'));
        $buttonarray[] = &$mform->createElement('submit', 'savetemplate', get_string('savetemplate', 'local_template'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        // Stepper section: Admin only.

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
            if (!empty($record->id)) {
                $mform->addElement('header', 'backupcontrollers', get_string('backupcontrollers', 'local_template'));
                $mform->addElement('html', $OUTPUT->box(controllers\backupcontroller::renderbackupcontrollers($record->id, true)));
            }
        }
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