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
 * @author     Franco Louie Magpusao, Jose Maria Abreu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. '/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');
require_once('querylib.php');

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
     * @return stdClass contents of block
     */
    public function get_content() {
        global $PAGE, $USER, $OUTPUT;
        $user = $USER;

        // call JS and CSS
        $PAGE->requires->js(new moodle_url('/blocks/gu_spdetails/lib.js'));
        $PAGE->requires->css(new moodle_url('/blocks/gu_spdetails/styles.css'));

        $returnassessments = self::return_assessments($user);

        $currentassessments = $returnassessments->currentassessments;
        $pastassessments = $returnassessments->pastassessments;

        $hascurrentassessments = (!empty($currentassessments)) ? true : false;
        $haspastassessments = (!empty($pastassessments)) ? true : false;
        $hasassessments = ($hascurrentassessments || $haspastassessments) ? true : false;

        $downarrow = $OUTPUT->image_url('downarrow', 'theme');
        $noassessments = $OUTPUT->image_url('noassessments', 'theme');

        $templatecontext = (array)[
            'currentdata'           => $currentassessments,
            'currentenroll'         => get_string('currentlyenrolledin', 'block_gu_spdetails'),
            'duedateextension'      => get_string('duedateextension', 'block_gu_spdetails'),
            'hasassessments'        => $hasassessments,
            'hascurrentassessments' => $hascurrentassessments,
            'haspastassessments'    => $haspastassessments,
            'header_course'         => get_string('header_course', 'block_gu_spdetails'),
            'header_assessment'     => get_string('header_assessment', 'block_gu_spdetails'),
            'header_type'           => get_string('header_type', 'block_gu_spdetails'),
            'header_weight'         => get_string('header_weight', 'block_gu_spdetails'),
            'header_duedate'        => get_string('header_duedate', 'block_gu_spdetails'),
            'header_status'         => get_string('header_status', 'block_gu_spdetails'),
            'header_grade'          => get_string('header_grade', 'block_gu_spdetails'),
            'header_feedback'       => get_string('header_feedback', 'block_gu_spdetails'),
            'nocurrentassessments'  => get_string('nocurrentassessments', 'block_gu_spdetails'),
            'nopastassessments'     => get_string('nopastassessments', 'block_gu_spdetails'),
            'pastdata'              => $pastassessments,
            'pastenroll'            => get_string('pastcourses', 'block_gu_spdetails'),
            'provisional'           => get_string('provisional', 'block_gu_spdetails'),
            'sort'                  => get_string('sort', 'block_gu_spdetails'),
            'sort_course'           => get_string('sort_course', 'block_gu_spdetails'),
            'sort_date'             => get_string('sort_date', 'block_gu_spdetails'),
            'view_submission'       => get_string('viewsubmission', 'block_gu_spdetails'),
            'noassessments_img'     => $noassessments,
            'downarrow_img'         => $downarrow,
        ];

        $this->content         = new stdClass();
        $this->content->text   = $OUTPUT->render_from_template('block_gu_spdetails/spdetails', $templatecontext);

        return $this->content;
    }

    /**
     * Returns an array of gradable activities (assignment, quiz, forum, workshop)
     * from the user's enrolled courses
     *
     * @param stdClass $user
     * @return array
     */
    public static function return_assessments($user) {
        global $DB;

        $allowedactivities = array('assign', 'quiz', 'forum', 'workshop');
        $currentassessments = array();
        $pastassessments = array();

        $mods = get_all_user_courses_gradable_activities($user->id);

        if ($mods) {
            foreach($mods as $mod) {
                // check if user has role 'student' in the course
                $isstudent = self::return_isstudent($mod->modname, $mod->id);

                $modinfo = get_fast_modinfo($mod->course);
                $cm = $modinfo->get_cm($mod->id);
                // check if course module is visible to the user
                $iscmvisible = $cm->uservisible;

                if($isstudent && $iscmvisible) {
                    $assessment = new stdClass;    // object storage for assessment row
                    $courseparams = array($mod->courseid, $mod->category, $mod->sortorder,
                                        $mod->fullname, $mod->shortname, $mod->idnumber,
                                        $mod->summary, $mod->summaryformat, $mod->format,
                                        $mod->showgrades, $mod->newsitems, $mod->startdate,
                                        $mod->enddate, $mod->relativedatesmode, $mod->marker,
                                        $mod->maxbytes, $mod->legacyfiles, $mod->showreports,
                                        $mod->visible, $mod->visibleold, $mod->groupmode,
                                        $mod->groupmodeforce, $mod->defaultgroupingid, $mod->lang,
                                        $mod->calendartype, $mod->theme, $mod->timecreated,
                                        $mod->timemodified , $mod->requested, $mod->enablecompletion,
                                        $mod->completionnotify, $mod->cacherev);
                    $course = self::set_assessmentcourse($courseparams);

                    $gradeitemparams = array($mod->gradeitemid, $mod->categoryid, $mod->gradetype,
                                             $mod->grademax, $mod->grademin, $mod->scaleid,
                                             $mod->aggregationcoef, $mod->aggregationcoef2);
                    $gradeitem = set_assessmentgradeitem($gradeitemparams);
                    $gradecategory = retrieve_gradecategory($mod->categoryid);
                    $activity = retrieve_activity($user, $cm, $course, $gradeitem);

                    $assessment->id = $mod->id;
                    $assessment->coursetitle = self::return_topic($mod->course, $mod->section, $course->fullname);
                    $assessment->courseurl = self::return_courseurl($mod->course);
                    $assessment->assessmenturl = self::return_activityurl($mod->id, $mod->modname);
                    $assessment->assessmentname = $activity->name;
                    // $assessment->activity = $activity;
                    $assessment->assessmenttype = self::return_assessmenttype($gradecategory->fullname);
                    $assessment->weight = self::return_weight($assessment->assessmenttype, $gradecategory->aggregation,
                                                              $gradeitem->aggregationcoef, $gradeitem->aggregationcoef2);
                    $assessment->duedate = self::return_duedate($mod->modname, $activity);
                    $assessment->grading = self::return_grading($mod->modname, $activity, $gradeitem);
                    $assessment->feedback = self::return_feedback($mod->modname, $activity, $assessment->assessmenturl);

                    $ispastcourse = self::return_ispastcourse($course->enddate, $assessment->duedate->duedate);
                    if($ispastcourse){
                        $assessment->startdate = date(get_string('pastcourseconvertdate', 'block_gu_spdetails'), $course->startdate);
                        $assessment->enddate = date(get_string('pastcourseconvertdate', 'block_gu_spdetails'), $course->enddate);
                        array_push($pastassessments, $assessment);
                    } else {
                        $assessment->status = self::return_status($mod->modname, $activity, $assessment->duedate);
                        array_push($currentassessments, $assessment);
                    }
                }
            }
        }

        return (object) array(
            'currentassessments' => $currentassessments,
            'pastassessments' => $pastassessments
        );
    }

    /**
     * Checks if the user has a 'student' role in the course
     *
     * @param string $modname expected arguments 'assign', 'quiz', 'forum' and 'workshop'
     * @param int $instance
     * @return boolean
     */
    public static function return_isstudent($modname, $instance) {
        $context = context_module::instance($instance);
        $isstudent = false;
        switch ($modname) {
            case 'assign' :
                $isstudent = has_capability("mod/assign:submit", $context, null, false);
                break;
            case 'quiz' :
                $isstudent = has_capability("mod/quiz:attempt", $context, null, false);
                break;
            case 'forum' :
                $isstudent = has_capability("mod/forum:deleteownpost", $context, null, false) &&
                            !has_capability("mod/forum:deleteanypost", $context, null, false);
                break;
            case 'workshop' :
                $isstudent = has_capability("mod/workshop:submit", $context, null, false);
                break;
        } 
        return $isstudent;
    }

    /**
     * Checks if the course is a past course
     *
     * @param int $courseenddate
     * @param int $duedate
     * @return boolean
     */
    public static function return_ispastcourse($courseenddate, $duedate){
        $iscourseenddatefuture = time() + (86400 * 30) > $courseenddate;
        $isassessmentduedatefuture = time() + (86400 * 30) > $duedate;

        return $iscourseenddatefuture && $isassessmentduedatefuture;
    }

    /**
     * Groups assessment course values in one object
     *
     * @param array $params
     * @return stdClass
     */
    public static function set_assessmentcourse($params) {
        $keys = array('id', 'category', 'sortorder',
                    'fullname', 'shortname', 'idnumber',
                    'summary', 'summaryformat', 'format',
                    'showgrades', 'newsitems', 'startdate',
                    'enddate', 'relativedatesmode', 'marker',
                    'maxbytes', 'legacyfiles', 'showreports',
                    'visible', 'visibleold', 'groupmode',
                    'groupmodeforce', 'defaultgroupingid', 'lang',
                    'calendartype', 'theme', 'timecreated',
                    'timemodified' , 'requested', 'enablecompletion',
                    'completionnotify', 'cacherev');
        $values = $params;
        $course = array_combine($keys, $values);
        return (object) $course;
    }

    /**
     * Returns the section name if it exists, otherwise, returns the course name
     *
     * @param int $courseid
     * @param int $sectionid
     * @param string $coursename
     * @return string
     */
    public static function return_topic($courseid, $sectionid, $coursename) {
        // retrieve_section() from /block_gu_spdetails/querylib.php
        $section = retrieve_section($courseid, $sectionid);
        // get_section_name() from /course/lib.php
        $topic = ($section > 0) ? get_section_name($courseid, $section) : $coursename;

        return $topic;
    }

    /**
     * Returns the course URL
     *
     * @param int $courseid
     * @return string
     */
    public static function return_courseurl($courseid) {
        $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
        return $courseurl;
    }

    /**
     * Returns the activity URL
     *
     * @param int $id
     * @param string $modname
     * @return string
     */
    public static function return_activityurl($id, $modname) {
        $activityurl = new moodle_url('/mod/'.$modname.'/view.php', array('id' => $id));
        return $activityurl;
    }

    /**
     * Returns the 'assessment type'
     *
     * @param string $categoryname
     * @return string 'Formative', 'Summative', or '—'
     */
    public static function return_assessmenttype($categoryname) {
        $type = strtolower($categoryname);
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

    /**
     * Returns the 'weight' in percentage
     *
     * @param string $assessmenttype
     * @param string $aggregation
     * @param string $aggregationcoef
     * @param string $aggregationcoef2
     * @return string Weight (in percentage), or '—' if empty
     */
    public static function return_weight($assessmenttype, $aggregation, $aggregationcoef, $aggregationcoef2) {
        $type = strtolower($assessmenttype);
        $summative = get_string('summative', 'block_gu_spdetails');
        $weight = 0;

        if($type === $summative) {
            // $aggregation == '10', meaning 'Weighted mean of grades' is used
            $weight = ($aggregation == '10') ?
                      (($aggregationcoef > 1) ? $aggregationcoef : $aggregationcoef * 100) :
                      $aggregationcoef2 * 100;
        }

        $finalweight = ($weight > 0) ? round($weight, 2).'%' : get_string('emptyvalue', 'block_gu_spdetails');

        return $finalweight;
    }

    /**
     * Returns an object containing due date and related data
     *
     * @param string $modname
     * @param stdClass $aggregation
     * @return stdClass Object containing duedate, hasduedateextension, formattedduedate
     */
    public static function return_duedate($modname, $activity) {
        $duedate = new stdClass;
        $duedate->duedate = null;
        $duedate->hasduedateextension = false;
        $duedate->formattedduedate = get_string('emptyvalue', 'block_gu_spdetails');

        switch($modname) {
            case 'assign':
                $duedate->duedate = (isset($activity->extensionduedate)) ?
                                    $activity->extensionduedate :
                                    ((isset($activity->hasduedateoverride) && $activity->hasduedateoverride) ?
                                     $activity->duedate : (($activity->duedate > 0) ? $activity->duedate : null));
                $duedate->hasduedateextension = (isset($activity->extensionduedate)) ?
                                                true : false;
                $duedate->formattedduedate = (isset($duedate->duedate)) ?
                                             userdate($duedate->duedate, get_string('convertdate', 'block_gu_spdetails')) :
                                             $duedate->formattedduedate;
                break;
            default:
                $duedate->duedate = $activity->duedate;
                $duedate->formattedduedate = ($activity->duedate > 0) ?
                                             userdate($duedate->duedate, get_string('convertdate', 'block_gu_spdetails')) :
                                             get_string('emptyvalue', 'block_gu_spdetails');
                break;
        }

        return $duedate;
    }

    /**
     * Returns the status object of an assessment
     *
     * @param string $modname
     * @param stdClass $activity
     * @param stdClass $duedate
     * @return stdClass Object containing status, suffix, hasstatusurl
     */
    public static function return_status($modname, $activity, $duedate) {
        $status = new stdClass;
        $status->status = '';
        $status->suffix = '';
        $status->hasstatusurl = false;

        switch($modname) {
            case 'assign':
                if($activity->submissionsenabled) {
                    if($activity->graded) {
                        $status->status = get_string('graded', 'block_gu_spdetails');
                        $status->suffix = get_string('graded', 'block_gu_spdetails');
                    }else{
                        if($activity->submissionstatus === 'submitted') {
                            $status->status = get_string('submitted', 'block_gu_spdetails');
                            $status->suffix = get_string('submitted', 'block_gu_spdetails');
                        }else{
                            if($activity->startdate > time() || $activity->duedate == 0) {
                                $status->status = get_string('notopen', 'block_gu_spdetails');
                                $status->suffix = get_string('class_notopen', 'block_gu_spdetails');
                            }else{
                                if($duedate->duedate < time()) {
                                    if($activity->cutoffdate < time()) {
                                        $status->status = get_string('overdue', 'block_gu_spdetails');
                                        $status->suffix = ($activity->submissionslocked) ?
                                                          get_string('overdue', 'block_gu_spdetails') :
                                                          (($activity->cutoffdate == 0) ?
                                                            get_string('overduelinked', 'block_gu_spdetails') :
                                                            get_string('overdue', 'block_gu_spdetails'));
                                        $status->hasstatusurl = ($activity->submissionslocked) ? false :
                                                                (($activity->cutoffdate == 0) ? true : false);
                                    }else{
                                        $status->status = get_string('notsubmitted', 'block_gu_spdetails');
                                        $status->suffix = get_string('class_notsubmitted', 'block_gu_spdetails');
                                    }
                                }else{
                                    $status->status = get_string('submit', 'block_gu_spdetails');
                                    $status->suffix = get_string('submit', 'block_gu_spdetails');
                                    $status->hasstatusurl = true;
                                }
                            }
                        }
                    }
                }else{
                    $status->status = get_string('unavailable', 'block_gu_spdetails');
                    $status->suffix = get_string('class_unavailable', 'block_gu_spdetails');
                }
                break;
            case 'forum':
                if($activity->graded) {
                    $status->status = get_string('graded', 'block_gu_spdetails');
                    $status->suffix = get_string('graded', 'block_gu_spdetails');
                }else{
                    if($duedate->duedate < time()) {
                        if($activity->cutoffdate < time()) {
                            $status->status = get_string('overdue', 'block_gu_spdetails');
                            $status->suffix = ($activity->cutoffdate == 0) ? get_string('overduelinked', 'block_gu_spdetails') :
                                              get_string('overdue', 'block_gu_spdetails');
                            $status->hasstatusurl = ($activity->cutoffdate == 0) ? true : false;
                        }else{
                            $status->status = get_string('notsubmitted', 'block_gu_spdetails');
                            $status->suffix = get_string('class_notsubmitted', 'block_gu_spdetails');
                        }
                    }else{
                        $status->status = get_string('submit', 'block_gu_spdetails');
                        $status->suffix = get_string('submit', 'block_gu_spdetails');
                        $status->hasstatusurl = true;
                    }
                }
                break;
            case 'quiz':
                if($activity->submissionsenabled) {
                    if($activity->graded) {
                        $status->status = get_string('graded', 'block_gu_spdetails');
                        $status->suffix = get_string('graded', 'block_gu_spdetails');
                    }else{
                        if($activity->startdate > time()) {
                            $status->status = get_string('notopen', 'block_gu_spdetails');
                            $status->suffix = get_string('class_notopen', 'block_gu_spdetails');
                        }else{
                            if($duedate->duedate < time() && $activity->duedate != 0) {
                                $status->status = get_string('notsubmitted', 'block_gu_spdetails');
                                $status->suffix = get_string('class_notsubmitted', 'block_gu_spdetails');
                            }else{
                                $status->status = get_string('submit', 'block_gu_spdetails');
                                $status->suffix = get_string('submit', 'block_gu_spdetails');
                                $status->hasstatusurl = true;
                            }
                        }
                    }
                }else{
                    $status->status = get_string('unavailable', 'block_gu_spdetails');
                    $status->suffix = get_string('class_unavailable', 'block_gu_spdetails');
                }
                break;
            case 'workshop':
                if($activity->graded) {
                    $status->status = get_string('graded', 'block_gu_spdetails');
                    $status->suffix = get_string('graded', 'block_gu_spdetails');
                }else{
                    if(isset($activity->submissionstatus) && $activity->submissionstatus === 'submitted') {
                        $status->status = get_string('submitted', 'block_gu_spdetails');
                        $status->suffix = get_string('submitted', 'block_gu_spdetails');
                    }else{
                        if($activity->startdate > time() || $activity->duedate == 0) {
                            $status->status = get_string('notopen', 'block_gu_spdetails');
                            $status->suffix = get_string('class_notopen', 'block_gu_spdetails');
                        }else{
                            if($duedate->duedate < time()) {
                                $status->status = get_string('notsubmitted', 'block_gu_spdetails');
                                $status->suffix = get_string('class_notsubmitted', 'block_gu_spdetails');
                            }else{
                                $status->status = get_string('submit', 'block_gu_spdetails');
                                $status->suffix = get_string('submit', 'block_gu_spdetails');
                                $status->hasstatusurl = true;
                            }
                        }
                    }
                }
                break;
            default:
                break;
        }

        return $status;
    }

    /**
     * Returns the grading object of an assessment
     *
     * @param string $modname
     * @param stdClass $activity
     * @param stdClass $gradeitem
     * @return stdClass Object containing grade text (could be actual grade or grading due date),
     *         hasgrade, isprovisional
     */
    public static function return_grading($modname, $activity, $gradeitem) {
        $grading = new stdClass;
        $grading->grade = 0;
        $grading->hasgrade = false;
        $grading->isprovisional = false;

        if($activity->graded) {
            $grademin = $gradeitem->grademin;
            $grademax = $gradeitem->grademax;
            $intgrade = round($activity->grade);
            $grading->hasgrade = true;
            $grading->isprovisional = ($modname === 'quiz' && $activity->submissionslocked) ? false :
                                      (($activity->gradeinformation) ? false : true);

            switch($gradeitem->gradetype) {
                // gradetype = value
                case '1':
                    $grading->grade = ($grademax == 22 && $grademin == 0) ?
                                      self::return_22grademaxpoint($intgrade) :
                                      round(($intgrade / ($grademax - $grademin)) * 100, 2).'%';
                    break;
                // gradetype = scale
                case '2':
                    $formattedgrade = $activity->formattedgrade;

                    if(strpos($formattedgrade, ':')){
                        $updatedgrade = explode(':', $formattedgrade);
                        $grading->grade  = $updatedgrade[0];
                    }else{
                        $grading->grade  = $formattedgrade;
                    }
                    break;
                // gradetype = text
                default:
                    $grading->grade = $activity->formattedgrade;
                    break;
            }
        }else{
            $duedate = get_string('due', 'block_gu_spdetails').
                       userdate($activity->gradingduedate, get_string('convertdate', 'block_gu_spdetails'));

            if($activity->gradingduedate > 0) {
                if($activity->feedback === 'MV') {
                    $grading->grade = $duedate;
                }

                if($activity->gradingduedate > time()) {
                    $grading->grade = ($activity->feedback === 'NS') ?
                                      get_string('notavailable', 'block_gu_spdetails') : $duedate;
                }else{
                    $grading->grade = ($activity->feedback === 'NS') ?
                                      get_string('notavailable', 'block_gu_spdetails') :
                                      ucfirst(get_string('overdue', 'block_gu_spdetails'));
                }
            }else{
                $grading->grade = get_string('tobeconfirmed', 'block_gu_spdetails');
            }
        }

        return $grading;
    }

    /**
     * Returns the feedback object of an assessment
     *
     * @param string $modname
     * @param stdClass $activity
     * @param string $assessmenturl
     * @return stdClass Object containing feedback text (could be feedback text for the link or feedback due date),
     *         hasfeedback, feedbackurl
     */
    public static function return_feedback($modname, $activity, $assessmenturl) {
        $feedback = new stdClass;
        $feedback->feedback = null;
        $feedback->hasfeedback = $activity->hasfeedback;

        if($feedback->hasfeedback) {
            $feedback->feedback = get_string('readfeedback', 'block_gu_spdetails');
            switch($modname) {
                case 'assign':
                    $feedback->feedbackurl = ($activity->hasturnitin) ?
                                             $assessmenturl.get_string('id_intro', 'block_gu_spdetails') :
                                             $assessmenturl.get_string('id_pagefooter', 'block_gu_spdetails');
                    break;
                case 'quiz':
                    $feedback->feedbackurl = $assessmenturl.get_string('id_feedback', 'block_gu_spdetails');
                    break;
                case 'workshop':
                    $activityurl = new moodle_url('/mod/'.$modname.'/submission.php', array('cmid' => $activity->cmid));
                    $feedback->feedbackurl = $activityurl.get_string('id_pagefooter', 'block_gu_spdetails');
                    break;
                default:
                    $feedback->feedbackurl = $assessmenturl.get_string('id_pagefooter', 'block_gu_spdetails');
                    break;
            }
        }else{
            $duedate = get_string('due', 'block_gu_spdetails').
                       userdate($activity->gradingduedate, get_string('convertdate', 'block_gu_spdetails'));

            if($activity->gradingduedate > 0) {
                if($activity->feedback === 'MV') {
                    $feedback->feedback = $duedate;
                }

                if($activity->gradingduedate > time()) {
                    $feedback->feedback = ($activity->feedback === 'NS') ?
                                          get_string('notavailable', 'block_gu_spdetails') : $duedate;
                }else{
                    $feedback->feedback = ($activity->feedback === 'NS') ?
                                          get_string('notavailable', 'block_gu_spdetails') :
                                          ucfirst(get_string('overdue', 'block_gu_spdetails'));
                }
            }else{
                $feedback->feedback = get_string('tobeconfirmed', 'block_gu_spdetails');
            }
        }

        return $feedback;
    }

    /**
     * Returns a corresponding value for grades with gradetype = "value" and grademax = "22"
     *
     * @param int $grade
     * @return string
     */
    public static function return_22grademaxpoint($grade) {
        $values = array('H', 'G2', 'G1', 'F3', 'F2', 'F1',
                        'E3', 'E2', 'E1', 'D3', 'D2', 'D1',
                        'C3', 'C2', 'C1', 'B3', 'B2', 'B1',
                        'A5', 'A4', 'A3', 'A2', 'A1');
        return $values[$grade];
    }
}
