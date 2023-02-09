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

    // Category tree structure
    private $categorytree;

    /**
     * Class constructor
     * @param int $courseid
     */
    function __construct($courseid) {
        $this->courseid = $courseid;

        // Get category tree data structure
        $this->categorytree = \grade_category::fetch_course_tree($courseid);
    }

    /**
     * Get first level categories (should be summative / formative and so on)
     */
    public function get_firstlevel() {
        $coursecategory = $this->categorytree['object'];

        return $coursecategory->get_children();
    }

    public function get_categorytree() {
        return $this->categorytree;
    }

}