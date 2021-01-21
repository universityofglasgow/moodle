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
 * Contains the class for the UofG Assessments Details block.
 *
 * @package    block_gu_spdetails
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');

class block_gu_spdetails extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_gu_spdetails');
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * Returns the contents.
     *
     * @todo Move foreach($assessments as $a) to a new method
     * @return stdClass contents of block
     */
    public function get_content() {
        global $PAGE, $USER, $OUTPUT;
        $userid = $USER->id;

        // call JS and CSS
        $PAGE->requires->js(new moodle_url('/blocks/gu_spdetails/lib.js'));
        $PAGE->requires->css(new moodle_url('/blocks/gu_spdetails/styles.css'));

        $courses = enrol_get_all_users_courses($userid, true);
        $courseids = array_column($courses, 'id');
        $assessments = self::return_assessments($courseids, $userid);
        $hasassessments = ($assessments) ? true : false;

        $downarrow = $OUTPUT->image_url('downarrow', 'theme');
        $noassessments = $OUTPUT->image_url('noassessments', 'theme');

        $templatecontext = (array)[
            'assessments'       => json_encode($assessments),
            'data'              => $assessments,
            'hasassessments'    => $hasassessments,
            'header_course'     => get_string('header_course', 'block_gu_spdetails'),
            'header_assessment' => get_string('header_assessment', 'block_gu_spdetails'),
            'header_type'       => get_string('header_type', 'block_gu_spdetails'),
            'header_weight'     => get_string('header_weight', 'block_gu_spdetails'),
            'header_duedate'    => get_string('header_duedate', 'block_gu_spdetails'),
            'header_status'     => get_string('header_status', 'block_gu_spdetails'),
            'header_grade'      => get_string('header_grade', 'block_gu_spdetails'),
            'header_feedback'   => get_string('header_feedback', 'block_gu_spdetails'),
            'noassessments'     => get_string('noassessments', 'block_gu_spdetails'),
            'sort'              => get_string('sort', 'block_gu_spdetails'),
            'sort_course'       => get_string('sort_course', 'block_gu_spdetails'),
            'sort_date'         => get_string('sort_date', 'block_gu_spdetails'),
            'noassessments_img' => $noassessments,
            'downarrow_img'     => $downarrow,
        ];

        $this->content         = new stdClass();
        $this->content->text   = $OUTPUT->render_from_template('block_gu_spdetails/spdetails', $templatecontext);

        return $this->content;
    }

    public static function return_assessments($courseids, $userid) {
        $assessments = array();
        $allowedactivities = array('assign', 'quiz', 'forum', 'workshop');

        foreach($courseids as $courseid) {
            $mods = grade_get_gradable_activities($courseid);

            if (is_array($mods) || is_object($mods)) {
                foreach($mods as $mod) {
                    $modinfo = get_fast_modinfo($mod->course);
                    $course = get_course($courseid);
                    $cm = $modinfo->get_cm($mod->id);

                    $isactivityvisible = $cm->uservisible;
                    $isallowedactivity = in_array($mod->modname, $allowedactivities);
                    $mod->isstudent = self::return_isstudent($userid, $courseid);

                    $completionview = $cm->completionview;
                    $completiontype = $cm->completiongradeitemnumber;
                    $activity = self::retrieve_activity($mod->modname, $mod->instance, $mod->course, $userid);
                    $gradeitem = self::retrieve_gradeitem($mod->course, $mod->modname, $mod->instance, $activity);
                    $gradecategory = self::retrieve_gradecategory($gradeitem->categoryid);
                    $mod->grades = self::retrieve_grades($userid, $gradeitem->id);
                    $mod->isprovisional = (!isset($mod->grades->information)) ? true : false;
                    $mod->provisionaltext = ($mod->isprovisional) ? get_string('provisional', 'block_gu_spdetails') : null;

                    $mod->coursename = $course->fullname;
                    $mod->coursecode = $course->shortname;
                    $mod->coursetitle = self::return_coursetitle($mod->course, $mod->section, $mod->coursename);
                    $mod->courseurl = self::return_courseurl($mod->course);
                    $mod->assessmenturl = self::return_assessmenturl($mod->id, $mod->modname);
                    $mod->assessmenttype = self::return_assessmenttype($gradecategory->fullname);
                    $mod->weight = self::return_weight($mod->assessmenttype, $gradecategory->aggregation,
                                                    $gradeitem->aggregationcoef, $gradeitem->aggregationcoef2);
                    $mod->finalgrade = self::return_grade($gradeitem, $mod->grades);
                    $mod->dates = self::return_dates($mod->modname, $activity);
                    $mod->dates->formattedduedate = (!empty($mod->dates->duedate)) ?
                                                    userdate($mod->dates->duedate,
                                                             get_string('convertdate', 'block_gu_spdetails')) :
                                                    get_string('emptyvalue', 'block_gu_spdetails');
                    $mod->dates->gradingduedate = (isset($mod->dates->gradingduedate)) ? $mod->dates->gradingduedate : '0';
                    $mod->gradingduedate = self::return_gradingduedate($mod->finalgrade, $mod->dates->gradingduedate);
                    $mod->feedback = (!empty($mod->grades->feedback)) ? $mod->grades->feedback :
                                     ((!empty($mod->grades->feedbackformat) && $mod->modname === 'workshop') ?
                                      $mod->grades->feedbackformat : null);
                    $mod->submissionid = ($mod->modname === 'assign') ? $activity->submissionid : null;
                    $mod->feedbackduedate = self::return_feedback($userid, $mod->course, $mod->modname, $mod->id, $mod->instance,
                                                                  $mod->submissionid, $mod->feedback, $mod->finalgrade,
                                                                  $mod->dates->gradingduedate);
                    $mod->status = self::return_status($mod->modname, $mod->finalgrade, $mod->dates, $activity);

                    if($isactivityvisible && $isallowedactivity && $mod->isstudent) {
                        array_push($assessments, $mod);
                    }
                }
            }
        }

        return $assessments;
    }

    public static function return_coursetitle($courseid, $sectionid, $coursename) {
        global $DB;

        $sectionrecord = $DB->get_record('course_sections', array('id' => $sectionid), 'id, section');
        return $coursetitle = ($sectionrecord->section > 0) ?
                              get_section_name($courseid, $sectionrecord->section) : $coursename;
    }

    public static function return_courseurl($courseid) {
        $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
        return $courseurl;
    }

    public static function return_assessmenturl($id, $modname) {
        $assessmenturl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));
        return $assessmenturl;
    }

    public static function retrieve_activity($modname, $instance, $courseid, $userid) {
        global $DB;
        $activity = new stdClass();

        switch($modname) {
            case 'assign':
                $sql = 'SELECT assign.id, assign.name, assign.duedate, assign.nosubmissions,
                           assign.allowsubmissionsfromdate as `startdate`, assign.cutoffdate,
                           assign.gradingduedate, assign.teamsubmissiongroupingid,
                           auf.extensionduedate, ao.duedate as `overrideduedate`,
                           ao.allowsubmissionsfromdate as `overridestartdate`,
                           ao.cutoffdate as `overridecutoffdate`,
                           asub.id as `submissionid`, asub.status
                           FROM {assign} assign
                           LEFT JOIN {assign_user_flags} auf
                           ON auf.userid = ? AND auf.assignment = assign.id
                           LEFT JOIN {assign_overrides} ao
                           ON ao.userid = ? AND ao.assignid = assign.id
                           LEFT JOIN {assign_submission} asub
                           ON asub.userid = ? AND asub.assignment = assign.id
                           WHERE assign.id = ? AND assign.course = ?';
                $conditions = array($userid, $userid, $userid, $instance, $courseid);
                $activity = $DB->get_record_sql($sql, $conditions);
                break;
            case 'forum':
                $conditions = array('id' => $instance, 'course' => $courseid);
                $columns = 'id, name, duedate, cutoffdate, assessed, grade_forum';
                $activity = $DB->get_record('forum', $conditions, $columns);
                break;
            case 'quiz':
                $sql = 'SELECT quiz.id, quiz.name, quiz.timeopen as `startdate`,
                        quiz.timeclose as `duedate`, quiz.attempts,
                        qo.timeopen as `overridestartdate`,
                        qo.timeclose as `overrideduedate`,
                        qo.attempts as `overrideattempts`,
                        qa.attempt, qa.state
                        FROM {quiz} quiz
                        LEFT JOIN {quiz_overrides} qo
                        ON qo.quiz = quiz.id AND qo.userid = ?
                        LEFT JOIN {quiz_attempts} qa
                        ON qa.quiz = quiz.id AND qa.attempt = quiz.attempts
                        AND qa.userid = ?
                        WHERE quiz.id = ? AND quiz.course = ?';
                $conditions = array($userid, $userid, $instance, $courseid);
                $activity = $DB->get_record_sql($sql, $conditions);
                break;
            case 'workshop':
                $sql = 'SELECT workshop.id, workshop.name, workshop.submissionstart as `startdate`,
                        workshop.submissionend as `duedate`, workshop.assessmentstart,
                        workshop.assessmentend as `gradingduedate`,
                        ws.title, ws.grade, wa.gradinggrade, wa.feedbackauthor
                        FROM {workshop} workshop
                        LEFT JOIN {workshop_submissions} ws
                        ON ws.workshopid = workshop.id AND ws.authorid = ?
                        LEFT JOIN {workshop_assessments} wa
                        ON wa.submissionid = ws.id
                        WHERE workshop.id = ? and workshop.course = ?';
                $conditions = array($userid, $instance, $courseid);
                $activity = $DB->get_record_sql($sql, $conditions);
                break;
            default:
                $activity = new stdClass();
                break;
        }

        return $activity;
    }

    public static function retrieve_gradeitem($courseid, $modname, $instance, $activity) {
        global $DB;
        $itemnumber = ($modname == 'forum') ? (($activity->grade_forum) ? 1 : 0) : 0;
        $conditions = array('courseid' => $courseid, 'itemmodule' => $modname,
                            'iteminstance' => $instance, 'itemnumber' => $itemnumber);
        $columns = 'id, categoryid, itemname, gradetype, grademax, grademin, scaleid,
                    aggregationcoef, aggregationcoef2';

        $gradeitem = $DB->get_record('grade_items', $conditions, $columns);
        return $gradeitem;
    }

    public static function retrieve_gradecategory($gradecategoryid) {
        global $DB;
        $columns = 'id, parent, fullname, aggregation';

        $gradecategory = $DB->get_record('grade_categories', array('id' => $gradecategoryid), $columns);
        return $gradecategory;
    }

    public static function retrieve_grades($userid, $gradeitemid) {
        global $DB;
        $conditions = array('itemid' => $gradeitemid, 'userid' => $userid);
        $columns = 'id, rawgrade, rawgrademax, rawgrademin, rawscaleid, finalgrade, feedback, feedbackformat, information';

        $grades = $DB->get_record('grade_grades', $conditions, $columns);
        return $grades;
    }

    public static function return_assessmenttype($gradecategoryname) {
        $type = strtolower($gradecategoryname);
        $formative = get_string('formative', 'block_gu_spdetails');
        $summative = get_string('summative', 'block_gu_spdetails');

        if($type === $formative || strpos($type, $formative)) {
            $assessmenttype = ucwords($formative);
        } else if($type === $summative || strpos($type, $summative)) {
            $assessmenttype = ucwords($summative);
        } else {
            $assessmenttype = get_string('emptyvalue', 'block_gu_spdetails');
        }

        return $assessmenttype;
    }

    public static function return_weight($assessmenttype, $aggregation, $aggregationcoef, $aggregationcoef2) {
        $type = strtolower($assessmenttype);
        $summative = get_string('summative', 'block_gu_spdetails');
        $weight = 0;

        if($type === $summative) {
            $weight = ($aggregation == '10') ? (($aggregationcoef > 1) ? $aggregationcoef : $aggregationcoef * 100) :
                      $aggregationcoef2 * 100;
        }

        $finalweight = round($weight, 2).'%';

        return $finalweight;
    }

    public static function return_dates($modname, $activity) {
        $date = new stdClass();
        $date->duedate = (isset($activity->duedate)) ? $activity->duedate : '0';
        $date->isdueextended = false;
        $date->startdate = (isset($activity->startdate)) ? $activity->startdate : '0';
        $date->cutoffdate = (isset($activity->cutoffdate)) ? $activity->cutoffdate : '0';

        switch($modname) {
            case 'assign':
                if(!empty($activity->overrideduedate) || !empty($activity->extensionduedate)) {
                    $date->duedate = ($activity->overrideduedate >= $activity->extensionduedate) ?
                                     $activity->overrideduedate : $activity->extensionduedate;
                    $date->isdueextended = true;
                }

                $date->startdate = (!empty($activity->overridestartdate)) ?
                                   $activity->overridestartdate : $activity->startdate;
                $date->cutoffdate = (!empty($activity->overridecutoffdate)) ?
                                   $activity->overridecutoffdate : $activity->cutoffdate;
                $date->gradingduedate = (!empty($activity->gradingduedate)) ? $activity->gradingduedate : '0';
                break;
            case 'quiz':
                if($activity->overrideduedate) {
                    $date->duedate = $activity->overrideduedate;
                    $date->isdueextended = true;
                }

                $date->startdate = (!empty($activity->overridestartdate)) ?
                                   $activity->overridestartdate : $activity->startdate;
                $date->gradingduedate = (!(empty($date->duedate)) ? $date->duedate : '0');
                break;
            case 'forum':
                $date->gradingduedate = (!empty($activity->cutoffdate)) ?
                                        $activity->cutoffdate :
                                        (!(empty($date->duedate)) ? $date->duedate : '0');
                break;
            case 'workshop':
                $date->gradingduedate = (!empty($activity->gradingduedate)) ? $activity->gradingduedate : '0';
                break;
        }

        return $date;
    }

    public static function return_gradingduedate($finalgrade, $gradingduedate) {
        $duedateobj = new stdClass();
        $duedateobj->hasgrade = (!empty($finalgrade)) ? true : false;
        $duedateobj->gradetext = get_string('due', 'block_gu_spdetails').
                                        userdate($gradingduedate, get_string('convertdate', 'block_gu_spdetails'));

        if (!empty($finalgrade)){
            $duedateobj->gradetext = $finalgrade;
        } else {
            $duedateobj->gradetext = (empty($gradingduedate)) ? get_string('tobeconfirmed', 'block_gu_spdetails') :
                                     ((time() > $gradingduedate) ? ucwords(get_string('overdue', 'block_gu_spdetails')) :
                                      $duedateobj->gradetext);
        }

        return $duedateobj;
    }

    public static function return_grade($gradeitem, $grades) {
        $finalgrade = null;
        $grademax = $gradeitem->grademax;
        $grademin = $gradeitem->grademin;

        // check if $grades != false and $grades->finalgrade != NULL
        if($grades && $grades->finalgrade) {
            switch($gradeitem->gradetype) {
                // grade type = value
                case "1":
                    $intgrade = round($grades->finalgrade);
                    $finalgrade = ($grademax == '22' && $grademin == '0') ?
                                    self::return_22grademaxpoint(round($intgrade)) :
                                    round(($intgrade / ($grademax - $grademin)) * 100, 2).'%';
                    break;
                // grade type = scale
                case "2":
                    $intgrade = round($grades->finalgrade);
                    $scales = self::retrieve_scale($gradeitem->scaleid);
                    $scale = make_menu_from_list($scales->scale);
                    $scalevalue = (!empty($intgrade)) ? $scale[$intgrade] : null;

                    if(strpos($scalevalue, ':')){
                        $expscalevalue = explode(':', $scalevalue);
                        $scalevalue = $expscalevalue[0];
                    }

                    $finalgrade = $scalevalue;
                    break;
                // grade type = text
                default:
                    $finalgrade = $grades->finalgrade;
                    break;
            }
        }

        return $finalgrade;
    }

    public static function retrieve_scale($scaleid) {
        global $DB;

        $scale = $DB->get_record('scale', array('id' => $scaleid), 'id, scale');
        return $scale;
    }

    public static function return_22grademaxpoint($grade) {
        $values = array('H', 'G2', 'G1', 'F3', 'F2', 'F1',
                        'E3', 'E2', 'E1', 'D3', 'D2', 'D1',
                        'C3', 'C2', 'C1', 'B3', 'B2', 'B1',
                        'A5', 'A4', 'A3', 'A2', 'A1');
        return $values[$grade];
    }

    public static function return_feedback($userid, $courseid, $modname, $cmid, $instance, $itemid,
                                           $feedback, $finalgrade, $gradingduedate) {
        $feedbackobj = new stdClass();
        $feedbackobj->hasfeedback = (!empty($feedback)) ? true : false;
        $feedbackobj->feedbacktext = get_string('due', 'block_gu_spdetails').
                                    userdate($gradingduedate, get_string('convertdate', 'block_gu_spdetails'));
        $feedbackobj->feedbackurl = null;
        $footer = get_string('id_pagefooter', 'block_gu_spdetails');
        $intro = get_string('id_intro', 'block_gu_spdetails');

        if($feedbackobj->hasfeedback) {
            $feedbackobj->hasfeedback = (($feedback === 'MV' || $feedback === 'NS') &&
                                        empty($finalgrade)) ? false : true;
            if($modname === 'workshop') {
                $feedbackurl = new moodle_url('/mod/'.'workshop'.'/submission.php', array('cmid' => $cmid));
                $feedbackobj->feedbackurl  = $feedbackurl.$footer;
            }
        }else{
            if($modname === 'assign') {
                $feedbackobj->file = self::retrieve_file($userid, $itemid);
                $feedbackobj->tii = self::retrieve_turnitin($courseid, $userid, $itemid);
                $feedbackobj->hasfeedback = $feedbackobj->hasfeedback ? true :
                                            ((!empty($feedbackobj->file) && $finalgrade) ? true : false);
                $feedbackurl = ($feedbackobj->hasfeedback) ?
                               new moodle_url('/mod/'.$modname.'/view.php', array('id' => $cmid)) : null;
                $feedbackobj->feedbackurl = ($feedbackobj->hasfeedback) ?
                                            (!empty($feedbackobj->file) ? $feedbackurl.$intro : $feedbackurl.$footer) : null;
            }
        }

        // Display feedback regardless if there's a grade or not.
        $feedbackobj->feedbacktext = ($feedbackobj->hasfeedback) ? get_string('readfeedback', 'block_gu_spdetails') :
                                     (((time() > $gradingduedate) ? ucwords(get_string('overdue', 'block_gu_spdetails')) :
                                     $feedbackobj->feedbacktext));

        return $feedbackobj;
    }

    public static function return_status($modname, $finalgrade, $dates, $activity) {
        $status = new stdClass();
        $status->hasurl = false;

        // assuming $finalgrade can be 0
        if(!is_null($finalgrade)) {
            $status->text = get_string('graded', 'block_gu_spdetails');
            $status->suffix = get_string('graded', 'block_gu_spdetails');
        }else{
            if($modname == 'assign' && $activity->nosubmissions == '1'){
                $status->text = get_string('submission_unavailable', 'block_gu_spdetails');
                $status->suffix = get_string('unavailable', 'block_gu_spdetails');
            }else if($dates->startdate == '0' || time() >= $dates->startdate) {
                if(time() <= $dates->duedate) {
                    $status->hasurl = true;
                    if($dates->isdueextended) {
                        $status->text = get_string('overdue', 'block_gu_spdetails');
                        $status->suffix = get_string('class_overduelinked', 'block_gu_spdetails');
                    }else{
                        if($modname === 'assign' && $activity->status === 'submitted') {
                            $status->text = get_string('submitted', 'block_gu_spdetails');
                            $status->suffix = get_string('submitted', 'block_gu_spdetails');
                            $status->hasurl = false;
                        }else{
                            $status->text = get_string('submit', 'block_gu_spdetails');
                            $status->suffix = get_string('submit', 'block_gu_spdetails');
                        }
                    }
                }else{
                    if($dates->duedate == 0) {
                        $status->text = get_string('notopen', 'block_gu_spdetails');
                        $status->suffix = get_string('class_notopen', 'block_gu_spdetails');
                    }else{
                        $status->text = get_string('overdue', 'block_gu_spdetails');

                        if(time() <= $dates->cutoffdate || $dates->cutoffdate == 0) {
                            $status->suffix = get_string('class_overduelinked', 'block_gu_spdetails');
                            $status->hasurl = true;
                        }else{
                            $status->suffix = get_string('overdue', 'block_gu_spdetails');
                        }
                    }
                }
            }else{
                $status->text = get_string('notopen', 'block_gu_spdetails');
                $status->suffix = get_string('class_notopen', 'block_gu_spdetails');
            }
        }

        return $status;
    }

    public static function return_isstudent($userid, $courseid) {
        $roles_array = array();
        $roles = strtolower(strip_tags(get_user_roles_in_course($userid, $courseid)));
        $roles_array = explode(',', $roles);
        $isstudent = in_array(get_string('student', 'block_gu_spdetails'), $roles_array);

        return $isstudent;
    }

    public static function retrieve_file($userid, $itemid) {
        global $DB;
        $filetype = 'assignsubmission_file';

        $sql = 'SELECT *
                FROM {files}
                WHERE userid = ? AND itemid = ?
                AND component = ? AND filesize > 0';
        $conditions = array($userid, $itemid, $filetype);

        $file = $DB->get_record_sql($sql, $conditions);
        return $file;
    }

    public static function retrieve_turnitin($courseid, $userid, $itemid) {
        global $DB;

        $sql = 'SELECT *
                        FROM {turnitintooltwo} tii
                        LEFT JOIN {turnitintooltwo_submissions} tiisub
                        ON tiisub.userid = ? AND tiisub.turnitintooltwoid = tii.id
                        LEFT JOIN {turnitintooltwo_courses} tiicourse
                        ON tiicourse.courseid = ?
                        LEFT JOIN {plagiarism_turnitin_files} tiifile
                        ON tiifile.itemid = ?
                        WHERE tii.course = ?';
        $conditions = array($userid, $courseid, $itemid, $courseid);
        $turnitin = $DB->get_records_sql($sql, $conditions);
        return $turnitin;
    }
}
