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
 * Test privacy provider.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/base_testcase.php');
require_once($CFG->dirroot . '/blocks/xp/tests/fixtures/events.php');

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use block_xp\di;
use block_xp\local\config\course_world_config;
use local_xp\privacy\provider;

/**
 * Privacy provider testcase.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_privacy_addon_provider_testcase extends local_xp_base_testcase {

    public function setUp() {
        global $CFG;
        if (!class_exists('core_privacy\manager')) {
            $this->markTestSkipped('Moodle versions does not support privacy subsystem.');
        }
        parent::setUp();
    }

    protected function get_world($courseid) {
        $world = di::get('course_world_factory')->get_world($courseid);
        $world->get_config()->set('enabled', 1);
        $world->get_config()->set('enablecheatguard', 0);
        $world->get_config()->set('defaultfilters', course_world_config::DEFAULT_FILTERS_MISSING);
        return $world;
    }

    public function test_get_metadata() {
        $data = provider::get_metadata(new collection('block_xp'));
        $this->assertCount(1, $data->get_collection());
    }

    public function test_add_addon_context_for_userid() {
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();
        $c2 = $dg->create_course();
        $c3 = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();

        $world = $this->get_world($c1->id);
        $strategy = $world->get_collection_strategy();
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id,
            'courseid' => $c1->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);

        $world = $this->get_world($c2->id);
        $strategy = $world->get_collection_strategy();
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u2->id,
            'courseid' => $c2->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);

        $world = $this->get_world($c3->id);
        $strategy = $world->get_collection_strategy();
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id,
            'courseid' => $c3->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u2->id,
            'courseid' => $c3->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);

        $contextlist = new contextlist();
        $data = provider::add_addon_contexts_for_userid($contextlist, $u1->id);
        $this->assert_contextlist_equals($contextlist, [
            context_course::instance($c1->id)->id,
            context_course::instance($c3->id)->id,
        ]);

        $contextlist = new contextlist();
        $data = provider::add_addon_contexts_for_userid($contextlist, $u2->id);
        $this->assert_contextlist_equals($contextlist, [
            context_course::instance($c2->id)->id,
            context_course::instance($c3->id)->id,
        ]);
    }

    public function test_export_addon_user_data() {
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();
        $c2 = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();

        $world = $this->get_world($c1->id);
        $strategy = $world->get_collection_strategy();
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id,
            'courseid' => $c1->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id,
            'courseid' => $c1->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id,
            'courseid' => $c1->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u2->id,
            'courseid' => $c1->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);

        $world = $this->get_world($c2->id);
        $strategy = $world->get_collection_strategy();
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id,
            'courseid' => $c2->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u2->id,
            'courseid' => $c2->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);

        $contextlist = new approved_contextlist($u1, 'block_xp', [context_course::instance($c1->id)->id]);
        provider::export_addon_user_data(['root', 'path'], $contextlist);

        $writer = writer::with_context(context_course::instance($c1->id));
        $logs = $writer->get_data(['root', 'path', get_string('privacy:path:logs', 'block_xp')]);

        $this->assertNotEmpty($logs);
        $this->assertCount(3, $logs->data);
        foreach ($logs->data as $log) {
            $this->assertEquals('local_xp\local\reason\event_reason', $log->type);
            $this->assertSame(0, strpos($log->signature, '\block_xp\event\something_happened'));
            $this->assertEquals(45, $log->points);
            $this->assertEquals($u1->id, $log->userid);
        }
    }

    public function test_delete_addon_data_for_all_users_in_context() {
        $db = di::get('db');
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();
        $c2 = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

        $world = $this->get_world($c1->id);
        $strategy = $world->get_collection_strategy();
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id,
            'courseid' => $c1->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u2->id,
            'courseid' => $c1->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);

        $world = $this->get_world($c2->id);
        $strategy = $world->get_collection_strategy();
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id,
            'courseid' => $c2->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u2->id,
            'courseid' => $c2->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);

        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c1ctx->id, 'userid' => $u1->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c1ctx->id, 'userid' => $u2->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c2ctx->id, 'userid' => $u1->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c2ctx->id, 'userid' => $u2->id]));

        provider::delete_addon_data_for_all_users_in_context($c1ctx);

        $this->assertFalse($db->record_exists('local_xp_log', ['contextid' => $c1ctx->id, 'userid' => $u1->id]));
        $this->assertFalse($db->record_exists('local_xp_log', ['contextid' => $c1ctx->id, 'userid' => $u2->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c2ctx->id, 'userid' => $u1->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c2ctx->id, 'userid' => $u2->id]));
    }

    public function test_delete_addon_data_user() {
        $db = di::get('db');
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();
        $c2 = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

        $world = $this->get_world($c1->id);
        $strategy = $world->get_collection_strategy();
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id,
            'courseid' => $c1->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u2->id,
            'courseid' => $c1->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);

        $world = $this->get_world($c2->id);
        $strategy = $world->get_collection_strategy();
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id,
            'courseid' => $c2->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);
        $e = \block_xp\event\something_happened::mock(['crud' => 'c', 'userid' => $u2->id,
            'courseid' => $c2->id, 'edulevel' => \core\event\base::LEVEL_PARTICIPATING]);
        $strategy->collect_event($e);

        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c1ctx->id, 'userid' => $u1->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c1ctx->id, 'userid' => $u2->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c2ctx->id, 'userid' => $u1->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c2ctx->id, 'userid' => $u2->id]));

        $contextlist = new approved_contextlist($u1, 'block_xp', [$c1ctx->id]);
        provider::delete_addon_data_for_user($contextlist);

        $this->assertFalse($db->record_exists('local_xp_log', ['contextid' => $c1ctx->id, 'userid' => $u1->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c1ctx->id, 'userid' => $u2->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c2ctx->id, 'userid' => $u1->id]));
        $this->assertTrue($db->record_exists('local_xp_log', ['contextid' => $c2ctx->id, 'userid' => $u2->id]));
    }

    protected function assert_contextlist_equals($contextlist, $expectedids) {
        $contextids = array_map('intval', $contextlist->get_contextids());
        sort($contextids);
        sort($expectedids);
        $this->assertEquals($expectedids, $contextids);
    }
}
