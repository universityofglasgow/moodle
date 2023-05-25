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

    // Course id
    private $courseid;

    // Grade items
    private $gradeitems;

    // Grade categories
    private $gradecategories;

    // Grade types
    private $gradetypes;

    // Gradetypes by id
    private $gradetypes_id;

    /**
     * Class constructor
     * @param int $courseid
     */
    function __construct($courseid) {
        global $DB;

        $this->courseid = $courseid;

        // Read all grade items (not hidden) for current course
        $this->gradeitems = $DB->get_records('grade_items', [
            'courseid' => $this->courseid,
            'hidden' => 0,
        ]);

        // Read all grade categories (not hidden) for current course
        $this->gradecategories = $DB->get_records('grade_categories', [
            'courseid' => $this->courseid,
            'hidden' => 0,
        ]);

        // Get the list of gradetypes
        $this->get_gradetypes();
    }

    /**
     * Get grade types from database
     */
    private function get_gradetypes() {
        global $DB;

        $gradetypes = $DB->get_records('local_gugrades_gradetype');
        $this->gradetypes_id = $gradetypes;
        $this->gradetypes = [];
        foreach ($gradetypes as $gradetype) {
            $this->gradetypes[$gradetype->shortname] = $gradetype;
        }
    }

    /**
     * Get gradetype (reason) record given shortname
     * @param string $shortname
     * @return object
     */
    private function get_gradetype(string $shortname) {
        if (!array_key_exists($shortname, $this->gradetypes)) {
            throw new \coding_exception('Gradetype with shortname "' . $shortname . '" does not exist.');
        }
        
        return $this->gradetypes[$shortname];
    }

    /**
     * Get gradetype (reason) given id
     * @param int $id
     * @return object
     */
    private function get_gradetype_from_id(int $id) {
        return $this->gradetypes_id[$id];
    }

    /**
     * Utility function to get reason name
     * @param int $id
     * @return string
     */
    public function get_reason_from_id(int $id) {
        return $this->get_gradetype_from_id($id)->fullname;
    }

    /**
     * Get item name from gradeitemid
     * @param int $gradeitemid
     * @return string
     */
    public function get_item_name_from_itemid(int $itemid) {
        global $DB;

        if ($grade_item = $DB->get_record('grade_items', ['id' => $gradeitemid])) {
            return $grade_item->itemname;
        }

        return '';
    }

    /**
     * Get first level categories (should be summative / formative and so on)
     * Actually depth==2 in the database (1 == top level)
     */
    public function get_firstlevel() {
        global $DB;

        $cats = [];
        foreach ($this->gradecategories as $category) {
            if ($category->depth == 2) {
                $cats[] = $category;
            }
        }

        return $cats;
    }

    /**
     * Get the category/item tree beneath the selected depth==2 category.
     * @param int $categoryid
     * @return object
     */
    public function get_activitytree($categoryid) {
        $category = $this->gradecategories[$categoryid];  
        $categorytree = $this->recurse_activitytree($category);
        
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
     * @return object 
     */
    private function recurse_activitytree($category) {
        $tree = [];

        // first find any grade items attached to the current category
        $items = [];
        foreach ($this->gradeitems as $item) {
            if ($item->categoryid == $category->id) {
                $items[$item->id] = $item;
            }
        }

        // next find any sub-categories of this category
        $categories = [];
        foreach ($this->gradecategories as $gradecategory) {
            if ($gradecategory->parent == $category->id) {
                $categories[$gradecategory->id] = $this->recurse_activitytree($gradecategory);
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
     * @param in $gradeitemid
     * @param int $userid
     * @param float $grade
     * @param float $weightedgrade
     * @param string $reason  - gradetype shortname
     * @param string $other
     * @param bool $iscurrent;
     */
    public function write_grade(
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
        $reasonid = $this->get_gradetype($reason)->id;

        // Does this already exist
        if ($gugrade = $DB->get_record('local_gugrades_grade', [
            'courseid' => $this->courseid,
            'gradeitemid' => $this->gradeitemid,
            'userid' => $userid,
            'reason' => $reasonid,
        ])) {
            // Update grade
            // TODO: it won't be as simple as this
            $gugrade->grade = $grade;
            $gugrade->weightedgrade = $weightedgrade;
            $gugrade->iscurrent = true;
            $gugrade->auditby = $USER->id;
            $gugrade->audittimecreated = time();
            $gugrade->auditcomment = '';
            $DB->update_record('local_gugrades_grade', $gugrade);
        } else {
            $gugrade = new \stdClass;
            $gugrade->courseid = $this->courseid;
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
    }

    /**
     * Get user grades
     * @param int $gradeitemid
     * @param int $userid
     * @param string $reason (FIRST, SECOND... null = get all)
     * @param o
     */
    public function get_user_grades(int $gradeitemid, int $userid, string $reason = null) {
        global $DB;

        if ($reason) {
            $reasonid = $this->get_gradetype($reason)->id;
            $gugrade = $DB->get_record('local_gugrades_grade', [
                'courseid' => $this->courseid,
                'gradeitemid' => $this->gradeitemid,
                'userid' => $userid,
                'reason' => $reasonid
            ]);

            return $gugrade;
        } else {
            $gugrades = $DB->get_records('local_gugrades_grade', [
                'courseid' => $this->courseid,
                'gradeitemid' => $gradeitemid,
                'userid' => $userid,
            ]);

            // Index by reason
            $reasongrades = [];
            foreach ($gugrades as $gugrade) {
                $reasonshortname = $this->get_gradetype_from_id($gugrade->reason)->shortname;
                $reasongrades[$reasonshortname] = $gugrade;
            }

            return $reasongrades;
        }
    }

    /**
     * Add grades to user records for capture page
     * @param int $gradeitemid
     * @param array $users
     * @return array
     */
    public function add_grades_to_user_records(int $gradeitemid, array $users) {
        foreach ($users as $user) {
            $grades = $this->get_user_grades($gradeitemid, $user->id);
            $user->grades = $grades;
        }

        return $users;
    }
}