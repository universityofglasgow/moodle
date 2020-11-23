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
defined('MOODLE_INTERNAL') || die();
 /**
 * Class representing a grade capture item.
 */

class grade_capture_item{

    /**
     * The id of the user/ student.
     * @var int $id
     */
    public $id;

    /**
     * The student number from student info.
     * @var int $studentno
     */
    public $studentno;

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
     * The second grade of the student in a specific activity.
     * @var string $secondgrade
     */
    public $secondgrade;

    /**
     * The third grade of the student in a specific activity.
     * @var string $thirdgrade
     */
    public $thirdgrade;

    /**
     * The good cause grade of the student in a specific activity.
     * @var string $goodcausegrade
     */
    public $goodcausegrade;

    /**
     * The late penalty grade of the student in a specific activity.
     * @var string $latepenaltygrade
     */
    public $latepenaltygrade;

    /**
     * The capped grade of the student in a specific activity.
     * @var string $cappedgrade
     */
    public $cappedgrade;

    /**
     * The agreed grade of the student in a specific activity.
     * @var string $agreedgrade
     */
    public $agreedgrade;

    /**
     * The moderate grade of the student in a specific activity.
     * @var string $moderategrade
     */
    public $moderategrade;

    /**
     * The custom grade of the student given by the staff in a specific activity.
     * @var array $othergrade
     */
    public $othergrade;

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
}