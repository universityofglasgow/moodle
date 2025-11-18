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

use block_xp\di;
use block_xp\form\dynamic_world_trait;
use core_form\dynamic_form;
use moodle_url;

/**
 * Drop edit form.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class drop extends dynamic_form {
    use dynamic_world_trait;

    protected $routename = 'drops';

    protected function get_drop_record() {
        $db = di::get('db');
        $id = $this->optional_param('id', -1, PARAM_INT);
        $courseid = $this->get_world()->get_courseid();
        if ($id > 0) {
            return $db->get_record('local_xp_drops', ['courseid' => $courseid, 'id' => $id], '*', MUST_EXIST);
        }
        return (object) ['courseid' => $courseid];
    }

    public function process_dynamic_submission() {
        $db = di::get('db');
        $urlresolver = di::get('url_resolver');
        $data = $this->get_data();
        $record = $this->get_drop_record();
        $iscreating = empty($record->id);

        if (!$iscreating) {
            $record->name = $data->name;
            $record->points = $data->points;
            $record->enabled = $data->enabled;
            $db->update_record('local_xp_drops', $record);

        } else {
            do {
                $secret = substr(bin2hex(random_bytes(128)), 0, 7);
            } while ($db->record_exists('local_xp_drops', ['secret' => $secret]));
            $record->secret = $secret;
            $record->name = $data->name;
            $record->points = $data->points;
            $record->enabled = $data->enabled;
            $record->id = $db->insert_record('local_xp_drops', $record);
        }

        $listurl = $urlresolver->reverse('drops', ['courseid' => $this->get_world()->get_courseid()]);
        $redirecturl = new moodle_url($listurl);
        if ($iscreating) {
            $redirecturl = new moodle_url($listurl, ['setupid' => $record->id]);
        }

        return ['id' => $record->id, 'redirecturl' => $redirecturl->out_as_local_url()];
    }

    public function set_data_for_dynamic_submission(): void {
        $droprecord = $this->get_drop_record();

        $data = ['contextid' => $this->get_world()->get_context()->id];
        if (!empty($droprecord->id)) {
            $data += (array) $droprecord;
        } else {
            $data += ['points' => 50];
        }
        $this->set_data($data);
    }

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        if ($this->_ajaxformdata) {
            $mform->addElement('hidden', 'id');
            $mform->setType('id', PARAM_INT);
            $mform->addElement('hidden', 'contextid', $this->get_world()->get_context()->id);
            $mform->setType('contextid', PARAM_INT);
        }

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

        if (!$this->_ajaxformdata) {
            $this->add_action_buttons();
        }
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
