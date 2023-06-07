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
//        $plusonemonth = strtotime("+1 month", $currentdate);

        $coursetypewhere = "";

        global $DB;

        $fields = "c.id";
/*        $customfieldjoin = "JOIN {customfield_field} cff
                            ON cff.shortname = 'show_on_studentdashboard'
                            JOIN {customfield_data} cfd
                            ON (cfd.fieldid = cff.id AND cfd.instanceid = c.id)"; */
//        $customfieldwhere = "cfd.value = 1 AND c.visible = 1 AND c.visibleold = 1";
        $fieldwhere = "c.visible = 1 AND c.visibleold = 1";

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
        /*
        $sql = "SELECT $fields FROM {course} c $customfieldjoin $enrolmentjoin
                WHERE $customfieldwhere $coursetypewhere";
        */
        $sql = "SELECT $fields FROM {course} c $enrolmentjoin
                WHERE $fieldwhere $coursetypewhere";
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


/**
 * Returns the 'assessment type'
 *
 * @param string $gradecategoryname
 * @param int $aggregationcoef
 * @return string 'Formative', 'Summative', or '—'
 */
public static function return_assessmenttype($gradecategoryname, $aggregationcoef) {
    $type = strtolower($gradecategoryname);
    $hasweight = !empty((float)$aggregationcoef);

    if (strpos($type, 'summative') !== false || $hasweight) {
        $assessmenttype = get_string('summative', 'block_newgu_spdetails');
    } else if (strpos($type, 'formative') !== false) {
        $assessmenttype = get_string('formative', 'block_newgu_spdetails');
    } else {
        $assessmenttype = get_string('emptyvalue', 'block_newgu_spdetails');
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
 * @param string $subcategoryparentfullname
 * @return string Weight (in percentage), or '—' if empty
 */
public static function return_weight($assessmenttype, $aggregation, $aggregationcoef,
                                     $aggregationcoef2, $subcategoryparentfullname) {
    $summative = get_string('summative', 'block_newgu_spdetails');

    // If $aggregation == '10', meaning 'Weighted mean of grades' is used.
    $weight = ($aggregation == '10') ?
                (($aggregationcoef > 1) ? $aggregationcoef : $aggregationcoef * 100) :
                (($assessmenttype === $summative || $subcategoryparentfullname === $summative) ?
                    $aggregationcoef2 * 100 : 0);

    $finalweight = ($weight > 0) ? round($weight, 2).'%' : get_string('emptyvalue', 'block_newgu_spdetails');

    return $finalweight;
}


public static function fetch_itemsnotvisibletouser($userid, $strcourses) {

  global $DB;

  $courses = explode(",", $strcourses);

  $itemsnotvisibletouser = array();

  $itemsnotvisibletouser[] = 0;

  foreach ($courses as $courseid) {

    $modinfo = get_fast_modinfo($courseid);
    $cms = $modinfo->get_cms();

    foreach($cms as $cm) {
// Check if course module is visible to the user.
      $iscmvisible = $cm->uservisible;

                if (!$iscmvisible) {
                  $sql_modinstance = 'SELECT cm.id, cm.instance, cm.module, m.name FROM {modules} m, {course_modules} cm WHERE cm.id=' . $cm->id . ' AND cm.module=m.id';
                  $arr_modinstance = $DB->get_record_sql($sql_modinstance);
                  $instance = $arr_modinstance->instance;
                  $module = $arr_modinstance->module;
                  $cmid = $arr_modinstance->id;
                  $modname = $arr_modinstance->name;

                  $sql_gradeitemtoexclude = "SELECT id FROM {grade_items} WHERE courseid = " . $courseid . " AND itemmodule='". $modname ."' AND iteminstance=" . $instance;
                  $arr_gradeitemtoexclude = $DB->get_record_sql($sql_gradeitemtoexclude);
                  if (!empty($arr_gradeitemtoexclude)) {
                    $itemsnotvisibletouser[] = $arr_gradeitemtoexclude->id;
                  }
                }

      }
  }
  $str_itemsnotvisibletouser = implode(",",$itemsnotvisibletouser);

  return $str_itemsnotvisibletouser;

}


public static function return_gradestatus($modulename, $iteminstance, $courseid, $itemid, $userid) {

  global $DB, $USER, $CFG;

  $status = "";
  $link = "";
  $duedate = 0;
  $allowsubmissionsfromdate = 0;
  $cutoffdate = 0;
  $gradingduedate = 0;
  $provisionalgrade = 0;
  $convertedgrade = 0;
  $provisional_22grademaxpoint = 0;
  $converted_22grademaxpoint = 0;

  $rawgrade = 0;
  $finalgrade = 0;

  $sql_grade = "SELECT rawgrade,finalgrade FROM {grade_grades} where itemid=" . $itemid . " AND userid=" . $userid;
  // . " AND rawgrade IS NOT NULL AND finalgrade IS NULL";
  $arr_grade = $DB->get_record_sql($sql_grade);

  if (!empty($arr_grade)) {
    $rawgrade = $arr_grade->rawgrade;
    $finalgrade = $arr_grade->finalgrade;
  }

  if (!empty($arr_grade)) {
      if (is_null($arr_grade->rawgrade) && !is_null($arr_grade->finalgrade)) {
          $provisionalgrade = $arr_grade->finalgrade;
      }
      if (!is_null($arr_grade->rawgrade) && is_null($arr_grade->finalgrade)) {
          $provisionalgrade = $arr_grade->rawgrade;
      }
  }

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
            if (time()>$duedate + (86400 * 30) && $duedate!=0) {
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

    if (!empty($arr_grades)) {
        $finalgrade = $arr_grades->finalgrade;
    }

    if (floor($rawgrade)>0 && floor($finalgrade)==0) {
      $provisional_22grademaxpoint = newassessments_statistics::return_22grademaxpoint((floor($rawgrade))-1, 1);
    }
    if (floor($finalgrade)>0) {
      $converted_22grademaxpoint = newassessments_statistics::return_22grademaxpoint((floor($finalgrade))-1, 1);
    }

//    echo "<br/>XXXX<br/>" . floor($rawgrade) . " / " . floor($finalgrade) . " // " . $converted_22grademaxpoint . "<br/>XXXX<br/>";

    $gradestatus = array( "status"=>$status,
                          "link"=>$link,
                          "allowsubmissionsfromdate"=>$allowsubmissionsfromdate,
                          "duedate"=>$duedate,
                          "cutoffdate"=>$cutoffdate,
                          "rawgrade"=>$rawgrade,
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



function get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $userid, $grademax, $gradetype) {
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

$rawgrade = $gradestatus["rawgrade"];
$finalgrade = $gradestatus["finalgrade"];

$provisional_22grademaxpoint = $gradestatus["provisional_22grademaxpoint"];
$converted_22grademaxpoint = $gradestatus["converted_22grademaxpoint"];

$cmid = newassessments_statistics::get_cmid($modulename, $courseid, $iteminstance);

if ($finalgrade!=Null) {
    if ($gradetype==1) {
      $gradetodisplay = '<span class="graded">' . number_format((float)$finalgrade) . " / " . number_format((float)$grademax) . '</span>' . ' (Provisional)';
    }
    if ($gradetype==2) {
      $gradetodisplay = '<span class="graded">' . $converted_22grademaxpoint . '</span>' . ' (Provisional)';
    }
    $link = $CFG->wwwroot . '/mod/'.$modulename.'/view.php?id=' . $cmid . '#page-footer';

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

return array("gradetodisplay"=>$gradetodisplay, "link"=>$link, "provisional_22grademaxpoint"=>$provisional_22grademaxpoint, "converted_22grademaxpoint"=>$converted_22grademaxpoint, "finalgrade"=>floor($finalgrade), "rawgrade"=>floor($rawgrade));
//return array("gradetodisplay"=>$gradetodisplay, "link"=>$link);

}


function get_weight($courseid,$categoryid,$aggregationcoef,$aggregationcoef2) {
  global $DB;

  $arr_gradecategory = $DB->get_record('grade_categories',array('courseid'=>$courseid, 'id'=>$categoryid));
  if (!empty($arr_gradecategory)) {
    $gradecategoryname = $arr_gradecategory->fullname;
    $aggregation = $arr_gradecategory->aggregation;
  }

  $finalweight = "—";

  $assessmenttype = newassessments_statistics::return_assessmenttype($gradecategoryname, $aggregationcoef);

  $summative = get_string('summative', 'block_newgu_spdetails');

  $weight = ($aggregation == '10') ?
              (($aggregationcoef > 1) ? $aggregationcoef : $aggregationcoef * 100) :
              (($assessmenttype === $summative) ?
                  $aggregationcoef2 * 100 : 0);

  $finalweight = ($weight > 0) ? round($weight, 2).'%' : get_string('emptyvalue', 'block_newgu_spdetails');


return $finalweight;
}


function get_duedateorder() {

global $USER, $DB, $CFG;

$currentcourses = newassessments_statistics::return_enrolledcourses($USER->id, "current");
$str_currentcourses = implode(",", $currentcourses);

$currentxl = array();

$str_itemsnotvisibletouser = newassessments_statistics::fetch_itemsnotvisibletouser($USER->id, $str_currentcourses);

$sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_currentcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id';

$arr_cc = $DB->get_records_sql($sql_cc);

$arr_order = array();

foreach ($arr_cc as $key_cc) {
  $cmid = $key_cc->id;
  $modulename = $key_cc->itemmodule;
  $iteminstance = $key_cc->iteminstance;
  $courseid = $key_cc->courseid;
  $itemid = $key_cc->id;

  // DUE DATE
  $duedate = 0;
  $extspan = "";
  $extensionduedate = 0;
  $str_duedate = "—";

  // READ individual TABLE OF ACTIVITY (MODULE)
  if ($modulename!="") {
    $arr_duedate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));

  if (!empty($arr_duedate)) {
    if ($modulename=="assign") {
      $duedate = $arr_duedate->duedate;

      $arr_userflags = $DB->get_record('assign_user_flags', array('userid'=>$USER->id, 'assignment'=>$iteminstance));

      if ($arr_userflags) {
      $extensionduedate = $arr_userflags->extensionduedate;
      if ($extensionduedate>0) {
        $extspan = '<a href="javascript:void(0)" title="' . get_string('extended', 'block_newgu_spdetails') . '" class="extended">*</a>';
      }
      }

    }
    if ($modulename=="forum") {
      $duedate = $arr_duedate->duedate;
    }
    if ($modulename=="quiz") {
      $duedate = $arr_duedate->timeclose;
    }
    if ($modulename=="workshop") {
      $duedate = $arr_duedate->submissionend;
    }
  }
}

  if ($duedate!=0) {
    $str_duedate = date("d/m/Y", $duedate) . $extspan;
  }

  $arr_order[$itemid] = $duedate;
//    $arr_order2[$duedate] = $itemid;
}

asort($arr_order);

$str_order = "";
foreach ($arr_order as $key_order=>$value) {
$str_order .= $key_order . ",";
}
$str_order = rtrim($str_order,",");
return $str_order;
}


function get_startenddateorder() {

      global $USER, $DB, $CFG;

      $pastcourses = newassessments_statistics::return_enrolledcourses($USER->id, "past");
      $str_pastcourses = implode(",", $pastcourses);

      $pastxl = array();

      if ($str_pastcourses!="") {

      $str_itemsnotvisibletouser = newassessments_statistics::fetch_itemsnotvisibletouser($USER->id, $str_pastcourses);

      $sql_cc = 'SELECT gi.*, c.fullname as coursename FROM {grade_items} gi, {course} c WHERE gi.courseid in ('.$str_pastcourses.') && gi.courseid>1 && gi.itemtype="mod" && gi.id not in ('.$str_itemsnotvisibletouser.') && gi.courseid=c.id';

      $arr_cc = $DB->get_records_sql($sql_cc);


      $arr_sdorder = array();
      $arr_edorder = array();

      foreach ($arr_cc as $key_cc) {
          $cmid = $key_cc->id;
          $modulename = $key_cc->itemmodule;
          $iteminstance = $key_cc->iteminstance;
          $courseid = $key_cc->courseid;
          $categoryid = $key_cc->categoryid;
          $itemid = $key_cc->id;
          $aggregationcoef = $key_cc->aggregationcoef;
          $aggregationcoef2 = $key_cc->aggregationcoef2;

          // FETCH ASSESSMENT TYPE
          $arr_gradecategory = $DB->get_record('grade_categories',array('courseid'=>$courseid, 'id'=>$categoryid));
          if (!empty($arr_gradecategory)) {
            $gradecategoryname = $arr_gradecategory->fullname;
          }

          $assessmenttype = newassessments_statistics::return_assessmenttype($gradecategoryname, $aggregationcoef);


          // START DATE
          $submissionstartdate = 0;
          $startdate = "";
          $duedate = 0;
          $enddate = "";

          // READ individual TABLE OF ACTIVITY (MODULE)
          if ($modulename!="") {
            $arr_submissionstartdate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));

          if (!empty($arr_submissionstartdate)) {
            if ($modulename=="assign") {
              $submissionstartdate = $arr_submissionstartdate->allowsubmissionsfromdate;
              $duedate = $arr_submissionstartdate->duedate;
            }
            if ($modulename=="forum") {
              $submissionstartdate = $arr_submissionstartdate->assesstimestart;
              $duedate = $arr_submissionstartdate->duedate;
            }
            if ($modulename=="quiz") {
              $submissionstartdate = $arr_submissionstartdate->timeopen;
              $duedate = $arr_submissionstartdate->timeclose;
            }
            if ($modulename=="workshop") {
              $submissionstartdate = $arr_submissionstartdate->submissionstart;
              $duedate = $arr_submissionstartdate->submissionend;
            }
          }
        }


            $startdate = date("d/m/Y", $submissionstartdate);
            $arr_sdorder[$itemid] = $submissionstartdate;
            $enddate = date("d/m/Y", $duedate);
            $arr_edorder[$itemid] = $duedate;


      }

    }

  asort($arr_sdorder);
  $str_sdorder = "";
  foreach ($arr_sdorder as $key_order=>$value) {
    $str_sdorder .= $key_order . ",";
  }
  $str_sdorder = rtrim($str_sdorder,",");

  asort($arr_edorder);
  $str_edorder = "";
  foreach ($arr_edorder as $key_order=>$value) {
    $str_edorder .= $key_order . ",";
  }
  $str_edorder = rtrim($str_edorder,",");

  $array_order = array("startdateorder"=>$str_sdorder, "enddateorder"=>$str_edorder);

  return $array_order;

}

?>
