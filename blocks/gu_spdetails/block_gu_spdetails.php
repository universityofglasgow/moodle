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
        global $CFG, $USER, $OUTPUT;
        $lang = 'block_gu_spdetails';
        $userid = $USER->id;

        $courses = enrol_get_all_users_courses($userid, true);
        $courseids = array_column($courses, 'id');
        $activities = array('assign', 'quiz', 'workshop');

        $assessments = (count($courseids) > 0) ? self::retrieve_assessments($courseids, $activities) :
                                                 array();
        $hasassessments = !empty($assessments) ? true : false;
        $assessments_data = array();

        foreach($assessments as $a) {
            $a->courserecord = self::retrieve_courserecord($a->course);
            $a->courserecord->coursename = $a->courserecord->shortname.' '.$a->courserecord->fullname;
            $a->courserecord->url = self::return_courseurl($a->course);

            $a->assessment = self::retrieve_assessmentrecord($a->name, $a->instance, $userid);
            $a->assessment->categoryname = property_exists($a->assessment, 'categoryname') ?
                                           (($a->assessment->categoryname != '?') ?
                                           $a->assessment->categoryname :
                                           get_string('emptyvalue', $lang)) :
                                           get_string('emptyvalue', $lang);
            $a->assessment->startdate = property_exists($a->assessment, 'startdate') ? $a->assessment->startdate : null;
            $a->assessment->duedate = property_exists($a->assessment, 'duedate') ? $a->assessment->duedate : null;
            $a->assessment->overrideduedate = property_exists($a->assessment, 'overrideduedate') ? $a->assessment->overrideduedate : null;
            $a->assessment->actualduedate = ($a->assessment->overrideduedate) ? $a->assessment->overrideduedate : $a->assessment->duedate;
            $a->assessment->extensionduedate = ($a->name == 'assign') ? self::retrieve_extendedduedate($userid, $a->instance) : null;
            $a->assessment->hasextension = ($a->assessment->extensionduedate) ? true : false;
            $a->assessment->weight = property_exists($a->assessment, 'weight') ? $a->assessment->weight : null;
            $a->assessment->gradeid = property_exists($a->assessment, 'gradeid') ? $a->assessment->gradeid : null;
            $a->assessment->gradingduedate = property_exists($a->assessment, 'gradingduedate') ?
                                             $a->assessment->gradingduedate : null;
            $submission = self::retrieve_submission($a->name, $a->instance, $userid);
            $submission->status = property_exists($submission, 'status') ? $submission->status : null;

            $a->formatted = new stdClass();
            $a->grades = new stdClass();
            $a->feedback = new stdClass();
            $a->formatted->weight = ($a->assessment->weight) ? (($a->assessment->weight) * 100).'%' :
                                        get_string('emptyvalue', $lang);
            $a->formatted->duedate = ($a->assessment->actualduedate) ?
                                        userdate($a->assessment->actualduedate,  get_string('convertdate', $lang)) :
                                        get_string('emptyvalue', $lang);
            $a->formatted->extensionduedate = ($a->assessment->extensionduedate) ?
                                                userdate($a->assessment->extensionduedate,  get_string('convertdate', $lang)) :
                                                null;
            $a->grades = self::retrieve_grades($a->name, $a->assessment->gradeid, $userid);
            if ($a->name == 'quiz') {
                $a->grades->feedback = $a->assessment->feedback;
            }
            $a->grades->finalgrade = property_exists($a->grades, 'finalgrade') ? $a->grades->finalgrade : null;
            $a->assessment->gradeid = array_key_exists('gradeid', $a->assessment) ? $a->assessment->gradeid : null;
            $a->grades->feedback = property_exists($a->grades, 'feedback') ? $a->grades->feedback : null;
            $a->grades->gradetext = ($a->assessment->gradingduedate) ?
                                    self::return_grade($a->grades->finalgrade, $a->assessment->gradingduedate) :
                                    get_string('emptyvalue', $lang);
            $a->feedback = ($a->assessment->gradingduedate) ?
                            self::return_feedback($a->grades->feedback, $a->assessment->gradingduedate) :
                            get_string('emptyvalue', $lang);

            $a->submission = self::return_status($submission->status,
                                                    $a->assessment->startdate,
                                                    $a->assessment->duedate,
                                                    $a->grades->finalgrade);
            $a->assessment->url = self::return_assessmenturl($a->name, $a->id, $a->submission->hasurl);

            array_push($assessments_data, $a);
        }

        $templatecontext = (array)[
            'assessments'       => json_encode($assessments_data),
            'data'              => $assessments_data,
            'hasassessments'    => $hasassessments,
            'header_course'     => get_string('header_course', $lang),
            'header_assessment' => get_string('header_assessment', $lang),
            'header_type'       => get_string('header_type', $lang),
            'header_weight'     => get_string('header_weight', $lang),
            'header_duedate'    => get_string('header_duedate', $lang),
            'header_status'     => get_string('header_status', $lang),
            'header_grade'      => get_string('header_grade', $lang),
            'header_feedback'   => get_string('header_feedback', $lang),
            'noassessments'     => get_string('noassessments', $lang),
            'sort'              => get_string('sort', $lang),
            'sort_course'       => get_string('sort_course', $lang),
            'sort_date'         => get_string('sort_date', $lang),
            'noassessments_img' => '../blocks/gu_spdetails/pix/assignment.svg',
            'downarrow_img'     => '../blocks/gu_spdetails/pix/down-arrow.svg'
        ];

        $this->content         = new stdClass();
        $this->content->text   = $OUTPUT->render_from_template('block_gu_spdetails/spdetails', $templatecontext);

        //adding js/css file
        global $PAGE;

        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/blocks/gu_spdetails/lib.js'));
        $PAGE->requires->css('/blocks/gu_spdetails/styles.css');
     
        return $this->content;
    }

    /**
     * Retrieves specific Course Modules from database.
     *
     * @param array $courseids Course IDs
     * @param array $activities Array of Activities (e.g. 'assign', 'quiz') 
     * @return array $assessments Course Modules 
     */
    public static function retrieve_assessments($courseids, $activities) {
        global $DB, $CFG;

        $assessments = array();
        list($inactivities, $aparams) = $DB->get_in_or_equal($activities, SQL_PARAMS_NAMED);
        list($incourses, $cparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        $params = array();
        $params += $aparams;
        $params += $cparams;

        $sql = "SELECT DISTINCT mcm.id, mcm.course, mcm.instance, mm.name,
                    mcm.completionexpected
                    FROM `". $CFG->prefix ."course_modules` mcm
                    JOIN `". $CFG->prefix ."modules` mm ON mm.id = mcm.module
                    WHERE mm.name {$inactivities}
                    AND mcm.course {$incourses}";

        if ($assessments = $DB->get_records_sql($sql, $params)){
            return $assessments;
        } else {
            return $assessments;
        }
    }

    /**
     * Retrieves Course object based on a specific Course ID.
     *
     * @param int $courseid Course ID
     * @return mixed $courseinfo Course object containing a course's `fullname` and `shortname`
     *  if a record is found, false if otherwise
     */
    public static function retrieve_courserecord($courseid) {
        global $DB;

        $courserecord = $DB->get_record('course', array('id' => $courseid), 'fullname, shortname');
        return $courserecord;
    }

    /**
     * Retrieves Assessment object based on Activity Name and Activity Instance.
     *
     * @todo Update `workshop` DB query, needs JOIN with `grade_items`
     * @param string $name Activity Name (e.g. 'assign', 'quiz')
     * @param int $instance Activity Instance
     * @return mixed $courseinfo Assessment object if the Activity Name matches a case
     *  and a corresponding record is found, false if no record is found
     */
    public static function retrieve_assessmentrecord($name, $instance, $userid) {
        global $DB, $CFG;

        switch($name) {
            case 'assign':
                $sql = "SELECT ma.id, ma.name,
                        ma.allowsubmissionsfromdate as `startdate`,
                        ma.duedate, ma.cutoffdate, ma.gradingduedate,
                        mgi.id as `gradeid`, mgi.aggregationcoef as `weight`,
                        mao.allowsubmissionsfromdate as `overridestartdate`,
                        mao.duedate as `overrideduedate`,
                        mao.cutoffdate as `overridecutoffdate`,
                        mgc.fullname as `categoryname`
                        FROM `". $CFG->prefix ."assign` ma
                        JOIN `". $CFG->prefix ."grade_items` mgi
                        ON mgi.iteminstance = ma.id AND mgi.itemmodule = ?
                        JOIN `". $CFG->prefix ."grade_categories` mgc
						ON mgc.id = mgi.categoryid AND mgc.courseid = mgi.courseid
                        LEFT JOIN `". $CFG->prefix ."assign_overrides` mao
                        ON mao.userid = ? AND mao.assignid = ?
                        WHERE ma.id = ?";
                $assessmentrecord = ($assessmentrecord = $DB->get_record_sql($sql,
                                     array($name, $userid, $instance, $instance))) ?
                                     $assessmentrecord : new stdClass();
                break;
            case 'quiz':
                $sql = "SELECT DISTINCT mq.id as `gradeid`, mq.name,
                        mq.timeopen as `startdate`,
                        mq.timeclose as `duedate`, mq.timelimit,
                        (mq.timeclose + (86400 * 14)) as `gradingduedate`,
                        mgi.aggregationcoef as `weight`, mgg.feedback,
                        mqo.timeopen as `overridestartdate`,
                        mqo.timeclose as `overrideduedate`,
                        mqo.timelimit as `overridelimit`,
                        mgc.fullname as `categoryname`
                        FROM `". $CFG->prefix ."quiz` mq
                        JOIN `". $CFG->prefix ."grade_items` mgi
                        ON mgi.iteminstance = mq.id AND mgi.itemmodule = ?
                        JOIN `". $CFG->prefix ."grade_grades` mgg
                        ON mgg.itemid = mgi.id
                        JOIN `". $CFG->prefix ."grade_categories` mgc
						ON mgc.id = mgi.categoryid AND mgc.courseid = mgi.courseid
                        LEFT JOIN `". $CFG->prefix ."quiz_overrides` mqo
                        ON mqo.userid = ? AND mqo.quiz = ?
                        WHERE mq.id = ?";
                $assessmentrecord = ($assessmentrecord = $DB->get_record_sql($sql,
                                     array($name, $userid, $instance, $instance))) ?
                                     $assessmentrecord : new stdClass();
                break;
            case 'workshop':
                $assessmentrecord = $DB->get_record('workshop', array('id' => $instance),
                                                    'name, id as `gradeid`, submissionstart as `startdate`, ' .
                                                    'submissionend as `duedate`, assessmentend as `gradingduedate`');
                break;
            default:
                $assessmentrecord = ($assessmentrecord = $DB->get_record($name, array('id' => $instance), 'name')) ?
                                 $assessmentrecord : new stdClass();
                break;
        }

        return $assessmentrecord;
    }

    /**
     * Returns Course URL
     *
     * @param int $courseid Course ID
     * @return string $courseurl Course URL
     */
    public static function return_courseurl($courseid) {
        global $CFG;
        
        $courseurl = $CFG->wwwroot."/course/view.php?id=".$courseid;
        return $courseurl;
    }

    /**
     * Returns Assessment URL
     *
     * @param string $name Activity Name (e.g. 'assign', 'quiz')
     * @param int $id Activity Module ID
     * @param boolean $hasurl Submission object hasurl
     * @return string $assessmenturl Assessment URL
     */
    public static function return_assessmenturl($name, $id, $hasurl) {
        global $CFG;

        $assessmenturl = $hasurl ?
            $CFG->wwwroot."/mod/".$name."/view.php?id=".$id."&action=editsubmission" :
            $CFG->wwwroot."/mod/".$name."/view.php?id=".$id;
        return $assessmenturl;
    }

    /**
     * Retrieves Assessment Status from database.
     *
     * @todo Query for Workshop activity type
     * @param string $name Activity Name (e.g. 'assign', 'quiz')
     * @param int $instance Activity Instance
     * @param int $userid User ID
     * @return stdClass $status Assessment Status object if the Activity has a corresponding
     *  Submission table and a corresponding record is found, return empty object if no
     *  record is found
     */
    public static function retrieve_submission($name, $instance, $userid) {
        global $DB;

        switch($name) {
            case 'assign':
                $statusrecord = $DB->get_record('assign_submission',
                                                array('assignment' => $instance, 'userid' => $userid),
                                                'status');
                $statusrecord = is_bool($statusrecord) ? new stdClass() : $statusrecord;
                break;
            case 'quiz': 
                $record = $DB->get_record('quiz_grades',
                                                array('quiz' => $instance, 'userid' => $userid));
                $statusrecord = new stdClass();
                $statusrecord->status = $record ? 'submitted' : null ;
                break;
            case 'workshop':
                $record = $DB->get_record('workshop_submissions',
                                                array('workshopid' => $instance, 'authorid' => $userid));
                $statusrecord = new stdClass();
                $statusrecord->status = $record ? 'submitted' : null ;
                break;
            default:
                $statusrecord = new stdClass();
                break;
        }

        return $statusrecord;
    }

    /**
     * Returns actual Assessment Status text to display on UI
     *  and corresponding class element modifier.
     *
     * @param string $status Assessment Status (e.g. 'new', 'submitted')
     * @param int $startdate Start Date for activity to be started
     * @param int $enddate Due Date
     * @param float $grade Final Grade
     * @return stdClass $submission Object containing Status text and class suffix
     */
    public static function return_status($status, $startdate, $enddate, $grade) {
        global $DB;
        $lang = 'block_gu_spdetails';
        $submission = new stdClass();
        $submission->hasurl = false;

        if($status == 'submitted') {
            $submission->status = ($grade) ? get_string('status_graded', $lang) : $status;
            $submission->suffix = ($grade) ? get_string('status_graded', $lang) : $status;
        }else{
            if(time() > $startdate && $enddate) {
                $submission->status = (time() <= $enddate) ? get_string('status_submit', $lang) :
                                                             get_string('status_overdue', $lang);
                $submission->suffix = (time() <= $enddate) ? get_string('status_submit', $lang) :
                                                             get_string('status_overdue', $lang);
                $submission->hasurl = (time() <= $enddate) ? true : false;
            }else{
                $submission->status = get_string('status_notopen', $lang);
                $submission->suffix = get_string('class_notopen', $lang);
            }
        }

        return $submission;
    }

    /**
     * Retrieves Grades from database.
     *
     * @param string $name Activity Name (e.g. 'assign', 'quiz')
     * @param int $gradeid Grade ID
     * @param int $userid User ID
     * @return stdClass $gradesrecord Grades object if the Activity has a corresponding
     *  Grade Grades table and a corresponding record is found, return empty object if no
     *  record is found.
     */
    public static function retrieve_grades($name, $gradeid, $userid) {
        global $DB;

        switch($name) {
            case 'assign':
                $gradesrecord = $DB->get_record('grade_grades',
                                                array('itemid' => $gradeid, 'userid' => $userid),
                                                'finalgrade, feedback');

                $gradesrecord = is_bool($gradesrecord) ? new stdClass() : $gradesrecord;
                break;
            case 'quiz':
                $gradesrecord = $DB->get_record('quiz_grades',
                                                array('quiz' => $gradeid, 'userid' => $userid),
                                                '*');
                $gradesrecord = is_bool($gradesrecord) ? new stdClass() : $gradesrecord;
                $gradesrecord->finalgrade = property_exists($gradesrecord, 'grade') ? $gradesrecord->grade : null;
                break;
            case 'workshop':
                $gradesrecord = $DB->get_record('workshop_submissions',
                                                array('workshopid' => $gradeid, 'authorid' => $userid),
                                                'grade as `finalgrade`, feedbackauthor as `feedback`');

                $gradesrecord = is_bool($gradesrecord) ? new stdClass : $gradesrecord;
                break;
            default:
                $gradesrecord = new stdClass();
                break;
        }

        return $gradesrecord;
    }

    /**
     * Returns Grade or Grading Due Date text
     *
     * @param float $grade
     * @param int $gradingduedate
     * @return mixed $gradetext float value of Grade if the assessment is graded, otherwise return
     *  the Grading Due Date string
     */
    public static function return_grade($grade, $gradingduedate) {
        global $DB;
        $lang = 'block_gu_spdetails';

        $gradetext = $grade ? $grade : 
                     get_string('due', $lang).userdate($gradingduedate,  get_string('convertdate', $lang));
        return $gradingduedate == 0 ? get_string('emptyvalue', $lang) : $gradetext;
    }

    /**
     * Returns Feedback object containing (bool) hasurl and (string) text
     *
     * @todo Return Feedback for Workshop
     * @param string $feedback
     * @param int $gradingduedate
     * @return stdClass $feedbackrecord Feedback object containing (bool) `hasurl` to determine
     *  if a feedback is already provided and (string) `text` to display on UI
     */
    public static function return_feedback($feedback, $gradingduedate) {
        global $DB;
        $lang = 'block_gu_spdetails';
        $feedbackrecord = new stdClass();
        $feedbackrecord->hasurl = false;

        $feedbackrecord->hasurl = $feedback ? true : false;
        $feedbackrecord->text = $feedback ? get_string('readfeedback', $lang) :
                                get_string('due', $lang).userdate($gradingduedate, get_string('convertdate', $lang));
        if ($gradingduedate == 0){
            $feedbackrecord->text = get_string('emptyvalue', $lang);
        }
        return $feedbackrecord;
    }

    public static function retrieve_extendedduedate($userid, $assignid) {
        global $DB, $CFG;

        $extensionduedaterecord = ($extensionduedaterecord = $DB->get_record('assign_user_flags',
                                array('userid' => $userid, 'assignment' => $assignid), 'id, extensionduedate')) ?
                                $extensionduedaterecord : new stdClass();
        $extensionduedate = property_exists($extensionduedaterecord, 'extensionduedate') ?
                            $extensionduedaterecord->extensionduedate : null;
        return $extensionduedate;
    }
}
