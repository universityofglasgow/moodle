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
require_once($CFG->dirroot . '/course/format/tiles/locallib.php');

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
$displaysection = optional_param('section', 0, PARAM_INT);
if (!empty($displaysection)) {
    $format->set_section_number($displaysection);
}

if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

$renderer = $PAGE->get_renderer('format_tiles');

$ismobile = core_useragent::get_device_type() == core_useragent::DEVICETYPE_MOBILE ? 1 : 0;
$allowphototiles = get_config('format_tiles', 'allowphototiles');
$userstopjsnav = get_user_preferences('format_tiles_stopjsnav', 0);

// JS navigation and modals in Internet Explorer are not supported by this plugin so we disable JS nav here.
$usejsnav = !$userstopjsnav && get_config('format_tiles', 'usejavascriptnav') && !core_useragent::is_ie();

// Inline CSS may be required if this course is using different tile colours to default - echo this first if so.
$inlinecsstemplateable = new \format_tiles\output\inline_css_output($course, $ismobile, $usejsnav, $allowphototiles);
$inlinecssdata = $inlinecsstemplateable->export_for_template($renderer);
echo $renderer->render_from_template('format_tiles/inline-css', $inlinecssdata);

if ($isediting) {
    // If user is editing, we render the page the new way.
    // TODO we will use this for non editing as well, but not yet.
    $outputclass = $format->get_output_classname('content');
    $widget = new $outputclass($format);

    echo $renderer->render($widget);
} else {
    if (display_multiple_section_page($displaysection, $usejsnav, $context, $isediting)) {
        $templateable = new \format_tiles\output\course_output($course, false, null, $renderer);
        $data = $templateable->export_for_template($renderer);
        echo $renderer->render_from_template('format_tiles/multi_section_page', $data);
    } else {
        $SESSION->editing_last_edited_section = $course->id . "-" . $displaysection;
        $templateable = new \format_tiles\output\course_output($course, false, $displaysection, $renderer);
        $data = $templateable->export_for_template($renderer);
        echo $renderer->render_from_template('format_tiles/single_section_page', $data);
    }
}

// Include format.js (required for dragging sections around).
$PAGE->requires->js('/course/format/tiles/format.js');

// Include amd module required for AJAX calls to change tile icon, filter buttons etc.
if (!empty($displaysection)) {
    $jssectionnum = $displaysection;
} else if (! $jssectionnum = optional_param('expand', 0, PARAM_INT)) {
    $jssectionnum = 0;
} else if (isset($SESSION->editing_last_edited_section)) {
    $jssectionnum = $SESSION->editing_last_edited_section;
}

$allowedmodmodals = format_tiles_allowed_modal_modules();

$jsparams = array(
    'courseId' => $course->id,
    'useJSNav' => $usejsnav, // See also lib.php page_set_course().
    'isMobile' => $ismobile,
    'jsSectionNum' => $jssectionnum,
    'displayFilterBar' => $course->displayfilterbar,
    'assumeDataStoreContent' => get_config('format_tiles', 'assumedatastoreconsent'),
    'reOpenLastSection' => get_config('format_tiles', 'reopenlastsection'),
    'userId' => $USER->id,
    'fitTilesToWidth' => get_config('format_tiles', 'fittilestowidth')
        && !optional_param("skipcheck", 0, PARAM_INT)
        && !isset($SESSION->format_tiles_skip_width_check)
        && $usejsnav,
    'enablecompletion' => $course->enablecompletion
);

if (!$isediting) {
    // Initalise the main JS module for non editing users.
    $PAGE->requires->js_call_amd(
        'format_tiles/course', 'init', $jsparams
    );
}
if ($isediting) {
    // Initalise the main JS module for editing users.
    $jsparams['pagetype'] = $PAGE->pagetype;
    $jsparams['allowphototiles'] = $allowphototiles;
    $jsparams['usesubtiles'] = get_config('format_tiles', 'allowsubtilesview') && $course->courseusesubtiles;
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
// Now the modules which we want whether editing or not.

// If we are allowing course modules to be displayed in modal windows when clicked.
if (!empty($allowedmodmodals['resources']) || !empty($allowedmodmodals['modules'])) {
    $PAGE->requires->js_call_amd(
        'format_tiles/course_mod_modal', 'init', array($course->id, $isediting)
    );
}
if ($course->enablecompletion) {
    $PAGE->requires->js_call_amd('format_tiles/completion', 'init', array($course->id));
}

/**
 * Should we display a multiple section page or not?
 * I.e. do we display all tiles on screen or just one open section?
 * @param int $displaysection the param to say if we are displaying one sec and if so which.
 * @param bool $usejsnav are we using JS nav or not.
 * @param \context_course $context the context we are in
 * @param bool $isediting are we editing or not.
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 */
function display_multiple_section_page($displaysection, $usejsnav, $context, $isediting) {
    global $SESSION;
    // We display the multi section page if the user is not requesting a specific single section.
    // We also display it if user is requesting a specific section (URL &section=xx) with JS enabled.
    // We know they have JS if $SESSION->format_tiles_jssuccessfullyused is set.
    // In that case we show them the multi section page and use JS to open the section.
    if (optional_param('canceljssession', false, PARAM_BOOL)) {
        // The user is shown a link to cancel the successful JS flag for this session in <noscript> tags if their JS is off.
        unset($SESSION->format_tiles_jssuccessfullyused);
    }

    if (empty($displaysection)) {
        // If the URL does not request a specific section page (&section=xx) we always show multiple secs.
        return true;
    }

    if (optional_param('singlesec', 0, PARAM_INT)) {
        // Singlesec param is appended to inplace editable links by format_tiles\inplace_editable_render_section_name().
        return false;
    }

    // Otherwise, even if URL requests single, we may show multiple in certain situations.
    if ($usejsnav && isset($SESSION->format_tiles_jssuccessfullyused)) {
        if (!$isediting && get_config('format_tiles', 'usejsnavforsinglesection')) {
            return true;
        }
    }
    return false;
}
