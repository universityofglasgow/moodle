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
 * Template lib
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_template\utils;

/**
 * Serve the files from the local_template file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function local_template_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    // Should not be in a course context.
    if ($course != null) {
        return false;
    }

    // Should not be in a mod context.
    if ($cm != null) {
        return false;
    }

    // Should be in a course category context.
    if ($context->contextlevel != CONTEXT_COURSECAT) {
        return false;
    }

    // Should be summary or overviewfiles file area only
    if (!($filearea == 'summary' || $filearea == 'overviewfiles')) {
        return false;
    }

    // Enforce login and capability checks.
    utils::enforce_security();

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Managetemplate users have access to all files.
    if (!utils::is_admin()) {
        $template = new \local_template\models\template($itemid);
        global $USER;

        // Enforce template userid is correct file userid.
        if ($USER->id != $template->get('usercreated')) {
            return false;
        }
    }

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_template', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}


/**
 * Hook to insert a link in settings navigation menu block
 *
 * @param settings_navigation $settings
 * @param context $context
 * @return void
 */
function local_template_extend_settings_navigation(settings_navigation $navigation, context $context) {

    // Only extend navigation for category contexts.
    if ($context == null) {
        return;
    }

    $addnewcoursenavigation = get_config('local_template', 'addnewcoursenavigation');
    if (!$addnewcoursenavigation) {
        return false;
    }

    $categorynode = $navigation->get('categorysettings');
    if ($categorynode == null) {
        return;
    }

    if (has_capability('local/template:usetemplate', $context)) {
        global $CFG;
        $url = new moodle_url($CFG->wwwroot . '/local/template/index.php', array('category' => $context->instanceid));

        //public function add($text, $action=null, $type=self::TYPE_CUSTOM, $shorttext=null, $key=null, pix_icon $icon=null) {
        $categorynode->add(get_string('pluginname', 'local_template'), $url, navigation_node::TYPE_SETTING, null, 'local_template', new pix_icon('i/return', ''));
    }

}