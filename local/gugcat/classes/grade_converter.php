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
use stdClass;

defined('MOODLE_INTERNAL') || die();

class grade_converter{

    /**
     * Saves grade converter items into gcat_grade_converter table
     * @param array $conversion
     * @param int $modid gradeitem id
     * @param int $scale Scale type either schedule A or B (1/2)
     */
    public static function save_grade_conversion($conversion, $modid = null, $scale = null){
        global $DB;
        if(!is_null($modid) && !is_null($scale)){
            $DB->set_field('grade_items', 'idnumber', $scale, array('id'=>$modid));
        }
        return $DB->insert_records('gcat_grade_converter', $conversion);
    }

    /**
     * Converts all grades in converted | provisional grade
     *
     * @param array $conversion List of grade element conversion
     * @param mixed $module Selected module/activity
     * @param int  $prvid Provisional grade item id
     * @param int $scale scaletype
     */
    public static function convert_provisional_grades($conversion, $module, $prvid, $scale){
        global $COURSE;
        // Get all provisional grade_grades
        if($prvgrades = grade_grade::fetch_all(array('itemid' => $prvid))){
            // Create/get converted grade item
            $convertedgi = local_gugcat::add_grade_item($COURSE->id, get_string('convertedgrade', 'local_gugcat'), $module);
            foreach ($prvgrades as $prvgrade) {
                $grade = null;
                // Get converted grade 
                $cvtgrade = grade_grade::fetch(array('itemid' => $convertedgi, 'userid' => $prvgrade->userid));
                // If converted grade is not null 
                // Else, get the provisional grade
                if($cvtgrade && !is_null($cvtgrade->rawgrade)){
                    $grade = $cvtgrade->rawgrade;
                }else{
                    if(!is_null($prvgrade->rawgrade)){
                        $grade = $prvgrade->rawgrade;
                    }
                }
                // If grade is not null, convert the grade and save it to provisional grade 
                if(!is_null($grade)){
                    $notes = ",_gradeitem: converted ,_scale: $scale";
                    $converted = self::convert($conversion, $grade);
                    // Update converted grade
                    $cvtgrade->finalgrade = $grade;
                    $cvtgrade->rawgrade = $grade;
                    $cvtgrade->timemodified = time();
                    $cvtgrade->update();
                    // Update provisional grade
                    $prvgrade->finalgrade = $converted;
                    $prvgrade->rawgrade = $converted;
                    $prvgrade->timemodified = time();
                    $prvgrade->feedback = $notes;
                    $prvgrade->update();
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
     * Process schedule A or B array on display 
     *
     * @param boolean $defaultscale Boolean if scale is the default one
     * @param array $scale schedule A or B
     * @param array $defaultvalue Default scale values from gcat table
     * @return array $grades 
     */
    public static function process_defaults($defaultscale, $scale, $defaultvalue){
        $grades = array();
        $default = array_column($defaultvalue, 'lowerboundary', 'grade');
        foreach ($scale as $key => $grade) {
            $grd = new stdClass();
            $grd->lowerboundary = $defaultscale && isset($default[$key]) ? $default[$key] : null;
            $grd->grade = $grade;
            $grades[$key] = $grd;
        }
        return $grades;
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
        // If conversion is flat array ([1 => 'A1, ...]) - For schedule B
        if(empty(array_column($conversion, 'lowerboundary'))){
            $convs = array();
            foreach ($conversion as $lower=>$item) { 
                $obj = new stdClass();
                $obj->lowerboundary = $lower;
                $obj->grade = $item;
                $convs[] = $obj;
            }
        }else{
            // Reindex $conversion, so array indexes will start in 0
            $convs = array_values($conversion);
        }

        $convertedgrade = null;
        $grade = round($grade);
        foreach ($convs as $index=>$cobj) { 
            $cobj = (object) $cobj;     
            // Get the upperbound from the preceding element lowerboundary
            $upperbound = (isset($convs[$index-1]) && $precendent = (object)$convs[$index-1])
                ? $precendent->lowerboundary : null;
            // Check if grade is within the range of lower and upper boundary
            if($upperbound && $grade < $upperbound  && $grade >= $cobj->lowerboundary){
                $convertedgrade = $cobj->grade;
                break;
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
            $grade_ = empty($convs) ? $grade : min(array_column($convs, 'grade'));
            $convertedgrade = empty($convs) ? $grade : ($grade_ == 0 ? 0 : intval($grade_)-1);
        }
        return $convertedgrade;
    }

    /**
     * Returns array of grade converter templates 
     *
     * @param int $itemid
     * @return array | false Array of grade elements for conversion
     */
    public static function get_conversion_templates(){
        global $DB, $USER;
        return $DB->get_records('gcat_converter_templates', array('userid'=>$USER->id));
    }

    
    /**
     * Saves new template in gcat_converter_templates table
     * @param string $templatename
     * @param int $scaletype Either schedule A or B (1/2)
     * @return mixed array | false
     */
    public static function save_new_template($templatename, $scaletype){
        global $DB, $USER;
        $data = array(
            'userid' => $USER->id,
            'templatename' => $templatename,
            'scaletype' => $scaletype,
        );
        return $DB->insert_record('gcat_converter_templates', $data);
    }
    
    /**
     * Converts points to percentage and vise versa
     * @param int $maxgrade
     * @param int $grade
     * @param bool $ispercent
     * @return int convertedgrade
     */
    public static function convert_point_percentage($maxgrade, $grade, $ispoint = true){
            return $ispoint ? (($grade / $maxgrade) * 100) : (($grade / 100) * $maxgrade);
    }
}