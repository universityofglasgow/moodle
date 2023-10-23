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
 * Upgradecopy form.
 *
 * @package    tool_upgradecopy
 * @copyright  Howard Miller 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_upgradecopy\forms;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Site wide search-replace form.
 */
class upgradecopy extends \moodleform {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'upgradecopyhdr', get_string('pluginname', 'tool_upgradecopy'));
        $mform->setExpanded('upgradecopyhdr', true);

        $mform->addElement('text', 'pathfrom', get_string('pathfrom', 'tool_upgradecopy'), 'size="50"');
        $mform->setType('pathfrom', PARAM_RAW);
        $mform->addElement('static', 'pathfromst', '', get_string('pathfromhelp', 'tool_upgradecopy'));
        $mform->addRule('pathfrom', get_string('required'), 'required', null, 'client');


        $mform->addElement('text', 'pathto', get_string('pathto', 'tool_upgradecopy'), 'size="50"');
        $mform->setType('pathto', PARAM_RAW);
        $mform->addElement('static', 'pathtost', '', get_string('pathtohelp', 'tool_upgradecopy'));
        $mform->addRule('pathto', get_string('required'), 'required', null, 'client');


        $this->add_action_buttons(false, get_string('doit', 'tool_replace'));
    }
}
