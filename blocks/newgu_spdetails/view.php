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
 * View
 *
 * @package   block_newgu_spdetails
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $USER, $DB;

$heading = get_string('pluginname', 'block_newgu_spdetails');
$url = new \moodle_url('/blocks/newgu_spdetails/view.php');

require_login();

require_once('locallib.php');

$context = \context_system::instance();

$pagesize = optional_param("pagesize", 20, PARAM_INT);

require("assessment_table.php");


// Page setup.
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string("assessment", "block_newgu_spdetails"));
$PAGE->set_heading(get_string("pluginname", "block_newgu_spdetails"));
$PAGE->navbar->ignore_active();
$PAGE->requires->jquery();

$PAGE->navbar->add((get_string('navtitle','block_newgu_spdetails')), new moodle_url('/blocks/newgu_spdetails/view.php'));


$PAGE->set_url($url);
$PAGE->set_heading($heading);

$returnurl = optional_param('returnurl', '', PARAM_URL);

echo $OUTPUT->header();

$currentcourses = newassessments_statistics::return_enrolledcourses($USER->id, "current");
$str_currentcourses = implode(",", $currentcourses);
/*
echo "<br/>==CURRENT===<br/>";
echo "<pre>";
print_r($currentcourses);
echo "</pre>";
echo $str_currentcourses;
echo "<br/>=====<br/>";
*/
$pastcourses = newassessments_statistics::return_enrolledcourses($USER->id, "past");
$str_pastcourses = implode(",", $pastcourses);

//$str_currentcourses = $str_pastcourses;
/*
echo "<br/>==PAST===<br/>";
echo "<pre>";
print_r($pastcourses);
echo "</pre>";
echo $str_pastcourses;
echo "=====";
*/

$itemmodules = "'assign','forum','quiz','workshop'";

$html = html_writer::start_tag('div', array('id' => 'spdetails'));
$html .= html_writer::tag('p', '<img src="img/loader.gif">', array('style' => 'text-align:center;'));
$html .= html_writer::end_tag('div');

echo $html;

