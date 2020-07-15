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
 * CSV import.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir . '/formslib.php');

use context_user;
use core_text;
use csv_import_reader;
use moodleform;
use local_xp\local\provider\user_state_store_points;

/**
 * CSV import form.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csv_import extends moodleform {

    /**
     * Form definintion.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('header', 'generalhdr', get_string('importsettings', 'local_xp'));

        // CSV file.
        $mform->addElement('filepicker', 'csvfile', get_string('csvfile', 'local_xp'));
        $mform->addRule('csvfile', null, 'required');
        $mform->addHelpButton('csvfile', 'csvfile', 'local_xp');

        // Delimiter.
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimname', get_string('csvfieldseparator', 'mod_scheduler'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimname', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimname', 'semicolon');
        } else {
            $mform->setDefault('delimname', 'comma');
        }
        $mform->setAdvanced('delimname', true);

        // File encoding.
        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'core_grades'), $choices);
        $mform->setDefault('encoding', 'UTF-8');
        $mform->setAdvanced('encoding', true);

        // Import action.
        $mform->addElement('select', 'resetoradd', get_string('importpointsaction', 'local_xp'), [
            user_state_store_points::ACTION_SET => 'Set as total',
            user_state_store_points::ACTION_INCREASE => 'Increase'
        ]);
        $mform->setDefault('resetoradd', user_state_store_points::ACTION_INCREASE);
        $mform->addHelpButton('resetoradd', 'importpointsaction', 'local_xp');

        // Send notification.
        $mform->addElement('advcheckbox', 'sendnotification', get_string('sendawardnotification', 'local_xp'));
        $mform->addHelpButton('sendnotification', 'sendawardnotification', 'local_xp');
        $mform->hideIf('sendnotification', 'resetoradd', 'eq', user_state_store_points::ACTION_SET);

        $this->add_action_buttons(true, get_string('preview'));
    }

    /**
     * Get the data.
     *
     * @return stdClass
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }

        $this->_customdata['cir']->load_csv_content($this->get_file_content('csvfile'), $data->encoding, $data->delimname);
        $data->cir = $this->_customdata['cir'];
        $data->iid = $this->_customdata['iid'];

        if ($data->resetoradd == user_state_store_points::ACTION_SET) {
            $data->sendnotification = 0;
        }

        return $data;
    }

    /**
     * Validation.
     *
     * @param array $data The data.
     * @param array $files The files.
     * @return array
     */
    public function validation($data, $files) {
        global $USER;
        $errors = parent::validation($data, $files);
        $data = (object) $data;

        // Get file content.
        $fs = get_file_storage();
        $draftid = (int) $data->csvfile;
        $context = context_user::instance($USER->id);
        $content = null;
        if ($files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
            $content = reset($files)->get_content();
        }

        // Validate the CSV file.
        if (!empty($content)) {
            $iid = csv_import_reader::get_new_iid('local_xp_import_validation');
            $cir = new csv_import_reader($iid, 'local_xp_import_validation');
            $cir->load_csv_content($content, $data->encoding, $data->delimname);

            $importer = $this->_customdata['makeimporter']($cir, $data->resetoradd, null);
            if ($csverrors = $importer->validate_csv()) {
                $errors['csvfile'] = implode('. ', $csverrors);
            } else {
                $it = $importer->getIterator();
                $it->next();
                if (!$it->valid()) {
                    $errors['csvfile'] = get_string('csvisempty', 'local_xp');
                }
            }

            $cir->cleanup();
        }

        return $errors;
    }
}
