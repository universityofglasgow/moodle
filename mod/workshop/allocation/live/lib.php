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
 * Allocates late submissions using the scheduled allocator settings
 *
 * @package    workshopallocation_live
 * @subpackage mod_workshop
 * @copyright  2014 Albert Gasset <albertgasset@fsfe.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/workshop/locallib.php');
require_once($CFG->dirroot.'/mod/workshop/allocation/lib.php');
require_once($CFG->dirroot.'/mod/workshop/allocation/random/lib.php');

class workshop_live_allocator_form extends moodleform {

    public function definition() {
        global $PAGE;

        $mform = $this->_form;
        $workshop = $this->_customdata['workshop'];

        $plugindefaults = get_config('workshopallocation_random');

        // Check late submissions setting
        if (!$workshop->latesubmissions) {
            $settingsurl = new moodle_url('/course/modedit.php', array('update' => $workshop->cm->id, 'return' => 1));
            $settingslink = html_writer::link($settingsurl, get_string('workshopsettings', 'workshopallocation_live'));
            $latesubmissions = html_writer::tag('strong', get_string('latesubmissions', 'workshop'));
            $a = array('latesubmissions' => $latesubmissions, 'workshopsettings' => $settingslink);
            $message = get_string('latesubmissionsdisabled', 'workshopallocation_live', $a);
            $mform->addElement('html', html_writer::tag('p', $message));
            return;
        }

        $strenbaled = get_string('enabled', 'workshopallocation_live');
        $cm = get_coursemodule_from_instance('workshop', $workshop->id, 0, false, MUST_EXIST);
        $url = new moodle_url($PAGE->url, array('method' => 'scheduled', 'cmid' => $cm->id));
        $strenabledinfo = get_string('enabledinfo', 'workshopallocation_live', $url->out());
        $mform->addElement('checkbox', 'enabled', $strenbaled, $strenabledinfo);

        // Number of reviews
        $group = array();
        $options = workshop_random_allocator::available_numofreviews_list();
        $group[] = $mform->createElement('select', 'numofreviews', '', $options);
        $mform->setDefault('numofreviews', $plugindefaults->numofreviews);
        $options = array(
            workshop_random_allocator_setting::NUMPER_SUBMISSION => get_string('numperauthor', 'workshopallocation_random'),
            workshop_random_allocator_setting::NUMPER_REVIEWER   => get_string('numperreviewer', 'workshopallocation_random')
        );
        $group[] = $mform->createElement('select', 'numper', '', $options);
        $mform->setDefault('numper', workshop_random_allocator_setting::NUMPER_SUBMISSION);
        $label = get_string('numofreviews', 'workshopallocation_random');
        $mform->addGroup($group, 'groupnumofreviews', $label,  array(' '), false);

        // Group mode
        $groupmode = groups_get_activity_groupmode($workshop->cm, $workshop->course);
        switch ($groupmode) {
            case NOGROUPS:
                $grouplabel = get_string('groupsnone', 'group');
                break;
            case VISIBLEGROUPS:
                $grouplabel = get_string('groupsvisible', 'group');
                break;
            case SEPARATEGROUPS:
                $grouplabel = get_string('groupsseparate', 'group');
                break;
        }

        // Exclude same group
        $mform->addElement('static', 'groupmode', get_string('groupmode', 'group'), $grouplabel);
        if (VISIBLEGROUPS == $groupmode) {
            $label = get_string('excludesamegroup', 'workshopallocation_random');
            $mform->addElement('checkbox', 'excludesamegroup', $label);
            $mform->setDefault('excludesamegroup', 0);
        } else {
            $mform->addElement('hidden', 'excludesamegroup', 0);
            $mform->setType('excludesamegroup', PARAM_BOOL);
        }

        // Self assessment
        if (empty($workshop->useselfassessment)) {
            $label = get_string('addselfassessment', 'workshopallocation_random');
            $content = get_string('selfassessmentdisabled', 'workshop');
            $mform->addElement('static', 'addselfassessment', $label, $content);
        } else {
            $label = get_string('addselfassessment', 'workshopallocation_random');
            $mform->addElement('checkbox', 'addselfassessment', $label);
        }

        $this->add_action_buttons();
    }
}

class workshop_live_allocator implements workshop_allocator {

    protected $workshop;
    protected $mform;

    public function __construct(workshop $workshop) {
        $this->workshop = $workshop;
    }

    public function init() {
        global $PAGE, $DB;

        $result = new workshop_allocation_result($this);

        $customdata = array();
        $customdata['workshop'] = $this->workshop;

        $record = $DB->get_record('workshopallocation_live', array('workshopid' => $this->workshop->id));
        if (!$record) {
            $record = new stdClass;
            $record->workshopid = $this->workshop->id;
            $record->enabled = false;
            $record->settings = '{}';
        }

        $this->mform = new workshop_live_allocator_form($PAGE->url, $customdata);

        if ($this->mform->is_cancelled()) {
            redirect($this->workshop->view_url());
        } else if ($data = $this->mform->get_data()) {
            $record->enabled = !empty($data->enabled);
            $record->settings = json_encode(array(
                'numofreviews' => $data->numofreviews,
                'numper' => $data->numper,
                'excludesamegroup' => !empty($data->excludesamegroup),
                'addselfassessment' => !empty($data->addselfassessment),
            ));
            if (isset($record->id)) {
                $DB->update_record('workshopallocation_live', $record);
            } else {
                $DB->insert_record('workshopallocation_live', $record);
            }
            if ($record->enabled) {
                $msg = get_string('resultenabled', 'workshopallocation_live');
            } else {
                $msg = get_string('resultdisabled', 'workshopallocation_live');
            }
            $result->set_status(workshop_allocation_result::STATUS_CONFIGURED, $msg);
        } else {
            $data = json_decode($record->settings) ?: new stdClass;
            $data->enabled = $record->enabled;
            $this->mform->set_data($data);
            $result->set_status(workshop_allocation_result::STATUS_VOID);
        }

        return $result;
    }

    public function ui() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_workshop');

        $html = $output->container_start('live-allocator');
        ob_start();
        $this->mform->display();
        $html .= ob_get_contents();
        ob_end_clean();
        $html .= $output->container_end();

        return $html;
    }

    public static function delete_instance($workshopid) {
        global $DB;

        $DB->delete_records('workshopallocation_live', array('workshopid' => $workshopid));
    }
}

function workshopallocation_live_assessable_uploaded($event) {
    global $DB;

    $cm = get_coursemodule_from_id('workshop', $event->contextinstanceid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $instance = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
    $workshop = new workshop($instance, $cm, $course);

    $record = $DB->get_record('workshopallocation_live', array('workshopid' => $workshop->id));

    if ($workshop->phase == workshop::PHASE_ASSESSMENT and $record and $record->enabled) {
        $randomallocator = $workshop->allocator_instance('random');
        $settings = workshop_random_allocator_setting::instance_from_text($record->settings);
        $result = new workshop_allocation_result($randomallocator);
        $randomallocator->execute($settings, $result);
    }

    return true;
}
