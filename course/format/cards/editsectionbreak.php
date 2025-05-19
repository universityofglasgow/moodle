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
 * Adds or removes a section break.
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use format_cards\section_break;

require_once(__DIR__ . "/../../../config.php");

$courseid = required_param('courseid', PARAM_INT);
$sectionid = required_param('sectionid', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

// Login and permissions check.
require_login($courseid, false);
$context = context_course::instance($courseid);
require_capability('moodle/course:update', $context);

global $DB;

$format = course_get_format($courseid);
$modinfo = get_fast_modinfo($courseid);
$section = $modinfo->get_section_info_by_id($sectionid);

$redirect = new moodle_url(course_get_url($courseid), null, "sectionid-$sectionid-title");

$cache = cache::make_from_params(
    cache_store::MODE_APPLICATION,
    'format_cards',
    'section_breaks'
);

// Remove the section break at this section ID.
if ($action === 'remove') {
    $sectionbreak = section_break::get_break_for_section_id($sectionid);

    if (is_null($sectionbreak)) {
        redirect($redirect);
    }

    $sectionbreak->delete();

    $cache->delete($courseid);
}

// Add a new section break at this section.
if ($action === 'add') {
    $sectionbreak = new section_break();
    $sectionbreak->set('courseid', $courseid);
    $sectionbreak->set('section', $section->section);

    $sectionbreak->save();

    $cache->delete($courseid);
}

redirect($redirect);
