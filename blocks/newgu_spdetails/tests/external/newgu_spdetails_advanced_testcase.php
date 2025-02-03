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
    protected $mygrades_summative_category;

    /**
     * @var object $mygrades_summative_subcategory
     */
    protected $mygrades_summative_subcategory;

    /**
     * var @object $mygrades_summative_subcategory2
     */
    protected $mygrades_summative_subcategory2;

    /**
     * @var object $mygrades_formativecategory
     */
    protected $mygrades_formative_category;

    /**
     * @var object $gradebookcategory
     */
    protected $gradebook_category;

    /**
     * @var object $attendance_activity
     */
    protected $attendance_activity;

    /**
     * @var object $checklist_activity
     */
    protected $checklist_activity;

    /**
     * @var object $data_activity
     */
    protected $data_activity;

    /**
     * @var object $default_activity
     */
    protected $default_activity;

    /**
     * @var object $forum_activity
     */
    protected $forum_activity;

    /**
     * @var object $game_activity
     */
    protected $game_activity;

    /**
     * @var object $glossary_activity
     */
    protected $glossary_activity;

    /**
     * @var object $h5p_activity
     */
    protected $h5p_activity;

    /**
     * @var object $hsuforum_activity
     */
    protected $hsuforum_activity;

    /**
     * @var object $hvp_activity
     */
    protected $hvp_activity;

    /**
     * @var object $kalvidassign_activity
     */
    protected $kalvidassign_activity;

    /**
     * @var object $lesson_activity
     */
    protected $lesson_activity;

    /**
     * @var object $lti_activity
     */
    protected $lti_activity;

    /**
     * @var object $oublog_activity
     */
    protected $oublog_activity;

    /**
     * @var object $peerwork_activity
     */
    protected $peerwork_activity;

    /**
     * @var object $questionnaire_activity
     */
    protected $questionnaire_activity;

    /**
     * @var object $quiz_activity
     */
    protected $quiz_activity;

    /**
     * @var object $scheduler_activity
     */
    protected $scheduler_activity;

    /**
     * @var object $scorm_activity
     */
    protected $scorm_activity;

    /**
     * @var object $workshop_activity
     */
    protected $workshop_activity;

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

        //  Create the parent grade category for the MyGrades course.
        $mygrades_summative_category = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Summative Assessments',
            'courseid' => $this->mygradescourse->id,
            'aggregation' => 10 // Weighted mean of grades
        ]);

        // Set a weighting for the parent category.
        $record = $DB->get_record("grade_items", ['iteminstance' => $mygrades_summative_category->id]);
        $DB->update_record('grade_items', [
            'id' => $record->id,
            'aggregationcoef' => 1.00000
        ]);

        // Now create the sub categories that live under this parent.
        $mygrades_summative_subcategory = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'October Assessments Aggregated',
            'courseid' => $this->mygradescourse->id,
            'parent' => $mygrades_summative_category->id,
            'aggregation' => 10 // Weighted mean of grades
        ]);

        // Set a weighting for this sub category.
        $record = $DB->get_record("grade_items", ['iteminstance' => $mygrades_summative_subcategory->id]);
        $DB->update_record('grade_items', [
            'id' => $record->id,
            'aggregationcoef' => 0.20000
        ]);

        // This sub category lives under the above grade category - we may or may not use this.
        $mygrades_summative_subcategory2 = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Written Assessments 1 A and B Parts',
            'courseid' => $this->mygradescourse->id,
            'parent' => $mygrades_summative_subcategory->id,
            'aggregation' => 10 // Weighted mean of grades
        ]);

        // This is just an empty category for now.
        $mygrades_formative_category = $this->getDataGenerator()->create_grade_category([
            'fullname' => 'Formative Assessments',
            'courseid' => $this->mygradescourse->id,
        ]);

        // Howard's API adds some additional data.
        $this->mygradescourse->firstlevel[] = [
            'id' => $mygrades_summative_category->id,
            'fullname' => $mygrades_summative_category->fullname,
        ];

        $this->mygrades_summative_category = $mygrades_summative_category;
        $this->mygrades_summative_subcategory = $mygrades_summative_subcategory;
        $this->mygrades_summative_subcategory2 = $mygrades_summative_subcategory2;
        $this->mygrades_formative_category = $mygrades_formative_category;
    }
}
