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
 * Navigation link for Course Settings
 *
 * @package    report_coursediagnositc
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Include the required classes.
require_once($CFG->dirroot.'/report/coursediagnostic/classes/diagnostic_factory.php');

/**
 * @param $navigation
 * @param $course
 * @param $context
 * @return void
 * @throws coding_exception
 * @throws moodle_exception
 */
function report_coursediagnostic_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/coursediagnostic:view', $context)) {
        $url = new moodle_url('/report/coursediagnostic/index.php', ['courseid' => $course->id]);
        $navigation->add(
            get_string('pluginname', 'report_coursediagnostic'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            null,
            new pix_icon('i/report', '')
        );
    }
}

/**
 * Utility method to return bytes converted to KB/MB/GB etc.
 *
 * @param $abytes
 * @return string|void
 */
function formatsize($abytes) {
    if ($abytes < 1024) {
        return $abytes .' B';
    } else if ($abytes < 1048576) {
        return round($abytes / 1024, 1) .' KB';
    } else if ($abytes < 1073741824) {
        return round($abytes / 1048576, 1) . ' MB';
    } else if ($abytes < 1099511627776) {
        return round($abytes / 1073741824, 1) . ' GB';
    } else if ($abytes < 1125899906842624) {
        return round($abytes / 1099511627776, 1) .' TB';
    }
}
