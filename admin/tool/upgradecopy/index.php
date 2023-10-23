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
 * Search and replace strings throughout all texts in the whole database
 *
 * @package    tool_upgradecopy
 * @copyright  Howard Miller 2003
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once('../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('toolupgradecopy');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pageheader', 'tool_upgradecopy'));

if (!$DB->replace_all_text_supported()) {
    echo $OUTPUT->notification(get_string('notimplemented', 'tool_replace'));
    echo $OUTPUT->footer();
    die;
}


$form = new \tool_upgradecopy\forms\upgradecopy();

if (!$data = $form->get_data()) {
    $form->display();
    echo $OUTPUT->footer();
    die();
}

// Scroll to the end when finished.
$PAGE->requires->js_init_code("window.scrollTo(0, 5000000);");

echo $OUTPUT->box_start();
$paths = \tool_upgradecopy\process::get_paths();
$fromprefix = $data->pathfrom;
$toprefix = $data->pathto;
$command = "cp -R";
echo "<pre>\n";
foreach ($paths as $path) {
    echo "$command $fromprefix$path->from $toprefix$path->to\n";
}
echo $OUTPUT->box_end();

echo $OUTPUT->continue_button(new moodle_url('/admin/index.php'));

echo $OUTPUT->footer();
