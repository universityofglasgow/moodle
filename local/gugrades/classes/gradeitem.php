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
 * Handles gradeitem and scale conversion (if required)
 */
class gradeitem {

    /**
     * @var int $courseid
     */
    protected int $courseid;

    /**
     * @var int $gradeitemid
     */
    protected int $gradeitemid;

    /**
     * @var object $gradeitem
     */
    protected $gradeitem;

    /**
     * @var object $course
     */
    protected $course;

    /**
     * Constructor
     * @param int $courseid
     * @param int $gradeitemid
     */
    public function __construct(int $courseid, int $gradeitemid) {
        global $DB;

        $this->courseid = $courseid;
        $this->gradeitemid = $gradeitemid;
        $this->$gradeitem = \local_gugrades\grades::get_gradeitem($gradeitemid);
        $this->course = get_course($courseid);
    }

    /**
     * Get name
     * @return string
     */
    public function get_name() {
        return $this->gradeitem->itemname;
    }

}
