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

    public function display_grade_capture($activities_, $rows, $columns) {
        $courseid = $this->page->course->id;
        $modid = (($this->page->cm) ? $this->page->cm->id : null);
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        //url to add form
        $addformurl = new moodle_url('/local/gugcat/add/index.php', array('id' => $courseid, 'activityid' => $modid));
        //url action form
        $actionurl = 'index.php?id=' . $courseid . '&activityid=' . $modid;
        //add category id in the url if not null
        if(!is_null($categoryid)){
            $addformurl->param('categoryid', $categoryid);
            $actionurl .= '&categoryid=' . $categoryid;
        }

        //reindex activities and grades array
        $activities = array_values($activities_);
        $grades = array_values(local_gugcat::$GRADES);
        //grade capture columns and rows in html
        $htmlcolumns = null;
        $htmlrows = null;
        foreach ($columns as $col) {
            $htmlcolumns .= html_writer::tag('th', $col, array('class'=>'gradeitems'));
        }
        $htmlcolumns .= html_writer::tag('th', get_string('addallnewgrade', 'local_gugcat'), array('class' => 'togglemultigrd'));
        $htmlcolumns .= html_writer::tag('th', get_string('reasonnewgrade', 'local_gugcat'), array('class' => 'togglemultigrd'));
        $htmlcolumns .= html_writer::tag('th', get_string('provisionalgrd', 'local_gugcat'));
        $htmlcolumns .= html_writer::empty_tag('th');
        //grade capture rows
        foreach ($rows as $row) {
            //url to add grade form page
            $addformurl->param('studentid', $row->studentno);
            $htmlrows .= html_writer::start_tag('tr');
            //hidden inputs for id and provisional grades
            $htmlrows .= html_writer::empty_tag('input', array('name' => 'grades['.$row->studentno.'][id]', 'type' => 'hidden', 'value' => $row->studentno));
            $htmlrows .= html_writer::empty_tag('input', array('name' => 'grades['.$row->studentno.'][provisional]', 'type' => 'hidden', 'value' => ((strpos($row->provisionalgrade, 'Null') !== false) ? null : $row->provisionalgrade)));
            $htmlrows .= html_writer::tag('td', $row->cnum);
            $htmlrows .= html_writer::tag('td', $row->studentno);
            $htmlrows .= html_writer::tag('td', $row->surname);
            $htmlrows .= html_writer::tag('td', $row->forename);
            $htmlrows .= '<td>'. (($row->discrepancy) 
                ? '<div class="grade-discrepancy">'.$row->firstgrade.'</div>' 
                : $row->firstgrade ) .'</td>';
            foreach((array) $row->grades as $item) {
                $htmlrows .= '<td>'. (($item->discrepancy) 
                    ? '<div class="grade-discrepancy">'.$item->grade.'</div>' 
                    : $item->grade ).'</td>';
            }
            $htmlrows .= '<td class="togglemultigrd">'
                        .$this->display_custom_select(
                            $grades,
                            'grades['.$row->studentno.'][grade]',
                            get_string('choosegrade', 'local_gugcat')).'
                    </td>';
            $htmlrows .= '<td class="togglemultigrd">'.$this->display_custom_select(
                            local_gugcat::get_reasons(),
                            'reason',
                            get_string('selectreason', 'local_gugcat'),
                            'multi-select-reason',
                            'select-grade-reason').'
                            <input name="reason" value="" class="input-reason" id="input-reason" type="text"/>
                    </td>';
            $htmlrows .= '<td><b>'.$row->provisionalgrade.'</b></td>';
            $htmlrows .= '<td>
                            <button type="button" class="btn btn-default addnewgrade" onclick="location.href=\''.$addformurl.'\'">
                                '.get_string('addnewgrade', 'local_gugcat').'
                            </button>
                    </td>';
            $htmlrows .= html_writer::end_tag('tr');
        }
        $tabheader = !empty($activities) ? (object)[
            'addallgrdstr' =>get_string('addmultigrades', 'local_gugcat'),
            'downloadcsvstr' =>get_string('downloadcsv', 'local_gugcat'),
            'saveallbtnstr' =>get_string('saveallnewgrade', 'local_gugcat'),
            'grddiscrepancystr' => get_string('gradediscrepancy', 'local_gugcat'),
            'importgradesstr' => get_string('importgrades', 'local_gugcat'),
            'displayactivities' => true,
            'activities' => $activities,
        ] : null;
        //start displaying the table
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_tab_header', $tabheader);
        $html .= html_writer::start_tag('form', array('id' => 'multigradesform', 'method' => 'post', 'action' => $actionurl));
        $html .= $this->display_table($htmlrows, $htmlcolumns);
        $html .= html_writer::empty_tag('button', array('id' => 'release-submit', 'name' => 'release', 'type' => 'submit'));
        $html .= html_writer::empty_tag('button', array('id'=>'importgrades-submit', 'name'=> 'importgrades', 'type'=>'submit'));
        $html .= html_writer::end_tag('form');
        $html .= $this->footer();
        return $html;
    }

    public function display_add_grade_form($course, $student, $gradeversions) {
        $modname = (($this->page->cm) ? $this->page->cm->name : null);
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_add_form', (object)[
            'addnewgrade' =>get_string('addnewgrade', 'local_gugcat'),
            'course' => $course,
            'section' => $modname,
            'student' => $student
        ]);
        $html .= html_writer::start_tag('div', array('class'=>'mform-container'));
        foreach($gradeversions as $gradeversion){
            $html .= html_writer::start_tag('div', array('class'=>'form-group row'));
            $html .= html_writer::start_tag('div', array('class'=> 'col-md-3'));
            $html .= html_writer::tag('label', $gradeversion->itemname);
            $html .= html_writer::end_tag('div');
            $html .= html_writer::div(local_gugcat::convert_grade($gradeversion->grades[$student->id]->finalgrade), 'col-md-9 form-inline felement');
            $html .= html_writer::end_tag('div');
        }
        $html .= html_writer::end_tag('div');
        return $html;
    }

    public function display_aggregation_tool($rows, $activities) {
        $htmlcolumns = null;
        $htmlrows = null;
        foreach ($activities as $act) {
            $htmlcolumns .= html_writer::tag('th', $act->name);
        }
        $htmlcolumns .= html_writer::tag('th', get_string('requiresresit', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('aggregatedgrade', 'local_gugcat').'<i class="fa fa-cog"></i></th>');
        //grade capture rows
        foreach ($rows as $row) {
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->cnum);
            $htmlrows .= html_writer::tag('td', $row->studentno);
            $htmlrows .= html_writer::tag('td', $row->surname);
            $htmlrows .= html_writer::tag('td', $row->forename);
            foreach((array) $row->grades as $grade) {
                $htmlrows .= '<td>'.$grade.((strpos($grade, 'No grade') !== false) ? null : $this->context_actions()).'</td>';
            }
            $htmlrows .= '<td><i class="fa fa-times-circle"></i></td>';
            $htmlrows .= html_writer::empty_tag('td');
            $htmlrows .= html_writer::end_tag('tr');
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
        $html = html_writer::start_tag('div', array('class' => 'table-responsive'));
        $html .= html_writer::start_tag('table', array('class' => 'table'));
        $html .= html_writer::start_tag('thead');
        $html .= html_writer::start_tag('tr');
        $html .= html_writer::tag('th', get_string('candidateno', 'local_gugcat'));
        $html .= html_writer::tag('th', get_string('studentno', 'local_gugcat'));
        $html .= html_writer::tag('th', get_string('surname', 'local_gugcat'));
        $html .= html_writer::tag('th', get_string('forename', 'local_gugcat'));
        $html .= $columns;
        $html .= html_writer::end_tag('tr');
        $html .= html_writer::end_tag('thead');
        $html .= html_writer::start_tag('tbody');
        $html .= $rows;
        $html .= html_writer::end_tag('tbody');
        $html .= html_writer::end_tag('table');
        $html .= html_writer::end_tag('div');

        return $html;
    }

    private function context_actions() {
        $html = html_writer::empty_tag('i', array('class' => 'fa fa-ellipsis-h', 'data-toggle' => 'dropdown'));
        $html .= html_writer::start_tag('ul', array('class' => 'dropdown-menu'));
        $html .= html_writer::tag('li', get_string('amendgrades', 'local_gugcat'), array('class' => 'dropdown-item'));
        $html .= html_writer::tag('li', get_string('historicalamendments', 'local_gugcat'), array('class' => 'dropdown-item'));
        $html .= html_writer::end_tag('ul');
        return $html;
    }

    private function header() {
        $courseid = $this->page->course->id;
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        //reindex grade category array
        $categories = local_gugcat::get_grade_categories($courseid);
        $assessmenturl = new moodle_url('/local/gugcat/index.php', array('id' => $courseid));
        $assessmenturl.= $this->page->cm ? '&activityid='.$this->page->cm->id : null;
        $overviewurl = new moodle_url('/local/gugcat/overview/index.php', array('id' => $courseid));
        //add category id in the url if not null
        if(!is_null($categoryid)){
            $assessmenturl .= '&categoryid=' . $categoryid;
            $overviewurl .= '&categoryid=' . $categoryid;
        }
        $html = html_writer::start_tag('div', array('class' => 'gcat-container'));
        $html .= html_writer::tag('h4', get_string('title', 'local_gugcat'), array('class' => 'title'));
        $html .= $this->display_custom_select(
            array_values($categories),
            'select-category',
            null,
            'select-category',
            'select-category');
        $html .= html_writer::empty_tag('br');
        $html .= $this->render_from_template('local_gugcat/gcat_tabs', (object)[
            'assessmenttabstr' =>get_string('assessmentlvlscore', 'local_gugcat'),
            'overviewtabstr' =>get_string('overviewaggregrade', 'local_gugcat'),
            'releaseprvgrdstr' =>get_string('releaseprvgrades', 'local_gugcat'),
            'assessmenturl' =>$assessmenturl,
            'overviewurl' =>$overviewurl,
        ]);
        $html .= html_writer::start_tag('div', array('class' => 'tabcontent'));
        return $html;
    }

    private function footer() {
        $html = html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');
        return $html;
    }


}