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
        $courseid = $this->page->course->id;
        $modid = $this->page->cm->id;
        $addformurl = new moodle_url('/local/gugcat/add/index.php', array('id' => $courseid, 'activityid' => $modid));
        //reindex activities and grades array
        $activities_ = array_values($activities);
        $grades = array_values(local_gugcat::$GRADES);
        //grade capture columns and rows in html
        $htmlcolumns = null;
        $htmlrows = null;
        foreach ($columns as $col) {
            $htmlcolumns .= '<th>'.$col.'</th>';
        }
        $htmlcolumns .= '          <th class="togglemultigrd">'.get_string('addallnewgrade', 'local_gugcat').'</th>';
        $htmlcolumns .= '          <th class="togglemultigrd">'.get_string('reasonnewgrade', 'local_gugcat').'</th>';
        $htmlcolumns .= '          <th>'.get_string('provisionalgrd', 'local_gugcat').'</th>';
        $htmlcolumns .= '          <th></th>';
        //grade capture rows
        foreach ($rows as $row) {
            $addformurl->param('studentid', $row->studentno);
            $htmlrows .= '<tr>';
            $htmlrows .= '<td>'.$row->cnum.'</td>';
            $htmlrows .= '<td>'.$row->studentno.'</td>';
            $htmlrows .= '<td>'.$row->surname.'</td>';
            $htmlrows .= '<td>'.$row->forename.'</td>';
            $htmlrows .= '<td>'. (($row->discrepancy) 
                ? '<div class="grade-discrepancy">'.$row->firstgrade.'</div>' 
                : $row->firstgrade ) .'</td>';
            foreach((array) $row->grades as $item) {
                $htmlrows .= '<td>'. (($item->discrepancy) 
                    ? '<div class="grade-discrepancy">'.$item->grade.'</div>' 
                    : $item->grade ).'</td>';
            }
            $htmlrows .= '<td class="togglemultigrd">
                        <input type="hidden" name="grades['.$row->studentno.'][id]" value="'.$row->studentno.'" />
                        '.$this->display_custom_select(
                            $grades,
                            'grades['.$row->studentno.'][grade]',
                            get_string('choosegrade', 'local_gugcat')).'
                    </td>';
            $htmlrows .= '<td class="togglemultigrd">
                        '.$this->display_custom_select(
                            local_gugcat::get_reasons(),
                            'reason',
                            get_string('selectreason', 'local_gugcat'),
                            'multi-select-reason',
                            'select-grade-reason').'
                            <input name="reason" value="" class="input-reason" id="input-reason" type="text"/>
                    </td>';
            $htmlrows .= '<td><b>'.$row->provisionalgrade.'</b></td>';
            $htmlrows .= '<td>
                            <button type="button" class="btn btn-default" onclick="location.href=\''.$addformurl.'\'">
                                '.get_string('addnewgrade', 'local_gugcat').'
                            </button>
                    </td>';
            $htmlrows .= '</tr>';
        }

        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_tab_header', (object)[
            'addallgrdstr' =>get_string('addallnewgrade', 'local_gugcat'),
            'downloadcsvstr' =>get_string('downloadcsv', 'local_gugcat'),
            'saveallbtnstr' =>get_string('saveallnewgrade', 'local_gugcat'),
            'grddiscrepancystr' => get_string('gradediscrepancy', 'local_gugcat'),
            'displayactivities' => true,
            'activities' => $activities_,
        ]);
        $html .= '<form action="index.php?id=' . $courseid . '&amp;activityid=' . $modid . '" method="post" id="multigradesform">';
        $html .= $this->display_table($htmlrows, $htmlcolumns);
        $html .= '</form>';
        $html .= $this->footer();
        return $html;
    }

    public function display_add_grade_form($course, $activity, $gbgrade, $student, $gradeversions) {
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_add_form', (object)[
            'addnewgrade' =>get_string('addnewgrade', 'local_gugcat'),
            'course' => $course,
            'section' => $activity,
            'student' => $student,
            'gbgrade' => $gbgrade
        ]);
        $html .= '<div class="mform-container">';
        foreach ($gradeversions as $gradeversion){
            $html .= '   <div class="form-group row">';
            $html .= '   <div class="col-md-3">';
            $html .= '         <label>'.$gradeversion->itemname.'</label>';
            $html .= '   </div>';
            $html .= '   <div class="col-md-9 form-inline felement">'.local_gugcat::convert_grade($gradeversion->grades[$student->id]->finalgrade).'</div>';
            $html .= '  </div>';
        }
        $html .= '</div>';
        return $html;
    }

    public function display_aggregation_tool($rows, $activities) {
        $htmlcolumns = null;
        $htmlrows = null;
        foreach ($activities as $act) {
            $htmlcolumns .= '<th>'.$act->name.'</th>';
        }
        $htmlcolumns .= '<th>'.get_string('requiresresit', 'local_gugcat').'</th>';
        $htmlcolumns .= '<th>'.get_string('aggregatedgrade', 'local_gugcat')
                    .'<i class="fa fa-cog"></i></th>';
        //grade capture rows
        foreach ($rows as $row) {
            $htmlrows .= '<tr>';
            $htmlrows .= '<td>'.$row->cnum.'</td>';
            $htmlrows .= '<td>'.$row->studentno.'</td>';
            $htmlrows .= '<td>'.$row->surname.'</td>';
            $htmlrows .= '<td>'.$row->forename.'</td>';
            foreach((array) $row->grades as $grade) {
                $htmlrows .= '<td>'.$grade.((strpos($grade, 'Null') !== false) ? null : $this->context_actions()).'</td>';
            }
            $htmlrows .= '<td><i class="fa fa-times-circle"></i></td>';
            $htmlrows .= '<td></td>';
            $htmlrows .= '</tr>';
        }
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_tab_header', (object)[
            'downloadcsvstr' =>get_string('downloadcsv', 'local_gugcat'),
        ]);
        $html .= $this->display_table($htmlrows, $htmlcolumns);
        $html .= $this->footer();
        return $html;
    }

    public function display_custom_select($options, $name = null, $default = null, $class = null, $id = null) {
        $html = $this->render_from_template('local_gugcat/gcat_custom_select', (object)[
            'default' => $default ,
            'options' => $options,
            'class' => $class,
            'id' => $id,
            'name' => $name,
        ]);
        return $html;
    }

    private function display_table($rows, $columns) {
        $html = '<div class="table-responsive">';
        $html .= '<table class="table">';
        $html .= '  <thead>';
        $html .= '      <tr>';
        $html .= '          <th>'.get_string('candidateno', 'local_gugcat').'</th>';
        $html .= '          <th>'.get_string('studentno', 'local_gugcat').'</th>';
        $html .= '          <th>'.get_string('surname', 'local_gugcat').'</th>';
        $html .= '          <th>'.get_string('forename', 'local_gugcat').'</th>';
        $html .= $columns;
        $html .= '      </tr>';
        $html .= '  </thead>';
        $html .= '  <tbody>';
        $html .= $rows;
        $html .= '  </tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    private function context_actions() {
        $html = '<i class="fa fa-ellipsis-h" data-toggle="dropdown" ></i>';
        $html .= '<ul class="dropdown-menu">';
        $html .=    '<li class="dropdown-item">'.get_string('amendgrades', 'local_gugcat').'</li>';
        $html .=    '<li class="dropdown-item">'.get_string('historicalamendments', 'local_gugcat').'</li>';
        $html .= '</ul>';
        return $html;
    }

    private function header() {
        $courseid = $this->page->course->id;
        $assessmenturl = new moodle_url('/local/gugcat/index.php', array('id' => $courseid));
        $assessmenturl.= $this->page->cm ? '&activityid='.$this->page->cm->id : null;
        $overviewurl = new moodle_url('/local/gugcat/overview/index.php', array('id' => $courseid));
        $html = '<div class="gcat-container">';
        $html .= '<h4 class="title">'.get_string('title', 'local_gugcat').'</h4>';
        $html .= $this->render_from_template('local_gugcat/gcat_tabs', (object)[
            'assessmenttabstr' =>get_string('assessmentlvlscore', 'local_gugcat'),
            'overviewtabstr' =>get_string('overviewaggregrade', 'local_gugcat'),
            'approvebtnstr' =>get_string('approvegrades', 'local_gugcat'),
            'assessmenturl' =>$assessmenturl,
            'overviewurl' =>$overviewurl,
        ]);
        $html .= '<div class="tabcontent">';

        return $html;
    }

    private function footer() {
        $html = '</div></div>';
        return $html;
    }


}