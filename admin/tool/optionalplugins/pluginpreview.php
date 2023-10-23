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
 * Display a preview page of plugins that can be installed.
 *
 * @package tool_optionalplugins
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__ . '/classes/pluginpreview_form.php');

admin_externalpage_setup('tooloptionalplugins');

$title = get_string('pagetitle', 'tool_optionalplugins');
$sessionkey = sesskey();
$context = context_system::instance();

$PAGE->set_url(new moodle_url("/admin/tool/optionalplugins/pluginpreview.php"));
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);

require_capability('tool/optionalplugins:importplugins', $context);

global $SESSION;
$pformparams = array('canbeinstalled' => $SESSION->canbeinstalled, 'alreadyinstalled' => $SESSION->alreadyinstalled,
    'cannotbeinstalled' => $SESSION->cannotbeinstalled);

$pform = new pluginpreview_form('pluginpreview.php', $pformparams);
if ($pform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    $redirect = new moodle_url("/admin/search.php", [], 'linkdevelopment');
    redirect($redirect);
}
if ($pform->is_submitted() && $data = $pform->get_data()) {
    if (isset($data->installationchoice)) {
        // Create a list of items that will, or won't be installed...
        $SESSION->installationchoice = $data->installationchoice;
    }
    $redirect = new moodle_url("controller.php", array('action' => 'installoptionalplugins', 'sesskey' => $sessionkey));
    redirect($redirect);
}

echo $OUTPUT->header();
$pform->display();
echo $OUTPUT->footer();
