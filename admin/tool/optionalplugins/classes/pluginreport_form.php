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
 * A report of optional plugin installation attempts
 *
 * @package    tool_optionalplugins
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

/**
 * This class is responsible for the report
 */
class pluginreport_form extends moodleform
{

    /**
     * This function defines the elements on the form.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function definition() {
        global $DB;

        $mform = $this->_form; // Don't forget the underscore!
        $data = $this->_customdata;

        $availablereports = $DB->get_records('tool_optionalplugins_log', null, 'timecreated DESC', 'id, timecreated', '', 10);
        $options = array(0 => get_string('choose') . '...');
        foreach ($availablereports as $availablereport) {
            $installrundate = userdate($availablereport->timecreated);
            $options[$availablereport->id] = $installrundate;
        }

        $mform->addElement('select', 'reportid', get_string('reportdate', 'tool_optionalplugins'), $options);
        $mform->setDefault('reportid', $data['selectedid']);

        $this->add_action_buttons(false, get_string('display_btn_string', 'tool_optionalplugins'));

        $this->set_data($data);
    }
}
