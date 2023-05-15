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
 * Contains the DB query methods for UofG Assessments Details block.
 *
 * @package    block_newgu_spdetails
 * @copyright
 * @author
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class newassessments_statistics {

public static function return_enrolledcourses($userid, $coursetype) {
  /*
        global $DB;
        $fields = "c.id";

        $customfieldwhere = "c.visible = 1 AND c.visibleold = 1";
        $enrolmentselect = "SELECT DISTINCT e.courseid FROM {enrol} e
                            JOIN {user_enrolments} ue
                            ON (ue.enrolid = e.id AND ue.userid = ?)";
        $enrolmentjoin = "JOIN ($enrolmentselect) en ON (en.courseid = c.id)";

        $sql = "SELECT $fields FROM {course} c $enrolmentjoin
                WHERE $customfieldwhere";
        $param = array($userid);
        $results = $DB->get_records_sql($sql, $param);

        if ($results) {
            $studentcourses = array();
            foreach ($results as $courseid => $courseobject) {
                    array_push($studentcourses, $courseid);
            }
            return $studentcourses;
        } else {
            return array();
        }
*/

        $currentdate = time();
        $plusonemonth = strtotime("+1 month", $currentdate);

        $coursetypewhere = "";

        global $DB;

       

        $fields = "c.id";
        $customfieldjoin = "JOIN {customfield_field} cff
                            ON cff.shortname = 'show_on_studentdashboard'
                            JOIN {customfield_data} cfd
                            ON (cfd.fieldid = cff.id AND cfd.instanceid = c.id)";
        $customfieldwhere = "cfd.value = 1 AND c.visible = 1 AND c.visibleold = 1";


// // TEMPORARY ->
// $fields = "c.id";
//
// $customfieldwhere = "c.visible = 1 AND c.visibleold = 1";
// $enrolmentselect = "SELECT DISTINCT e.courseid FROM {enrol} e
//                     JOIN {user_enrolments} ue
//                     ON (ue.enrolid = e.id AND ue.userid = ?)";
// $enrolmentjoin = "JOIN ($enrolmentselect) en ON (en.courseid = c.id)";
// // <- TEMPORARY

        if ($coursetype=="past") {
//          $coursetypewhere = " AND c.enddate<=" . $currentdate . " AND c.enddate!=0";

          //$coursetypewhere = " AND c.enddate<=" . $currentdate ;

          $coursetypewhere = " AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(c.enddate))) <=" . $currentdate;

        }
        if ($coursetype=="current") {
//          $coursetypewhere = " AND c.enddate>" . $currentdate . " OR c.enddate=0";
          $coursetypewhere = " AND c.enddate >" . $currentdate;
        }


        $enrolmentselect = "SELECT DISTINCT e.courseid FROM {enrol} e
                            JOIN {user_enrolments} ue
                            ON (ue.enrolid = e.id AND ue.userid = ?)";
        $enrolmentjoin = "JOIN ($enrolmentselect) en ON (en.courseid = c.id)";
        $sql = "SELECT $fields FROM {course} c $customfieldjoin $enrolmentjoin
                WHERE $customfieldwhere $coursetypewhere";
//echo $coursetype . " " . $sql . "<br/><br>";

        $param = array($userid);

        $results = $DB->get_records_sql($sql, $param);

//        print_r($results);


        if ($results) {
            $studentcourses = array();
            foreach ($results as $courseid => $courseobject) {

              $cnt_totalclose = 0;
              $cnt_quizclose = 0;
              $cnt_assignclose = 0;
              $cnt_forumclose = 0;
              $cnt_workshopclose = 0;

                if (newassessments_statistics::return_isstudent($courseid, $userid)) {

                    /* CHECK IF ANY ASSIGNMENT HAS DUEDATE (timeclose) + 1 MONTH IS LESS THAN CURRENTIMESTAMP
                      AND ADD IT IN ARRAY IF CONDITION IS FOR PAST TAB
                    */
                    /* QUIZ */
                    if ($coursetype=="past") {
//                        $sql_quizclose = 'SELECT COUNT(*) AS cnt_quizclose FROM {quiz} WHERE course=' . $courseid . ' AND timeclose!=0 AND timeclose>=' . $plusonemonth;
                        $sql_quizclose = 'SELECT COUNT(*) AS cnt_quizclose FROM {quiz} WHERE course=' . $courseid . ' AND timeclose!=0 AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(timeclose))) <=' . $currentdate;
//                        " AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(c.enddate))) <=" . $currentdate;
                        $arr_quizclose = $DB->get_record_sql($sql_quizclose);
                        $cnt_quizclose = $arr_quizclose->cnt_quizclose;

                        $cnt_totalclose += $cnt_quizclose;

                    /* ASSIGN */
                        $sql_assignclose = 'SELECT COUNT(*) AS cnt_assignclose FROM {assign} WHERE course=' . $courseid . ' AND duedate!=0 AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(duedate))) <=' . $currentdate;
                        $arr_assignclose = $DB->get_record_sql($sql_assignclose);
                        $cnt_assignclose = $arr_assignclose->cnt_assignclose;

                        $cnt_totalclose += $cnt_assignclose;

                    /* FORUM */
                        $sql_forumclose = 'SELECT COUNT(*) AS cnt_forumclose FROM {forum} WHERE course=' . $courseid . ' AND duedate!=0 AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(duedate))) <=' . $currentdate;
                        $arr_forumclose = $DB->get_record_sql($sql_forumclose);
                        $cnt_forumclose = $arr_forumclose->cnt_forumclose;

                        $cnt_totalclose += $cnt_forumclose;

                    /* WORKSHOP */
                        $sql_workshopclose = 'SELECT COUNT(*) AS cnt_workshopclose FROM {workshop} WHERE course=' . $courseid . ' AND assessmentend!=0 AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(assessmentend))) <=' . $currentdate;
                        $arr_workshopclose = $DB->get_record_sql($sql_workshopclose);
                        $cnt_workshopclose = $arr_workshopclose->cnt_workshopclose;

                        $cnt_totalclose += $cnt_workshopclose;

                    }

//echo "<br/>" . $courseid . " / " . $cnt_totalclose . "<br/>";


                    if ($cnt_totalclose!=0) {
                      array_push($studentcourses, $courseid);
                    }

                }
            }
            return $studentcourses;
        } else {
            return array();
        }

    }

    /**
     * Checks if user has capability of a student
     *
     * @param int $courseid
     * @param int $userid
     * @return boolean has_capability
     */
    public static function return_isstudent($courseid, $userid) {
        $context = context_course::instance($courseid);
        return has_capability('moodle/grade:view', $context, $userid, false);
    }

public static function get_stats_counts($userid)
{
    global $DB;

    $courses = newassessments_statistics::return_enrolledcourses($userid, "");

    $courseids = implode(', ', $courses);

    $currentdate = time();
    $total_overdue = 0;
    $total_submissions = 0;
    $total_tosubmit = 0;
    $marked = 0;



    //Submissions

    $sql_assign_submissions = "SELECT count(*) as assignsubmissions FROM {assign_submission} WHERE assignment in (SELECT id FROM {assign} WHERE course IN ('$courseids')) AND userid=" . $userid;
    $arr_assign_submissions = $DB->get_record_sql($sql_assign_submissions);
    $assign_submissions = $arr_assign_submissions->assignsubmissions;

    $sql_forum_submissions = "SELECT count(*) as forumsubmissions FROM {forum_discussions} WHERE forum in (SELECT id FROM {forum} WHERE type!='news' AND course IN ('$courseids')) AND userid=" . $userid;
    $arr_forum_submissions = $DB->get_record_sql($sql_forum_submissions);
    $forum_submissions = $arr_forum_submissions->forumsubmissions;

    $sql_quiz_submissions = "SELECT count(*) as quizsubmissions FROM {quiz_attempts} WHERE quiz in (SELECT id FROM {quiz} WHERE course IN ('$courseids')) AND userid=" . $userid;
    $arr_quiz_submissions = $DB->get_record_sql($sql_quiz_submissions);
    $quiz_submissions = $arr_quiz_submissions->quizsubmissions;

    $sql_workshop_submissions = "SELECT count(*) as workshopsubmissions FROM {workshop_submissions} WHERE workshopid in (SELECT id FROM {workshop} WHERE course IN ('$courseids')) AND authorid=" . $userid;
    $arr_workshop_submissions = $DB->get_record_sql($sql_workshop_submissions);
    $workshop_submissions = $arr_workshop_submissions->workshopsubmissions;

    $total_submissions = $assign_submissions + $forum_submissions + $quiz_submissions + $workshop_submissions;

    //To be submitted
    $assigns = $DB->count_records_sql("SELECT count(*) as assigns FROM {assign} WHERE course IN ('$courseids')");
    $forums = $DB->count_records_sql("SELECT count(*) as forums FROM {forum} WHERE course IN ('$courseids') AND type!='news'");
    $quizzes = $DB->count_records_sql("SELECT count(*) as quizzes FROM {quiz} WHERE course IN ('$courseids')");
    $workshops = $DB->count_records_sql("SELECT count(*) as workshops FROM {workshop} WHERE course IN ('$courseids')");

    // To Submit
    $assign_tosubmit = $assigns - $assign_submissions;
    $forum_tosubmit = $forums - $forum_submissions;
    $quiz_tosubmit = $quizzes - $quiz_submissions;
    $workshop_tosubmit = $workshops - $workshop_submissions;
    $total_tosubmit = $assign_tosubmit + $forum_tosubmit + $quiz_tosubmit + $workshop_tosubmit;


    //Overdue
    $current_time = time();

    $sql_assign_due1 = 'SELECT id FROM {assign} WHERE duedate < ?';
    $assign_due1_param = array($current_time);
    $arr_assign_due1 = $DB->get_records_sql($sql_assign_due1, $assign_due1_param);

    foreach ($arr_assign_due1 as $key_assign_due1) {

       $sql_assign_grantext = 'SELECT * FROM {assign_user_flags} WHERE assignment=? AND userid=?';
       $param_grantext = array($key_assign_due1->id, $userid);
       $arr_assign_grantext = $DB->get_record_sql($sql_assign_grantext, $param_grantext);

       if (!empty($arr_assign_grantext)) {
          $extensiondate = $arr_assign_grantext->extensionduedate;
       }

       if (isset($extensiondate) && $current_time<$extensiondate) {
         $sql_assign_due2 = 'SELECT * FROM {assign_submission} WHERE status!="submitted" AND userid=' . $userid . ' AND assignment=' . $key_assign_due1->id;
         $arr_assign_due2 = $DB->get_records_sql($sql_assign_due2);
           if (empty($arr_assign_due2)) {
              $total_overdue++;
           }
       }

     }

     // WRITE CODES HERE TO FETCH overdue FOR forum, quiz and workshop




     // ASSESSMENTS MARKED
     $marked = $DB->count_records_select('grade_grades', 'finalgrade != "Null" AND usermodified != "Null" AND userid=:userid', ['userid' => $userid]);

     // FINAL STATISTICS TO BE RETURNED
     $counts = new stdClass;
     $counts->submitted = $total_submissions;
     $counts->tobesubmit = $total_tosubmit;
     $counts->overdue = $total_overdue;
     $counts->marked = $marked;

     return $counts;

}

public static function get_cmid($cmodule, $courseid, $instance) {
  // cmodule is module name e.g. quiz, forums etc.
  global $DB;

  $arr_module = $DB->get_record('modules', array('name'=>$cmodule));
  $moduleid = $arr_module->id;

  $arr_coursemodule = $DB->get_record('course_modules', array('course'=>$courseid, 'module'=>$moduleid, 'instance'=>$instance));

  $cmid = $arr_coursemodule->id;

  return $cmid;

}

/**
 * Returns a corresponding value for grades with gradetype = "value" and grademax = "22"
 *
 * @param int $grade
 * @param int $idnumber = 1 - Schedule A, 2 - Schedule B
 * @return string 22-grade max point value
 */
public static function return_22grademaxpoint($grade, $idnumber) {
    $values = array('H', 'G2', 'G1', 'F3', 'F2', 'F1', 'E3', 'E2', 'E1', 'D3', 'D2', 'D1',
                    'C3', 'C2', 'C1', 'B3', 'B2', 'B1', 'A5', 'A4', 'A3', 'A2', 'A1');
    $value = $values[$grade];
    if ($idnumber == 2) {
        $stringarray = str_split($value);
        if ($stringarray[0] != 'H') {
            $value = $stringarray[0] . '0';
        }
    }
    return $value;
}


}

?>
