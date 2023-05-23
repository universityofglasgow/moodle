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

        if ($coursetype=="past") {
          $coursetypewhere = " AND ( c.enddate + (86400 * 30) <=" . $currentdate . " AND c.enddate!=0 )";
        }
        if ($coursetype=="current") {
          $coursetypewhere = " AND ( c.enddate + (86400 * 30) >" . $currentdate . " OR c.enddate=0 )";
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

                    /*
                    // QUIZ
                    if ($coursetype=="past") {
//                        $sql_quizclose = 'SELECT COUNT(*) AS cnt_quizclose FROM {quiz} WHERE course=' . $courseid . ' AND timeclose!=0 AND timeclose>=' . $plusonemonth;
                        $sql_quizclose = 'SELECT COUNT(*) AS cnt_quizclose FROM {quiz} WHERE course=' . $courseid . ' AND timeclose!=0 AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(timeclose))) <=' . $currentdate;
//                        " AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(c.enddate))) <=" . $currentdate;
                        $arr_quizclose = $DB->get_record_sql($sql_quizclose);
                        $cnt_quizclose = $arr_quizclose->cnt_quizclose;

                        $cnt_totalclose += $cnt_quizclose;

                    // ASSIGN
                        $sql_assignclose = 'SELECT COUNT(*) AS cnt_assignclose FROM {assign} WHERE course=' . $courseid . ' AND duedate!=0 AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(duedate))) <=' . $currentdate;
                        $arr_assignclose = $DB->get_record_sql($sql_assignclose);
                        $cnt_assignclose = $arr_assignclose->cnt_assignclose;

                        $cnt_totalclose += $cnt_assignclose;

                    // FORUM
                        $sql_forumclose = 'SELECT COUNT(*) AS cnt_forumclose FROM {forum} WHERE course=' . $courseid . ' AND duedate!=0 AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(duedate))) <=' . $currentdate;
                        $arr_forumclose = $DB->get_record_sql($sql_forumclose);
                        $cnt_forumclose = $arr_forumclose->cnt_forumclose;

                        $cnt_totalclose += $cnt_forumclose;

                    // WORKSHOP
                        $sql_workshopclose = 'SELECT COUNT(*) AS cnt_workshopclose FROM {workshop} WHERE course=' . $courseid . ' AND assessmentend!=0 AND UNIX_TIMESTAMP(TIMESTAMPADD(MONTH, 1, from_unixtime(assessmentend))) <=' . $currentdate;
                        $arr_workshopclose = $DB->get_record_sql($sql_workshopclose);
                        $cnt_workshopclose = $arr_workshopclose->cnt_workshopclose;

                        $cnt_totalclose += $cnt_workshopclose;

                    }
*/
//echo "<br/>" . $courseid . " / " . $cnt_totalclose . "<br/>";


                    //if ($cnt_totalclose!=0) {
                      array_push($studentcourses, $courseid);
                    //}

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

    $courses = newassessments_statistics::return_enrolledcourses($userid, "current");

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

    $sql_assign_due1 = 'SELECT id,duedate,course FROM {assign} WHERE duedate < ' . $current_time . ' AND course IN (' . $courseids . ') AND duedate!=0';


    //$assign_due1_param = array("duedate"=>$current_time + (86400*30));
    //$arr_assign_due1 = $DB->get_records_sql($sql_assign_due1, $assign_due1_param);
    $arr_assign_due1 = $DB->get_records_sql($sql_assign_due1);
// echo $sql_assign_due1 ;
// echo "<pre>";
// print_r($arr_assign_due1);
// exit;
    foreach ($arr_assign_due1 as $key_assign_due1) {
      $extensiondate = $key_assign_due1->duedate;
       $sql_assign_grantext = 'SELECT * FROM {assign_user_flags} WHERE assignment=' . $key_assign_due1->id . ' AND userid=' . $userid;
// echo "<br/>";
//       $param_grantext = array("assignment"=>$key_assign_due1->id, "userid"=>$userid);

       $arr_assign_grantext = $DB->get_record_sql($sql_assign_grantext);

       if (!empty($arr_assign_grantext)) {
          $extensiondate = $arr_assign_grantext->extensionduedate;
       }

//echo "<br/>" . $key_assign_due1->id . " / " . $userid . " // " . $extensiondate . " /// " . $current_time . "<br/>";
       if ($extensiondate!=0 && $extensiondate < $current_time) {
         $sql_assign_due2 = 'SELECT * FROM {assign_submission} WHERE status!="submitted" AND userid=' . $userid . ' AND assignment=' . $key_assign_due1->id;
         $arr_assign_due2 = $DB->get_records_sql($sql_assign_due2);
           if (!empty($arr_assign_due2)) {
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


public static function return_gradestatus($modulename, $iteminstance, $courseid, $itemid, $userid) {

  global $DB, $USER, $CFG;

  $provisionalgrade = 0;
  $convertedgrade = 0;
  $provisional_22grademaxpoint = 0;
  $converted_22grademaxpoint = 0;

  $sql_grade = "SELECT rawgrade,finalgrade FROM {grade_grades} where itemid=" . $itemid . " AND userid=" . $userid;
  // . " AND rawgrade IS NOT NULL AND finalgrade IS NULL";
  $arr_grade = $DB->get_record_sql($sql_grade);

  if (!empty($arr_grade)) {
      if (is_null($arr_grade->rawgrade) && !is_null($arr_grade->finalgrade)) {
          $provisionalgrade = $arr_grade->finalgrade;
      }
      if (!is_null($arr_grade->rawgrade) && is_null($arr_grade->finalgrade)) {
          $provisionalgrade = $arr_grade->rawgrade;
      }
  }

  $sql_provisionalgrade = 'SELECT DISTINCT CONCAT(gg.id," ",gg.userid) as unikid, gi.itemname, gi.iteminfo, gg.itemid,gg.userid, gg.rawgrade, gg.finalgrade
          FROM {grade_items} gi
          LEFT JOIN {grade_grades} gg ON (gi.iteminfo = gg.id AND gi.itemname = "Provisional Grade")
          WHERE gi.iteminfo IS NOT NULL && gi.iteminfo!="" AND gg.itemid IS NOT NULL AND gg.userid=' . $userid;

  $arr_provisionalgrade = $DB->get_record_sql($sql_provisionalgrade);

  if (!empty($arr_provisionalgrade)) {
    if (!is_null($arr_provisionalgrade->rawgrade) && is_null($arr_provisionalgrade->finalgrade)) {
        $provisionalgrade = $arr_provisionalgrade->rawgrade;
    }
    if (is_null($arr_provisionalgrade->rawgrade) && !is_null($arr_provisionalgrade->finalgrade)) {
        $provisionalgrade = $arr_provisionalgrade->finalgrade;
    }
    $provisional_22grademaxpoint = return_22grademaxpoint($provisionalgrade, 1);
  }

  $sql_convertedgrade = 'SELECT DISTINCT CONCAT(gg.id," ",gg.userid) as unikid, gi.itemname, gi.iteminfo, gg.itemid,gg.userid, gg.rawgrade, gg.finalgrade
                  FROM {grade_items} gi
                  LEFT JOIN {grade_grades} gg ON (gi.iteminfo = gg.id AND gi.itemname = "Converted Grade")
                  WHERE gi.iteminfo IS NOT NULL && gi.iteminfo!="" AND gg.itemid IS NOT NULL AND gg.userid=' . $userid;

  $arr_convertedgrade = $DB->get_record_sql($sql_convertedgrade);

                  if (!empty($arr_convertedgrade)) {
                    if (!is_null($arr_convertedgrade->rawgrade) && is_null($arr_convertedgrade->finalgrade)) {
                        $convertedgrade = $arr_convertedgrade->rawgrade;
                    }
                    if (is_null($arr_convertedgrade->rawgrade) && !is_null($arr_convertedgrade->finalgrade)) {
                        $convertedgrade = $arr_convertedgrade->finalgrade;
                    }

                    $converted_22grademaxpoint = return_22grademaxpoint($convertedgrade, 2);

                  }



  $status = "";
  $link = "";
  $duedate = 0;
  $allowsubmissionsfromdate = 0;
  $cutoffdate = 0;
  $gradingduedate = 0;

  if ($modulename=="assign") {
      $arr_assign = $DB->get_record('assign', array('id'=>$iteminstance));

      $cmid = newassessments_statistics::get_cmid('assign', $courseid, $iteminstance);

      if (!empty($arr_assign)) {
        $allowsubmissionsfromdate = $arr_assign->allowsubmissionsfromdate;
        $duedate = $arr_assign->duedate;
        $cutoffdate = $arr_assign->cutoffdate;
        $gradingduedate = $arr_assign->gradingduedate;
      }
      if ($allowsubmissionsfromdate>time()) {
        $status = 'notopen';
      }
      if ($status=="") {
        $arr_assignsubmission = $DB->get_record('assign_submission', array('assignment'=>$iteminstance, 'userid'=>$USER->id));

        if (!empty($arr_assignsubmission)) {
          $status = $arr_assignsubmission->status;

          if ($status=="new") {
            $status = "notsubmitted";
            if (time()>$duedate + (86400 * 30)) {
              $status = 'overdue';
            }
          }

        } else {
          $status = 'tosubmit';

          if (time()>$duedate && $duedate!=0) {
            $status = 'notsubmitted';
          }

          if (time()>$duedate + (86400 * 30) && $duedate!=0) {
            $status = 'overdue';
          }

          $link = $CFG->wwwroot . '/mod/assign/view.php?id=' . $cmid;
        }
      }
  }

  if ($modulename=="forum") {
        $forumsubmissions = $DB->count_records('forum_discussion_subs', array('forum'=>$iteminstance, 'userid'=>$USER->id));

        $cmid = newassessments_statistics::get_cmid('forum', $courseid, $iteminstance);

        if ($forumsubmissions>0) {
            $status = 'submitted';
        } else {
            $status = 'tosubmit';
            $link = $CFG->wwwroot . '/mod/forum/view.php?id=' . $cmid;
        }
    }

    if ($modulename=="quiz") {

          $cmid = newassessments_statistics::get_cmid('quiz', $courseid, $iteminstance);

          $quizattempts = $DB->count_records('quiz_attempts', array('quiz'=>$iteminstance, 'userid'=>$USER->id, 'state'=>'finished'));
          if ($quizattempts>0) {
              $status = 'submitted';
          } else {
              $status = 'tosubmit';
              $link = $CFG->wwwroot . '/mod/quiz/view.php?id=' . $cmid;
          }
    }

    if ($modulename=="workshop") {

          $arr_workshop = $DB->get_record('workshop', array('id'=>$iteminstance));

          $cmid = newassessments_statistics::get_cmid('workshop', $courseid, $iteminstance);

          $workshopsubmissions = $DB->count_records('workshop_submissions', array('workshopid'=>$iteminstance, 'authorid'=>$USER->id));
          if ($workshopsubmissions>0) {
              $status = 'submitted';
          } else {
              $status = 'tosubmit';
              if ($arr_workshop->submissionstart==0) {
                $status = 'notopen';
              }
              $link = $CFG->wwwroot . '/mod/workshop/view.php?id=' . $cmid;
          }
    }

    $arr_grades = $DB->get_record('grade_grades',array('itemid'=>$itemid, 'userid'=>$userid));

    $finalgrade = "";
    if (!empty($arr_grades)) {
        $finalgrade = $arr_grades->finalgrade;
    }

    $gradestatus = array( "status"=>$status,
                          "link"=>$link,
                          "allowsubmissionsfromdate"=>$allowsubmissionsfromdate,
                          "duedate"=>$duedate,
                          "cutoffdate"=>$cutoffdate,
                          "finalgrade"=>$finalgrade,
                          "gradingduedate"=>$gradingduedate,
                          "provisionalgrade"=>$provisionalgrade,
                          "convertedgrade"=>$convertedgrade,
                          "provisional_22grademaxpoint"=>$provisional_22grademaxpoint,
                          "converted_22grademaxpoint"=>$converted_22grademaxpoint,
                        );

    return $gradestatus;

}



}



function get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $userid, $grademax) {
global $CFG, $DB, $USER;

$link = "";
$gradetodisplay = "";

$gradestatus = newassessments_statistics::return_gradestatus($modulename, $iteminstance, $courseid, $itemid, $userid);

$status = $gradestatus["status"];
$link = $gradestatus["link"];
$allowsubmissionsfromdate = $gradestatus["allowsubmissionsfromdate"];
$duedate = $gradestatus["duedate"];
$cutoffdate = $gradestatus["cutoffdate"];
$gradingduedate = $gradestatus["gradingduedate"];

$finalgrade = $gradestatus["finalgrade"];

$cmid = newassessments_statistics::get_cmid($modulename, $courseid, $iteminstance);

// if ($modulename=="assign" || $modulename=="forum") {
//     $cmid = newassessments_statistics::get_cmid($modulename, $courseid, $iteminstance);
// }


if ($finalgrade!=Null) {
    $gradetodisplay = '<span class="graded">' . number_format((float)$finalgrade) . " / " . number_format((float)$grademax) . '</span>' . ' (Provisional)';
    //if ($modulename=="assign" || $modulename=="forum") {
      $link = $CFG->wwwroot . '/mod/'.$modulename.'/view.php?id=' . $cmid . '#page-footer';
    //}
}

if ($finalgrade==Null  && $duedate<time()) {
  if ($status=="notopen" || $status=="notsubmitted") {
      $gradetodisplay = 'To be confirmed';
      $link = "";
  }
  if ($status=="overdue") {
      $gradetodisplay = 'Overdue';
      $link = "";
  }
  if ($status=="notsubmitted") {
      $gradetodisplay = 'Not submitted';
      if ($gradingduedate>time()) {
          $gradetodisplay = "Due " . date("d/m/Y",$gradingduedate);
      }
  }

}

if ($status=="tosubmit") {
    $gradetodisplay = 'To be confirmed';
    $link = "";
}

return array("gradetodisplay"=>$gradetodisplay, "link"=>$link);
//return array("gradetodisplay"=>$gradetodisplay, "link"=>$link);

}

?>
