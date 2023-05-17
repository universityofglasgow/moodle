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

        $gradetypes = $DB->get_records('local_gugrades_gradetypes');
        $this->gradetypes = [];
        foreach ($gradetypes as $gradetype) {
            $this->gradetypes[$gradetype->shortname] = $gradetype->fullname;
        }
    }

    /**
     * Get gradetype record given shortname
     * @param string $shortname
     * @return object
     */
    private function get_gradetype(string $shortname) {
        if (!array_key_exists($shortname, $this->gradetypes)) {
            throw new coding_exception('Gradetype with shortname "' . $shortname . '" does not exist.');
        }
        
        return $this->gradetypes[$shortname];
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
        int $reason,
        string $other,
        bool $iscurrent,
    ) {
        global $DB, $USER;

        $g = new stdClass;
        $g->courseid = $this->courseid;
        $g->gradeitemid = $gradeitemid;
        $g->userid = $userid;
        $g->grade = $grade;
        $g->weightedgrade = $weightedgrade;
        $g->reason = $this->get_gradetype($reason)->id;
        $g->other = $other;
        $g->iscurrent = true;
        $g->auditby = $USER->id;
        $g->audittimecreated = time();
        $g->auditcomment = '';
        $DB->insert_record('local_gugrades_grade', $g);
    }
}