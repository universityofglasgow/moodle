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
 * Language EN
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

class api {

    /**
     * Get activities
     * @param int $courseid
     * @param int $categoryid
     * @return object List of activities/subcategories in
     */
    public static function get_activities(int $courseid, int $categoryid) {
        $grades = new \local_gugrades\grades($courseid);
        $tree = $grades->get_activitytree($categoryid);

        return $tree;
    }

    /**
     * Get capture page
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $pageno
     * @param int $pagelength
     * @param string $firstname (first letter of)
     * @param string $lastname (last letter of)
     * @return array[users, hidden]
     */
    public static function get_capture_page(int $courseid, int $gradeitemid, int $pageno, int $pagelength, string $firstname, string $lastname) {

        // Instantiate object for this activity type
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid);
        $activity->set_name_filter($firstname, $lastname);

        // Get start point for LIMIT
        $pagingfrom = ($pageno - 1) * $pagelength;

        // Get list of users.
        // Will be everybody for 'manual' grades or filtered list for modules.
        $users = $activity->get_users();

        return [
            'users' => json_encode($users),
            'hidden' => $activity->is_names_hidden(),
        ];
    }

    /**
     * Get grade item
     * @param int $itemid
     * @return array
     */
    public static function get_grade_item(int $itemid) {
        global $DB;

        // Get item (if it exists)
        $item = $DB->get_record('grade_items', ['id' => $itemid], '*', MUST_EXIST);

        return [
            'id' => $item->id,
            'courseid' => $item->courseid,
            'categoryid' => $item->categoryid,
            'itemname' => $item->itemname,
            'itemtype' => $item->itemtype,
            'itemmodule' => $item->itemmodule,
            'iteminstance' => $item->iteminstance,            
        ];
    }

    /**
     * get_levelonecategories
     * @param int $courseid
     * @return array
     */
    public static function get_levelonecategories(int $courseid) {
        $grades = new \local_gugrades\grades($courseid);
        $results = [];
        $categories = $grades->get_firstlevel();
        foreach ($categories as $category) {
            $results[] = [
                'id' => $category->id,
                'fullname' => $category->fullname,
            ];
        }

        return $results;
    }

    /**
     * Import grade
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @return 
     */
    public static function import_grade(int $courseid, int $gradeitemid, int $userid) {
        $grades = new \local_grades\grades($courseid);

        // Instantiate object for this activity type
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid);

        // Ask activity for grade
        $grade = $activity->get_first_grade($userid);
    }
}