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

namespace local_xp\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use moodleform;

/**
 * Drop edit form.
 *
 * @package    block_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class drop extends moodleform {

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        $mform->addElement('text', 'name', get_string('dropname', 'local_xp'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'dropname', 'local_xp');
        $mform->addRule('name', '', 'required', null, 'clent');

        $mform->addElement('text', 'points', get_string('droppoints', 'local_xp'));
        $mform->setType('points', PARAM_INT);
        $mform->addHelpButton('points', 'droppoints', 'local_xp');
        $mform->addRule('points', '', 'required', null, 'clent');

        $mform->addElement('select', 'enabled', get_string('dropenabled', 'local_xp'), [
            0 => get_string('no', 'core'),
            1 => get_string('yes', 'core'),
        ]);
        $mform->addHelpButton('enabled', 'dropenabled', 'local_xp');
        $mform->setDefault('enabled', 1);

        $this->add_action_buttons();
    }

    /**
     * Validation.
     *
     * @param array $data The data.
     * @param array $files The files.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['points'] <= 0) {
            $errors['points'] = get_string('invaliddata', 'core_error');
        }
        return $errors;
    }

}
