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
 * Quiz Filedownloader report version information.
 *
 * @package   quiz_filedownloader
 * @copyright 2019 ETH Zurich
 * @author    Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Settingsform for filedonwloader report.
 */
class quiz_filedownloader_settings_form extends moodleform {

    /**
     * Add elements to form
     */
    public function definition() {

        global $CFG;

        $mform = $this->_form;

        $showdownloadsettings = get_config('quiz_filedownloader', 'chooseablefilestructure') == 1
        || get_config('quiz_filedownloader', 'chooseableanonymization') == 1;

        if ($showdownloadsettings) {
            $mform->addElement('header', 'preferencespage', get_string('downloadsettings', 'quiz_filedownloader'));
        }

        if (get_config('quiz_filedownloader', 'chooseablefilestructure')) {

            $mform->addElement('select', 'zip_inonefolder', get_string('zip_inonefolder', 'quiz_filedownloader'), array(
                0 => get_string('no', 'quiz_filedownloader'),
                1 => get_string('yes', 'quiz_filedownloader')
            ));

            $mform->addHelpButton('zip_inonefolder', 'zip_inonefolder', 'quiz_filedownloader');
        }

        if (get_config('quiz_filedownloader', 'chooseableanonymization')) {

            $mform->addElement('select', 'chooseableanonymization',
                get_string('adminsetting_anonymizedownload', 'quiz_filedownloader'), array(
                    0 => get_string('no', 'quiz_filedownloader'),
                    1 => get_string('yes', 'quiz_filedownloader')
            ));

            $mform->addHelpButton('chooseableanonymization', 'adminsetting_anonymizedownload', 'quiz_filedownloader');
        }

        if ($showdownloadsettings) {
            $mform->closeHeaderBefore('downloadfiles');
        }

        $mform->addElement('hidden', 'id', '');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', '');
        $mform->setType('mode', PARAM_ALPHA);

        $mform->addElement('submit', 'downloadfiles', get_string('download', 'quiz_filedownloader'));
    }
}