$PAGE->requires->js_amd_inline("
                                    require(['jquery'], function(\$) {

                                    $.ajax({
                                    url: 'ajax.php',
                                    type: 'POST',
                                    data: {request: 'loadspdetails'},
                                    success: function (data) {
                                        if (data !== '') {
                                            $('#spdetails').html(data);
                                        }
                                    }
                                    });

                                    });
                                    ");

                                    $tab = optional_param('t', 1, PARAM_INT);

                                    $tsort = optional_param('tsort', "", PARAM_ALPHA);
                                    $tdir = optional_param('tdir', 1, PARAM_INT);

                                    $td = 4;
                                    if ($tdir==4) {
                                        $td=3;
                                    }
                                    if ($tdir==3) {
                                        $td=4;
                                    }
                                    $courseselected = "";
                                    if ($tsort=="coursename") {
                                        $courseselected = "selected";
                                    }


                                    $tabs = [];
                                    $tab1_title = get_string('currentlyenrolledin', 'block_newgu_spdetails');
                                    $tab2_title = get_string('pastcourses', 'block_newgu_spdetails');
                                    $tabs[] = new tabobject(1, new moodle_url($url, ['t'=>1]), $tab1_title);
                                    $tabs[] = new tabobject(2, new moodle_url($url, ['t'=>2]), $tab2_title);
                                    echo $OUTPUT->tabtree($tabs, $tab);

                                    $filteroptions = '
                                    <div id="forborder" style="border:1px solid #ccc; padding-top:8px; margin-top:-17px;">
                                    <div style="width:97%; margin:0 12px;">
                                    <label>Sort by:
                                        <select onchange="document.location.href=this.value" name="grades_sortby" aria-controls="grades" class="" fdprocessedid="qno">
                                          <option value="view.php?t=1">Select</option>
                                          <option ' . $courseselected . ' value="view.php?t=' . $tab . '&tsort=coursename&tdir=' . $td . '">Course</option>
                                        </select> </label>

                                    </div>
                                    <div style="clear:both;"></div>';


                                    if ($tab == 1) {
                                        // Show data for tab 1
// ' . $baseurl . '&tsort=coursename&tdir=' . $ts . '


                                        if ($str_currentcourses!="") {
                                        $table = new currentassessment_table('tab1');

                                        $search = optional_param('search', '', PARAM_ALPHA);


                                        $str_itemsnotvisibletouser = newassessments_statistics::fetch_itemsnotvisibletouser($USER->id, $str_currentcourses);

//                                        $table->set_sql('gi.*, c.fullname as coursename', "{grade_items} gi, {course} c", "gi.courseid in (".$str_currentcourses.") && gi.courseid>1 && gi.itemtype='mod' && gi.id not in (".$str_itemsnotvisibletouser.") && gi.courseid=c.id && gi.itemmodule in (" . $itemmodules . ")");
                                        $table->set_sql('gi.*, c.fullname as coursename', "{grade_items} gi, {course} c", "gi.courseid in (".$str_currentcourses.") && gi.courseid>1 && gi.itemtype='mod' && gi.id not in (".$str_itemsnotvisibletouser.") && gi.courseid=c.id");


//                                        $table->no_sorting('course');
                                        $table->no_sorting('assessment');
                                        $table->no_sorting('itemmodule');
                                        $table->no_sorting('assessmenttype');
                                        $table->no_sorting('includedingcat');
                                        $table->no_sorting('weight');
                                        $table->no_sorting('duedate');
                                        $table->no_sorting('status');
                                        $table->no_sorting('yourgrade');
                                        $table->no_sorting('feedback');



                                        $table->define_baseurl("$CFG->wwwroot/blocks/newgu_spdetails/view.php?t=1");

                                        echo $filteroptions;

                                        $table->out(20, true);
                                      } else {
                                          echo "<p style='text-align: center;'>". get_string('noassessments','block_newgu_spdetails') .".</p>";
                                      }

                                    }

                                    if ($tab == 2) {

                                      /*
                                        ‘Past courses’ are where the end date of the course has finished
                                        and the due dates for any assessments
                                        (plus an additional 30 days) have passed.
                                        The additional 30 days is to allow for the assessment to be
                                        marked and feedback returned to the student all in the
                                        ‘Currently enrolled in’ view.
                                      */

                                        // Show data for tab 2

                                        if ($str_pastcourses!="") {
                                        $table = new pastassessment_table('tab2');

                                        $search = optional_param('search', '', PARAM_ALPHA);

                                        $str_itemsnotvisibletouser = newassessments_statistics::fetch_itemsnotvisibletouser($USER->id, $str_pastcourses);

//                                        $table->set_sql('gi.*, c.fullname as coursename', "{grade_items} gi, {course} c", "gi.courseid in (".$str_pastcourses.") && gi.courseid>1 && gi.itemtype='mod' && gi.id not in (".$str_itemsnotvisibletouser.") && gi.courseid=c.id && gi.itemmodule in (" . $itemmodules . ")");
                                        $table->set_sql('gi.*, c.fullname as coursename', "{grade_items} gi, {course} c", "gi.courseid in (".$str_pastcourses.") && gi.courseid>1 && gi.itemtype='mod' && gi.id not in (".$str_itemsnotvisibletouser.") && gi.courseid=c.id");

//                                        $table->no_sorting('course');
                                        $table->no_sorting('assessment');
                                        $table->no_sorting('itemmodule');
                                        $table->no_sorting('assessmenttype');
                                        $table->no_sorting('includedingcat');
                                        $table->no_sorting('weight');
                                        $table->no_sorting('startdate');
                                        $table->no_sorting('enddate');
                                        $table->no_sorting('viewsubmission');
                                        $table->no_sorting('yourgrade');
                                        $table->no_sorting('feedback');

                                        $table->define_baseurl("$CFG->wwwroot/blocks/newgu_spdetails/view.php?t=2");

                                        echo $filteroptions;

                                        $table->out(20, true);
                                      } else {
                                          echo "<p style='text-align: center; margin-left:20px;'>". get_string('noassessments','block_newgu_spdetails') .".</p>";
                                      }

                                    }

                                    echo "</div>";


                                    $pdflink = "";
                                    $excellink = "";

                                    if ($tab==1 && $str_currentcourses!="") {
                                      $pdflink = "downloadspdetails.php?spdetailstype=pdf&coursestype=current";
                                      $excellink = "downloadspdetails.php?spdetailstype=excel&coursestype=current";
                                    }
                                    if ($tab==2 && $str_pastcourses!="") {
                                      $pdflink = "downloadspdetails.php?spdetailstype=pdf&coursestype=past";
                                      $excellink = "downloadspdetails.php?spdetailstype=excel&coursestype=past";
                                    }

                                    echo "<p></p>";
                                    echo "<p style='text-align:right;'>";
                                    if ($pdflink!="") {
                                    echo "<a target='_blank' href='".$pdflink."'><i style='color:red;' class='fa fa-file-pdf-o fa-2x' aria-hidden='true'></i></a>";
                                    }
                                    if ($excellink!="") {
                                      echo "<a target='_blank' href='".$excellink."'><i style='padding-left:20px; color:green;' class='fa fa-file-excel-o fa-2x' aria-hidden='true'></i></a>";
                                    }
                                    echo "</p>";

                                    echo $OUTPUT->footer();
