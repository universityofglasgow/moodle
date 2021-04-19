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
 * Settings form.
 *
 * @copyright 2014-2017 Albert Gasset <albertgasset@fsfe.org>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   workshopallocation_live
 */

namespace workshopallocation_live;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/mod/workshop/allocation/random/lib.php');

/**
 * Settings form.
 *
 * @copyright 2014-2017 Albert Gasset <albertgasset@fsfe.org>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   workshopallocation_live
 */
class settings_form extends \moodleform {

    /**
     * Form definition based on the random allocator form.
     */
    public function definition() {
        global $PAGE;

        $mform = $this->_form;
        $workshop = $this->_customdata['workshop'];

        $plugindefaults = get_config('workshopallocation_random');

        // Check late submissions setting.
        if (!$workshop->latesubmissions) {
            $settingsurl = new \moodle_url('/course/modedit.php', ['update' => $workshop->cm->id, 'return' => 1]);
            $settingslink = \html_writer::link($settingsurl, get_string('workshopsettings', 'workshopallocation_live'));
            $latesubmissions = \html_writer::tag('strong', get_string('latesubmissions', 'workshop'));
            $a = ['latesubmissions' => $latesubmissions, 'workshopsettings' => $settingslink];
            $message = get_string('latesubmissionsdisabled', 'workshopallocation_live', $a);
            $mform->addElement('html', \html_writer::tag('p', $message));
            return;
        }

        $strenbaled = get_string('enabled', 'workshopallocation_live');
        $cm = get_coursemodule_from_instance('workshop', $workshop->id, 0, false, MUST_EXIST);
        $url = new \moodle_url($PAGE->url, ['method' => 'scheduled', 'cmid' => $cm->id]);
        $strenabledinfo = get_string('enabledinfo', 'workshopallocation_live', $url->out());
        $mform->addElement('checkbox', 'enabled', $strenbaled, $strenabledinfo);

        // Number of reviews.
        $group = [];
        $options = \workshop_random_allocator::available_numofreviews_list();
        $group[] = $mform->createElement('select', 'numofreviews', '', $options);
        $mform->setDefault('numofreviews', $plugindefaults->numofreviews);
        $options = [
            \workshop_random_allocator_setting::NUMPER_SUBMISSION => get_string('numperauthor', 'workshopallocation_random'),
            \workshop_random_allocator_setting::NUMPER_REVIEWER   => get_string('numperreviewer', 'workshopallocation_random')
        ];
        $group[] = $mform->createElement('select', 'numper', '', $options);
        $mform->setDefault('numper', \workshop_random_allocator_setting::NUMPER_SUBMISSION);
        $label = get_string('numofreviews', 'workshopallocation_random');
        $mform->addGroup($group, 'groupnumofreviews', $label,  [' '], false);

        // Group mode.
        $groupmode = groups_get_activity_groupmode($workshop->cm, $workshop->course);
        if ($groupmode == NOGROUPS) {
            $grouplabel = get_string('groupsnone', 'group');
        } else if ($groupmode == VISIBLEGROUPS) {
            $grouplabel = get_string('groupsvisible', 'group');
        } else if ($groupmode == SEPARATEGROUPS) {
            $grouplabel = get_string('groupsseparate', 'group');
        } else {
            $grouplabel = '';
        }
        $mform->addElement('static', 'groupmode', get_string('groupmode', 'group'), $grouplabel);

        // Exclude same group.
        if ($groupmode == VISIBLEGROUPS) {
            $label = get_string('excludesamegroup', 'workshopallocation_random');
            $mform->addElement('checkbox', 'excludesamegroup', $label);
            $mform->setDefault('excludesamegroup', 0);
        } else {
            $mform->addElement('hidden', 'excludesamegroup', 0);
            $mform->setType('excludesamegroup', PARAM_BOOL);
        }

        // Self assessment.
        if (empty($workshop->useselfassessment)) {
            $label = get_string('addselfassessment', 'workshopallocation_random');
            $content = get_string('selfassessmentdisabled', 'workshop');
            $mform->addElement('static', 'addselfassessmentinfo', $label, $content);
        } else {
            $label = get_string('addselfassessment', 'workshopallocation_random');
            $mform->addElement('checkbox', 'addselfassessment', $label);
        }

        $this->add_action_buttons();
    }
}
