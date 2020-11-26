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
 * Renderer for gugcat
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_gugcat_renderer extends plugin_renderer_base {

    public function display_grade_capture($activities, $rows, $columns) {
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_tab_header', (object)[
            'addallgrdstr' =>get_string('addallnewgrade', 'local_gugcat'),
            'downloadcsvstr' =>get_string('downloadcsv', 'local_gugcat'),
            'saveallbtnstr' =>get_string('saveallnewgrade', 'local_gugcat'),
            'activities' => $activities,
        ]);
        $html .= $this->display_table($rows, $columns);
        $html .= $this->footer();
        return $html;
    }

    public function display_add_grade_form($course, $activity, $gbgrade, $student) {
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_add_form', (object)[
            'addnewgrade' =>get_string('addnewgrade', 'local_gugcat'),
            'course' => $course,
            'section' => $activity,
            'student' => $student,
            'gbgrade' => $gbgrade
        ]);
        return $html;
    }

    public function display_custom_select($options, $default = null, $class = null, $id = null) {
        $html = $this->render_from_template('local_gugcat/gcat_custom_select', (object)[
            'default' => $default ,
            'options' => $options,
            'class' => $class,
            'id' => $id,
        ]);
        return $html;
    }

    private function display_table($rows, $columns) {
        $grades = array_values(local_gugcat::$GRADES);
        global $CFG, $selectedmodule, $courseid;
        $html = '<div class="table-responsive">';
        $html .= '<table class="table">';
        $html .= '  <thead>';
        $html .= '      <tr>';
        $html .= '          <th>'.get_string('candidateno', 'local_gugcat').'</th>';
        $html .= '          <th>'.get_string('studentno', 'local_gugcat').'</th>';
        $html .= '          <th>'.get_string('surname', 'local_gugcat').'</th>';
        $html .= '          <th>'.get_string('forename', 'local_gugcat').'</th>';
        foreach ($columns as $col) {
            $html .= '<th>'.$col.'</th>';
        }
        $html .= '          <th class="togglemultigrd">'.get_string('addallnewgrade', 'local_gugcat').'</th>';
        $html .= '          <th class="togglemultigrd">'.get_string('reasonnewgrade', 'local_gugcat').'</th>';
        $html .= '          <th>'.get_string('provisionalgrd', 'local_gugcat').'</th>';
        $html .= '          <th></th>';
        $html .= '      </tr>';
        $html .= '  </thead>';
        $html .= '  <tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
                $html .= '<td>'.$row->cnum.'</td>';
                $html .= '<td>'.$row->studentno.'</td>';
                $html .= '<td>'.$row->surname.'</td>';
                $html .= '<td>'.$row->forename.'</td>';
                $html .= '<td>'.$row->firstgrade.'</td>';
                foreach((array) $row->grades as $grade) {
                    $html .= '<td>'.$grade.'</td>';
                }
                $html .= '<td class="togglemultigrd">
                            '.$this->display_custom_select(
                                $grades,
                                get_string('choosegrade', 'local_gugcat')).'
                        </td>';
                $html .= '<td class="togglemultigrd">
                            '.$this->display_custom_select(
                                local_gugcat::$REASONS,
                                get_string('selectreason', 'local_gugcat'),
                                'multi-select-reason').'
                        </td>';
                $html .= '<td><b>'.$row->provisionalgrade.'</b></td>';
                $html .= '<td>
                            <a href="'.$CFG->wwwroot.'/local/gugcat/add/index.php?id='.$courseid.'&amp;activityid='.$selectedmodule->id.'&amp;studentid='.$row->studentno.'">
                                <button class="btn btn-default">
                                '.get_string('addgrade', 'local_gugcat').'
                                </button>
                            </a>
                        </td>';
            $html .= '</tr>';
        }
        $html .= '  </tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    private function header() {
        $html = '<div class="container">';
        $html .= '<h4 class="title">'.get_string('title', 'local_gugcat').'</h4>';
        $html .= $this->render_from_template('local_gugcat/gcat_tabs', (object)[
            'assessmenttabstr' =>get_string('assessmentlvlscore', 'local_gugcat'),
            'overviewtabstr' =>get_string('overviewaggregrade', 'local_gugcat'),
            'approvebtnstr' =>get_string('approvegrades', 'local_gugcat'),
        ]);
        $html .= '<div class="tabcontent">';

        return $html;
    }

    private function footer() {
        $html = '</div></div>';
        return $html;
    }


}