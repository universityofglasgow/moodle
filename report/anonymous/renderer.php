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
 * This file contains functions used by the participation report
 *
 * @package    report
 * @subpackage anonymous
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class report_anonymous_renderer extends plugin_renderer_base {

    public function list_assign($url, $assignments) {
        echo "<h3>" . get_string('anonymousassignments', 'report_anonymous') . "</h3>";
        echo '<div class="alert alert-info">' . get_string('selectassignment', 'report_anonymous') . '</div>';
        if (empty($assignments)) {
            echo "<div class=\"alert alert-warning\">" . get_string('noassignments', 'report_anonymous') . "</div>";
            return;
        }
        echo "<ul>";
        foreach ($assignments as $assignment) {
            $url->params(array('mod' => 'assign', 'assign' => $assignment->id));
            echo "<li><a href=\"$url\">";
            echo $assignment->name;
            if ($assignment->blindmarking) {
                echo ' (' . get_string('anonymous', 'report_anonymous') . ')';
            }
            echo "</a></li>";
        }
        echo "</ul>";
    }

    /**
     * List of assignment users
     * @param int $courseid course id
     * @param object $assignment assignment
     * @param array $submissions list of submissions/users
     * @param boolean $reveal Display full names or not
     */
    public function report($courseid, $assignment, $submissions, $reveal) {
        echo '<div class="alert alert-primary">' . get_string('assignnotsubmit', 'report_anonymous', $assignment->name) . '</div>';

        // Keep a track of records with no idnumber.
        $table = new html_table();
        $table->head = array(
           get_string('idnumber', 'report_anonymous'),
           get_string('participantnumber', 'report_anonymous'),
           get_string('submitted', 'report_anonymous'),
           get_string('name', 'report_anonymous'),
        );
        $nosubmitcount = 0;
        foreach ($submissions as $s) {
            $line = array();

            // Matric/ID Number
            if ($s->user->idnumber) {
                $line[] = $s->user->idnumber;
            } else {
                $line[] = '-';
            }

            // Participant number
            $line[] = $s->user->participantid;

            // Submitted
            if ($s->submission) {
                $line[] = userdate($s->submission->timemodified);
            } else {
                $line[] = get_string('no');
                $nosubmitcount++;
            }

            // Name
            if ($reveal || !$assignment->blindmarking) {
                $userurl = new moodle_url('/user/view.php', array('id' => $s->user->id, 'course' => $courseid));
                $line[] = "<a href=\"$userurl\">".fullname($s->user)."</a>";
            } else {
                $line[] = get_string('hidden', 'report_anonymous');
            }
            $table->data[] = $line;
        }
        echo html_writer::table($table);

        // Totals.
        echo '<ul>';
        echo "<li><strong>" . get_string('totalassignusers', 'report_anonymous', count($submissions)) . "</strong></li>";
        echo "<li><strong>" . get_string('totalnotassignusers', 'report_anonymous', $nosubmitcount) . "</strong></li>";
        echo '</ul>';
    }

    /**
     * Display the additional actions some capabilities allow
     * @param moodle_url $url
     * @param boolean $reveal on/off
     */
    public function actions($context, $url, $reveal, $assignment) {
        echo "<div>";
        if (has_capability('report/anonymous:shownames', $context) && $assignment->blindmarking) {
            $showurl = clone($url);
            if ($reveal) {
                $showurl->params(array('reveal' => 0));
                $text = get_string('clickhidenames', 'report_anonymous');
            } else {
                $showurl->params(array('reveal' => 1));
                $text = get_string('clickshownames', 'report_anonymous');
            }
            echo "<a class=\"btn\" href=\"$showurl\">$text</a>";
        }

        if (has_capability('report/anonymous:export', $context)) {
            $url->params(array('export' => 1));
            $text = get_string('export', 'report_anonymous');
            echo "<a class=\"btn\" href=\"$url\">$text</a>";
        }
        echo "</div>";
    }

    public function back_button($url) {
        echo "<div style=\"margin-top: 20px;\">";
        echo "<a class=\"btn\" href=\"$url\">" . get_string('backtolist', 'report_anonymous') . "</a>";
        echo "</div>";
    }

}
