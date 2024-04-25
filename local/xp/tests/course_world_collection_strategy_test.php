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
 * Course world collection strategy tests.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/base_testcase.php');
require_once(__DIR__ . '/mocks/calculator_mock.php');
require_once(__DIR__ . '/mocks/collection_logger_mock.php');
require_once(__DIR__ . '/mocks/state_store_mock.php');
require_once(__DIR__ . '/mocks/subject_mock.php');

use block_xp\di;
use block_xp\local\notification\course_level_up_notification_service;
use local_xp\local\reason\activity_completion_reason;
use local_xp\local\reason\course_completed_reason;
use local_xp\local\reason\graded_reason;
use local_xp\local\reason\manual_reason;
use local_xp\local\reason\unknown_reason;
use local_xp\local\rule\static_result;
use local_xp\local\strategy\course_world_collection_strategy;
use local_xp\local\strategy\user_collection_target_resolver;

/**
 * Course world collection strategy testcase.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_course_world_collection_strategy_testcase extends local_xp_base_testcase {

    protected function get_world($courseid) {
        $world = di::get('course_world_factory')->get_world($courseid);
        $world->get_config()->set('enabled', 1);
        return $world;
    }

    protected function get_collection_strategy($world) {
        $calculator = new local_xp_calculator_mock();
        $logger = new local_xp_collection_logger_mock();
        $store = new local_xp_state_store_mock();
        $cs = new course_world_collection_strategy(
            $world->get_context(),
            $world->get_config(),
            $store,
            $calculator,
            $logger,
            $logger,
            $logger,
            new course_level_up_notification_service($world->get_courseid()),
            new user_collection_target_resolver(),
            $logger
        );

        $method = new ReflectionMethod($cs, 'collect_for_user');
        $method->setAccessible(true);

        $collectforuser = function() use ($method, $cs) {
            return $method->invokeArgs($cs, func_get_args());
        };

        return compact('collectforuser', 'calculator', 'logger', 'store');
    }

    public function test_collect_basic() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        extract($this->get_collection_strategy($w));

        $this->assertEquals(0, $store->get_state(1)->get_xp());
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(10, $store->get_state(1)->get_xp());
    }

    public function test_collect_disabled() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        extract($this->get_collection_strategy($w));

        $this->assertEquals(0, $store->get_state(1)->get_xp());
        $calculator->result = new static_result(10, true);
        $w->get_config()->set('enabled', false);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(0, $store->get_state(1)->get_xp());
    }

    public function test_collect_action_happened() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        extract($this->get_collection_strategy($w));

        $w->get_config()->set('timebetweensameactions', 10000);

        $this->assertEquals(0, $store->get_state(1)->get_xp());

        $logger->hasreasonhappenedsince = false;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(10, $store->get_state(1)->get_xp());

        $logger->hasreasonhappenedsince = false;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(20, $store->get_state(1)->get_xp());

        // Now flag as already happened.
        $logger->hasreasonhappenedsince = time() - 10;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(20, $store->get_state(1)->get_xp());

        // With cheat guard disabled.
        $w->get_config()->set('enablecheatguard', false);
        $logger->hasreasonhappenedsince = time() - 10;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(30, $store->get_state(1)->get_xp());
    }

    public function test_collect_max_actions_in_time() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        extract($this->get_collection_strategy($w));

        $w->get_config()->set('maxactionspertime', 5);
        $w->get_config()->set('timeformaxactions', 1000);

        $this->assertEquals(0, $store->get_state(1)->get_xp());

        $logger->collectionssince = 0;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(10, $store->get_state(1)->get_xp());

        $logger->collectionssince = 1;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(20, $store->get_state(1)->get_xp());

        // Now flag as happened more than allowed times.
        $logger->collectionssince = 6;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(20, $store->get_state(1)->get_xp());

        // With cheat guard disabled.
        $w->get_config()->set('enablecheatguard', false);
        $logger->collectionssince = 6;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(30, $store->get_state(1)->get_xp());
    }

    public function test_collect_max_points_in_time() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        extract($this->get_collection_strategy($w));

        $w->get_config()->set('maxpointspertime', 20);
        $w->get_config()->set('timeformaxpoints', 1000);

        $this->assertEquals(0, $store->get_state(1)->get_xp());

        $logger->pointscollectedsince = 0;
        $calculator->result = new static_result(5, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(5, $store->get_state(1)->get_xp());

        $logger->pointscollectedsince = 5;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(15, $store->get_state(1)->get_xp());

        // Now flag as points already exceed what's allowed.
        $logger->pointscollectedsince = 21;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(15, $store->get_state(1)->get_xp());

        // Now flag as points will exceed what's allowed.
        $logger->pointscollectedsince = 15;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(15, $store->get_state(1)->get_xp());

        // Now flag as points won't exceed what's allowed.
        $logger->pointscollectedsince = 15;
        $calculator->result = new static_result(1, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(16, $store->get_state(1)->get_xp());

        // With cheat guard disabled.
        $w->get_config()->set('enablecheatguard', false);
        $logger->pointscollectedsince = 100;
        $calculator->result = new static_result(10, true);
        $collectforuser(1, new local_xp_subject_mock(), new unknown_reason());
        $this->assertEquals(26, $store->get_state(1)->get_xp());
    }

    public function test_collect_activity_completion() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        extract($this->get_collection_strategy($w));

        $w->get_config()->set('timebetweensameactions', 10000);
        $w->get_config()->set('maxactionspertime', 999);
        $w->get_config()->set('timeformaxactions', 999);
        $w->get_config()->set('maxpointspertime', 999);
        $w->get_config()->set('timeformaxpoints', 999);

        $subject = new local_xp_subject_mock();
        $reason = new activity_completion_reason(1, 1);
        $calculator->result = new static_result(10, true);
        $logger->pointscollectedwithreasonsince = 10000;   // Set to show it's got no effect.

        $this->assertEquals(0, $store->get_state(1)->get_xp());

        $collectforuser(1, $subject, $reason);
        $this->assertEquals(10, $store->get_state(1)->get_xp());

        // Skipped when already happened recently.
        $logger->hasreasonhappenedsince = time() - 10;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(10, $store->get_state(1)->get_xp());
        $logger->hasreasonhappenedsince = false;

        // Skipped when already happened, since is the dawn of time.
        $logger->hasreasonhappenedsince = 0;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(10, $store->get_state(1)->get_xp());
        $logger->hasreasonhappenedsince = false;

        // Record points when collections allowed not already maxed out.
        $logger->collectionssince = 998;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(20, $store->get_state(1)->get_xp());

        // Not skipped when collections allowed already maxed out.
        $logger->collectionssince = 1000;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(30, $store->get_state(1)->get_xp());
        $logger->collectionssince = 0;

        // Records points when points allowed already (or about to) max out.
        $logger->pointscollectedsince = 900;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(40, $store->get_state(1)->get_xp());

        // Not skipped when points allowed already maxed out.
        $logger->pointscollectedsince = 1000;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(50, $store->get_state(1)->get_xp());
    }

    public function test_collect_course_completed() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        extract($this->get_collection_strategy($w));

        $w->get_config()->set('timebetweensameactions', 10000);
        $w->get_config()->set('maxactionspertime', 999);
        $w->get_config()->set('timeformaxactions', 999);
        $w->get_config()->set('maxpointspertime', 999);
        $w->get_config()->set('timeformaxpoints', 999);

        $subject = new local_xp_subject_mock();
        $reason = new course_completed_reason(1);
        $calculator->result = new static_result(10, true);
        $logger->pointscollectedwithreasonsince = 10000;   // Set to show it's got no effect.

        $this->assertEquals(0, $store->get_state(1)->get_xp());

        $collectforuser(1, $subject, $reason);
        $this->assertEquals(10, $store->get_state(1)->get_xp());

        // Not skipped when already happened a very long time ago.
        // Arguably, we let that happen because it's out of the student's control.
        $logger->hasreasonhappenedsince = 0;
        $calculator->result = new static_result(1, true);
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(11, $store->get_state(1)->get_xp());
        $logger->hasreasonhappenedsince = false;

        // Not skipped when already happened recently.
        // Arguably, we let that happen because it's out of the student's control.
        $logger->hasreasonhappenedsince = time() - 10;
        $calculator->result = new static_result(1, true);
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(12, $store->get_state(1)->get_xp());
        $logger->hasreasonhappenedsince = false;

        // Record points when collections allowed not already maxed out.
        $calculator->result = new static_result(8, true);
        $logger->collectionssince = 998;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(20, $store->get_state(1)->get_xp());

        // Not skipped when collections allowed already maxed out.
        $calculator->result = new static_result(2, true);
        $logger->collectionssince = 1000;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(22, $store->get_state(1)->get_xp());
        $logger->collectionssince = 0;

        // Records points when points allowed already (or about to) max out.
        $calculator->result = new static_result(8, true);
        $logger->pointscollectedsince = 900;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(30, $store->get_state(1)->get_xp());

        // Skipped when points allowed already maxed out.
        $logger->pointscollectedsince = 1000;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(38, $store->get_state(1)->get_xp());
    }

    public function test_collect_graded() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        extract($this->get_collection_strategy($w));

        $w->get_config()->set('timebetweensameactions', 999);
        $w->get_config()->set('maxactionspertime', 999);
        $w->get_config()->set('timeformaxactions', 999);
        $w->get_config()->set('maxpointspertime', 999);
        $w->get_config()->set('timeformaxpoints', 999);

        $subject = new local_xp_subject_mock();
        $reason = new graded_reason(1, 1);
        $calculator->result = new static_result(10, true);

        $this->assertEquals(0, $store->get_state(1)->get_xp());

        $collectforuser(1, $subject, $reason);
        $this->assertEquals(10, $store->get_state(1)->get_xp());

        // Only rewards the difference from next result.
        $logger->pointscollectedwithreasonsince = 10;
        $calculator->result = new static_result(15, true);
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(15, $store->get_state(1)->get_xp());

        // Not skipped when already happened.
        $logger->pointscollectedwithreasonsince = 15;
        $calculator->result = new static_result(20, true);
        $logger->hasreasonhappenedsince = time() - 10;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(20, $store->get_state(1)->get_xp());
        $logger->hasreasonhappenedsince = false;

        // Record points when collections allowed not already maxed out.
        $logger->pointscollectedwithreasonsince = 20;
        $calculator->result = new static_result(25, true);
        $logger->collectionssince = 998;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(25, $store->get_state(1)->get_xp());

        // Not skipped when collections allowed already maxed out.
        $logger->pointscollectedwithreasonsince = 25;
        $calculator->result = new static_result(30, true);
        $logger->collectionssince = 1000;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(30, $store->get_state(1)->get_xp());
        $logger->collectionssince = 0;

        // Records points when points allowed already (or about to) max out.
        $logger->pointscollectedwithreasonsince = 30;
        $calculator->result = new static_result(50, true);
        $logger->pointscollectedsince = 900;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(50, $store->get_state(1)->get_xp());

        // Not skipped when points allowed already maxed out.
        $logger->pointscollectedwithreasonsince = 50;
        $calculator->result = new static_result(87, true);
        $logger->pointscollectedsince = 1000;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(87, $store->get_state(1)->get_xp());

        // Skipped when points collected already higher, grades are capped.
        $logger->pointscollectedwithreasonsince = 1000;
        $calculator->result = new static_result(999, true);
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(87, $store->get_state(1)->get_xp());
    }

    public function test_collect_manual() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        extract($this->get_collection_strategy($w));

        $w->get_config()->set('timebetweensameactions', 999);
        $w->get_config()->set('maxactionspertime', 999);
        $w->get_config()->set('timeformaxactions', 999);
        $w->get_config()->set('maxpointspertime', 999);
        $w->get_config()->set('timeformaxpoints', 999);

        $subject = new local_xp_subject_mock();
        $reason = new manual_reason(1);
        $calculator->result = new static_result(10, true);

        $this->assertEquals(0, $store->get_state(1)->get_xp());

        $collectforuser(1, $subject, $reason);
        $this->assertEquals(10, $store->get_state(1)->get_xp());

        // Not skipped when already happened.
        $logger->hasreasonhappenedsince = time() - 10;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(20, $store->get_state(1)->get_xp());
        $logger->hasreasonhappenedsince = false;

        // Record points when collections allowed not already maxed out.
        $logger->collectionssince = 998;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(30, $store->get_state(1)->get_xp());

        // Not skipped when collections allowed already maxed out.
        $logger->collectionssince = 1000;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(40, $store->get_state(1)->get_xp());
        $logger->collectionssince = 0;

        // Records points when points allowed already (or about to) max out.
        $logger->pointscollectedsince = 900;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(50, $store->get_state(1)->get_xp());

        // Not skipped when points allowed already maxed out.
        $logger->pointscollectedsince = 1000;
        $collectforuser(1, $subject, $reason);
        $this->assertEquals(60, $store->get_state(1)->get_xp());
    }

}
