<?php
/**
 * Test table class to be put in test_table.php of root of Moodle installation.
 *  for defining some custom column names and proccessing
 * Username and Password feilds using custom and other column methods.
 */

require_once(dirname(dirname(__FILE__)).'../../config.php');
global $CFG,$USER, $DB;

require "$CFG->libdir/tablelib.php";

require_once('locallib.php');


class currentassessment_table extends table_sql
{

    /**
     * Constructor
     * @param int $unequeid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($unequeid)
    {
        parent::__construct($unequeid);
        // Define the list of columns to show.


        $columns = array('course', 'assessment', 'assessmenttype', 'weight', 'duedate', 'status', 'yourgrade', 'feedback');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.

        $headers = array(
            get_string('course'),
/*            get_string('coursecode', 'block_newgu_spdetails'), */
            get_string('assessment'),
            get_string('assessmenttype', 'block_newgu_spdetails'),
            get_string('weight', 'block_newgu_spdetails'),
            get_string('duedate','block_newgu_spdetails'),
            get_string('status'),
            get_string('yourgrade', 'block_newgu_spdetails'),
            get_string('feedback')
        );
        $this->define_headers($headers);
    }

    function col_course($values){
        global $DB;
        $courseid = $values->courseid;

        $arr_course = $DB->get_record('course',array('id'=>$courseid));
        if (!empty($arr_course)) {
            $coursename = $arr_course->fullname;
        }

        return $coursename;
    }
