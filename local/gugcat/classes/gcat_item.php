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
defined('MOODLE_INTERNAL') || die();
 /**
 * Class representing a gcat item.
 */

class gcat_item{

    /**
     * The student number from student info.
     * @var int $studentno
     */
    public $studentno;

      /**
     * The student number from student info.
     * @var int $idnumber
     */
    public $idnumber;

    /**
     * The surname of the student.
     * @var string $surname
     */
    public $surname;

    /**
     * The forename of the student.
     * @var string $forename
     */
    public $forename;

    /**
     * The intial grade of the student in a specific activity.
     * @var int $firstgrade
     */
    public $firstgrade;

    /**
     * The provisional grade of the student that needs to be approve in a specific activity.
     * @var string $provisionalgrade
     */
    public $provisionalgrade;

    /**
     * An array of grades of the student given by the staff in a specific activity.
     * @var array $grades
     */
    public $grades;

    /**
     * Boolean value if 1st/2nd/3rd grades of the student have discrepancies.
     * @var boolean $discrepancy
     */
    public $discrepancy;
   
    /**
     * Boolean value if grade is -1 === medical exemption.
     * @var boolean $medicalexemption
     */
    public $medicalexemption;

    /**
     * Boolean value if grade is -2 === non submission.
     * @var boolean $nonsubmission
     */
    public $nonsubmission;
    
    /**
     * The string percentage of the accomplish assessment of the student
     *@var string $completed
     */
    public $completed = '0%';
    
    /**
     * The formated aggregated grade of the student, can also be string 'missing grade'
     * @var string $aggregatedgrade
     */
    public $aggregatedgrade;
    
    /**
     * Boolean value if resit is required to the student
     * @var boolean $resit
     */
    public $resit;

     /**
     * Boolean value if resit grade item weight is = 0
     * @var boolean $resitexist
     */
    public $resitexist;
}
