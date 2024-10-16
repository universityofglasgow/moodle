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
 * Version details.
 *
 * @package    report
 * @subpackage guid
 * @copyright  2017 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_guid\forms;

defined('MOODLE_INTERNAL') || die;

use \moodleform;

class courseupload extends moodleform {

    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        // Parameters.
        $mode = $this->_customdata['mode'];
        $roles = $this->_customdata['roles'];
        $studentroleid = $this->_customdata['studentroleid'];
        $courseid = $this->_customdata['id'];
        $contextid = $this->_customdata['contextid'];
        $downloadlink = $this->_customdata['downloadlink'];
        $firstcoloptions = array(
            'guid' => get_string('guidusername', 'report_guid'),
            'idnumber' => get_string('idnumber', 'report_guid'),
            'email' => get_string('email', 'report_guid'),
        );
        $actions = [
            'enrol' => get_string('enrol', 'report_guid'),
            'unenrol' => get_string('unenrol', 'report_guid')
        ];

        // File download
        if ($mode == UPLOAD_COURSE) {
            $mform->addElement('header', 'guidenroldownload', get_string('enroldownloadheader', 'report_guid'));
            $mform->addElement('html', '<div>' . get_string('enroldownloadinstructions', 'report_guid') . '</div>');
            $mform->addElement('html', '<a class="btn btn-primary mb-3 mt-3" href="' . $downloadlink . '">' . get_string('downloadcsv', 'report_guid') . '</a>');
        }

        // File upload.
        $mform->addElement('header', 'guidcourseupload', get_string('uploadheader', 'report_guid' ) );
        if ($mode == UPLOAD_COURSE) {
            $mform->addElement('html', '<div class="alert">'.get_string('courseuploadinstructions', 'report_guid' ).'</div>' );
        } else {
            $mform->addElement('html', '<div class="alert">'.get_string('categoryuploadinstructions', 'report_guid' ).'</div>' );
        }
        $mform->addElement('filepicker', 'csvfile', get_string('csvfile', 'report_guid' ) );

        // Role to assign.
        $mform->addElement('select', 'role', get_string('roletoassign', 'report_guid'), $roles);
        $mform->addHelpButton('role', 'roletoassign', 'report_guid');
        $mform->setDefault('role', $studentroleid);

        // First column.
        $mform->addElement('select', 'firstcolumn', get_string('firstcolumn', 'report_guid'), $firstcoloptions);
        $mform->addHelpButton('firstcolumn', 'firstcolumn', 'report_guid');

        // Add groups.
        if ($mode == UPLOAD_COURSE) {
            $mform->addElement('selectyesno', 'addgroups', get_string('addgroups', 'report_guid'), 0);
            $mform->addHelpButton('addgroups', 'addgroups', 'report_guid');
        }

        // Unenrol
        $mform->addElement('html', '<div class="alert alert-danger">' . get_string('unenrolwarn', 'report_guid') . '</div>');
        $mform->addElement('select', 'action', get_string('uploadaction', 'report_guid'), $actions);
        $mform->addHelpButton('action', 'uploadaction', 'report_guid');
        $mform->setDefault('action', 'enrol');

        // Allow multiple enrolments
        $mform->addElement('selectyesno', 'allowmultiple', get_string('allowmultiple', 'report_guid'), 0);
        $mform->addHelpButton('allowmultiple', 'allowmultiple', 'report_guid');

        // Action buttons.
        $this->add_action_buttons(false, get_string('submitfile', 'report_guid'));

        // Hidden.
        $mform->addElement('hidden', 'id', $courseid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'contextid', $contextid);
        $mform->setType('contextid', PARAM_INT);        
    }

}
