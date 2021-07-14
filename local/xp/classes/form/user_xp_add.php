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
 * Add user points.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

use moodleform;

/**
 * Add user points form.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_xp_add extends moodleform {

    /**
     * Form definintion.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('text', 'total', get_string('currentpoints', 'local_xp'));
        $mform->setType('total', PARAM_INT);
        $mform->hardFreeze('total');

        $mform->addElement('text', 'xp', get_string('increaseby', 'local_xp'));
        $mform->setType('xp', PARAM_INT);
        $mform->addHelpButton('xp', 'increaseby', 'local_xp');
        $mform->setDefault('xp', 10);

        $mform->addElement('advcheckbox', 'sendnotification', get_string('sendawardnotification', 'local_xp'));
        $mform->addHelpButton('sendnotification', 'sendawardnotification', 'local_xp');

        $mform->addElement('textarea', 'message', get_string('increasemsg', 'local_xp'));
        $mform->setType('message', PARAM_NOTAGS);
        $mform->addHelpButton('message', 'increasemsg', 'local_xp');
        $mform->disabledIf('message', 'sendnotification', 'eq', 0);

        $this->add_action_buttons(true, get_string('confirm'));
    }

    /**
     * Data validate.
     *
     * @param array $data The data submitted.
     * @param array $files The files submitted.
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = array();

        // Validating the XP points.
        $xp = (int) $data['xp'];
        if ($xp <= 0) {
            $errors['xp'] = get_string('invalidxp', 'block_xp');
        }

        return $errors;
    }

}
