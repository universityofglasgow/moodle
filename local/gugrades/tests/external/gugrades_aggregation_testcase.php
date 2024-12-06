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
 * Custom advanced_testcase which sets up (complex) gradebook schemas and data for
 * aggregation testing
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\external;

use externallib_advanced_testcase;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/gugrades/tests/external/gugrades_base_testcase.php');

/**
 * Test(s) for (both) save_settings and get_settings webservices
 */
class gugrades_aggregation_testcase extends gugrades_base_testcase {

    /**
     * @var array $gradeitems
     */
    protected array $gradeitems;

    /**
     * Process schema json (recursive)
     * $gradeitemid specifies where to put new grade items
     * @param array $items
     * @param int $gradeitemid
     */
    protected function build_schema(array $items, int $gradeitemid) {
        global $DB;

        // Array defines which aggregation type calls which function.
        $lookup = [
            'mean' => \GRADE_AGGREGATE_MEAN,
            'median' => \GRADE_AGGREGATE_MEDIAN,
            'min' => \GRADE_AGGREGATE_MIN,
            'max' => \GRADE_AGGREGATE_MAX,
            'mode' => \GRADE_AGGREGATE_MODE,
            'weighted_mean' => \GRADE_AGGREGATE_WEIGHTED_MEAN,
            'weighted_mean2' => \GRADE_AGGREGATE_WEIGHTED_MEAN2,
            'extracredit_mean' => \GRADE_AGGREGATE_EXTRACREDIT_MEAN,
            'sum' => \GRADE_AGGREGATE_MEAN, // Natural does the same thing as mean.
        ];

        $this->gradeitems = [];

        foreach ($items as $item) {

            // Get weight ('aggregationcoef' in the grade_items table).
            if (isset($item->weight)) {
                $weight = $item->weight;
            } else {
                $weight = 1;
            }

            // Get grademax for points only (default is 100).
            if (isset($item->grademax)) {
                $grademax = $item->grademax;
            } else {
                $grademax = 100;
            }

            // Is it a grade item?
            if (!$item->category) {
                $gradeitem = $this->getDataGenerator()->create_grade_item(
                    ['courseid' => $this->course->id, 'itemname' => $item->name]
                );

                // Default is points.
                $type = empty($item->type) ? "points" : $item->type;

                // Is it a scale (default is points)?
                if ($type == 'schedulea') {
                    $gradeitem->gradetype = GRADE_TYPE_SCALE;
                    $gradeitem->grademax = 23.0;
                    $gradeitem->grademin = 1.0;
                    $gradeitem->scaleid = $this->scale->id;
                    $gradeitem->aggregationcoef = $weight;
                    $DB->update_record('grade_items', $gradeitem);
                } else if ($type == 'scheduleb') {
                    $gradeitem->gradetype = GRADE_TYPE_SCALE;
                    $gradeitem->grademax = 8.0;
                    $gradeitem->grademin = 1.0;
                    $gradeitem->scaleid = $this->scaleb->id;
                    $gradeitem->aggregationcoef = $weight;
                    $DB->update_record('grade_items', $gradeitem);
                } else if ($type == "points") {
                    $gradeitem->gradetype = GRADE_TYPE_VALUE;
                    $gradeitem->grademax = $grademax;
                    $gradeitem->grademin = 0;
                    $gradeitem->aggregationcoef = $weight;
                    $DB->update_record('grade_items', $gradeitem);
                } else {
                    throw new \moodle_exception('JSON contains invalid grade type - ' . $type);
                }
                $this->move_gradeitem_to_category($gradeitem->id, $gradeitemid);
                $this->gradeitems[] = $gradeitem;
            } else {

                // Aggregation? (default is weighted_mean).
                if (!empty($item->aggregation)) {
                    $aggregation = $lookup[$item->aggregation];
                } else {
                    $aggregation = \GRADE_AGGREGATE_WEIGHTED_MEAN;
                }

                // Drop lowest (droplow)?
                if (!empty($item->droplow)) {
                    $droplow = $item->droplow;
                } else {
                    $droplow = 0;
                }

                // In which case it must be a grade category.
                $gradecategory = $this->getDataGenerator()->create_grade_category([
                    'courseid' => $this->course->id,
                    'fullname' => $item->name,
                    'parent' => $gradeitemid,
                    'aggregation' => $aggregation,
                    'droplow' => $droplow,
                ]);

                // Set weight (aggregationcoef).
                $gradeitem = $DB->get_record('grade_items',
                    ['itemtype' => 'category', 'iteminstance' => $gradecategory->id], '*', MUST_EXIST);
                $gradeitem->aggregationcoef = $weight;
                $DB->update_record('grade_items', $gradeitem);

                // Create child items (if present).
                if (!empty($item->children)) {
                    $this->build_schema($item->children, $gradecategory->id);
                }
            }
        }
    }

    /**
     * Import json grades schema
     * Returns array of gradeitemids (probably need to run import and such)
     * @param string $name
     * @return array
     */
    public function load_schema(string $name) {
        global $CFG, $DB;

        $path = $CFG->dirroot . '/local/gugrades/tests/external/gradedata/' . $name . '.json';
        $filecontents = file_get_contents($path);

        $json = json_decode($filecontents);
        $this->build_schema($json, 0);

        // Get gradeitems.
        $gradeitems = $DB->get_records('grade_items', ['itemtype' => 'manual']);
        return array_column($gradeitems, 'id');
    }

