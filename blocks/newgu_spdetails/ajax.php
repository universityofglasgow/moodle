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

require_login();

require_once('locallib.php');

$context = \context_system::instance();

global $USER, $SESSION;

    $currenttime = time();
    $twohours = $currenttime - 2*60*60;

    if (!isset($SESSION->statscount) || $SESSION->statscount["timeupdated"]<$twohours) {
    $get_stats_counts = assessments_statistics::get_stats_counts($USER->id);

    $sub_assess = $get_stats_counts->submitted;
    $tobe_sub = $get_stats_counts->tobesubmit;
    $overdue = $get_stats_counts->overdue;
    $assess_marked = $get_stats_counts->marked;

    $statscount = array(
                        "timeupdated"=>time(),
                        "sub_assess"=>$sub_assess,
                        "tobe_sub"=>$tobe_sub,
                        "overdue"=>$overdue,
                        "assess_marked"=>$assess_marked
                      );

                      $SESSION->statscount = $statscount;
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
