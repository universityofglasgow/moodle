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
 * Object contains capture data for a single user
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
 * Represents a single row of capture data for a user
 */
class usercapture {

    /**
     * @var int $courseid
     */
    protected int $courseid;

    /**
     * @var int $gradeitemid
     */
    protected int $gradeitemid;

    /**
     * @var int $userid
     */
    protected int $userid;

    /**
     * @var array $grades
     */
    protected $grades;

    /**
     * @var object $provisional
     */
    protected $provisional;

    /**
     * @var array $gradebygradetye
     */
    protected $gradesbygradetype;

    /**
     * @var bool $alert
     */
    protected bool $alert;

    /**
     * Static function to create this class.
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @return \local_gugrades\usercapture
     */
    public static function create(int $courseid, int $gradeitemid, int $userid) {

        $usercapture = new usercapture($courseid, $gradeitemid, $userid);
        
        return $usercapture;
    }

    /**
     * Constructor
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     */
    private function __construct(int $courseid, int $gradeitemid, int $userid) {

        $this->courseid = $courseid;
        $this->gradeitemid = $gradeitemid;
        $this->userid = $userid;

        $this->read_grades();
        $this->get_gradesbygradetype();
    }

    /**
     * Organise grades by gradetype.
     * For this we ignore OTHER types as they are not needed
     * (e.g. for applying grade rules)
     * @param array $grades
     */
    protected function find_gradesbygradetype($grades) {
        $this->gradesbygradetype = [];
        foreach ($grades as $grade) {
            if ($grade->gradetype != 'OTHER') {
                $this->gradesbygradetype[$grade->gradetype] = $grade;
            }
        }
    }

    /**
     * Get the provisional grade given the array of active grades
     * Default is just to return the most recent one
     * TODO: Figure out what to do with admin grades
     * (Grades array is required as object field hasn't been assigned yet)
     * @param array $grades
     * @return mixed
     */
    protected function get_provisional_from_grades(array $grades) {

        // We're just going to assume that the grades are in ascending date order.
        if ($grades) {
            $provisional = clone end($grades);
            $provisional->gradetype = 'PROVISIONAL';

            return $provisional;
        } else {

            return null;
        }
    }

    /**
     * Determine if we need to place an alert on the capture row
     * For example, 1st and 2nd grade not matching plus no agreed grade
     * @return boolean
     */
    protected function is_alert() {
        $gradesbygt = $this->get_gradesbygradetype();

        // 1st, 2nd and 3rd grade have to agree
        // unless there is an agreed grade
        if (array_key_exists('AGREED', $gradesbygt)) {
            return false;
        }

        // The -1 if they don't exist (not existing is proxy for equal).

        $first = array_key_exists('FIRST', $gradesbygt)  ? $gradesbygt['FIRST']->rawgrade : -1;
        $second = array_key_exists('SECOND', $gradesbygt) ? $gradesbygt['SECOND']->rawgrade : -1;
        $third = array_key_exists('THIRD', $gradesbygt) ? $gradesbygt['THIRD']->rawgrade : -1;

        // MGU-1374. 'No grade' for FIRST shouldn't be a discrepancy.
        if ($first === null) {
            $first = -1;
        }

        // Only 1st grade is acceptable.
        if (($second == -1) && ($third == -1)) {
            return false;
        }

        // If no third then first and second must agree.
        if ($third == -1) {
            return ($first != $second);
        }

        // Failing all of above, must agree.
        if (($first == $second) && ($second == $third)) {
            return false; // All equal.
        } else {
            return true; // Not all equal.
        }
    }

    /**
     * Will the grade be hidden due to gradebook settings
     * @return boolean
     */
    public function is_gradebookhidden() {
        global $DB;

        if ($grade = $DB->get_record('grade_grades', ['itemid' => $this->gradeitemid, 'userid' => $this->userid])) {
            
            return $grade->hidden != 0;
        }

        return false;
    }

    /**
     * Get the released grade. For base this is exactly the same as provisional
     * @return object
     */
    public function get_released() {
        $released = $this->provisional;
        if ($released) {
            $released->gradetype = 'RELEASED';
        }

        return $released;
    }

    /**
     * Acquire and check grades in database
     *
     */
    private function read_grades() {
        global $DB;

        $this->provisional = null;

        // Get the gradeitem to sanity check grade, MGU-1344
        $gradeitem = \local_gugrades\grades::get_gradeitem($this->gradeitemid);

        // ...id is a proxy for time added.
        // Cannot use the timestamp as the unit tests write the test grades all in the
        // same second (potentially).
        $grades = $DB->get_records('local_gugrades_grade', [
            'gradeitemid' => $this->gradeitemid,
            'userid' => $this->userid,
            'iscurrent' => 1,
        ], 'id ASC');

        // Work out / add provisional grade.
        if ($grades) {
            $provisional = $this->get_provisional_from_grades($grades);

            // MGU-1344: If points grade, check for over-range. Can happen if grade item edited after import. 
            if ($provisional->points && ($provisional->rawgrade > $gradeitem->grademax)) {
                throw new \moodle_exception('Out of range grade detected for ' . $gradeitem->itemname . 
                    '. gradeitemid = ' . $this->gradeitemid . ', userid = ' . $this->userid . ', rawgrade = ' . $provisional->rawgrade . ', grademax = ' . $gradeitem->grademax);
            }
            $provisionalcolumn = \local_gugrades\grades::get_column($this->courseid, $this->gradeitemid, 'PROVISIONAL', '',
                $provisional->points);

            $provisional->columnid = $provisionalcolumn->id;
            $this->provisional = $provisional;
            $grades[] = $provisional;
        }

        // Organise by gradetype.
        $this->find_gradesbygradetype($grades);

        // Check if there should be an alert.
        $this->alert = $this->is_alert();

        $this->grades = $grades;
    }

    /**
     * Get the grade array
     * @return array
     */
    public function get_grades() {
        return $this->grades;
    }

    /**
     * Get the gradesbygradetype array
     * @return array
     */
    public function get_gradesbygradetype() {
        return $this->gradesbygradetype;
    }

    /**
     * Get provisional grade
     * @return object or null
     */
    public function get_provisional() {
        return $this->provisional;
    }

    /**
     * Get alert status
     * @return boolean
     *
     */
    public function alert() {
        return $this->alert;
    }

}
