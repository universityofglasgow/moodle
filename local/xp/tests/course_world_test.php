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
 * Course world test.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once(__DIR__ . '/base_testcase.php');
require_once($CFG->dirroot . '/blocks/xp/tests/fixtures/events.php');

/**
 * Course world testcase.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_course_world_testcase extends local_xp_base_testcase {

    public function test_delete_user_state() {
        global $DB;

        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $u2 = $this->getDataGenerator()->create_user();
        $c1ctxid = context_course::instance($c1->id)->id;
        $c2ctxid = context_course::instance($c2->id)->id;

        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);
        $this->getDataGenerator()->enrol_user($u2->id, $c1->id);
        $this->getDataGenerator()->enrol_user($u1->id, $c2->id);

        $world = $this->get_world($c1->id);
        $world->get_config()->set_many(['enabled' => true, 'timebetweensameactions' => 0]);
        $strategy = $world->get_collection_strategy();

        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'edulevel' => \core\event\base::LEVEL_PARTICIPATING,
            'userid' => $u1->id, 'courseid' => $c1->id]);
        $strategy->collect_event($e);
        $strategy->collect_event($e);

        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'edulevel' => \core\event\base::LEVEL_PARTICIPATING,
            'userid' => $u2->id, 'courseid' => $c1->id]);
        $strategy->collect_event($e);
        $strategy->collect_event($e);

        $world = $this->get_world($c2->id);
        $world->get_config()->set_many(['enabled' => true, 'timebetweensameactions' => 0]);
        $strategy = $world->get_collection_strategy();

        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'edulevel' => \core\event\base::LEVEL_PARTICIPATING,
            'userid' => $u1->id, 'courseid' => $c2->id]);
        $strategy->collect_event($e);

        $world = $this->get_world($c1->id);

        $this->assertGreaterThan(0, $world->get_store()->get_state($u1->id)->get_xp());
        $this->assertGreaterThan(0, $world->get_store()->get_state($u2->id)->get_xp());
        $this->assertEquals(1, $DB->count_records('block_xp', ['courseid' => $c1->id, 'userid' => $u1->id]));
        $this->assertEquals(1, $DB->count_records('block_xp', ['courseid' => $c1->id, 'userid' => $u2->id]));
        $this->assertEquals(2, $DB->count_records('local_xp_log', ['contextid' => $c1ctxid, 'userid' => $u1->id]));
        $this->assertEquals(2, $DB->count_records('local_xp_log', ['contextid' => $c1ctxid, 'userid' => $u2->id]));
        $this->assertEquals(1, $DB->count_records('block_xp', ['courseid' => $c2->id]));
        $this->assertEquals(1, $DB->count_records('local_xp_log', ['contextid' => $c2ctxid]));

        $world->get_store()->delete($u1->id);

        $this->assertEquals(0, $world->get_store()->get_state($u1->id)->get_xp());
        $this->assertGreaterThan(0, $world->get_store()->get_state($u2->id)->get_xp());
        $this->assertEquals(0, $DB->count_records('block_xp', ['courseid' => $c1->id, 'userid' => $u1->id]));
        $this->assertEquals(1, $DB->count_records('block_xp', ['courseid' => $c1->id, 'userid' => $u2->id]));
        $this->assertEquals(0, $DB->count_records('local_xp_log', ['contextid' => $c1ctxid, 'userid' => $u1->id]));
        $this->assertEquals(2, $DB->count_records('local_xp_log', ['contextid' => $c1ctxid, 'userid' => $u2->id]));
        $this->assertEquals(1, $DB->count_records('block_xp', ['courseid' => $c2->id]));
        $this->assertEquals(1, $DB->count_records('local_xp_log', ['contextid' => $c2ctxid]));
    }

}