    /**
     * Set the aggregation strategy for a gradecategorid
     * @param int $gradecategoryid
     * @param int $aggregation
     */
    public function set_strategy(int $gradecategoryid, int $aggregation): void {
        global $DB;

        $gcat = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);
        $gcat->aggregation = $aggregation;
        $DB->update_record('grade_categories', $gcat);
    }

    /**
     * Write a grade_grade
     * One would think there should be an API for this but I can't find
     * anything that makes sense...
     * @param object $gradeitem
     * @param int $userid
     * @param float|string $rawgrade
     */
    protected function write_grade_grades(object $gradeitem, int $userid, float|string $rawgrade) {
        global $DB;

        // If gradeitem is a scale...
        if ($gradeitem->gradetype == GRADE_TYPE_SCALE) {
            if (!$scale = $DB->get_record('scale', ['id' => $gradeitem->scaleid])) {
                throw new \moodle_exception('Scale not found for id = ' . $gradeitem->scaleid);
            }
            $items = array_map('trim', explode(',', $scale->scale));
            if (($key = array_search($rawgrade, $items)) === false) {
                throw new \exception('Scale item ' . $rawgrade . ' not found in scale');
            }

            // New rawgrade is array key + 1 (scales start at 1, not 0).
            $rawgrade = $key + 1;
        }

        $grade = new \stdClass();
        $grade->itemid = $gradeitem->id;
        $grade->userid = $userid;
        $grade->rawgrade = $rawgrade;
        $grade->finalgrade = $rawgrade;
        $grade->timecreated = time();
        $grade->timemodified = time();
        $grade->information = 'UnitTest grade';
        $grade->informationformat = FORMAT_PLAIN;
        $grade->feedback = 'UnitTest Feedback';
        $grade->feedbackformat = FORMAT_PLAIN;

        $DB->insert_record('grade_grades', $grade);
    }

    /**
     * Get grade category id given name of category
     * @param string $catname
     * @return int
     *
     */
    public function get_grade_category(string $catname) {
        global $DB;

        $gcat = $DB->get_record('grade_categories', ['fullname' => $catname], '*', MUST_EXIST);

        return $gcat->id;
    }

    /**
     * Get gradeitemid for given name of item
     * @param string $itemname
     * @return int
     */
    public function get_gradeitemid(string $itemname) {
        global $DB;

        $item = $DB->get_record('grade_items', ['itemname' => $itemname], '*', MUST_EXIST);

        return $item->id;
    }

    /**
     * Get gradeitemid for a category name
     * @param string $categoryname
     * @return int
     */
    public function get_gradeitemid_for_category(string $categoryname) {
        global $DB;

        $categoryid = $this->get_grade_category($categoryname);
        $item = $DB->get_record('grade_items', ['itemtype' => 'category', 'iteminstance' => $categoryid], '*', MUST_EXIST);

        return $item->id;
    }

    /**
     * Import json data
     * Data refers to item names already uploaded in the schema,
     * so make sure the data matches the schema!
     * Data is imported for a single user
     * @param string $name
     * @param int $userid
     */
    public function load_data(string $name, int $userid): void {
        global $CFG, $DB;

        $path = $CFG->dirroot . '/local/gugrades/tests/external/gradedata/' . $name . '.json';
        $filecontents = file_get_contents($path);

        $json = json_decode($filecontents);

        foreach ($json as $item) {

            // Look up grade item just using name
            // There's only one course, anyway.
            $gradeitem = $DB->get_record('grade_items', ['itemname' => $item->item], '*', MUST_EXIST);
            $this->write_grade_grades($gradeitem, $userid, $item->grade);
        }
    }

    /**
     * Apply admingrade - grade needs to be imported / exist first
     * @param string $gradeitemname
     * @param int $userid
     * @param string $admingrade
     */
    public function apply_admingrade(string $itemname, int $userid, string $admingrade): void {
        global $DB;

        $gradeitem = $item = $DB->get_record('grade_items', ['itemname' => $itemname], '*', MUST_EXIST);
        $nothing = write_additional_grade::execute(
            courseid:       $gradeitem->courseid,
            gradeitemid:    $gradeitem->id,
            userid:         $userid,
            reason:         'AGREED',
            other:          '',
            admingrade:     $admingrade,
            scale:          0,
            grade:          0,
            notes:          'Agreed grade'
        );
        $nothing = external_api::clean_returnvalue(
            write_additional_grade::execute_returns(),
            $nothing
        );
    }

    /**
     * Create default conversion map
     * @return int
     */
    protected function make_conversion_map() {

        // Read map with id 0 (new map) for Schedule A.
        $mapstuff = get_conversion_map::execute($this->course->id, 0, 'schedulea');
        $mapstuff = external_api::clean_returnvalue(
            get_conversion_map::execute_returns(),
            $mapstuff
        );

        // Write map back.
        $name = 'Test conversion map';
        $schedule = 'schedulea';
        $maxgrade = 100.0;
        $map = $mapstuff['map'];
        $mapida = write_conversion_map::execute($this->course->id, 0, $name, $schedule, $maxgrade, $map);
        $mapida = external_api::clean_returnvalue(
            write_conversion_map::execute_returns(),
            $mapida
        );
        $mapida = $mapida['mapid'];

        return $mapida;
    }

    /**
     * Called before every test
     * This adds example GradeBook and activity data for many of the tests.
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();
        $this->resetAfterTest(true);

        $course = $this->course;
        $scale = $this->scale;
        $scaleb = $this->scaleb;
        $student = $this->student;
        $student2 = $this->student2;
        $teacher = $this->teacher;
    }
}
