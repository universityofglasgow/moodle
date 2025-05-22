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
 * Custom class for setting up our course types, gradebook and activities.
 * We should try and represent all the activities that Moodle provides,
 * however, the main focus, for now at least, should be on the activities
 * that are used regularly, namely assignment, quiz, possibly workshop and forum.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2024 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

use externallib_advanced_testcase;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/blocks/newgu_spdetails/tests/external/newgu_spdetails_base_testcase.php');

/**
 * Class containing setUp, activities and other utility methods.
 */
class newgu_spdetails_advanced_testcase extends newgu_spdetails_base_testcase {

    /**
     * @var object $mygradesassignment1
     */
    protected $mygradesassignment1;

    /**
     * @var object $mygradesassignment2
     */
    protected $mygradesassignment2;

    /**
     * @var object $mygradesassignment3
     */
    protected $mygradesassignment3;

    /**
     * @var object $mygradesassignment4
     */
    protected $mygradesassignment4;

    /**
     * @var object $mygradesassignment5
     */
    protected $mygradesassignment45;

    /**
     * @var object $mygradesassignment6
     */
    protected $mygradesassignment6;

    /**
     * @var object $gradebookassignment1
     */
    protected $gradebookassignment1;

    /**
     * @var object $gradebookassignment2
     */
    protected $gradebookassignment2;

    /**
     * @var object $gradebookassignment3
     */
    protected $gradebookassignment3;

    /**
     * @var object $mygrades_summativecategory
     */
    protected $mygradessummativecategory;

    /**
     * @var object $mygradessummativesubcategory
     */
    protected $mygradessummativesubcategory;

    /**
     * @var object $mygradessummativesubcategory2
     */
    protected $mygradessummativesubcategory2;

    /**
     * @var object $mygrades_formativecategory
     */
    protected $mygradesformativecategory;

    /**
     * @var object $gradebookcategory
     */
    protected $gradebookcategory;

    /**
     * @var object $attendanceactivity
     */
    protected $attendanceactivity;

    /**
     * @var object $checklistactivity
     */
    protected $checklistactivity;

    /**
     * @var object $dataactivity
     */
    protected $dataactivity;

    /**
     * @var object $defaultactivity
     */
    protected $defaultactivity;

    /**
     * @var object $forumactivity
     */
    protected $forumactivity;

    /**
     * @var object $gameactivity
     */
    protected $gameactivity;

    /**
     * @var object $glossaryactivity
     */
    protected $glossaryactivity;

    /**
     * @var object $h5pactivity
     */
    protected $h5pactivity;

    /**
     * @var object $hsuforumactivity
     */
    protected $hsuforumactivity;

    /**
     * @var object $hvpactivity
     */
    protected $hvpactivity;

    /**
     * @var object $kalvidassignactivity
     */
    protected $kalvidassignactivity;

    /**
     * @var object $lessonactivity
     */
    protected $lessonactivity;

    /**
     * @var object $ltiactivity
     */
    protected $ltiactivity;

    /**
     * @var object $oublogactivity
     */
    protected $oublogactivity;

    /**
     * @var object $peerworkactivity
     */
    protected $peerworkactivity;

    /**
     * @var object $questionnaireactivity
     */
    protected $questionnaireactivity;

    /**
     * @var object $quizactivity
     */
    protected $quizactivity;

    /**
     * @var object $scheduleractivity
     */
    protected $scheduleractivity;

    /**
     * @var object $scormactivity
     */
    protected $scormactivity;

    /**
     * @var object $workshopactivity
     */
    protected $workshopactivity;

    /**
     * Get gradeitemid
     * @param string $itemtype
     * @param string $itemmodule
     * @param int $iteminstance
     * @return int
     */
    protected function get_grade_item(string $itemtype, string $itemmodule, int $iteminstance) {
        global $DB;

        $params = [
            'iteminstance' => $iteminstance,
        ];
        if ($itemtype) {
            $params['itemtype'] = $itemtype;
        }
        if ($itemmodule) {
            $params['itemmodule'] = $itemmodule;
        }
        $gradeitem = $DB->get_record('grade_items', $params, '*', MUST_EXIST);

        return $gradeitem->id;
    }

