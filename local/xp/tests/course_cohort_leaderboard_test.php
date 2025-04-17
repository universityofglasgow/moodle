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
 * Group leaderboard tests.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp;
defined('MOODLE_INTERNAL') || die();

global $CFG;

use block_xp\di;
use block_xp\local\sql\limit;
use local_xp\local\config\default_course_world_config;
use local_xp\local\leaderboard\cohort_leaderboard;
use local_xp\tests\base_testcase;

/**
 * Group leaderboard testcase.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_xp\local\leaderboard\cohort_leaderboard
 */
final class course_cohort_leaderboard_test extends base_testcase {

    protected function get_world($courseid) {
        $world = di::get('course_world_factory')->get_world($courseid);
        $world->get_config()->set('enabled', 1);
        $world->get_config()->set('enablegroupladder', default_course_world_config::GROUP_LADDER_COHORTS);
        return $world;
    }

    public function test_leaderboard_with_no_cohorts(): void {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        $l = new cohort_leaderboard(di::get('db'), $c->id, [], ['xp' => 'XP'], $w->get_levels_info());
        $this->assertCount(0, $l->get_ranking(new limit(100)));
        $this->assertSame(null, $l->get_rank(1));
        $this->assertSame(null, $l->get_position(1));
        $this->assertSame(0, $l->get_count());
    }

    public function test_leaderboard_with_cohorts_but_no_scores(): void {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $g1 = $dg->create_cohort();
        $g2 = $dg->create_cohort();
        cohort_add_member($g1->id, $u1->id);
        cohort_add_member($g1->id, $u2->id);
        cohort_add_member($g2->id, $u3->id);

        $w = $this->get_world($c->id);
        $l = new cohort_leaderboard(di::get('db'), $c->id, [], ['xp' => 'XP'], $w->get_levels_info());
        $this->assertCount(0, $l->get_ranking(new limit(100)));
        $this->assertSame(null, $l->get_rank($g1->id));
        $this->assertSame(null, $l->get_rank($g2->id));
        $this->assertSame(null, $l->get_position($g1->id));
        $this->assertSame(null, $l->get_position($g2->id));
        $this->assertSame(0, $l->get_count());
    }

    public function test_leaderboard_with_cohorts_and_scores(): void {
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();
        $c2 = $dg->create_course();

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();
        $u5 = $dg->create_user();
        $u6 = $dg->create_user();

        $g1 = $dg->create_cohort();
        $g2 = $dg->create_cohort();
        $g3 = $dg->create_cohort();

        cohort_add_member($g1->id, $u1->id);
        cohort_add_member($g1->id, $u2->id);
        cohort_add_member($g1->id, $u4->id);
        cohort_add_member($g2->id, $u3->id);
        cohort_add_member($g3->id, $u5->id);
        cohort_add_member($g1->id, $u6->id);

        $w1 = $this->get_world($c1->id);
        $w2 = $this->get_world($c2->id);

        $s1 = $w1->get_store();
        $s2 = $w2->get_store();

        $s1->set($u1->id, 41);
        $s1->set($u2->id, 47);
        $s1->set($u3->id, 61);
        $s1->set($u4->id, 79);
        $s1->set($u5->id, 19);
        $s2->set($u6->id, 101);

        $l = new cohort_leaderboard(di::get('db'), $c1->id, [], ['xp' => 'XP'], $w1->get_levels_info());
        $ranking = $l->get_ranking(new limit(100));
        $this->assertCount(3, $ranking);
        $this->assertEquals(1, $ranking[0]->get_rank());
        $this->assertEquals(41 + 47 + 79, $ranking[0]->get_state()->get_xp());
        $this->assertEquals(2, $ranking[1]->get_rank());
        $this->assertEquals(61, $ranking[1]->get_state()->get_xp());
        $this->assertEquals(3, $ranking[2]->get_rank());
        $this->assertEquals(19, $ranking[2]->get_state()->get_xp());

        $this->assertSame(1, $l->get_rank($g1->id)->get_rank());
        $this->assertSame(2, $l->get_rank($g2->id)->get_rank());
        $this->assertSame(3, $l->get_rank($g3->id)->get_rank());
        $this->assertSame(0, $l->get_position($g1->id));
        $this->assertSame(1, $l->get_position($g2->id));
        $this->assertSame(2, $l->get_position($g3->id));
        $this->assertSame(3, $l->get_count());

        $l = new cohort_leaderboard(di::get('db'), $c1->id, [$g1->id, $g2->id], ['xp' => 'XP'], $w1->get_levels_info());
        $ranking = $l->get_ranking(new limit(100));
        $this->assertCount(2, $ranking);
        $this->assertEquals(1, $ranking[0]->get_rank());
        $this->assertEquals(41 + 47 + 79, $ranking[0]->get_state()->get_xp());
        $this->assertEquals(2, $ranking[1]->get_rank());
        $this->assertEquals(61, $ranking[1]->get_state()->get_xp());
        $ranking = $l->get_ranking(new limit(1, 1));
        $this->assertEquals(2, $ranking[0]->get_rank());
        $this->assertEquals(61, $ranking[0]->get_state()->get_xp());

        $this->assertSame(1, $l->get_rank($g1->id)->get_rank());
        $this->assertSame(2, $l->get_rank($g2->id)->get_rank());
        $this->assertSame(null, $l->get_rank($g3->id));
        $this->assertSame(0, $l->get_position($g1->id));
        $this->assertSame(1, $l->get_position($g2->id));
        $this->assertSame(null, $l->get_position($g3->id));
        $this->assertSame(2, $l->get_count());
    }