/*
    function col_coursecode($values){
        global $DB;
        $courseid = $values->courseid;

        $arr_course = $DB->get_record('course',array('id'=>$courseid));
        if (!empty($arr_course)) {
            $coursename = $arr_course->fullname;
        }

        return $coursename;
    }
*/
    function col_assessment($values){
      global $DB;

      $itemname = $values->itemname;

      return $itemname;
    }

    function col_assessmenttype($values){

        $assessmenttype = "";
        if (strpos(mb_strtoupper($values->itemname), "SUMMATIVE")>0) {
            $assessmenttype = "Summative";
        }
        if (strpos(mb_strtoupper($values->itemname), "FORMATIVE")>0) {
            $assessmenttype = "Formative";
        }

        return $assessmenttype;
        //return json_encode($values);

    }

    function col_weight($values){

        global $DB;
/*
        $cmid = $values->id;
        $moduleid = $values->module;
        $instance = $values->instance;
        $courseid = $values->course;

        $grademax = 0;

        // GET MODULE
        $arr = $DB->get_record('modules',array('id'=>$moduleid));
        $modulename = $arr->name;

        // READ individual TABLE OF ACTIVITY (MODULE)
        $arr_gradeitem = $DB->get_record('grade_items',array('iteminstance'=>$instance, 'itemmodule'=>$modulename, 'courseid'=>$courseid));
        if (!empty($arr_gradeitem)) {
            $grademax = $arr_gradeitem->grademax;
        }
*/
        return number_format((float)$values->grademax, 2, '.', '');
    }

    function col_duedate($values){

      global $DB;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;

      $arr_module = $DB->get_record('modules', array('name'=>'quiz'));
      $moduleid = $arr_module->id;

//      $arr_coursemodule = $DB->get_record('course_modules', array('course'=>$courseid, 'module'=>$moduleid, 'instance'=>$instance));

      //$cmid = $arr_coursemodule->id;



      $duedate = 0;

      // READ individual TABLE OF ACTIVITY (MODULE)
      if ($modulename!="") {
        $arr_duedate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));


      if (!empty($arr_duedate)) {
        if ($modulename=="assign" || $modulename=="forum") {
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
        return date("d/m/Y", $duedate);
      }

    }

    function col_status($values){

// mdl_assign_submission

      global $DB, $USER, $CFG;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;
      $link = "";

      $status="";

      if ($modulename=="assign") {
          $arr_assign = $DB->get_record('assign', array('id'=>$iteminstance));

          $cmid = newassessments_statistics::get_cmid('assign', $courseid, $iteminstance);

          if (!empty($arr_assign)) {
            $allowsubmissionsfromdate = $arr_assign->allowsubmissionsfromdate;
            $duedate = $arr_assign->duedate;
            $cutoffdate = $arr_assign->cutoffdate;
          }
          if ($allowsubmissionsfromdate>time()) {
            $status = 'notopen';
          }
          if ($status=="") {
            $arr_assignsubmission = $DB->get_record('assign_submission', array('assignment'=>$iteminstance, 'userid'=>$USER->id));
            if (!empty($arr_assignsubmission)) {
              $status = $arr_assignsubmission->status;
            } else {
              $status = 'tosubmit';
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

      if($status == 'tosubmit'){
        return '<a href="' . $link . '"><span class="status-item status-submit">Submit</span></a>';
      }
      if($status == 'submitted'){
        return '<span class="status-item status-submitted">Submitted</span>';
      }
      if($status == "notopen"){
        return '<span class="status-item">Submission not open</span>';
      }
      if($status == "TO_BE_ASKED"){
        return '<span class="status-item status-graded">Individual components</span>';
      }

    }

    function col_yourgrade($values){

      global $DB, $USER;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;

      $arr_grades = $DB->get_record('grade_grades',array('itemid'=>$itemid, 'userid'=>$USER->id));

      if (!empty($arr_grades)) {
        return number_format((float)$arr_grades->finalgrade, 2, '.', '');
      }
    }

    function col_feedback($values){

      global $DB, $USER, $CFG;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;

      if ($modulename=="assign") {
          $arr_assign = $DB->get_record('assign', array('id'=>$iteminstance));
          $cmid = newassessments_statistics::get_cmid('assign', $courseid, $iteminstance);
          $link = $CFG->wwwroot . '/mod/assign/view.php?id=' . $cmid;
      }

      if ($modulename=="forum") {
            $forumsubmissions = $DB->count_records('forum_discussion_subs', array('forum'=>$iteminstance, 'userid'=>$USER->id));
            $link = $CFG->wwwroot . '/mod/forum/view.php?id=' . $cmid;
      }

      if ($modulename=="quiz") {
            $cmid = newassessments_statistics::get_cmid('quiz', $courseid, $iteminstance);
            $link = $CFG->wwwroot . '/mod/quiz/view.php?id=' . $cmid;
      }

      $arr_grades = $DB->get_record('grade_grades',array('itemid'=>$itemid, 'userid'=>$USER->id));

      if (!empty($arr_grades)) {
        /*return "COURSE ID = " . $courseid . " # ITEM ID = " . $itemid . " / USERID = " . $USER->id . " // " . $arr_grades->feedback
        . " ### " . '<a href="' . $link . '#intro">' . get_string('readfeedback', 'block_newgu_spdetails') . '</a>';*/
        return '<a href="' . $link . '#intro">' . get_string('readfeedback', 'block_newgu_spdetails') . '</a>';
      } else {
        //return "COURSE ID = " . $courseid . " # ITEM ID = " . $itemid . " ** item instance = " . $iteminstance . " / USERID = " . $USER->id . " // " . $arr_grades->feedback;
      }

    }

}





class pastassessment_table extends table_sql
{

    /**
     * Constructor
     * @param int $unequeid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($unequeid)
    {
        parent::__construct($unequeid);
        // Define the list of columns to show.


        $columns = array('course', 'assessment', 'assessmenttype', 'weight', 'startdate', 'enddate', 'viewsubmission', 'yourgrade', 'feedback');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.

        $headers = array(
            get_string('course'),
/*            get_string('coursecode', 'block_newgu_spdetails'), */
            get_string('assessment'),
            get_string('assessmenttype', 'block_newgu_spdetails'),
            get_string('weight', 'block_newgu_spdetails'),
            get_string('startdate','block_newgu_spdetails'),
            get_string('enddate','block_newgu_spdetails'),
            get_string('viewsubmission','block_newgu_spdetails'),
            get_string('yourgrade', 'block_newgu_spdetails'),
            get_string('feedback')
        );
        $this->define_headers($headers);
    }

    function col_course($values){
        global $DB;
        $courseid = $values->courseid;

        $arr_course = $DB->get_record('course',array('id'=>$courseid));
        if (!empty($arr_course)) {
            $coursename = $arr_course->fullname;
        }

        return $coursename;
    }

    function col_assessment($values){
      global $DB;

      $itemname = $values->itemname;

      return $itemname;
    }

    function col_assessmenttype($values){

        $assessmenttype = "";
        if (strpos(mb_strtoupper($values->itemname), "SUMMATIVE")>0) {
            $assessmenttype = "Summative";
        }
        if (strpos(mb_strtoupper($values->itemname), "FORMATIVE")>0) {
            $assessmenttype = "Formative";
        }

        return $assessmenttype;
        //return json_encode($values);

    }

    function col_weight($values){

        global $DB, $USER;

        $cmid = $values->id;
        $moduleid = $values->module;
        $instance = $values->instance;
        $courseid = $values->courseid;
        $iteminstance = $values->iteminstance;
/*
        $grademax = 0;

        // GET MODULE
        $arr = $DB->get_record('modules',array('id'=>$moduleid));
        $modulename = $arr->name;

        // READ individual TABLE OF ACTIVITY (MODULE)
        $arr_gradeitem = $DB->get_record('grade_items',array('iteminstance'=>$instance, 'itemmodule'=>$modulename, 'courseid'=>$courseid));
        if (!empty($arr_gradeitem)) {
            $grademax = $arr_gradeitem->grademax;
        }
*/

        // $sql_wt = 'SELECT u.id AS userid, u.username AS studentid, gi.id AS itemid, c.shortname AS courseshortname, gi.itemname AS itemname, gi.grademax AS itemgrademax, gi.aggregationcoef AS itemaggregation, g.finalgrade AS finalgrade FROM mdl_user u JOIN mdl_grade_grades g ON g.userid = u.id JOIN mdl_grade_items gi ON g.itemid = gi.id JOIN mdl_course c ON c.id = gi.courseid WHERE gi.id=' . $iteminstance . ' AND u.id = ' . $USER->id . ' AND c.id=' . $courseid;
        // $arr_wt = $DB->get_record_sql($sql_wt);
        // $aggregation = $arr_wt->itemaggregation;
        // return number_format((float)$aggregation, 2, '.', '');
        //return number_format((float)$values->grademax, 2, '.', '');
        // return $sql_wt;
    }


    function col_startdate($values){

      global $DB;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;


      $submissionstartdate = 0;

      // READ individual TABLE OF ACTIVITY (MODULE)
      if ($modulename!="") {
        $arr_submissionstartdate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));


      if (!empty($arr_submissionstartdate)) {
        if ($modulename=="assign" || $modulename=="forum") {
          $submissionstartdate = $arr_submissionstartdate->allowsubmissionsfromdate;
        }
        if ($modulename=="quiz") {
          $submissionstartdate = $arr_submissionstartdate->timeopen;
        }
        if ($modulename=="workshop") {
          $submissionstartdate = $arr_submissionstartdate->submissionstart;
        }
      }
    }

      if ($submissionstartdate!=0) {
        return date("d/m/Y", $submissionstartdate);
      }

    }


    function col_enddate($values){

      global $DB;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;


      $duedate = 0;

      // READ individual TABLE OF ACTIVITY (MODULE)
      if ($modulename!="") {
        $arr_duedate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));


      if (!empty($arr_duedate)) {
        if ($modulename=="assign" || $modulename=="forum") {
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
        return date("d/m/Y", $duedate);
      }

    }

    function col_viewsubmission($values){

// mdl_assign_submission

      global $DB, $USER, $CFG;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;
      $link = "";

      $status="";

      if ($modulename=="assign") {
          $arr_assign = $DB->get_record('assign', array('id'=>$iteminstance));
          $cmid = newassessments_statistics::get_cmid('assign', $courseid, $iteminstance);
          $link = $CFG->wwwroot . '/mod/assign/view.php?id=' . $cmid;
      }

      if ($modulename=="forum") {
            $forumsubmissions = $DB->count_records('forum_discussion_subs', array('forum'=>$iteminstance, 'userid'=>$USER->id));
            $link = $CFG->wwwroot . '/mod/forum/view.php?id=' . $cmid;
      }

      if ($modulename=="quiz") {
            $cmid = newassessments_statistics::get_cmid('quiz', $courseid, $iteminstance);
            $link = $CFG->wwwroot . '/mod/quiz/view.php?id=' . $cmid;
      }
      if (!empty($link)) {
          return '<a href="' . $link . '">' . get_string('viewsubmission', 'block_newgu_spdetails') . '</a>';
      }
    }

    function return_22grademaxpoint($grade, $idnumber) {
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

    function col_yourgrade($values){

      global $DB, $USER;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;

      $arr_grades = $DB->get_record('grade_grades',array('itemid'=>$itemid, 'userid'=>$USER->id));

      if (!empty($arr_grades)) {

        $gradeletter1 = newassessments_statistics::return_22grademaxpoint(($arr_grades->finalgrade)-1, 1);
        $gradeletter2 = newassessments_statistics::return_22grademaxpoint(($arr_grades->finalgrade)-1, 2);
        if ((float)$arr_grades->finalgrade==0) {
            return "To be confirmed";
        } else {

            return $gradeletter1 . " (Provisional)";
        }


        //return $gradeletter2 . " / " . $gradeletter1 . " /// " . number_format((float)$arr_grades->finalgrade, 2, '.', '');
      }
    }

    function col_feedback($values){

      global $DB, $USER, $CFG;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;

      if ($modulename=="assign") {
          $arr_assign = $DB->get_record('assign', array('id'=>$iteminstance));
          $cmid = newassessments_statistics::get_cmid('assign', $courseid, $iteminstance);
          $link = $CFG->wwwroot . '/mod/assign/view.php?id=' . $cmid;
      }

      if ($modulename=="forum") {
            $forumsubmissions = $DB->count_records('forum_discussion_subs', array('forum'=>$iteminstance, 'userid'=>$USER->id));
            $link = $CFG->wwwroot . '/mod/forum/view.php?id=' . $cmid;
      }

      if ($modulename=="quiz") {
            $cmid = newassessments_statistics::get_cmid('quiz', $courseid, $iteminstance);
            $link = $CFG->wwwroot . '/mod/quiz/view.php?id=' . $cmid;
      }

      $arr_grades = $DB->get_record('grade_grades',array('itemid'=>$itemid, 'userid'=>$USER->id));

      if (!empty($arr_grades)) {
        /*return "COURSE ID = " . $courseid . " # ITEM ID = " . $itemid . " / USERID = " . $USER->id . " // " . $arr_grades->feedback
        . " ### " . '<a href="' . $link . '#intro">' . get_string('readfeedback', 'block_newgu_spdetails') . '</a>';*/
        return '<a href="' . $link . '#intro">' . get_string('readfeedback', 'block_newgu_spdetails') . '</a>';
      } else {
        //return "COURSE ID = " . $courseid . " # ITEM ID = " . $itemid . " ** item instance = " . $iteminstance . " / USERID = " . $USER->id . " // " . $arr_grades->feedback;
      }

    }

}