    /**
     * Get gugrades grade item
     *
     * @param int $gradeitemid
     * @param string $gradetype
     * @return object
     */
    protected function get_gugrades_grade_item(int $gradeitemid, string $gradetype) {
        global $DB;

        $params = [
            'gradeitemid' => $gradeitemid,
        ];
        if ($gradetype) {
            $params['gradetype'] = $gradetype;
        }
        $gradeitem = $DB->get_record('local_gugrades_grade', $params, '*', MUST_EXIST);

        return $gradeitem;
    }

    /**
     * Add a peerwork grade
     * @param int $peerworkid
     * @param int $studentid
     * @param int $graderid
     * @param float $gradeval
     */
    protected function add_peerwork_grade(int $peerworkid, int $studentid, int $graderid, float $gradeval, int $score) {

        global $DB;

        $grade = new \stdClass();
        $grade->peerworkid = $peerworkid;
        $grade->userid = $studentid;
        $grade->grade = $gradeval;
        $grade->gradedby = $graderid;
        $grade->timecreated = time();
        $grade->timemodified = time();
        $DB->insert_record('peerwork_submission', $grade);

        $grade = new \stdClass();
        $grade->peerworkid = $peerworkid;
        $grade->userid = $studentid;
        $grade->grade = $gradeval;
        $grade->score = $score;
        $DB->insert_record('peerwork_grades', $grade);
    }

    /**
     * Set up our test conditions...
     *
     * All courses now need to be MyGrades "enabled" in order for them to appear in Student MyGrades.
     * Activities are added, "categorised", completed and graded in Gradebook as per usual. It is
     * (normally) at this point that grade information is then imported into MyGrades and processed
     * there. The quirk being that not all activity grades may end up getting imported or processed.
     * Therefore their state will still be considered to be "Gradebook" and will display as per the
     * settings made in Gradebook.
     *
     * @return void
     * @throws dml_exception
     */
    protected function setUp(): void {

        global $DB;

        parent::setUp();
        $this->resetAfterTest(true);

        // Create the parent grade category for the MyGrades course.
        $mygradessummativecategory = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Summative Assessments',
            'courseid' => $this->mygradescourse->id,
            'aggregation' => 10, // Weighted mean of grades.
        ]);

        // Set a weighting for the parent category.
        $record = $DB->get_record("grade_items", ['iteminstance' => $mygradessummativecategory->id]);
        $DB->update_record('grade_items', [
            'id' => $record->id,
            'aggregationcoef' => 1.00000,
        ]);

        // Now create the sub categories that live under this parent.
        $mygradessummativesubcategory = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'October Assessments Aggregated',
            'courseid' => $this->mygradescourse->id,
            'parent' => $mygradessummativecategory->id,
            'aggregation' => 10, // Weighted mean of grades.
        ]);

        // Set a weighting for this sub category.
        $record = $DB->get_record("grade_items", ['iteminstance' => $mygradessummativesubcategory->id]);
        $DB->update_record('grade_items', [
            'id' => $record->id,
            'aggregationcoef' => 0.20000,
        ]);

        // This sub category lives under the above grade category - we may or may not use this.
        $mygradessummativesubcategory2 = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Written Assessments 1 A and B Parts',
            'courseid' => $this->mygradescourse->id,
            'parent' => $mygradessummativesubcategory->id,
            'aggregation' => 10, // Weighted mean of grades.
        ]);

        // This is just an empty category for now.
        $mygradesformativecategory = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Formative Assessments',
            'courseid' => $this->mygradescourse->id,
        ]);

        // Howard's API adds some additional data.
        $this->mygradescourse->firstlevel[] = [
            'id' => $mygradessummativecategory->id,
            'fullname' => $mygradessummativecategory->fullname,
        ];

        $this->mygradessummativecategory = $mygradessummativecategory;
        $this->mygradessummativesubcategory = $mygradessummativesubcategory;
        $this->mygradessummativesubcategory2 = $mygradessummativesubcategory2;
        $this->mygradesformativecategory = $mygradesformativecategory;
    }
}
