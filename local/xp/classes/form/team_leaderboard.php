<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

namespace local_xp\form;

use block_xp\form\dynamic_world_trait;
use block_xp\local\config\course_world_config;
use core_form\dynamic_form;
use local_xp\local\config\default_course_world_config;

/**
 * Form.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class team_leaderboard extends dynamic_form {

    use dynamic_world_trait;

    protected $routename = 'group_ladder';

    public function process_dynamic_submission() {
        $config = $this->get_world()->get_config();
        $data = $this->get_data();
        $config->set_many((array) $data);
    }

    public function set_data_for_dynamic_submission(): void {
        $config = $this->get_world()->get_config();
        $this->set_data([
            'contextid' => $this->get_world()->get_context()->id,
        ] + $config->get_all());
    }

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $iomad = \block_xp\di::get('iomad_facade');

        $mform->addElement('hidden', 'contextid', $this->get_world()->get_context()->id);
        $mform->setType('contextid', PARAM_INT);

        $sources = [
            default_course_world_config::GROUP_LADDER_NONE => get_string('groupsourcenone', 'local_xp'),
            default_course_world_config::GROUP_LADDER_COURSE_GROUPS => get_string('groupsourcecoursegroups', 'local_xp'),
            default_course_world_config::GROUP_LADDER_COHORTS => get_string('groupsourcecohorts', 'local_xp'),
        ];
        if ($iomad->exists()) {
            $sources[default_course_world_config::GROUP_LADDER_IOMAD_COMPANIES] = get_string(
                'groupsourceiomadcompanies', 'local_xp');
            $sources[default_course_world_config::GROUP_LADDER_IOMAD_DEPARTMENTS] = get_string(
                'groupsourceiomaddepartments', 'local_xp');
        }
        $el = $mform->createElement('select', 'enablegroupladder', get_string('groupladdersource', 'local_xp'), $sources);
        $mform->addElement($el);
        $mform->addHelpButton('enablegroupladder', 'groupladdersource', 'local_xp');
        unset($el);

        // Group ladder identity mode.
        $el = $mform->createElement('select', 'groupidentitymode', get_string('groupanonymity', 'local_xp'), [
            course_world_config::IDENTITY_OFF => get_string('hidegroupidentity', 'local_xp'),
            course_world_config::IDENTITY_ON => get_string('displaygroupidentity', 'local_xp'),
        ]);
        $mform->addElement($el);
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
        $mform->addElement($el);
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
        $mform->addElement($el);
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

        // When not selecting any, the data is not sent.
        if (!isset($data->groupladdercols)) {
            $data->groupladdercols = [];
        }
        $data->groupladdercols = implode(',', $data->groupladdercols);

        // Remove the context ID.
        unset($data->contextid);

        return $data;
    }

    /**
     * Set the data.
     *
     * @param array $data
     */
    public function set_data($data) {
        $data = (array) $data;

        if (isset($data['groupladdercols'])) {
            $data['groupladdercols'] = explode(',', $data['groupladdercols']);
        }

        parent::set_data($data);
    }

}
