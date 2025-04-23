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

use block_xp\di;
use block_xp\form\dynamic_world_trait;
use core_form\dynamic_form;

/**
 * Add user points form.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_xp_add extends dynamic_form {

    use dynamic_world_trait;

    protected $routename = 'report';

    /**
     * Get the state.
     *
     * This will throw an exception if the state does not already exist for the user.
     *
     * @return \block_xp\local\xp\state
     */
    protected function get_state() {
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        return $this->get_world()->get_store()->get_state($userid);
    }

    public function process_dynamic_submission() {
        global $USER;

        $this->get_state(); // Acts as validation.
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        $data = $this->get_data();

        $store = $this->get_world()->get_store();
        $reason = new \local_xp\local\reason\manual_reason($USER->id);

        if ($store instanceof \block_xp\local\xp\state_store_with_reason) {
            $store->increase_with_reason($userid, $data->xp, $reason);
        } else {
            $store->increase($userid, $data->xp);
        }

        if ($data->sendnotification) {
            $notifier = new \local_xp\local\notification\award_notifier(di::get('config'), $this->world, $USER);
            $notifier->notify($userid, $data->points, $data->message ?? null);
        }
    }

    public function set_data_for_dynamic_submission(): void {
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        $state = $this->world->get_store()->get_state($userid);
        $this->set_data([
            'userid' => $userid,
            'total' => $state->get_xp(),
        ]);
    }

    /**
     * Form definintion.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        if ($this->_ajaxformdata) {
            $mform->addElement('hidden', 'contextid', $this->get_world()->get_context()->id);
            $mform->setType('contextid', PARAM_INT);
        }

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

        if (!$this->_ajaxformdata) {
            $this->add_action_buttons(true, get_string('confirm'));
        }
    }

    /**
     * Data validate.
     *
     * @param array $data The data submitted.
     * @param array $files The files submitted.
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = [];

        // Validating the XP points.
        $xp = (int) $data['xp'];
        if ($xp <= 0) {
            $errors['xp'] = get_string('invalidxp', 'block_xp');
        }

        return $errors;
    }

}
