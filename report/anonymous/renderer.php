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
        global $OUTPUT;

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
            if ($assignment->urkundenabled) {
                echo '&nbsp;<img src="' . $OUTPUT->pix_url('urkund', 'report_anonymous') . '" />';
            }
            echo "</a></li>";
        }
        echo "</ul>";
    }

    /**
     * Limit urkund filename
     * @param string $filename
     * @return string HTML snippet
     */
    private function urkund_filename($filename) {
        $original = $filename;
        if (strlen($filename) > 25) {
            $filename = substr($filename, 0, 25) . '...';
        }
        return '<span class="urkundtt" title="'.$original.'">'.$filename.'</span>';
    }

    private function head($strname, $column, $url) {
        global $OUTPUT; 

        $strtext = get_string($strname, 'report_anonymous');
        $ptsort = optional_param('tsort', '', PARAM_ALPHA);
        $dir = optional_param('tdir', 'asc', PARAM_ALPHA);
        if ($column == $ptsort) {
            $dir = $dir == 'asc' ? 'desc' : 'asc';
            $logo = $dir == 'asc' ? 'up' : 'down';
            $pix = '<img src="' . $OUTPUT->pix_url('i/'.$logo) . '" />';
            $strtext = '<u>' . $strtext . '</u>';
        } else {
            $pix = '';
        }
        return '<a href="' . $url . '&tsort=' . $column . '&tdir=' . $dir . '">' . $strtext . ' ' . $pix . '</a>';
    }

    /**
     * List of assignment users
     * @param int $courseid course id
     * @param object $assignment assignment
     * @param array $submissions list of submissions/users
     * @param boolean $reveal Display full names or not
     * @param boolean $urkund Is Urkund used in this assignment?
     */
    public function report($courseid, $assignment, $submissions, $reveal, $urkund, $baseurl) {
        echo '<div class="alert alert-primary">' . get_string('assignnotsubmit', 'report_anonymous', $assignment->name) . '</div>';

        // Pager
        // $this->pagercontrols();

        // Start to set up table.
        $table = new html_table();

        // Headers
        $columns = array('idnumber', 'participantid', 'status', 'date', 'name');
        $headers = array(
           $this->head('idnumber', 'idnumber', $baseurl),
           $this->head('participantnumber', 'participantid', $baseurl),
           $this->head('status', 'status', $baseurl),
           $this->head('submitdate', 'date', $baseurl),
           $this->head('name', 'name', $baseurl),
        );
        if ($urkund) {
            $columns[] = 'urkundfilename';
            $columns[] = 'urkundstatus';
            $columns[] = 'urkundscore';
            $headers[] = $this->head('urkundfile', 'urkundfilename', $baseurl);
            $headers[] = $this->head('urkundstatus', 'urkundstatus', $baseurl);
            $headers[] = $this->head('urkundscore', 'urkundscore', $baseurl);
        }
        $table->head = $headers;

        // Add data to table
        $nosubmitcount = 0;
        foreach ($submissions as $s) {
            $line = array();

            // Matric/ID Number
            $line[] = $s->idnumber;

            // Participant number
            $line[] = $s->participantid;

            // Submitted status
            $line[] = $s->status;
            if ($s->status == '-') {
                $nosubmitcount++;
            }

            // Submitted date
            $line[] = $s->date;

            // Name
            $line[] = $s->name;

            if ($urkund) {
                $line[] = $this->urkund_filename($s->urkundfilename);
                $line[] = $s->urkundstatus;
                $line[] = $s->urkundscore;
            }
      
            $table->data[] = $line;
        }

        // Finally, display the table.
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
            $text = get_string('export', 'report_anonymous');
            echo "<a class=\"btn\" href=\"$url&export=1\">$text</a>";
        }
        echo "</div>";
    }

    public function back_button($url) {
        echo "<div style=\"margin-top: 20px;\">";
        echo "<a class=\"btn\" href=\"$url\">" . get_string('backtolist', 'report_anonymous') . "</a>";
        echo "</div>";
    }

    /**
     * Display controls for jquery-pager.
     */
    public function pagercontrols() {
        global $OUTPUT;

        echo '<div id="anonymous_pager" class="anonymous_pager">';
        echo '  <form>';
        echo '    <img src="' . $OUTPUT->pix_url('first', 'report_anonymous') . '" class="first"/>';
        echo '    <img src="' . $OUTPUT->pix_url('prev', 'report_anonymous') . '" class="prev"/>';
        echo '    <span class="pagedisplay"></span>';
        echo '    <img src="' . $OUTPUT->pix_url('next', 'report_anonymous') . '" class="next"/>';
        echo '    <img src="' . $OUTPUT->pix_url('last', 'report_anonymous') . '" class="last"/>';
        echo '    <select class="pagesize">';
        echo '      <option value="10">10</option>';
        echo '      <option value="20">20</option>';
        echo '      <option value="30">30</option>';
        echo '      <option value="40">40</option>';
        echo '    </select>';
        echo '  </form>';
        echo '</div>';
    }

}
