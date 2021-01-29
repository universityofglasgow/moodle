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
    public function display_grade_capture($selectedmodule, $activities_, $rows, $columns) {
        $courseid = $this->page->course->id;
        $modid = (($selectedmodule) ? $selectedmodule->gradeitemid : null);
        $is_blind_marking = local_gugcat::is_blind_marking($this->page->cm);
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $ammendgradeparams = "?id=$courseid&activityid=$modid&page=$page";

        //url action form
        $actionurl = "index.php?id=$courseid&activityid=$modid&page=$page";
        //add category id in the url if not null
        if(!is_null($categoryid)){
            $actionurl .= '&categoryid=' . $categoryid;
            $ammendgradeparams .= '&categoryid=' . $categoryid;
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
        //released grade column
        $releasedarr = array_column($rows, 'releasedgrade');
        $displayreleasedgrade = (count(array_filter($releasedarr, function ($a) { return $a !== null;})) > 0);
        //--------COMMENT OUT FOR NOW
        // $htmlcolumns .= $displayreleasedgrade ? html_writer::tag('th', get_string('releasedgrade', 'local_gugcat')) : null;
        $htmlcolumns .= html_writer::empty_tag('th');
        //grade capture rows
        foreach ($rows as $row) {
            //url to add new grade
            $addformurl = new moodle_url('/local/gugcat/add/index.php', array('id' => $courseid, 'activityid' => $modid, 'studentid' => $row->studentno, 'page' => $page));
            if (!is_null($categoryid)){
                $addformurl->param('categoryid', $categoryid);
            }
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->idnumber);
            if(!$is_blind_marking){
                $htmlrows .= html_writer::tag('td', $row->surname, array('class' => 'blind-marking'));
                $htmlrows .= html_writer::tag('td', $row->forename, array('class' => 'blind-marking'));
            }
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
                            'newgrades['.$row->studentno.']',
                            get_string('choosegrade', 'local_gugcat'),
                            'multi-select-grade').'
                    </td>';
            $htmlrows .= '<td class="togglemultigrd">'.$this->display_custom_select(
                            local_gugcat::get_reasons(),
                            'reason',
                            get_string('selectreason', 'local_gugcat'),
                            'multi-select-reason',
                            'select-grade-reason').'
                            <input name="reason" value="" class="input-reason" id="input-reason" type="text"/>
                    </td>';
            $isgradehidden = (!isset($row->hidden)) ? null: (($row->hidden) ? '<br/>('.get_string('hiddengrade', 'local_gugcat').')' : '');
            if(is_null($row->provisionalgrade) || $row->provisionalgrade == '' || 
                $row->provisionalgrade == get_string('nograde', 'local_gugcat') || 
                $row->provisionalgrade == get_string('missinggrade', 'local_gugcat')){
                $htmlrows .= '<td class="provisionalgrade"><b>'.$row->provisionalgrade.'</b>'. $isgradehidden.'</td>';
            }else{
                $htmlrows .= '<td class="provisionalgrade"><b>'.$row->provisionalgrade.'</b>'.$this->context_actions($row->studentno, $isgradehidden, false, $ammendgradeparams, false).  $isgradehidden.'</td>';
            }
            // ----------COMMENT OUT FOR NOW
            // $htmlrows .= $displayreleasedgrade ? html_writer::tag('td', is_null($row->releasedgrade) ? get_string('nograde', 'local_gugcat') : $row->releasedgrade,array('class' => 'font-weight-bold') ) : null;
            $htmlrows .= '<td>
                            <button type="button" class="btn btn-default addnewgrade" onclick="location.href=\''.$addformurl.'\'">
                                '.get_string('addnewgrade', 'local_gugcat').'
                            </button>     
                    </td>';
        $htmlrows .= html_writer::end_tag('tr');
        }
        $tabheader = !empty($activities) ? (object)[
            'addallgrdstr' =>get_string('addmultigrades', 'local_gugcat'),
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
        $html .= html_writer::empty_tag('button', array('id' => 'multiadd-submit', 'name' => 'multiadd', 'type' => 'submit'));
        $html .= html_writer::empty_tag('button', array('id'=>'importgrades-submit', 'name'=> 'importgrades', 'type'=>'submit'));
        $html .= html_writer::empty_tag('input', array('name' => 'rowstudentno', 'type' => 'hidden', 'id'=>'studentno'));
        $html .= html_writer::empty_tag('button', array('id'=>'showhidegrade-submit', 'name'=> 'showhidegrade', 'type'=>'submit'));
        $html .= html_writer::empty_tag('button', array('id'=>'display-assessment', 'name'=> 'displayassessment', 'type'=>'submit'));
        $html .= html_writer::end_tag('form');
        $html .= $this->footer();
        return $html;
    }

    public function display_add_edit_grade_form($course, $student, $gradeversions, $isaddform) {
        $modname = (($this->page->cm) ? $this->page->cm->name : null);
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_form_details', (object)[
            'title' => $isaddform ? get_string('addnewgrade', 'local_gugcat') : get_string('editgrade', 'local_gugcat'),
            'course' => $course,
            'section' => $modname,
            'student' => $student,
            'blindmarking'=> !local_gugcat::is_blind_marking($this->page->cm) ? true : null
        ]);
        $html .= html_writer::start_tag('div', array('class'=>'mform-container'));
        foreach($gradeversions as $gradeversion){
            $html .= html_writer::start_tag('div', array('class'=>'form-group row'));
            $html .= html_writer::start_tag('div', array('class'=> 'col-md-3'));
            if ($gradeversion->itemname == get_string('moodlegrade', 'local_gugcat'))
                $html .= html_writer::tag('label', $gradeversion->itemname. date(" j/n/Y", strtotime(userdate($gradeversion->timemodified))));
            else 
                $html .= html_writer::tag('label', $gradeversion->itemname);
            $html .= html_writer::end_tag('div');
            $html .= html_writer::div(local_gugcat::convert_grade($gradeversion->grades[$student->id]->grade), 'col-md-9 form-inline felement');
            $html .= html_writer::end_tag('div');
        }
        $html .= html_writer::end_tag('div');
        return $html;
    }

    public function display_aggregation_tool($rows, $activities) {
        $courseid = $this->page->course->id;
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);  
        $is_blind_marking = local_gugcat::is_blind_marking();

        //url to grade form
        $actionurl = "index.php?id=$courseid&page=$page";
        //add category id in the url if not null
        $historyeditcategory = null;
        $gradeformhistorycategory = null;
        if(!is_null($categoryid)){
            $actionurl .= '&categoryid=' . $categoryid;
            $historyeditcategory = '&categoryid=' . $categoryid;
            $gradeformhistorycategory = '&categoryid=' . $categoryid;
        }

        $htmlcolumns = null;
        $htmlrows = null;
        foreach ($activities as $act) {
            $htmlcolumns .= html_writer::tag('th', $act->name);
        }
        $htmlcolumns .= html_writer::tag('th', get_string('requiresresit', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('percentcomplete', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('aggregatedgrade', 'local_gugcat'));
        //grade capture rows

        foreach ($rows as $row) {
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->cnum);
            $htmlrows .= html_writer::tag('td', $row->idnumber);
            if(!$is_blind_marking){
                $htmlrows .= html_writer::tag('td', $row->surname, array('class' => 'blind-marking'));
                $htmlrows .= html_writer::tag('td', $row->forename, array('class' => 'blind-marking'));
            }
            foreach((array) $row->grades as $grade) {
                $ammendgradeparams = "?id=$courseid&activityid=$grade->activityid&page=$page" . $historyeditcategory;
                $courseformhistoryparams = "?id=$courseid&cnum=$row->cnum&page=$page" . $gradeformhistorycategory;
                $htmlrows .= '<td>'.$grade->grade.((strpos($grade->grade, 'No grade') !== false) ? null : $this->context_actions($row->studentno, null, false, $ammendgradeparams, true)).'</td>';
            }
            $classname = (is_null($row->resit) ? "fa fa-times-circle" : "fa fa-check-circle");
            $htmlrows .= '<td><i class="'.$classname.'"
                        onclick="
                        studno = document.getElementById(\'resitstudentno\');
                        var button = document.getElementById(\'resit-submit\');
                        studno.value = \''.$row->studentno.'\';
                        button.click();
                        "></i></td>';
            $htmlrows .= html_writer::tag('td', $row->completed);
            $zero_resit_weight = $row->resitexist ? 'zero-resit-weight': '';
            $htmlrows .= ($row->aggregatedgrade->display != get_string('missinggrade', 'local_gugcat')) 
            ? html_writer::start_tag('td', array('class' => $zero_resit_weight)).$row->aggregatedgrade->display.$this->context_actions($row->studentno, null, true, $courseformhistoryparams, true).html_writer::end_tag('td')
            : html_writer::tag('td', $row->aggregatedgrade->display);
            $htmlrows .= html_writer::end_tag('tr');
        }
        $hide_release = in_array(get_string('missinggrade', 'local_gugcat'), array_column(array_column($rows, 'aggregatedgrade'), 'display'));
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_tab_header', (object)[
            'downloadcsvstr' =>get_string('downloadcsv', 'local_gugcat'),
            'releasefinalstr' =>$hide_release ? null : get_string('releasefinalassessment', 'local_gugcat'),
        ]);
        $html .= html_writer::start_tag('form', array('id' => 'requireresitform', 'method' => 'post', 'action' => $actionurl));
        $html .= $this->display_table($htmlrows, $htmlcolumns, false, true);
        $html .= html_writer::empty_tag('input', array('id'=>'resitstudentno', 'name' => 'rowstudentno', 'type' => 'hidden'));
        $html .= html_writer::empty_tag('button', array('id'=>'resit-submit', 'name'=> 'resit', 'type'=>'submit'));
        $html .= html_writer::empty_tag('button', array('id'=>'downloadcsv-submit', 'name'=> 'downloadcsv', 'type'=>'submit'));
        $html .= html_writer::empty_tag('button', array('id'=>'finalrelease-submit', 'name'=> 'finalrelease', 'type'=>'submit'));
        $html .= html_writer::empty_tag('button', array('id'=>'display-assessment', 'name'=> 'displayassessment', 'type'=>'submit'));
        $html .= html_writer::end_tag('form');
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

    public function display_adjust_override_grade_form($student) {
        $setting = required_param('setting', PARAM_INT);
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_form_details', (object)[
            'title' =>get_string(($setting != 0 ? 'overridestudgrade' : 'adjustcourseweight'), 'local_gugcat'),
            'student' => $student,
            'blindmarking'=> !local_gugcat::is_blind_marking() ? true : null
        ]);
        return $html;
    }

    public function display_grade_history($student, $activity, $rows){
        $htmlcolumns = null;
        $htmlrows = null;
        $htmlcolumns .= html_writer::tag('th', 'Date & Time');
        $htmlcolumns .= html_writer::tag('th', 'Grade');
        $htmlcolumns .= html_writer::tag('th', 'Revised By');
        $htmlcolumns .= html_writer::tag('th', 'Type');
        $htmlcolumns .= html_writer::tag('th', 'Notes / Reason for Revision');
        $htmlcolumns .= html_writer::tag('th', 'Attachments');
        foreach($rows as $row){
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->date);
            $htmlrows .= html_writer::tag('td', $row->grade);
            $htmlrows .= html_writer::tag('td', $row->modby);
            $htmlrows .= html_writer::tag('td', $row->type);
            $htmlrows .= html_writer::tag('td', $row->notes);

            $htmlrows .= '<td>'.(!is_null($row->docs) ? '<a href = '.$row->docs.'>'.$row->docname : null) .'</td>';
            $htmlrows .= html_writer::end_tag('tr');
        }
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_form_details', (object)[
            'title' =>get_string('assessmentgradehistory', 'local_gugcat'),
            'student' => $student,
            'activity' => $activity,
            'blindmarking'=> !local_gugcat::is_blind_marking($this->page->cm) ? true : null
        ]);
        $html .= $this->display_table($htmlrows, $htmlcolumns, true);
        return $html;
    }

    public function display_course_grade_history($student, $rows, $activities){
        $htmlcolumns = null;
        $htmlrows = null;

        $htmlcolumns .= html_writer::tag('th', 'Date & Time');
        $htmlcolumns .= html_writer::tag('th', 'Revised By');
        $htmlcolumns .= html_writer::tag('th', 'Course Grade');
        foreach($activities as $act){
            $htmlcolumns .= html_writer::tag('th', $act->name. '<br> Weigthing');
        }
        $htmlcolumns .= html_writer::tag('th', 'Notes / Reason for Revision');
        foreach($rows as $row){
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->date);
            $htmlrows .= html_writer::tag('td', $row->modby);
            $htmlrows .= html_writer::tag('td', $row->grade);
            for($i=0; $i<sizeof($activities); $i++){
                $weight = isset($row->overridden) ? get_string('nogradeweight', 'local_gugcat')  : 
                (isset($row->grades[$i]) ? round((float)$row->grades[$i]->information * 100) . '%' : get_string('nogradeweight', 'local_gugcat'));
                $htmlrows .= html_writer::tag('td', $weight);
            }
            $htmlrows .= html_writer::tag('td', $row->notes);

            $htmlrows .= html_writer::end_tag('tr');
        }
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_form_details', (object)[
            'title' =>get_string('coursegradehistory', 'local_gugcat'),
            'student' => $student,
            'blindmarking'=> !local_gugcat::is_blind_marking($this->page->cm) ? true : null
        ]);
        $html .= $this->display_table($htmlrows, $htmlcolumns, true);
        return $html;
    }

    private function display_table($rows, $columns, $history = false, $aggregation = false) {
        $is_blind_marking = local_gugcat::is_blind_marking($this->page->cm);
        $html = html_writer::start_tag('div', array('class' => 'table-responsive'));
        $html .= html_writer::start_tag('table', array('class' => 'table'));
        $html .= html_writer::start_tag('thead');
        $html .= html_writer::start_tag('tr');
        if(!$history){
            if($aggregation){
                $html .= html_writer::tag('th', get_string('candidateno', 'local_gugcat'));
            }
            $html .= html_writer::tag('th', get_string('studentno', 'local_gugcat'));
            if(!$is_blind_marking){
                $html .= html_writer::tag('th', get_string('surname', 'local_gugcat'), array('class' => 'blind-marking'));
                $html .= html_writer::tag('th', get_string('forename', 'local_gugcat'), array('class' => 'blind-marking'));
            }
        }
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

    private function context_actions($studentno, $ishidden=null, $is_aggregrade = false, $link = null, $is_overviewpage = false) {
        $html = html_writer::tag('i', null, array('class' => 'fa fa-ellipsis-h', 'data-toggle' => 'dropdown'));
        $html .= html_writer::start_tag('ul', array('class' => 'dropdown-menu'));
        $link .=  '&studentid='.$studentno;
        if($is_aggregrade){
            $gradeformurl = new moodle_url('/local/gugcat/overview/gradeform/index.php').$link;
            $coursehistoryurl = new moodle_url('/local/gugcat/overview/history/index.php').$link;
            $adjustlink = $gradeformurl. '&setting=' . ADJUST_WEIGHT_FORM;
            $overridelink = $gradeformurl . '&setting=' . OVERRIDE_GRADE_FORM;
            $html .= html_writer::tag('li', get_string('adjustcourseweight', 'local_gugcat'), array('class' => 'dropdown-item', 'onclick' => 'location.href=\''.$adjustlink.'\''));
            $html .= html_writer::tag('li', get_string('overrideggregrade', 'local_gugcat'), array('class' => 'dropdown-item', 'onclick' => 'location.href=\''.$overridelink.'\''));
            $html .= html_writer::tag('li', get_string('viewcoursehistory', 'local_gugcat'), array('class' => 'dropdown-item', 'onclick' => 'location.href=\''.$coursehistoryurl.'\''));
        }else{
            $historylink = new moodle_url('/local/gugcat/history/index.php').$link;
            $editlink = new moodle_url('/local/gugcat/edit/index.php').$link.'&overview='.($is_overviewpage ? 1 : 0);

            $html .= html_writer::tag('li', get_string('amendgrades', 'local_gugcat'), array('class' => 'dropdown-item', 'onclick' => 'location.href=\''.$editlink.'\''));
            $html .= html_writer::tag('li', get_string('assessmentgradehistory', 'local_gugcat'), array('class' => 'dropdown-item', 'onclick' => 'location.href=\''.$historylink.'\''));
            $html .= html_writer::tag('li', !empty($ishidden) ? get_string('showgrade', 'local_gugcat') : get_string('hidefromstudent', 'local_gugcat'), array('class' => 'dropdown-item hide-show-grade',
                'onclick'=>
                'document.getElementById("studentno").value = '.$studentno.';
                $("#showhidegrade-submit").click();'));
        }
        $html .= html_writer::end_tag('ul');
        return $html;
    }  

    private function gcat_settings() {
        $courseid = (int)$this->page->course->id;
        $coursecontext = context_course::instance($courseid);
        $checkboxvalue = local_gugcat::get_value_of_customefield_checkbox($courseid, $coursecontext->id);

        $html = html_writer::tag('i', null, array('id' => 'gcat-cog', 'class' => 'fa fa-cog', 'data-toggle' => 'dropdown'));
        $html .= html_writer::start_tag('ul', array('class' => 'dropdown-menu'));
        $html .= html_writer::tag('li', get_string('hideidentities', 'local_gugcat'), array('id'=>'btn-identities', 'class' => 'dropdown-item'));
        $html .= html_writer::tag('li', ($checkboxvalue == 1 ? get_string('switchoffdisplay', 'local_gugcat') : get_string('switchondisplay', 'local_gugcat')), array('id'=>'btn-switch-display', 'class' => 'dropdown-item'));
        $html .= html_writer::end_tag('ul');
        return $html;
    }  

    private function header() {
        $courseid = $this->page->course->id;
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $activityid = optional_param('activityid', null, PARAM_INT);
        //reindex grade category arrayco
        $categories = local_gugcat::get_grade_categories($courseid);
        $assessmenturl = new moodle_url('/local/gugcat/index.php', array('id' => $courseid));
        $assessmenturl.= $activityid ? '&activityid='.$activityid : null;
        $coursegradeurl = new moodle_url('/local/gugcat/overview/index.php', array('id' => $courseid));
        //add category id in the url if not null
        if(!is_null($categoryid)){
            $assessmenturl .= '&categoryid=' . $categoryid;
            $coursegradeurl .= '&categoryid=' . $categoryid;
        }
        $html = html_writer::start_tag('div', array('class' => 'gcat-container'));
        $html .= html_writer::tag('span', get_string('title', 'local_gugcat'), array('class' => 'gcat-title'));
        $html .= html_writer::start_tag('div', array('class' => 'row gcat-header'));
        $html .= $this->display_custom_select(
            array_values($categories),
            'select-category',
            null,
            'select-category',
            'select-category');
        $html .= has_capability('moodle/site:approvecourse', context_system::instance())
            ? $this->gcat_settings() 
            : null;
        $html .= html_writer::end_tag('div');
        $html .= $this->render_from_template('local_gugcat/gcat_tabs', (object)[
            'assessmenttabstr' =>get_string('assessmentgradecapture', 'local_gugcat'),
            'coursegradetabstr' =>get_string('coursegradeaggregation', 'local_gugcat'),
            'releaseprvgrdstr' =>get_string('releaseprvgrades', 'local_gugcat'),
            'assessmenturl' =>$assessmenturl,
            'coursegradeurl' =>$coursegradeurl,
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
