<?php
require_once(dirname(dirname(__FILE__)) . '../../config.php');

defined('MOODLE_INTERNAL') || die();

require_login();
$context = \context_system::instance();

global $USER, $SESSION;

require_once('locallib.php');

$sub_assess = 0;
$tobe_sub = 0;
$overdue = 0;
$assess_marked = 0;

$total_overdue = 0;
$total_submissions = 0;
$total_tosubmit = 0;
$marked = 0;

    $currenttime = time();
//    $twohours = $currenttime - 2*60*60;
    $twohours = $currenttime - 2*60*60;

    if (!isset($SESSION->statscount) || $SESSION->statscount["timeupdated"]<$twohours) {
    //$get_stats_counts = newassessments_statistics::get_stats_counts($USER->id);

    $currentcourses = newassessments_statistics::return_enrolledcourses($USER->id, "current");

    if (!empty($currentcourses)) {
    $str_currentcourses = implode(",", $currentcourses);

    $itemmodules = "'assign','forum','quiz','workshop'";

    $courseids = implode(', ', $currentcourses);
    $currentdate = time();

    $sql_gi = "SELECT * FROM {grade_items} WHERE courseid in (".$str_currentcourses.") && courseid>1 && itemtype='mod' && itemmodule in (" . $itemmodules . ")";

    $arr_gi = $DB->get_records_sql($sql_gi);

    foreach($arr_gi as $key_gi) {
      $modulename = $key_gi->itemmodule;
      $iteminstance = $key_gi->iteminstance;
      $courseid = $key_gi->courseid;
      $itemid = $key_gi->id;

      $gradestatus = newassessments_statistics::return_gradestatus($modulename, $iteminstance, $courseid, $itemid, $USER->id);

      $status = $gradestatus["status"];
      $link = $gradestatus["link"];
      $allowsubmissionsfromdate = $gradestatus["allowsubmissionsfromdate"];
      $duedate = $gradestatus["duedate"];
      $cutoffdate = $gradestatus["cutoffdate"];

      $finalgrade = $gradestatus["finalgrade"];


      $statustodisplay = "";

      if($status == 'tosubmit'){
        $total_tosubmit++;
      }
      if($status == 'notsubmitted'){
        $total_tosubmit++;
      }
      if($status == 'submitted'){
        $total_submissions++;
        if ($finalgrade!=Null) {
          $marked++;
        }
      }
      if($status == "notopen"){
        $statustodisplay = '<span class="status-item">Submission not open</span> ';
      }
      if($status == "TO_BE_ASKED"){
        $statustodisplay = '<span class="status-item status-graded">Individual components</span> ';
      }
      if($status == "overdue"){
        $total_overdue++;
      }

    }

    $sub_assess = $total_submissions;
    $tobe_sub = $total_tosubmit;
    $overdue = $total_overdue;
    $assess_marked = $marked;

    $statscount = array(
                        "timeupdated"=>time(),
                        "sub_assess"=>$total_submissions,
                        "tobe_sub"=>$total_tosubmit,
                        "overdue"=>$total_overdue,
                        "assess_marked"=>$marked
                      );

                      $SESSION->statscount = $statscount;
                    }
    } else {
      $sub_assess = $SESSION->statscount["sub_assess"];
      $tobe_sub = $SESSION->statscount["tobe_sub"];
      $overdue = $SESSION->statscount["overdue"];
      $assess_marked = $SESSION->statscount["assess_marked"];
    }

    $html = '';
    $html .= html_writer::start_tag('div', array('class' => 'assessments-overview-container border rounded my-2 p-2'));
    $html .= html_writer::tag('h4', get_string('headingataglance', 'block_newgu_spdetails'));
    $html .= html_writer::start_tag('div', array('class' => 'row'));

    $html .= html_writer::start_tag('div', array('class' => 'assessments-item assessments-submitted col-md-3 col-sm-6 col-xs-12'));
    $html .= html_writer::tag('h1', $sub_assess, array('class' => 'assessments-item-count h1'));
    $html .= html_writer::tag('p', get_string('assessment', 'block_newgu_spdetails') . ' ' . get_string('submitted', 'block_newgu_spdetails'), array('class' => 'assessments-item-label'));
    $html .= html_writer::end_tag('div');

    $html .= html_writer::start_tag('div', array('class' => 'assessments-item assessments-submitted col-md-3 col-sm-6 col-xs-12'));
    $html .= html_writer::tag('h1', $tobe_sub, array('class' => 'assessments-item-count h1', 'style' => 'color: #CC5500'));
    $html .= html_writer::tag('p', get_string('tobesubmitted', 'block_newgu_spdetails'), array('class' => 'assessments-item-label'));
    $html .= html_writer::end_tag('div');

    $html .= html_writer::start_tag('div', array('class' => 'assessments-item assessments-submitted col-md-3 col-sm-6 col-xs-12'));
    $html .= html_writer::tag('h1', $overdue, array('class' => 'assessments-item-count h1', 'style' => 'color: red'));
    $html .= html_writer::tag('p', get_string('overdue', 'block_newgu_spdetails'), array('class' => 'assessments-item-label'));
    $html .= html_writer::end_tag('div');

    $html .= html_writer::start_tag('div', array('class' => 'assessments-item assessments-submitted col-md-3 col-sm-6 col-xs-12'));
    $html .= html_writer::tag('h1', $assess_marked, array('class' => 'assessments-item-count h1', 'style' => 'color: green'));
    $html .= html_writer::tag('p', get_string('assessments', 'block_newgu_spdetails') . ' ' . get_string('marked', 'block_newgu_spdetails'), array('class' => 'assessments-item-label'));
    $html .= html_writer::end_tag('div');

    $html .= html_writer::end_tag('div');
    $html .= html_writer::end_tag('div');

    echo $html;
