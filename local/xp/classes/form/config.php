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
 * XP config form.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/blocks/xp/classes/form/itemspertime.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->libdir . '/grouplib.php');

use context_course;
use context_system;
use block_xp\local\config\course_world_config;
use local_xp\local\config\default_course_world_config;

/**
 * XP config form class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config extends \block_xp\form\config {

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        parent::definition();
        $mform = $this->_form;
        $world = $this->_customdata['world'];
        $config = \block_xp\di::get('config');
        $iomad = \block_xp\di::get('iomad_facade');
        $forwholesite = $config->get('context') == CONTEXT_SYSTEM;

        // Change the help text of the cheat guard.
        $mform->addHelpButton('enablecheatguard', 'enablecheatguard', 'local_xp');

        // Re-implement those to extend the units allowed.
        $mform->removeElement('maxactionspertime');
        $el = $mform->createElement('block_xp_form_itemspertime', 'maxactionspertime', get_string('maxactionspertime', 'block_xp'), [
            'maxunit' => DAYSECS,
            'itemlabel' => get_string('actions', 'block_xp')
        ]);
        $mform->insertElementBefore($el, 'timebetweensameactions');
        unset($el);
        $mform->addHelpButton('maxactionspertime', 'maxactionspertime', 'block_xp');

        $mform->removeElement('timebetweensameactions');
        $el = $mform->createElement('block_xp_form_duration', 'timebetweensameactions', get_string('timebetweensameactions', 'block_xp'), [
            'maxunit' => DAYSECS,
            'optional' => false,        // We must set this...
        ]);
        $mform->insertElementBefore($el, '__cheatguardend');
        unset($el);
        $mform->addHelpButton('timebetweensameactions', 'timebetweensameactions', 'block_xp');

        // Local plugin specific option.
        $el = $mform->createElement('block_xp_form_itemspertime', 'maxpointspertime', get_string('maxpointspertime', 'local_xp'), [
            'itemlabel' => get_string('points', 'local_xp')
        ]);
        $mform->insertElementBefore($el, '__cheatguardend');
        unset($el);
        $mform->addHelpButton('maxpointspertime', 'maxpointspertime', 'local_xp');
        $mform->disabledIf('maxpointspertime', 'enablecheatguard', 'eq', 0);

        // Progress bar.
        $el = $mform->createElement('header', 'progressbarhdr', get_string('progressbar', 'block_xp'));
        $mform->insertElementBefore($el, 'hdrcheating');
        unset($el);
        $el = $mform->createElement('select', 'progressbarmode', get_string('progressbarmode', 'local_xp'), [
            default_course_world_config::PROGRESS_BAR_MODE_LEVEL => get_string('progressbarmodelevel', 'local_xp'),
            default_course_world_config::PROGRESS_BAR_MODE_OVERALL => get_string('progressbarmodeoverall', 'local_xp'),
        ]);
        $mform->insertElementBefore($el, 'hdrcheating');
        $mform->addHelpButton('progressbarmode', 'progressbarmode', 'local_xp');
        unset($el);

        // Group ladder.
        $el = $mform->createElement('header', 'groupladderhdr', get_string('groupladder', 'local_xp'));
        $mform->insertElementBefore($el, 'hdrcheating');
        unset($el);
        $sources = [
            default_course_world_config::GROUP_LADDER_NONE => get_string('groupsourcenone', 'local_xp'),
            default_course_world_config::GROUP_LADDER_COURSE_GROUPS => get_string('groupsourcecoursegroups', 'local_xp'),
            default_course_world_config::GROUP_LADDER_COHORTS => get_string('groupsourcecohorts', 'local_xp')
        ];
        if ($iomad->exists()) {
            $sources[default_course_world_config::GROUP_LADDER_IOMAD_COMPANIES] = get_string(
                'groupsourceiomadcompanies', 'local_xp');
            $sources[default_course_world_config::GROUP_LADDER_IOMAD_DEPARTMENTS] = get_string(
                'groupsourceiomaddepartments', 'local_xp');
        }
        $el = $mform->createElement('select', 'enablegroupladder', get_string('groupladdersource', 'local_xp'), $sources);
        $mform->insertElementBefore($el, 'hdrcheating');
        $mform->addHelpButton('enablegroupladder', 'groupladdersource', 'local_xp');
        unset($el);

        // Group ladder identity mode.
        $el = $mform->createElement('select', 'groupidentitymode', get_string('groupanonymity', 'local_xp'), [
            course_world_config::IDENTITY_OFF => get_string('hidegroupidentity', 'local_xp'),
            course_world_config::IDENTITY_ON => get_string('displaygroupidentity', 'local_xp'),
        ]);
        $mform->insertElementBefore($el, 'hdrcheating');
        $mform->addHelpButton('groupidentitymode', 'groupanonymity', 'local_xp');
        $mform->disabledIf('groupidentitymode', 'enablegroupladder', 'eq', 0);
        unset($el);

        // Group ladder order by.
        $el = $mform->createElement('select', 'grouporderby', get_string('grouporderby', 'local_xp'), [
            default_course_world_config::GROUP_ORDER_BY_POINTS => get_string('grouppoints', 'local_xp'),
            default_course_world_config::GROUP_ORDER_BY_POINTS_COMPENSATED_BY_AVG =>
                get_string('grouppointswithcompensation', 'local_xp'),
            default_course_world_config::GROUP_ORDER_BY_PROGRESS => get_string('progress', 'block_xp'),
        ]);
        $mform->insertElementBefore($el, 'hdrcheating');
        $mform->addHelpButton('grouporderby', 'grouporderby', 'local_xp');
        $mform->disabledIf('grouporderby', 'enablegroupladder', 'eq', 0);
        unset($el);

        // Group ladder progress column.
        $options = [
            'xp' => get_string('grouppoints', 'local_xp'),
            'progress' => get_string('progress', 'block_xp'),
        ];
        $el = $mform->createElement('select', 'groupladdercols', get_string('groupladdercols', 'local_xp'), $options,
            ['style' => 'height: 4em;']);
        $el->setMultiple(true);
        $mform->insertElementBefore($el, 'hdrcheating');
        $mform->addHelpButton('groupladdercols', 'groupladdercols', 'local_xp');
        $mform->disabledIf('groupladdercols', 'enablegroupladder', 'eq', 0);
        unset($el);
    }

    /**
     * Get the data.
     *
     * @return stdClass
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }

        // Convert back from itemspertime.
        if (!isset($data->maxpointspertime) || !is_array($data->maxpointspertime)) {
            $data->maxpointspertime = 0;
            $data->timeformaxpoints = 0;

        } else {
            $data->timeformaxpoints = (int) $data->maxpointspertime['time'];
            $data->maxpointspertime = (int) $data->maxpointspertime['points'];
        }

        // When the cheat guard is disabled, we remove the config fields so that
        // we can keep the defaults and the data previously submitted by the user.
        if (empty($data->enablecheatguard)) {
            unset($data->timeformaxpoints);
            unset($data->maxpointspertime);
        }

        // When not selecting any, the data is not sent.
        if (!isset($data->groupladdercols)) {
            $data->groupladdercols = [];
        }
        $data->groupladdercols = implode(',', $data->groupladdercols);

        return $data;
    }

    /**
     * Set the data.
     */
    public function set_data($data) {
        $data = (array) $data;

        if (isset($data['groupladdercols'])) {
            $data['groupladdercols'] = explode(',', $data['groupladdercols']);
        }

        // Convert to itemspertime.
        if (isset($data['maxpointspertime']) && isset($data['timeformaxpoints'])) {
            $data['maxpointspertime'] = [
                'points' => (int) $data['maxpointspertime'],
                'time' => (int) $data['timeformaxpoints']
            ];
            unset($data['timeformaxpoints']);
        }

        parent::set_data($data);
    }

}
