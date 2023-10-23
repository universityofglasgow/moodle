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
 * Admin page to allow the export/import of optional plugins
 *
 * For each Moodle install, optional plugins need to be added one at a time.
 * This plugin allows admin users to export all *optional* plugins from one
 * version and import them into another, thereby streamlining this process.
 *
 * @package tool_optionalplugins
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__ . '/classes/importexport_form.php');
require_once(__DIR__ . '/classes/pluginpreview_form.php');

admin_externalpage_setup('tooloptionalplugins');

$title = get_string('pagetitle', 'tool_optionalplugins');
$sessionkey = sesskey();
$context = context_system::instance();
$errormsg = optional_param('errormsg', '', PARAM_TEXT);

$PAGE->set_url(new moodle_url("/admin/tool/optionalplugins/index.php"));
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);

require_capability('tool/optionalplugins:importplugins', $context);

$mform = new importexport_form('index.php', array('sesskey' => $sessionkey));
if ($mform->is_cancelled()) {
    $redirect = new moodle_url("/admin/search.php", [], 'linkdevelopment');
    redirect($redirect);
}

if ($mform->is_submitted() && $data = $mform->get_data()) {

    if ($filecontents = $mform->get_file_content('importfile')) {

        $SESSION->filecontents = $filecontents;
        $redirect = new moodle_url("controller.php", array('action' => 'validatesourcepluginlist', 'sesskey' => $sessionkey));
        redirect($redirect);

    } else {
        // Problems reading the file????
        echo $OUTPUT->header();
        $supportemail = ((get_config('moodle', 'supportemail') !== '') ? get_config('moodle', 'supportemail') : $CFG->supportemail);
        $out = html_writer::tag('span', get_string('importfile_error', 'tool_optionalplugins', $supportemail));
        echo $OUTPUT->box($out);
    }

} else {
    echo $OUTPUT->header();

    if ($errormsg != '') {
        $out = html_writer::tag('span', get_string('importfile_jsonerror', 'tool_optionalplugins', $errormsg));
        echo $OUTPUT->box($out);
    } else {
        $mform->display();
    }
}

echo $OUTPUT->footer();
