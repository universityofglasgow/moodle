<?php
/**
 * Test table class to be put in test_table.php of root of Moodle installation.
 *  for defining some custom column names and proccessing
 * Username and Password feilds using custom and other column methods.
 */

require_once(dirname(dirname(__FILE__)).'../../config.php');
global $CFG,$USER, $DB;

require "$CFG->libdir/tablelib.php";


class assessment_table extends table_sql
{

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($uniqueid)
    {
        parent::__construct($uniqueid);
        // Define the list of columns to show.


        $columns = array('course','assessment', 'assessmenttype', 'weight', 'duedate', 'status', 'yourgrade', 'feedback');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.

        $headers = array(
            get_string('course'),
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

    function col_assessment($values){
      global $DB;

      $itemname = $values->itemname;
/*
      $cmid = $values->id;
      $modulename = $values->itemmodule;
      $itemid = $values->id;



      // READ individual TABLE OF ACTIVITY (MODULE)
      $arr = $DB->get_record($modulename,array('id'=>$instance));
      $assessmentname = $arr->name;
*/
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
        return $values->grademax;
    }

    function col_duedate($values){

      global $DB;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;

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
              $link = $CFG->wwwroot . '/mod/assign/view.php?id=' . $itemid;
            }
          }
      }

      if ($modulename=="forum") {
            $forumsubmissions = $DB->count_records('forum_discussion_subs', array('forum'=>$iteminstance, 'userid'=>$USER->id));
            if ($forumsubmissions>0) {
                $status = 'submitted';
            } else {
                $status = 'tosubmit';
                $link = $CFG->wwwroot . '/mod/forum/view.php?id=' . $itemid;
            }
        }

        if ($modulename=="quiz") {
              $quizattempts = $DB->count_records('quiz_attempts', array('quiz'=>$iteminstance, 'userid'=>$USER->id, 'state'=>'finished'));
              if ($quizattempts>0) {
                  $status = 'submitted';
              } else {
                  $status = 'tosubmit';
                  $link = $CFG->wwwroot . '/mod/quiz/view.php?id=' . $iteminstance;
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
        return $arr_grades->finalgrade;
      }
    }

    function col_feedback($values){

      global $DB, $USER;

      $modulename = $values->itemmodule;
      $iteminstance = $values->iteminstance;
      $courseid = $values->courseid;
      $itemid = $values->id;

      $arr_grades = $DB->get_record('grade_grades',array('itemid'=>$itemid, 'userid'=>$USER->id));

      if (!empty($arr_grades)) {
        return $arr_grades->feedback;
      }

    }





}


/**
 * This function is called for each data row to allow processing of
 * columns which do not have a *_cols function.
 * @return string return processed value. Return NULL if no change has
 *     been made.
 */
