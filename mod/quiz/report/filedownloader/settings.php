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

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtextarea('quiz_filedownloader/acceptedqtypes',
                get_string('adminsetting_accepted_qtypes', 'quiz_filedownloader'),
                get_string('adminsetting_accepted_qtypes_help', 'quiz_filedownloader'),
                'essay, fileresponse', PARAM_RAW, 60, 5));

    $settings->add(new admin_setting_configtextarea('quiz_filedownloader/qtypefileareas',
                get_string('adminsetting_accepted_qtypefileareas', 'quiz_filedownloader'),
                get_string('adminsetting_accepted_qtypefileareas_help', 'quiz_filedownloader'),
                'attachments, attachments', PARAM_RAW, 60, 5));

    $settings->add(new admin_setting_configcheckbox('quiz_filedownloader/chooseableanonymization',
                get_string('adminsetting_chooseanonymize', 'quiz_filedownloader'),
                get_string('adminsetting_chooseanonymize_help', 'quiz_filedownloader'), 0));

    $settings->add(new admin_setting_configcheckbox('quiz_filedownloader/chooseablefilestructure',
                get_string('adminsetting_choosefilestructure', 'quiz_filedownloader'),
                get_string('adminsetting_choosefilestructure_help', 'quiz_filedownloader'), 0));
}
