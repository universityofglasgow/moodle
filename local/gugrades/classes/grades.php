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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/grade/lib.php');

/**
 * Class to store and manipulate grade structures for course
 */
class grades {

    /**
     * Get grade types from database
     * @return array [shortname => gradetype]
     */
    private static function get_gradetypes() {
        global $DB;

        $gradetypes = $DB->get_records('local_gugrades_gradetype');
        $gradetypesbyshortname = [];
        foreach ($gradetypes as $gradetype) {
            $gradetypesbyshortname[$gradetype->shortname] = $gradetype;
        }

        return $gradetypesbyshortname;
    }

    /**
     * Get gradetype (reason) record given shortname
     * @param string $shortname
     * @return object
     */
    private static function get_gradetype(string $shortname) {
        $gradetypes = self::get_gradetypes();
        if (!array_key_exists($shortname, $gradetypes)) {
            throw new \coding_exception('Gradetype with shortname "' . $shortname . '" does not exist.');
        }
        
        return $gradetypes[$shortname];
    }

    /**
     * Get item name from gradeitemid
     * @param int $gradeitemid
     * @return string
     */
    public static function get_item_name_from_itemid(int $gradeitemid) {
        global $DB;

        if ($grade_item = $DB->get_record('grade_items', ['id' => $gradeitemid])) {
            return $grade_item->itemname;
        }

        return '';
    }

    /**
     * Get first level categories (should be summative / formative and so on)
     * Actually depth==2 in the database (1 == top level)
     * @param int $courseid
     * @return array
     */
    public static function get_firstlevel(int $courseid) {
        global $DB;

        $gradecategories = $DB->get_records('grade_categories', [
            'courseid' => $courseid,
            'hidden' => 0,
        ]);

        $cats = [];
        foreach ($gradecategories as $category) {
            if ($category->depth == 2) {
                $cats[] = $category;
            }
        }

        return $cats;
    }

    /**
     * Get the category/item tree beneath the selected depth==2 category.
     * @param int $courseid
     * @param int $categoryid
     * @return object
     */
    public static function get_activitytree(int $courseid, int $categoryid) {
        global $DB; 

        $category = $DB->get_record('grade_categories', ['id' => $categoryid], '*', MUST_EXIST);
        $gradeitems = $DB->get_records('grade_items', [
            'courseid' => $courseid,
            'hidden' => 0,
        ]);
        $gradecategories = $DB->get_records('grade_categories', [
            'courseid' => $courseid,
            'hidden' => 0,
        ]);
        $categorytree = self::recurse_activitytree($category, $gradeitems, $gradecategories);
        
        return $categorytree;
    }

    /**
     * Recursive routine to build activity tree
     * Tree consists of both sub-categories and grade items
     * {
     *     category -> current category
     *     items -> array of grade items in this category
     *     categories -> array of grade categories, children of this category (recursive)
     * }
     * @param object $category
     * @param array $gradeitems
     * @param array $gradecategories
     * @return object 
     */
    private static function recurse_activitytree($category, $gradeitems, $gradecategories) {
        $tree = [];

        // first find any grade items attached to the current category
        $items = [];
        foreach ($gradeitems as $item) {
            if ($item->categoryid == $category->id) {
                $items[$item->id] = $item;
            }
        }

        // next find any sub-categories of this category
        $categories = [];
        foreach ($gradecategories as $gradecategory) {
            if ($gradecategory->parent == $category->id) {
                $categories[$gradecategory->id] = self::recurse_activitytree($gradecategory, $gradeitems, $gradecategories);
            }
        }

        // add this all up
        // array_values() to prevent arrays beening encoded as objects in JSON
        $record = new \stdClass();
        $record->category = $category;
        $record->items = array_values($items);
        $record->categories = array_values($categories);

        return $record;
    }

    /**
     * Write grade to local_gugrades_grade table
     *  
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @param float $grade
     * @param float $weightedgrade
     * @param string $reason  - gradetype shortname
     * @param string $other
     * @param bool $iscurrent;
     */
    public static function write_grade(
        int $courseid,
        int $gradeitemid,
        int $userid,
        float $grade,
        float $weightedgrade,
        string $reason,
        string $other,
        bool $iscurrent,
    ) {
        global $DB, $USER;

        // Get id of reason code
        $reasonid = self::get_gradetype($reason)->id;

        // Does this already exist
        if ($oldgrade = $DB->get_record('local_gugrades_grade', [
            'courseid' => $courseid,
            'gradeitemid' => $gradeitemid,
            'userid' => $userid,
            'reason' => $reasonid,
        ])) {
            // It's not current any more
            $oldgrade->iscurrent = false;
            $DB->update_record('local_gugrades_grade', $oldgrade);
        }

        $gugrade = new \stdClass;
        $gugrade->courseid = $courseid;
        $gugrade->gradeitemid = $gradeitemid;
        $gugrade->userid = $userid;
        $gugrade->grade = $grade;
        $gugrade->weightedgrade = $weightedgrade;
        $gugrade->reason = $reasonid;
        $gugrade->other = $other;
        $gugrade->iscurrent = true;
        $gugrade->auditby = $USER->id;
        $gugrade->audittimecreated = time();
        $gugrade->auditcomment = '';
        $DB->insert_record('local_gugrades_grade', $gugrade);
    }

    /**
     * Get user grades
     * @param int $courseid,
     * @param int $gradeitemid
     * @param int $userid
     * @param string $reason (FIRST, SECOND... null = get all)
     * @param o
     */
    public static function get_user_grades(int $courseid, int $gradeitemid, int $userid, string $reason = null) {
        global $DB;

        if ($reason) {
            $reasonid = $self::get_gradetype($reason)->id;
            $gugrade = $DB->get_record('local_gugrades_grade', [
                'courseid' => $courseid,
                'gradeitemid' => $gradeitemid,
                'userid' => $userid,
                'reason' => $reasonid
            ]);

            return $gugrade;
        } else {
            $gugrades = $DB->get_records('local_gugrades_grade', [
                'courseid' => $courseid,
                'gradeitemid' => $gradeitemid,
                'userid' => $userid,
            ]);

            // Get gradetypes
            $gradetypes = $DB->get_records('local_gugrades_gradetype');

            // Index by reason
            $reasongrades = [];
            foreach ($gugrades as $gugrade) {
                $reasonshortname = $gradetypes[$gugrade->reason]->shortname;
                $reasongrades[$reasonshortname] = $gugrade;
            }

            return $reasongrades;
        }
    }

    /**
     * Add grades to user records for capture page
     * @param int $courseid
     * @param int $gradeitemid
     * @param array $users
     * @return array
     */
    public static function add_grades_to_user_records(int $courseid, int $gradeitemid, array $users) {
        foreach ($users as $user) {
            $grades = self::get_user_grades($courseid, $gradeitemid, $user->id);
            $user->grades = $grades;
        }

        return $users;
    }

    /**
     * Get scale conversion table
     * @param int $gradeitemid
     * @return array or false (false if unsupported scale)
     */
}