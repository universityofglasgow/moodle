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
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_gugcat;

use local_gugcat;
use stdClass;

defined('MOODLE_INTERNAL') || die();
require_once('gcat_item.php');

 /**
 * Grade capture class.
 */

class grade_aggregation{

     /**
     * Returns rows for grade aggreation table
     *
     * @param mixed $course
     * @param mixed $module
     * @param mixed $students
     */
    public static function get_rows($course, $modules, $students){
        $rows = array();
        $gradebook = array();
        foreach ($modules as $mod) {
            $mod_grades = grade_get_grades($course->id, 'mod', $mod->modname, $mod->instance, array_keys($students));
            array_push($gradebook, $mod_grades);
        }

        $i = 1;
        foreach ($students as $student) {
            $gradecaptureitem = new gcat_item();
            $gradecaptureitem->cnum = $i;
            $gradecaptureitem->studentno = $student->id;
            $gradecaptureitem->surname = $student->lastname;
            $gradecaptureitem->forename = $student->firstname;
            $gradecaptureitem->grades = array();
            foreach ($gradebook as $item) {
                $gb = $item->items[0]->grades[$student->id];
                if(isset($gb)){
                    $grade = is_null($gb->grade) ? get_string('nograde', 'local_gugcat') : local_gugcat::convert_grade($gb->grade);
                    array_push($gradecaptureitem->grades, $grade);
                }
            }
            array_push($rows, $gradecaptureitem);
            $i++;
        }
        return $rows;
    }
}