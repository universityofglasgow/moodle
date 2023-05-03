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

public static function return_enrolledcourses($userid) {
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
    }

public static function get_stats_counts($userid)
{
    global $DB;

    $courses = newassessments_statistics::return_enrolledcourses($userid);

    $courseids = implode(', ', $courses);

    $currentdate = time();
    $total_overdue = 0;

    //Submissions
    $assign_submissions = $DB->count_records('assign_submission', ['userid' => $userid]);
    $forum_submissions = $DB->count_records('forum_discussions', ['userid' => $userid]);
    $quiz_submissions = $DB->count_records('quiz_attempts', ['userid' => $userid, 'state' => 'finished']);
    $workshop_submissions = $DB->count_records('workshop_submissions', ['authorid' => $userid]);

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

}

?>
