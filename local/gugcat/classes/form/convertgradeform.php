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
 * A moodleform allowing the conversion of grades to 22 point scale
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_gugcat\grade_converter;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/local/gugcat/locallib.php');
class convertform extends moodleform {
    //Add elements to form
    public function definition() {

        local_gugcat::set_grade_scale();

        $gradetypestr = array(
            GRADE_TYPE_TEXT => get_string('modgradetypenone', 'grades'),
            GRADE_TYPE_SCALE => get_string('modgradetypescale', 'grades'),
            GRADE_TYPE_VALUE => get_string('modgradetypepoint', 'grades'),
        );

        $scales = array(
            SCHEDULE_A => get_string('schedulea', 'local_gugcat'),
            SCHEDULE_B => get_string('scheduleb', 'local_gugcat')
        );

        $dbtemplates = grade_converter::get_conversion_templates();
        $templates = empty($dbtemplates) ? array() : array_column($dbtemplates, 'templatename', 'id');
        $templates[0] = get_string('selectconversion', 'local_gugcat');

        $activity = $this->_customdata['activity'];
        $is_converted = !is_null($activity->gradeitem->iteminfo) && !empty($activity->gradeitem->iteminfo);
        $defaulttype = $is_converted ? $activity->gradeitem->iteminfo : SCHEDULE_A;
        $existing = grade_converter::retrieve_grade_conversion($activity->gradeitemid);
        $notes = $existing ? 'convertexist' : 'convertnew';

        $grades = grade_converter::process_defaults($activity->gradeitem->iteminfo == SCHEDULE_A, local_gugcat::$SCHEDULE_A, $existing);
        $schedB = grade_converter::process_defaults($activity->gradeitem->iteminfo == SCHEDULE_B, local_gugcat::$SCHEDULE_B, $existing);
        // Divide schedule A into two array
        $schedA1 = array_slice($grades, 0, (count($grades) / 2)+1);
        $schedA2 = array_slice($grades, (count($grades) / 2)+1);
        $keys = array_keys($grades);
        $keysA1 = array_slice($keys, 0, (count($keys) / 2)+1);
        $keysA2 = array_slice($keys, (count($keys) / 2)+1);

        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('html', '<div class="mform-container">');
        $mform->addElement('static', 'assessment', get_string('assessment'), $activity->name); 
        $mform->setType('assessment', PARAM_NOTAGS); 
        $mform->addElement('static', 'gradetype', get_string('gradetype', 'grades'), $gradetypestr[$activity->gradeitem->gradetype]); 
        $mform->setType('gradetype', PARAM_NOTAGS); 
        $mform->addElement('static', 'maximumgrade', get_string('grademax', 'grades'), intval($activity->gradeitem->grademax)); 
        $mform->setType('maximumgrade', PARAM_NOTAGS); 
        $mform->addElement('select', 'scale', get_string('selectscale', 'local_gugcat'), $scales, ['id' => 'select-scale', 'class' => 'mform-custom-select']); 
        $mform->setType('scale', PARAM_NOTAGS); 
        $mform->setDefault('scale', $defaulttype);
        $mform->addElement('select', 'template', get_string('selectprevconv', 'local_gugcat'), $templates, ['id' => 'select-template', 'class' => 'mform-custom-select']); 
        $mform->setType('template', PARAM_NOTAGS); 
        $mform->setDefault('template', 0); 
        // Schedule A tables
        $mform->addElement('html', html_writer::start_tag('div', array('id' => 'table-schedulea', 'class' => 'row'))); 
        $this->setup_table($schedA1, $mform, 'schedA', $keysA1);
        $this->setup_table($schedA2, $mform, 'schedA', $keysA2);
        $mform->addElement('html', html_writer::end_tag('div')); 
        // Schedule B table
        $mform->addElement('html', html_writer::start_tag('div', array('id' => 'table-scheduleb', 'class' => 'row hidden'))); 
        $this->setup_table($schedB, $mform, 'schedB');
        $mform->addElement('html', html_writer::tag('div', null, array('class' => 'col'))); 
        $mform->addElement('html', html_writer::end_tag('div')); 

        $mform->addElement('html', html_writer::tag('p', get_string('noteconversion', 'local_gugcat'), array('class' => 'mt-3 font-weight-bold'))); 

        $mform->addElement('text', 'templatename', get_string('pleaseprovidetemplatename', 'local_gugcat')); 
        $mform->setType('templatename', PARAM_NOTAGS); 

        $mform->addElement('html', '</div>');
        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges', 'local_gugcat'));
        $buttonarray[] =& $mform->createElement('button', 'convertbutton', get_string('savechanges', 'local_gugcat'));
        $mform->addGroup($buttonarray, 'buttonarr', '', array(''), false);

        // hidden params
        $mform->addElement('hidden', 'id', required_param('id', PARAM_INT));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'activityid', required_param('activityid', PARAM_INT));
        $mform->setType('activityid', PARAM_INT);
        $mform->addElement('hidden', 'categoryid', optional_param('categoryid', null, PARAM_INT));
        $mform->setType('categoryid', PARAM_INT);
        $mform->addElement('hidden', 'childactivityid', optional_param('childactivityid', null, PARAM_INT));
        $mform->setType('childactivityid', PARAM_INT);
        $mform->addElement('hidden', 'grademax', intval($activity->gradeitem->grademax));
        $mform->setType('grademax', PARAM_INT);
        $mform->addElement('hidden', 'notes', $notes);
        $mform->setType('notes', PARAM_NOTAGS);

    }

    function setup_table($grades, $mform, $name, $keys = array()) {
        // Percent field attributes
        $prcattr = array(
            'class' => 'input-scale-pt mb-0 input-prc',
            'type' => 'number',
            'maxlength' => '6',
            'size' => '10'
        );
        // Point field attributes
        $pointattr = array(
            'class' => 'input-scale-pt mb-0 input-pt',
            'type' => 'number',
            'maxlength' => '6',
            'size' => '10',
            'data-toggle' => 'tooltip',
            'data-placement' => 'right',
            'data-html' => 'true',
            'title' => get_string('pointtooltip', 'local_gugcat')
        );
        $mform->addElement('html', html_writer::start_tag('div', array('class' => 'col'))); 
        $html = html_writer::start_tag('table', array_merge(array('id'=>'gcat-table', 'class' => 'table')));
        $html .= html_writer::start_tag('thead');
        $html .= html_writer::start_tag('tr');   
        $html .= html_writer::tag('th', get_string('grade'));
        $html .= html_writer::tag('th', get_string('lowerboundper', 'local_gugcat'));
        $html .= html_writer::tag('th', get_string('lowerboundpt', 'local_gugcat'));
        $html .= html_writer::end_tag('tr');
        $html .= html_writer::end_tag('thead');
        $html .= html_writer::start_tag('tbody');
        foreach ($grades as $index=>$grd) {
            $index = empty($keys) ? $index : $keys[$index];
            $html .= html_writer::start_tag('tr');
            $html .= html_writer::tag('td', $grd->grade);
            $html .= html_writer::start_tag('td');
            $mform->addElement('html', $html); 
            $mform->addElement('text', $name."[$index]", null, $prcattr); 
            $mform->setDefault($name."[$index]", is_null($grd->lowerboundary) ? null : floatval($grd->lowerboundary) );
            $mform->setType($name."[$index]", PARAM_NOTAGS);
            $mform->addRule($name."[$index]", null, 'numeric', null, 'client');
            if($index == 1){
                $mform->addRule($name."[$index]", get_string('errorfieldzero', 'local_gugcat'), 'regex', '/^[0]$/', 'client');
                $mform->addRule($name."[$index]", get_string('errorfieldzero', 'local_gugcat'), 'regex', '/^[0]$/', 'server');
            }else{
                $mform->addRule($name."[$index]", get_string('errorfielddecimal', 'local_gugcat'), 'regex', '/^[0-9]+(\.[0-9]{1,2})?$/', 'client');
                $mform->addRule($name."[$index]", get_string('errorfielddecimal', 'local_gugcat'), 'regex', '/^[0-9]+(\.[0-9]{1,2})?$/', 'server');
            }
            $html = html_writer::end_tag('td');
            $html .= html_writer::start_tag('td');
            $mform->addElement('html', $html); 
            $mform->addElement('text', $name."_pt[$index]", null, $pointattr); 
            $mform->setDefault($name."_pt[$index]", is_null($grd->lowerboundary) ? null : floatval($grd->lowerboundary) );
            $mform->setType($name."_pt[$index]", PARAM_NOTAGS);
            $mform->addRule($name."_pt[$index]", null, 'numeric', null, 'client');
            if($index == 1){
                $mform->addRule($name."_pt[$index]", get_string('errorfieldzero', 'local_gugcat'), 'regex', '/^[0]$/', 'client');
                $mform->addRule($name."_pt[$index]", get_string('errorfieldzero', 'local_gugcat'), 'regex', '/^[0]$/', 'server');
            }else{
                $mform->addRule($name."_pt[$index]", get_string('errorfielddecimal', 'local_gugcat'), 'regex', '/^[0-9]+(\.[0-9]{1,2})?$/', 'client');
                $mform->addRule($name."_pt[$index]", get_string('errorfielddecimal', 'local_gugcat'), 'regex', '/^[0-9]+(\.[0-9]{1,2})?$/', 'server');
            }
            $html = html_writer::end_tag('td');
            $html .= html_writer::end_tag('tr');
        }
        $html .= html_writer::end_tag('tbody');
        $html .= html_writer::end_tag('table');
        $mform->addElement('html', $html); 
        $mform->addElement('html', html_writer::end_tag('div')); 
    }
}
