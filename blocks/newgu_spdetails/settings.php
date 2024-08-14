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
 * Part of the MyGrades/Student Dashboard feature.
 *
 * This 'feature' allows Moodle admin's to control which LTI activies should
 * be included in the Dashboard. Point of note - on an initial install into a
 * new system, this page should be saved first time around - this will seed
 * the mdl_config table with the initial list of available LTI's.
 *
 * @package    block_newgu_spdetails
 * @author     Shubhendra Diophode <shubhendra.doiphode@gmail.com>
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    global $DB, $CFG;

    require_once($CFG->dirroot . '/mod/lti/locallib.php');
    require_once($CFG->dirroot . '/mod/lti/lib.php');

    $options = [
        LTI_COURSEVISIBLE_NO => get_string('show_in_course_no', 'lti'),
        LTI_COURSEVISIBLE_PRECONFIGURED => get_string('show_in_course_preconfigured', 'lti'),
        LTI_COURSEVISIBLE_ACTIVITYCHOOSER => get_string('show_in_course_activity_chooser', 'lti'),
    ];

    // Return a list of LTI types available in the system.
    // We are ignoring the 'coursevisible' setting which controls
    // where those tools are available from (Activity picker etc).
    $ltitypes = lti_get_lti_types();

    // To get around the problem of when new LTI's are added to the system, and ^no^ config
    // option initially existing for it (this page displays the items found in mdl_lti_types,
    // but the values are stored in mdl_config only once the page has been saved) - we could
    // query the mdl_config table, and if no matching option is found, just create it/them.
    // The reverse is true also - when an LTI gets removed, the config option hangs around.
    // As there are no events we can listen for in order to maintain consistency, this is one
    // way of keeping things in sync.

    $settings->add(new admin_setting_heading('block_newgu_spdetails/headinglti',
        new lang_string('includeltilabel', 'block_newgu_spdetails'),
        new lang_string('includeltidescription', 'block_newgu_spdetails')));

    // Include the current setting in the description, someone may find this useful.
    foreach ($ltitypes as $keyltitypes) {
        $settings->add(new admin_setting_configcheckbox('block_newgu_spdetails_include_' . $keyltitypes->id,
        $keyltitypes->name, 'Site Configuration: <strong>' . $options[$keyltitypes->coursevisible] . '</strong>' , 0));
    }
}
