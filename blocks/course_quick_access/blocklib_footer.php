<?php
// This file is part of course-quick-access plugin for Moodle
// http://moodle.org/
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
 * Contains all functions required by the blocks footer.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/consts.php');

/**
 * Returns a HTML-form, that contains the blocks-footers HTML elements.
 *
 * @return string The blocks-footers HTML elements as a string.
 * 
 */
function get_footer() {
    global $CFG;
    $content = '<ul class="unlist">';
    // User is logged in -> print "all-my-courses link"
    //if (isloggedin()) {
        // Add link to all courses, the user is enrolled in to the footer
    //    $allmycourseslink = "<li><a href=\"".MOODLE_USER_COURSES_URL."\">".
    //                get_string('allmycourses', 'block_course_quick_access').
    //                '</a>  &hellip;</li>';
    //    $content .= $allmycourseslink;
    //}
    // Add link to all moodle courses to the blocks footer
    $allcourseslink = "<li><a href=\"".MOODLE_ALL_COURSES_URL."\">".
                get_string('allmoodlecourses', 'block_course_quick_access').
                '</a> &hellip;</li>';    
    $content .= $allcourseslink;
    // User is logged in -> print link to block user-configuration
    if (isloggedin()) {
        $configuration_url = new moodle_url(PLUGIN_CONFIG_URL);
        $configuration_link = '<li><a href="'.$configuration_url.'">'.
                get_string('configureplugin', 'block_course_quick_access').
                '</a> &hellip;</li>';
        $content .= $configuration_link;
    }
    // Return the footer content
    return $content;
}