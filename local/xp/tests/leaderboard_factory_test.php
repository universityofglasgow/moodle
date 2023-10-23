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
 * Leaderboard factory tests.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/base_testcase.php');

use block_xp\di;
use block_xp\local\config\config_stack;
use block_xp\local\config\static_config;
use block_xp\local\sql\limit;
use local_xp\local\config\default_course_world_config;

/**
 * Leaderboard factory testcase.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_leaderboard_factory_testcase extends local_xp_base_testcase {

    protected function get_world($courseid) {
        return di::get('course_world_factory')->get_world($courseid);
    }

    /**
     * Test the plain factory.
     */
    public function test_plain_factory_without_groups() {
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();

        $world = $this->get_world($c1->id);
        $store = $world->get_store();
        $store->set($u1->id, 100);
        $store->set($u2->id, 120);
        $store->set($u3->id, 130);
        $store->set($u4->id, 140);

        $factory = di::get('course_world_leaderboard_factory');
        $lb = $factory->get_course_leaderboard($world);
        $ranking = $lb->get_ranking(new limit(0, 0));
        $ranking = !is_array($ranking) ? array_values(iterator_to_array($ranking)) : $ranking;

        $this->assertEquals(4, $lb->get_count());
        $this->assert_ranking($ranking, [
            [$u4, 1],
            [$u3, 2],
            [$u2, 3],
            [$u1, 4],
        ]);
        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(20, $ranking[0]->get_state()->get_xp_in_level());
        $this->assertEquals(156, $ranking[0]->get_state()->get_total_xp_in_level());

        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(10, $ranking[1]->get_state()->get_xp_in_level());
        $this->assertEquals(156, $ranking[1]->get_state()->get_total_xp_in_level());

        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(0, $ranking[2]->get_state()->get_xp_in_level());
        $this->assertEquals(156, $ranking[2]->get_state()->get_total_xp_in_level());

        $this->assertEquals(1, $ranking[3]->get_state()->get_level()->get_level());
        $this->assertEquals(100, $ranking[3]->get_state()->get_xp_in_level());
        $this->assertEquals(120, $ranking[3]->get_state()->get_total_xp_in_level());

        $world->get_config()->set('progressbarmode', default_course_world_config::PROGRESS_BAR_MODE_OVERALL);
        $lb = $factory->get_course_leaderboard($world);
        $ranking = $lb->get_ranking(new limit(0, 0));
        $ranking = !is_array($ranking) ? array_values(iterator_to_array($ranking)) : $ranking;
        $maxxp = $world->get_levels_info()->get_level($world->get_levels_info()->get_count())->get_xp_required();

        $this->assertEquals(4, $lb->get_count());
        $this->assert_ranking($ranking, [
            [$u4, 1],
            [$u3, 2],
            [$u2, 3],
            [$u1, 4],
        ]);
        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(140, $ranking[0]->get_state()->get_xp_in_level());
        $this->assertEquals($maxxp, $ranking[0]->get_state()->get_total_xp_in_level());

        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(130, $ranking[1]->get_state()->get_xp_in_level());
        $this->assertEquals($maxxp, $ranking[1]->get_state()->get_total_xp_in_level());

        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(120, $ranking[2]->get_state()->get_xp_in_level());
        $this->assertEquals($maxxp, $ranking[2]->get_state()->get_total_xp_in_level());

        $this->assertEquals(1, $ranking[3]->get_state()->get_level()->get_level());
        $this->assertEquals(100, $ranking[3]->get_state()->get_xp_in_level());
        $this->assertEquals($maxxp, $ranking[3]->get_state()->get_total_xp_in_level());
    }

    /**
     * Test the config factory.
     */
    public function test_config_factory_without_groups() {
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();

        $world = $this->get_world($c1->id);
        $store = $world->get_store();
        $store->set($u1->id, 100);
        $store->set($u2->id, 120);
        $store->set($u3->id, 130);
        $store->set($u4->id, 140);

        $factory = di::get('course_world_leaderboard_factory_with_config');
        $config = new config_stack([new static_config([]), $world->get_config()]);
        $lb = $factory->get_course_leaderboard_with_config($world, $config);
        $ranking = $lb->get_ranking(new limit(0, 0));
        $ranking = !is_array($ranking) ? array_values(iterator_to_array($ranking)) : $ranking;

        $this->assertEquals(4, $lb->get_count());
        $this->assert_ranking($ranking, [
            [$u4, 1],
            [$u3, 2],
            [$u2, 3],
            [$u1, 4],
        ]);
        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(20, $ranking[0]->get_state()->get_xp_in_level());
        $this->assertEquals(156, $ranking[0]->get_state()->get_total_xp_in_level());

        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(10, $ranking[1]->get_state()->get_xp_in_level());
        $this->assertEquals(156, $ranking[1]->get_state()->get_total_xp_in_level());

        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(0, $ranking[2]->get_state()->get_xp_in_level());
        $this->assertEquals(156, $ranking[2]->get_state()->get_total_xp_in_level());

        $this->assertEquals(1, $ranking[3]->get_state()->get_level()->get_level());
        $this->assertEquals(100, $ranking[3]->get_state()->get_xp_in_level());
        $this->assertEquals(120, $ranking[3]->get_state()->get_total_xp_in_level());

        $config = new config_stack([new static_config([
            'progressbarmode' => default_course_world_config::PROGRESS_BAR_MODE_OVERALL
        ]), $world->get_config()]);
        $lb = $factory->get_course_leaderboard_with_config($world, $config);
        $ranking = $lb->get_ranking(new limit(0, 0));
        $ranking = !is_array($ranking) ? array_values(iterator_to_array($ranking)) : $ranking;
        $maxxp = $world->get_levels_info()->get_level($world->get_levels_info()->get_count())->get_xp_required();

        $this->assertEquals(4, $lb->get_count());
        $this->assert_ranking($ranking, [
            [$u4, 1],
            [$u3, 2],
            [$u2, 3],
            [$u1, 4],
        ]);
        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(140, $ranking[0]->get_state()->get_xp_in_level());
        $this->assertEquals($maxxp, $ranking[0]->get_state()->get_total_xp_in_level());

        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(130, $ranking[1]->get_state()->get_xp_in_level());
        $this->assertEquals($maxxp, $ranking[1]->get_state()->get_total_xp_in_level());

        $this->assertEquals(2, $ranking[0]->get_state()->get_level()->get_level());
        $this->assertEquals(120, $ranking[2]->get_state()->get_xp_in_level());
        $this->assertEquals($maxxp, $ranking[2]->get_state()->get_total_xp_in_level());

        $this->assertEquals(1, $ranking[3]->get_state()->get_level()->get_level());
        $this->assertEquals(100, $ranking[3]->get_state()->get_xp_in_level());
        $this->assertEquals($maxxp, $ranking[3]->get_state()->get_total_xp_in_level());
    }

    protected function assert_ranking($ranking, array $expected) {
        $i = 0;
        foreach ($ranking as $rank) {
            $this->assertEquals($expected[$i][0]->id, $rank->get_state()->get_id(), $i);
            $this->assertEquals($expected[$i][1], $rank->get_rank(), $i);
            $i++;
        }
        $this->assertEquals($i, count($expected));
    }

}
