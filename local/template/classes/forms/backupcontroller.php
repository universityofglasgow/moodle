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
 * Local template
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_template\forms;
use local_template\models;
use local_template\controllers;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/lib/classes/form/persistent.php';

/**7
 * Class template
 *
 * @copyright  2023 David Aylmer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backupcontroller extends \core\form\persistent {

    /** @var string Persistent class name. */
    protected static $persistentclass = 'local_template\\models\\backupcontroller';

    /** @var array Fields to remove from the persistent validation. */
    protected static $foreignfields = array('action');

    /**
     * Define the form.
     */
    public function definition() {
        global $DB, $USER, $OUTPUT;
        $mform = $this->_form;

        $id = 0;
        if (!empty($this->_customdata['id'])) {
            $id = $this->_customdata['id'];
        }
        $isediting = false;
        if (!empty($id)) {
            $isediting = true;
        }

        $record = $this->get_persistent()->to_record();

        $mform->addElement('header', 'backupcontroller', get_string('backupcontroller', 'local_template'));

        // Action.
        $mform->addElement('hidden', 'action', $this->_customdata['action']);
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->setConstant('action', $this->_customdata['action']);

        // name.
        $mform->addElement('text', 'name', get_string('backupcontrollername', 'local_template'), 'maxlength="64" size="64"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'backupcontrollername', 'local_template');
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        // templateid.
        if (is_template_admin()) {
            // Attach this backupcontroller to any template.
            $template = $DB->get_records_menu('local_template', null, 'timemodified DESC', 'id, name');

        } else {
            // Attach this backupcontroller only to template created by current user.
            $template = $DB->get_records_menu('local_template', ['usercreated' => $USER->id], 'timemodified DESC', 'id, name');
        }

        // errormessages.
        if (is_template_admin()) {
            $mform->addElement('header', 'errors', get_string('errormessages', 'local_template'));
            $mform->addElement('textarea', 'errormessages', get_string('errormessages', 'local_template'), array('rows' => 6, 'cols' => 80, 'class' => 'smalltext'));
            $mform->setType('errormessages', PARAM_RAW);
        } else {
            $mform->addElement('header', 'errors', get_string('log', 'local_template'));
            $mform->addElement('html', $OUTPUT->box(nl2br($record->errormessages)));
        }

        $this->add_action_buttons(true);

    }

    function definition_after_data() {
        $mform = $this->_form;
        if (!empty($this->_customdata['id'])) {
            $mform->addElement('hidden', 'id', $this->_customdata['id']);
            $mform->setType('id', PARAM_INT);
        }
        if (!empty($this->_customdata['templateid'])) {
            $mform->getElement('templateid')->setSelected($this->_customdata['templateid']);
        }
    }
}