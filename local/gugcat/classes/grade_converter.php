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
 * Class containing helper methods for Grade Convertion page.
 * 
 * @package    local_gugcat
 * @copyright  2020x
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_gugcat;

use local_gugcat;
use grade_grade;

defined('MOODLE_INTERNAL') || die();

class grade_converter{

    public static function save_grade_converter($modid, $scale, $grades){
        global $DB;

        $DB->insert_records('gcat_grade_converter', $grades);
        $DB->set_field('grade_items', 'iteminfo', $scale, array('id'=>$modid));
    }

    /**
     * Converts all grades in provisional grade
     *
     * @param array $conversion List of grade element conversion
     * @param int  $itemid Provisional grade item id
     */
    public static function convert_provisional_grades($conversion, $itemid){
        if($grades = grade_grade::fetch_all(array('itemid' => $itemid))){
            foreach ($grades as $grdobj) { 
                if(!is_null($grdobj->rawgrade) && !local_gugcat::is_admin_grade($grdobj->rawgrade)){
                    $converted = self::convert($conversion, $grdobj->rawgrade);
                    $grdobj->finalgrade = $converted;
                    $grdobj->rawgrade = $converted;
                    $grdobj->timemodified = time();
                    $grdobj->update();
                }
            }
        }
    }

    /**
     * Returns array of grades for grade conversion
     *
     * @param int $itemid
     * @param array || null Array of grade elements for conversion
     */
    public static function retrieve_grade_conversion($itemid){
        global $DB, $COURSE;
        return $DB->get_records('gcat_grade_converter', array('courseid'=>$COURSE->id, 'itemid'=>$itemid));
    }

    /**
     * Deletes items in gcat_grade_converter for specific activity
     *
     * @param int $itemid
     * @param boolean
     */
    public static function delete_grade_conversion($itemid){
        global $DB, $COURSE;
        return $DB->delete_records('gcat_grade_converter', array('courseid'=>$COURSE->id, 'itemid'=>$itemid));
    }

     /**
     * Converts grade from the custom grade conversion
     *
     * @param array $conversion List of grade element conversion
     * @param int $grade
     * @return int $grade
     */
    public static function convert($conversion, $grade){
        // Return grade if its admin grade, -1, -2
        if(local_gugcat::is_admin_grade($grade)){
            return $grade;
        }
        // Reindex $conversion, so array indexes will start in 0
        $convs = array_values($conversion);
        $convertedgrade = null;

        foreach ($convs as $index=>$cobj) { 
            $cobj = (object) $cobj;     
            $upperbound = (isset($convs[$index-1]) && $precendent = (object)$convs[$index-1])
                ? $precendent->lowerboundary : null;
            // Get the upperbound from the preceding element lowerboundary
            if($upperbound){
                // Check if grade is within the range of lower and upper boundary
                if($grade < $upperbound  && $grade >= $cobj->lowerboundary){
                    $convertedgrade = $cobj->grade;
                    break;
                }
            }else{
                // Check if grade is greater than or equal to lower boundary
                if($grade >= $cobj->lowerboundary){
                    $convertedgrade = $cobj->grade;
                    break;
                }
            }
        }
        // Grade is lower than the lowerbound, get the lowest grade and -1
        // eg. lowest = G1 => 2 (-1), converted grade = G2 => 1
        // eg. lowest = H => 0, converted grade = H => 0
        if(is_null($convertedgrade)){
            $grade = min(array_column($convs, 'grade'));
            $convertedgrade = $grade == 0 ? 0 : intval($grade)-1;
        }
        return $convertedgrade;
    }

    
}