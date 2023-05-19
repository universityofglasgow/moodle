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

        if ($values->aggregationcoef!=0) {
          return number_format((float)$values->aggregationcoef, 2, '.', '');
        } else {
          return "-";
        }
    }

    function col_duedate($values){

      global $DB, $USER;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;

      $duedate = 0;
      $extspan = "";

      // READ individual TABLE OF ACTIVITY (MODULE)
      if ($modulename!="") {
        $arr_duedate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));

      if (!empty($arr_duedate)) {
        if ($modulename=="assign") {
          $duedate = $arr_duedate->duedate;

          $arr_userflags = $DB->get_record('assign_user_flags', array('userid'=>$USER->id, 'assignment'=>$iteminstance));

          $extensionduedate = $arr_userflags->extensionduedate;

          if ($extensionduedate>0) {
//            $extspan = '<span class="extended">*<span class="extended-tooltip">Due date extension</span></span>';
            $extspan = '<a href="javascript:void(0)" title="Due date extension" class="extended">*</a>';
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
        return date("d/m/Y", $duedate) . $extspan;
      }

    }



    function col_status($values){

// mdl_assign_submission

      global $DB, $USER, $CFG;

      $link = "";
      $status = "";


      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;


      $gradestatus = newassessments_statistics::return_gradestatus($modulename, $iteminstance, $courseid, $itemid, $USER->id);

      $status = $gradestatus["status"];
      $link = $gradestatus["link"];
      $allowsubmissionsfromdate = $gradestatus["allowsubmissionsfromdate"];
      $duedate = $gradestatus["duedate"];
      $cutoffdate = $gradestatus["cutoffdate"];

      $finalgrade = $gradestatus["finalgrade"];

      $statustodisplay = "";

      if($status == 'tosubmit'){
        $statustodisplay = '<a href="' . $link . '"><span class="status-item status-submit">Submit</span></a> ';
      }
      if($status == 'notsubmitted'){
        $statustodisplay = '<span class="status-item">Not Submitted</span> ';
      }
      if($status == 'submitted'){
        $statustodisplay = '<span class="status-item status-submitted">Submitted</span> ';
        if ($finalgrade!=Null) {
          $statustodisplay = '<span class="status-item status-item status-graded">Graded</span>';
        }
      }
      if($status == "notopen"){
        $statustodisplay = '<span class="status-item">Submission not open</span> ';
      }
      if($status == "TO_BE_ASKED"){
        $statustodisplay = '<span class="status-item status-graded">Individual components</span> ';
      }
      if($status == "overdue"){
        $statustodisplay = '<span class="status-item status-overdue">Overdue</span> ';
      }

      return $statustodisplay;

    }

    function col_yourgrade($values){

      global $DB, $USER, $CFG;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;

      $link = "";

      $arr_gradetodisplay = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $USER->id, $values->grademax);
      $link = $arr_gradetodisplay["link"];
      $gradetodisplay = $arr_gradetodisplay["gradetodisplay"];

      return $gradetodisplay;
    }



    function col_feedback($values){

      global $DB, $USER, $CFG;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;

      $link = "";

      $feedback = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $USER->id, $values->grademax);
      $link = $feedback["link"];
      $gradetodisplay = $feedback["gradetodisplay"];

      if ($link!="") {
        return '<a href="' . $link . '">' . get_string('readfeedback', 'block_newgu_spdetails') . '</a>';
      } else {
        if ($modulename!="quiz") {
          return $gradetodisplay;
        }
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
        if ($values->aggregationcoef!=0) {
          return number_format((float)$values->aggregationcoef, 2, '.', '');
        } else {
          return "-";
        }
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

      global $DB, $USER, $CFG;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;
      $link = "";

      $status="";

      $cmid = newassessments_statistics::get_cmid($modulename, $courseid, $iteminstance);

      $link = $CFG->wwwroot . '/mod/' . $modulename . '/view.php?id=' . $cmid;

      if (!empty($link)) {
          return '<a href="' . $link . '">' . get_string('viewsubmission', 'block_newgu_spdetails') . '</a>';
      }
    }

    function col_yourgrade($values){

            // $gradeletter1 = newassessments_statistics::return_22grademaxpoint(($arr_grades->finalgrade)-1, 1);
            // $gradeletter2 = newassessments_statistics::return_22grademaxpoint(($arr_grades->finalgrade)-1, 2);

            global $DB, $USER, $CFG;

            $modulename = $values->itemmodule;
            $iteminstance = $values->iteminstance;
            $courseid = $values->courseid;
            $itemid = $values->id;

            $link = "";

            $feedback = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $USER->id, $values->grademax);
            $link = $feedback["link"];
            $gradetodisplay = $feedback["gradetodisplay"];

            return $gradetodisplay;

    }

    function col_feedback($values){

      global $DB, $USER, $CFG;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;

      $link = "";

      $feedback = get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $USER->id, $values->grademax);
      $link = $feedback["link"];
      $gradetodisplay = $feedback["gradetodisplay"];

      if ($link!="") {
        return '<a href="' . $link . '">' . get_string('readfeedback', 'block_newgu_spdetails') . '</a>';
      } else {
        if ($modulename!="quiz") {
          return $gradetodisplay;
        }
      }

    }

}
