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
use core_form\dynamic_form;
use moodle_url;

/**
 * Form.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_download extends dynamic_form {

    use dynamic_world_trait;

    protected $routename = 'report';

    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'filename', get_string('filename', 'repository'), ['size' => '50']);
        $mform->setType('filename', PARAM_NOTAGS);

        $formats = \core_plugin_manager::instance()->get_plugins_of_type('dataformat');
        $options = [];
        foreach ($formats as $format) {
            if ($format->is_enabled()) {
                $options[$format->name] = get_string('dataformat', $format->component);
            }
        }
        $mform->addElement('select', 'dataformat', get_string('dataformat', 'block_xp'), $options);

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);

        $mform->addElement('hidden', 'pageurl');
        $mform->setType('pageurl', PARAM_LOCALURL);
    }

    public function set_data_for_dynamic_submission(): void {
        $pageurl = new moodle_url($this->optional_param('pageurl', '', PARAM_LOCALURL));
        $this->set_data((object) [
            'contextid' => $this->optional_param('contextid', 0, PARAM_INT),
            'filename' => $this->optional_param('filename', 'file', PARAM_NOTAGS),
            'dataformat' => get_user_preferences('local_xp_dataformat', ''),
            'pageurl' => $pageurl->out_as_local_url(false),
        ]);
    }

    public function process_dynamic_submission() {
        if ($data = $this->get_data()) {
            set_user_preference('local_xp_dataformat', $data->dataformat);
            $url = new moodle_url($data->pageurl);
            $url->param('download', $data->dataformat);
            if (!empty($data->filename)) {
                $url->param('downloadfilename', $data->filename);
            }
            return ['redirecturl' => $url->out_as_local_url(false)];
        }
    }

    public function validation($data, $files) {
        $errors = [];
        if (empty($data['dataformat'])) {
            $errors['dataformat'] = get_string('required', 'core');
        }
        return $errors;
    }

}
