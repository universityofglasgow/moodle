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
 * Tiles course format.  Display the whole course as "tiles" made of course modules.
 *
 * @package format_tiles
 * @copyright 2022 David Watson {@link http://evolutioncode.uk}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $PAGE, $USER, $SESSION, $CFG;

// Horrible backwards compatible parameter aliasing.
if ($topic = optional_param('topic', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing.

// Retrieve course format option fields and add them to the $course object.
$format = course_get_format($course);
$course = $format->get_course();
$context = context_course::instance($course->id);
$isediting = $PAGE->user_is_editing();
$canedit = $PAGE->user_allowed_editing();
$displaysection = optional_param('section', 0, PARAM_INT);
if (!empty($displaysection)) {
    $format->set_sectionnum($displaysection);
}

if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

$renderer = $PAGE->get_renderer('format_tiles');

$allowphototiles = get_config('format_tiles', 'allowphototiles');
$usejsnav = \format_tiles\util::using_js_nav();

// This will take us to render_content() in /course/format/tiles/classes/output/renderer.php.
$outputclass = $format->get_output_classname('content');
$widget = new $outputclass($format);
echo $renderer->render($widget);

// Include format.js (required for dragging sections around).
$PAGE->requires->js('/course/format/tiles/format.js');

// Include amd module required for AJAX calls to change tile icon, filter buttons etc.
if (!empty($displaysection)) {
    $jssectionnum = $displaysection;
} else if (! $jssectionnum = optional_param('expand', 0, PARAM_INT)) {
    $jssectionnum = 0;
}
if ($canedit) {
    $SESSION->editing_last_edited_section = $course->id . "-" . $displaysection;
}

$jsparams = [
    'courseId' => $course->id,
    'useJSNav' => $usejsnav, // See also lib.php page_set_course().
    'isMobile' => core_useragent::get_device_type() == core_useragent::DEVICETYPE_MOBILE ? 1 : 0,
    'jsSectionNum' => $jssectionnum,
    'displayFilterBar' => $course->displayfilterbar,
    'assumeDataStoreContent' => get_config('format_tiles', 'assumedatastoreconsent'),
    'reOpenLastSection' => get_config('format_tiles', 'reopenlastsection'),
    'userId' => $USER->id,
    'fitTilesToWidth' => get_config('format_tiles', 'fittilestowidth')
        && !optional_param("skipcheck", 0, PARAM_INT)
        && !isset($SESSION->format_tiles_skip_width_check)
        && $usejsnav,
    'enablecompletion' => $course->enablecompletion,
    'usesubtiles' => get_config('format_tiles', 'allowsubtilesview') && $course->courseusesubtiles,
];

if (!$isediting) {
    // Initalise the main JS module for non editing users.
    $PAGE->requires->js_call_amd(
        'format_tiles/course', 'init', array_merge($jsparams, ['courseContextId' => $context->id])
    );
}
if ($isediting) {
    // Initalise the main JS module for editing users.
    $jsparams['pagetype'] = $PAGE->pagetype;
    $jsparams['allowphototiles'] = $allowphototiles;
    $jsparams['documentationurl'] = get_config('format_tiles', 'documentationurl');

    $PAGE->requires->js_call_amd('format_tiles/edit_course', 'init', $jsparams);
    if (strpos($PAGE->pagetype, 'course-view-') === 0 && $PAGE->theme->name == 'snap') {
        \core\notification::ERROR(
            get_string('snapwarning', 'format_tiles') . ' ' .
            html_writer::link(
                get_docs_url(get_string('snapwarning_help', 'format_tiles')),
                get_string('morehelp')
            )
        );
    }
}

if ($course->enablecompletion) {
    $PAGE->requires->js_call_amd('format_tiles/completion', 'init', [$course->id]);
}
