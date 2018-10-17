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
 * course_overview library
 *
 * @package    block_course_overview
 * @copyright  2018 Howard Miller (howardsmiller@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_overview;

defined('MOODLE_INTERNAL') || die();

class lib {

    /**
     * Check if user has entry in new database 
     */
    public function check_records() {
        global $DB, $USER;

        if (!$DB->record_exists('block_course_overview', array('userid' => $USER->id))) {
            
            // Create favourites tab
            $favourites = get_user_preferences('course_overview_favourites');
            $ftab = new \stdClass;
            $ftab->userid = $USER->id;
            $ftab->label = get_string('favourites', 'block_course_overview');
            $ftab->order = 0;
            $ftab->favourite = 1;
            $ftab->hide = 0;
            $ftab->courseorder = $favourites;
            $DB->insert_record('block_course_overview', $ftab);

            // Create normal courses tab
            $courses = get_user_preferences('course_overview_course_sortorder');
            $ctab = new \stdClass;
            $ctab->userid = $USER->id;
            $ctab->label = get_string('courses', 'block_course_overview');
            $ctab->order = 0;
            $ctab->favourite = 0;
            $ctab->hide = 0;
            $ctab->courseorder = $courses;
            $DB->insert_record('block_course_overview', $ctab);
        }
    }
    

}