    public function test_leaderboard_with_cohorts_and_deleted_users(): void {
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();

        $g1 = $dg->create_cohort();
        $g2 = $dg->create_cohort();

        cohort_add_member($g1->id, $u1->id);
        cohort_add_member($g1->id, $u2->id);
        cohort_add_member($g1->id, $u4->id);
        cohort_add_member($g2->id, $u3->id);

        $w1 = $this->get_world($c1->id);
        $s1 = $w1->get_store();

        $s1->set($u1->id, 10);
        $s1->set($u2->id, 500);
        $s1->set($u3->id, 30);
        $s1->set($u4->id, 3);

        delete_user($u2);

        $l = new cohort_leaderboard(di::get('db'), $c1->id, [], ['xp' => 'XP'], $w1->get_levels_info());
        $ranking = $l->get_ranking(new limit(100));
        $this->assertCount(2, $ranking);
        $this->assertEquals(1, $ranking[0]->get_rank());
        $this->assertEquals(30, $ranking[0]->get_state()->get_xp());
        $this->assertEquals(2, $ranking[1]->get_rank());
        $this->assertEquals(13, $ranking[1]->get_state()->get_xp());
    }

    public function test_leaderboard_with_cohorts_and_suspended_users(): void {
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();

        $g1 = $dg->create_cohort();
        $g2 = $dg->create_cohort();

        cohort_add_member($g1->id, $u1->id);
        cohort_add_member($g1->id, $u2->id);
        cohort_add_member($g1->id, $u4->id);
        cohort_add_member($g2->id, $u3->id);

        $w1 = $this->get_world($c1->id);
        $s1 = $w1->get_store();

        $s1->set($u1->id, 10);
        $s1->set($u2->id, 500);
        $s1->set($u3->id, 30);
        $s1->set($u4->id, 3);

        $u2->suspended = 1;
        user_update_user($u2, false);

        $l = new cohort_leaderboard(di::get('db'), $c1->id, [], ['xp' => 'XP'], $w1->get_levels_info());
        $ranking = $l->get_ranking(new limit(100));
        $this->assertCount(2, $ranking);
        $this->assertEquals(1, $ranking[0]->get_rank());
        $this->assertEquals(30, $ranking[0]->get_state()->get_xp());
        $this->assertEquals(2, $ranking[1]->get_rank());
        $this->assertEquals(13, $ranking[1]->get_state()->get_xp());
    }

}
