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

        // backupid.
        $mform->addElement('text', 'backupid', get_string('backupid', 'local_template'), 'maxlength="64" size="64"');
        $mform->setType('backupid', PARAM_TEXT);
        //$mform->addHelpButton('backupid', 'jobname', 'local_template');
        //$mform->addRule('backupid', get_string('required'), 'required', null, 'client');

        // operation.
        $choices = models\backupcontroller::get_operation_choices();
        $mform->addElement('select', 'operation', get_string('operation', 'local_template'), $choices);

        // type.
        $choices = models\backupcontroller::get_type_choices();
        $mform->addElement('select', 'type', get_string('type', 'local_template'), $choices);

        // itemid.
        $mform->addElement('text', 'itemid', get_string('itemid', 'local_template'));
        $mform->setType('itemid', PARAM_INT);

        // format.
        $choices = models\backupcontroller::get_format_choices();
        $mform->addElement('select', 'format', get_string('format'), $choices);

        // interactive.
        $choices = models\backupcontroller::get_interactive_choices();
        $mform->addElement('select', 'interactive', get_string('interactive', 'local_template'), $choices);

        // purpose.
        $choices = models\backupcontroller::get_purpose_choices();
        $mform->addElement('select', 'purpose', get_string('backupmode', 'backup'), $choices);

        // userid.
        $mform->addElement('text', 'userid', get_string('user'));
        $mform->setType('userid', PARAM_INT);

        // status.
        $choices = models\backupcontroller::get_status_choices();
        $mform->addElement('select', 'status', get_string('status'), $choices);

        // execution.
        $choices = models\backupcontroller::get_execution_choices();
        $mform->addElement('select', 'execution', get_string('execution', 'local_template'), $choices);

        // executiontime.
        $mform->addElement('text', 'executiontime', get_string('executiontime', 'local_template'));

        // checksum.
        $mform->addElement('text', 'checksum', get_string('checksum', 'local_template'));
        $mform->setType('checksum', PARAM_RAW);

        // progress.
        $mform->addElement('text', 'progress', get_string('progress', 'local_template'));
        $mform->setType('progress', PARAM_FLOAT);

        // controller.
        // $mform->addElement('textarea', 'controller', get_string('controller', 'local_template'), 'wrap="virtual" rows="20" cols="50"');

        $mform->addElement('header', 'controllerheader', get_string('controller', 'local_template'));
        $mform->addElement('html', '<pre>' . print_r(unserialize(base64_decode($record->controller)), true) . '</pre>');

        // errormessages.
        /*
        if (is_template_admin()) {
            $mform->addElement('header', 'errors', get_string('errormessages', 'local_template'));
            $mform->addElement('textarea', 'errormessages', get_string('errormessages', 'local_template'), array('rows' => 6, 'cols' => 80, 'class' => 'smalltext'));
            $mform->setType('errormessages', PARAM_RAW);
        } else {
            $mform->addElement('header', 'errors', get_string('log', 'local_template'));
            $mform->addElement('html', $OUTPUT->box(nl2br($record->errormessages)));
        }
        */

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