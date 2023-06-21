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
        $tree = \local_gugrades\grades::get_activitytree($courseid, $categoryid);

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

        // Sanity checks for selected grade item
        if (!\local_gugrades\grades::is_grade_supported($gradeitemid)) {
            return [
                'users' => json_encode([]),
                'hidden' => false,
                'itemtype' => '',
                'itemname' => '',
                'gradesupported' => false,
            ];
        }

        // Instantiate object for this activity type
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid);
        $activity->set_name_filter($firstname, $lastname);

        // Get start point for LIMIT
        $pagingfrom = ($pageno - 1) * $pagelength;

        // Get list of users.
        // Will be everybody for 'manual' grades or filtered list for modules.
        $users = $activity->get_users();
        $users = \local_gugrades\grades::add_grades_to_user_records($courseid, $gradeitemid, $users);

        return [
            'users' => json_encode($users),
            'hidden' => $activity->is_names_hidden(),
            'itemtype' => $activity->get_itemtype(),
            'itemname' => $activity->get_itemname(),
            'gradesupported' => true,
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
        $results = [];
        $categories = \local_gugrades\grades::get_firstlevel($courseid);
        foreach ($categories as $category) {
            $results[] = [
                'id' => $category->id,
                'fullname' => $category->fullname,
            ];
        }

        // If there are no results then the course is not configured
        if (!$results) {
            $audit = new \local_gugrades\audit\notoplevel($courseid, 0  );
            $audit->save();
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

        // Instantiate object for this activity type
        $activity = \local_gugrades\users::activity_factory($gradeitemid, $courseid);

        // Ask activity for grade
        $grade = $activity->get_first_grade($userid);

        if ($grade !== false) {
            \local_gugrades\grades::write_grade(
                $courseid,
                $gradeitemid,
                $userid,
                $grade,
                0,
                'FIRST',
                '',
                1
            );
        }
    }

    /**
     * Get user picture url
     * @param int $userid
     * @return moodle_url
     */
    public static function get_user_picture_url(int $userid) {
        global $DB, $PAGE;

        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        $user_picture = new \user_picture($user);

        return $user_picture->get_url($PAGE);
    }

    /**
     * Get user grades
     * Get site-wide grades for dashboard / Glasgow life / testing / etc. 
     * @param int $userid
     * @return array
     */
    public static function get_user_grades(int $userid) {
        global $DB;

        // Load *current* grades for this user
        if (!$grades = $DB->get_records('local_gugrades_grade', ['userid' => $userid, 'iscurrent' => 1])) {
            return [];
        }

        // "cache" course objects so we don't keep looking them up
        $courses = [];

        // Iterate over grades adding additional information
        $newgrades = [];
        foreach ($grades as $grade) {
            $courseid = $grade->courseid;

            // Find course or just skip if it doesn't exist (deleted?)
            if (array_key_exists($courseid, $courses)) {
                $course = $courses[$courseid];
            } else {
                if (!$course = $DB->get_record('course', ['id' => $courseid])) {
                    continue;
                }
                $courses[$courseid] = $course;
            }

            // Add course data
            $grade->coursefullname = $course->fullname;
            $grade->courseshortname = $course->shortname;

            // Additional grade data
            $gradetype = $DB->get_record('local_gugrades_gradetype', ['id' => $grade->reason], '*', MUST_EXIST);
            $grade->reasonname = $gradetype->fullname;

            // Item into
            $grade->itemname = $gradeobject->get_item_name_from_itemid($grade->gradeitemid);

            $newgrades[] = $grade;
        }

        return $newgrades;
    }

    /**
     * Get grade history for given user / grade item
     * @param int $gradeitemid
     * @param int $userid
     * @return array
     */
    public static function get_history(int $gradeitemid, int $userid) {
        global $DB;

        if (!$grades = $DB->get_records('local_gugrades_grade', ['userid' => $userid, 'gradeitemid' => $gradeitemid], 'audittimecreated DESC')) {
            return [];
        }

        // Additional info
        $newgrades = [];
        foreach ($grades as $grade) {
            $gradetype = $DB->get_record('local_gugrades_gradetype', ['id' => $grade->reason], '*', MUST_EXIST);
            $grade->reasonname = $gradetype->fullname;
            $grade->time = userdate($grade->audittimecreated);
            $grade->current = $grade->iscurrent ? get_string('yes') : get_string('no');

            $newgrades[] = $grade;
        }

        return $newgrades;
    }

    /**
     * Get audit history
     * @param int $courseid
     * @param int $userid
     * @return array
     */
    public static function get_audit(int $courseid, int $userid) {
        global $USER, $DB;

        // Must have capability to view somebody else's history
        if (!$userid) {
            $userid = $USER->id;
        }
        if ($USER->id != $userid) {
            $context = \context_course::instance($courseid);
            require_cabability('local/gugrades:readotheraudit', $context);
        }

        $items = $DB->get_records('local_gugrades_audit', ['courseid' => $courseid, 'userid' => $userid], 'timecreated DESC');

        // Additional info
        $newitems = [];
        $gradeitemcache = [];
        foreach ($items as $item) {
            $item->time = userdate($item->timecreated);
            if ($item->type == 'error') {
                $item->bgcolor = 'danger';
            } else if ($item->type == 'warning') {
                $item->bgcolor = 'warning';
            } else {
                $item->bgcolor = 'info';
            }

            // Get (if possible) name of grade item.
            $gradeitem = null;
            if (array_key_exists($item->gradeitemid, $gradeitemcache)) {
                $gradeitem = $gradeitemcache[$item->gradeitemid];
            } else {
                if ($gradeitem = $DB->get_record('grade_items', ['id' => $item->gradeitemid])) {
                    $gradeitemcache[$item->gradeitemid] = $gradeitem;
                }
            }
            if ($gradeitem) {
                $item->gradeitem = $gradeitem->itemname;
            } else {
                $item->gradeitem = '';
            }

            $newitems[] = $item;
        }

        return $newitems;
    }

    /**
     * Has anything been defined for gradeitemid
     * @param int $courseid
     * @param int $gradeitemid
     * @return boolean
     */
    public static function is_grades_imported(int $courseid, int $gradeitemid) {
        return \local_gugrades\grades::is_grades_imported($courseid, $gradeitemid);
    }
}