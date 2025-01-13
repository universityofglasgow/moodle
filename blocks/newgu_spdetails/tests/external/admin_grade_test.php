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
 * Test of the language string settings.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024
 * @author     Greg Pedder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/newgu_spdetails/tests/external/newgu_spdetails_advanced_testcase.php');

/**
 * Unit tests for admin grades.
 */

class get_admin_grade_test extends \block_newgu_spdetails\external\newgu_spdetails_advanced_testcase {

    /**
     * Test of the general admin grades that can be returned.
    */
    public function test_return_admingrade() {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));

        $mygradesassignment = $this->getDataGenerator()->create_module('assign', [
            'name' => 'Admin grade test assignment',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygrades_summative_subcategory->id,
            $mygradesassignment->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries
        $this->add_assignment_grade($mygradesassignment->id, $this->student1->id, $this->teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        $mygradesgradeitemid = $this->get_grade_item('', 'assign', $mygradesassignment->id);
        $DB->insert_record('grade_grades', [
            'itemid' => $mygradesgradeitemid,
            'userid' => $this->student1->id,
            'finalgrade' => 13,
        ]);

        // Give the activity an admin grade, and mark as RELEASED...
        $now  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $mygradesgradeitemid,
            'userid' => $this->student1->id,
            'admingrade' => '07',
            'displaygrade' => '07',
            'gradetype' => 'RELEASED',
            'columnid' => 0,
            'iscurrent' => 1,
            'auditby' => 0,
            'audittimecreated' => $now,
        ]);

        $admingrade = $this->get_gugrades_grade_item($mygradesgradeitemid, '');
        $expected = get_string('admin07', 'local_gugrades');

        $this->assertEquals($expected, $this->gradeapi->is_admin_or_generic_grade($admingrade->admingrade, $admingrade->displaygrade));
    }

    /**
     * MGU-1202 - Test that the student sees either Good Cause (non replicable) for MV0 or Good Cause (further opportunity) for MV.
     */
    public function test_return_MV0_admingrade() {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));

        $mygradesassignment = $this->getDataGenerator()->create_module('assign', [
            'name' => 'Admin grade test assignment',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygrades_summative_subcategory->id,
            $mygradesassignment->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries
        $this->add_assignment_grade($mygradesassignment->id, $this->student1->id, $this->teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        $mygradesgradeitemid = $this->get_grade_item('', 'assign', $mygradesassignment->id);
        $DB->insert_record('grade_grades', [
            'itemid' => $mygradesgradeitemid,
            'userid' => $this->student1->id,
            'finalgrade' => 13,
        ]);

        // Give the activity an admin grade, and mark as RELEASED...
        $now  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $mygradesgradeitemid,
            'userid' => $this->student1->id,
            'admingrade' => 'MV',
            'displaygrade' => 'MV',
            'gradetype' => 'RELEASED',
            'columnid' => 0,
            'iscurrent' => 1,
            'auditby' => 0,
            'audittimecreated' => $now,
        ]);

        $admingrade = $this->get_gugrades_grade_item($mygradesgradeitemid, '');
        $expected = get_string('adminmv', 'local_gugrades');

        $this->assertEquals($expected, $this->gradeapi->is_admin_or_generic_grade($admingrade->admingrade, $admingrade->displaygrade));

        // Update the record to MV0
        $params = [
            'admingrade' => 'MV0',
            'displaygrade' => 'MV0',
            'id' => $admingrade->id,
        ];
        $DB->execute("UPDATE {local_gugrades_grade} SET admingrade = ?, displaygrade = ? WHERE id = ?", $params);

        $admingrade = $this->get_gugrades_grade_item($mygradesgradeitemid, '');
        $expected = get_string('adminmv0', 'local_gugrades');
        $this->assertEquals($expected, $this->gradeapi->is_admin_or_generic_grade($admingrade->admingrade, $admingrade->displaygrade));
    }

    /**
     * MGU-1203 - Test that student sees 'Non Submission' - regardless of the grade being NS or NS0.
     */
    public function test_return_NS0_admingrade() {
        global $DB;

        // We're the test student.
        $this->setUser($this->student1->id);

        // Fake some due dates.
        $duedate = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));

        $mygradesassignment = $this->getDataGenerator()->create_module('assign', [
            'name' => 'Admin grade test assignment',
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'course' => $this->mygradescourse->id,
            'duedate' => $duedate,
            'gradetype' => 2,
            'grademax' => 50,
            'scaleid' => $this->scale->id,
        ]);

        // Create_module gives us stuff for free, however, it doesn't set the categoryid correctly.
        $params = [
            $this->mygrades_summative_subcategory->id,
            $mygradesassignment->id,
        ];
        $DB->execute("UPDATE {grade_items} SET categoryid = ? WHERE iteminstance = ?", $params);

        // Create_module also doesn't allow us to set an assignment plugin, which we check for in the main class.
        // Fake that we are allowing submissions.
        $params = [
            'nosubmissions' => 0,
            'id' => $mygradesassignment->id,
        ];
        $DB->execute("UPDATE {assign} SET nosubmissions = ? WHERE id = ?", $params);

        // Create the assignment submission entries
        $this->add_assignment_grade($mygradesassignment->id, $this->student1->id, $this->teacher->id, 40,
        ASSIGN_SUBMISSION_STATUS_SUBMITTED);

        $mygradesgradeitemid = $this->get_grade_item('', 'assign', $mygradesassignment->id);
        $DB->insert_record('grade_grades', [
            'itemid' => $mygradesgradeitemid,
            'userid' => $this->student1->id,
            'finalgrade' => 13,
        ]);

        // Give the activity an admin grade, and mark as RELEASED...
        $now  = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $DB->insert_record('local_gugrades_grade', [
            'courseid' => $this->mygradescourse->id,
            'gradeitemid' => $mygradesgradeitemid,
            'userid' => $this->student1->id,
            'admingrade' => 'NS',
            'displaygrade' => 'NS',
            'gradetype' => 'RELEASED',
            'columnid' => 0,
            'iscurrent' => 1,
            'auditby' => 0,
            'audittimecreated' => $now,
        ]);

        $admingrade = $this->get_gugrades_grade_item($mygradesgradeitemid, '');
        $expected = get_string('adminns', 'local_gugrades');

        $this->assertEquals($expected, $this->gradeapi->is_admin_or_generic_grade($admingrade->admingrade, $admingrade->displaygrade));

        // Update the record to MV0
        $params = [
            'admingrade' => 'NS0',
            'displaygrade' => 'NS0',
            'id' => $admingrade->id,
        ];
        $DB->execute("UPDATE {local_gugrades_grade} SET admingrade = ?, displaygrade = ? WHERE id = ?", $params);

        $admingrade = $this->get_gugrades_grade_item($mygradesgradeitemid, '');
        $expected = get_string('adminns', 'local_gugrades');
        $this->assertEquals($expected, $this->gradeapi->is_admin_or_generic_grade($admingrade->admingrade, $admingrade->displaygrade));
    }

}
