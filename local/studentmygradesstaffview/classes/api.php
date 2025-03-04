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
 * The main API
 *
 * @package     local_studentmygradesstaffview
 * @author      Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright   2025 University of Glasgow
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace local_studentmygradesstaffview;

 defined('MOODLE_INTERNAL') || die();
 
 require_once(dirname(__FILE__) . '/constants.php');
 
 /**
  * Actual implementation of all the external functions
  */
class api {
    /**
     * Get all the strings for this plugin as array of objects
     * @return array
     */
    public static function get_all_strings() {
        $stringmanager = get_string_manager();
        $lang = current_language();
        $cstrings = $stringmanager->load_component_strings('local_studentmygradesstaffview', $lang);

        $strings = [];
        foreach ($cstrings as $tag => $stringvalue) {
            $strings[] = [
                'tag' => $tag,
                'stringvalue' => $stringvalue,
            ];
        }

        return $strings;
    }

    public static function get_students($courseid) {
        $context = \context_course::instance($courseid);
        get_enrolled_users($context, 'moodle/grade:view', 0, 'u.id, u.firstname, u.lastname',  null, 0, 0, true);
    }
}