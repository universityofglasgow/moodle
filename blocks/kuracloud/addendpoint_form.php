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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_kuracloud;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Form for adding an endpoint
 *
 * @copyright 2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class addendpoint_form extends \moodleform {

    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('passwordunmask', 'token', get_string('token', 'block_kuracloud'), array('size' => '40'));
        $mform->setType('token', PARAM_TEXT);
        $mform->addRule('token', get_string('required'), 'required');

        $mform->addElement('text', 'api_endpoint', get_string('endpoint', 'block_kuracloud'), array('size' => '40'));
        $mform->setType('api_endpoint', PARAM_URL);
        $mform->setDefault('api_endpoint', BLOCK_KURACLOUD_API_ENDPOINT);
        $mform->addRule('api_endpoint', get_string('required'), 'required');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }

    /**
     * Validate the form
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {

        // Validate token and api_endpoint.
        $api = new api(new transport($data['api_endpoint'], $data['token']));
        try {
            $resp = $api->get_instance();
            return array();
        } catch (\Exception $e) {
            return array('token' => $e->getMessage());
        }
    }
}