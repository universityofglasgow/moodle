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

                                    $filteroptions = '<div style="width:100%">
                                    <label>Show
                                        <select onchange="alert(this.value)" name="grades_length" aria-controls="grades" class="" fdprocessedid="qno">
                                          <option value="10">10</option>
                                          <option value="25">25</option>
                                          <option value="50">50</option>
                                          <option value="100">100</option>
                                        </select> entries</label>

                                    <label style="float: right;">Search: <input type="search" class="" placeholder="" aria-controls="grades"></label>
                                    </div>
                                    <div style="clear:both;"></div>';


                                    $tab = optional_param('t', 1, PARAM_INT);
                                    $tabs = [];
                                    $tab1_title = get_string('currentlyenrolledin', 'block_newgu_spdetails');
                                    $tab2_title = get_string('pastcourses', 'block_newgu_spdetails');
                                    $tabs[] = new tabobject(1, new moodle_url($url, ['t'=>1]), $tab1_title);
                                    $tabs[] = new tabobject(2, new moodle_url($url, ['t'=>2]), $tab2_title);
                                    echo $OUTPUT->tabtree($tabs, $tab);
                                    if ($tab == 1) {
                                        // Show data for tab 1

                                        if ($str_currentcourses!="") {
                                        $table = new currentassessment_table('tab1');

                                        $search = optional_param('search', '', PARAM_ALPHA);

                                        $table->set_sql('*', "{grade_items}", "courseid in (".$str_currentcourses.") && courseid>1 && itemtype='mod' && itemmodule in (" . $itemmodules . ")");

                                        $table->no_sorting('course');
                                        $table->no_sorting('assessment');
                                        $table->no_sorting('assessmenttype');
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

                                        $table->set_sql('*', "{grade_items}", "courseid in (".$str_pastcourses.") && courseid>1 && hidden=0 && itemtype='mod' && itemmodule in (" . $itemmodules . ")");

                                        $table->no_sorting('course');
                                        $table->no_sorting('coursecode');
                                        $table->no_sorting('assessment');
                                        $table->no_sorting('assessmenttype');
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
                                          echo "<p style='text-align: center;'>". get_string('noassessments','block_newgu_spdetails') .".</p>";
                                      }

                                    }


                                    echo $OUTPUT->footer();
