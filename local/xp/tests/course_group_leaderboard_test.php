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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/base_testcase.php');

use block_xp\di;
use block_xp\local\sql\limit;
use local_xp\local\leaderboard\course_group_leaderboard;

/**
 * Group leaderboard testcase.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_course_group_leaderboard_testcase extends local_xp_base_testcase {

    protected function get_world($courseid) {
        $world = di::get('course_world_factory')->get_world($courseid);
        $world->get_config()->set('enabled', 1);
        $world->get_config()->set('enablegroupladder', 1);
        return $world;
    }

    public function test_leaderboard_with_no_groups() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $w = $this->get_world($c->id);

        $l = new course_group_leaderboard(di::get('db'), $c->id, ['xp' => 'XP'], $w->get_levels_info());
        $this->assertCount(0, $l->get_ranking(new limit(100)));
        $this->assertSame(null, $l->get_rank(1));
        $this->assertSame(null, $l->get_position(1));
        $this->assertSame(0, $l->get_count());
    }

    public function test_leaderboard_with_groups_but_no_scores() {
        $dg = $this->getDataGenerator();
        $c = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $dg->enrol_user($u1->id, $c->id);
        $dg->enrol_user($u2->id, $c->id);
        $dg->enrol_user($u3->id, $c->id);
        $g1 = $dg->create_group(['courseid' => $c->id]);
        $g2 = $dg->create_group(['courseid' => $c->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u1->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u2->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u3->id]);

        $w = $this->get_world($c->id);
        $l = new course_group_leaderboard(di::get('db'), $c->id, ['xp' => 'XP'], $w->get_levels_info());
        $this->assertCount(0, $l->get_ranking(new limit(100)));
        $this->assertSame(null, $l->get_rank($g1->id));
        $this->assertSame(null, $l->get_rank($g2->id));
        $this->assertSame(null, $l->get_position($g1->id));
        $this->assertSame(null, $l->get_position($g2->id));
        $this->assertSame(0, $l->get_count());
    }

    public function test_leaderboard_with_groups_and_scores() {
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();
        $c2 = $dg->create_course();

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();

        $dg->enrol_user($u1->id, $c1->id);
        $dg->enrol_user($u2->id, $c1->id);
        $dg->enrol_user($u3->id, $c1->id);
        $dg->enrol_user($u4->id, $c1->id);
        $dg->enrol_user($u4->id, $c2->id);

        $g1 = $dg->create_group(['courseid' => $c1->id]);
        $g2 = $dg->create_group(['courseid' => $c1->id]);
        $gx = $dg->create_group(['courseid' => $c2->id]);

        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u1->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u2->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u4->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u3->id]);
        $dg->create_group_member(['groupid' => $gx->id, 'userid' => $u4->id]);

        $w1 = $this->get_world($c1->id);
        $w2 = $this->get_world($c2->id);

        $s1 = $w1->get_store();
        $s2 = $w2->get_store();

        $s1->set($u1->id, 41);
        $s1->set($u2->id, 47);
        $s1->set($u3->id, 61);
        $s1->set($u4->id, 79);
        $s2->set($u4->id, 97);

        $l = new course_group_leaderboard(di::get('db'), $c1->id, ['xp' => 'XP'], $w1->get_levels_info());
        $ranking = $l->get_ranking(new limit(100));
        $this->assertCount(2, $ranking);
        $this->assertEquals(1, $ranking[0]->get_rank());
        $this->assertEquals(41+47+79, $ranking[0]->get_state()->get_xp());
        $this->assertEquals(2, $ranking[1]->get_rank());
        $this->assertEquals(61, $ranking[1]->get_state()->get_xp());
        $ranking = $l->get_ranking(new limit(1, 1));
        $this->assertEquals(2, $ranking[0]->get_rank());
        $this->assertEquals(61, $ranking[0]->get_state()->get_xp());

        $this->assertSame(1, $l->get_rank($g1->id)->get_rank());
        $this->assertSame(2, $l->get_rank($g2->id)->get_rank());
        $this->assertSame(null, $l->get_rank($gx->id));
        $this->assertSame(0, $l->get_position($g1->id));
        $this->assertSame(1, $l->get_position($g2->id));
        $this->assertSame(null, $l->get_position($gx->id));
        $this->assertSame(2, $l->get_count());
    }

    public function test_leaderboard_with_groups_and_intricacies() {
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();
        $u5 = $dg->create_user();
        $u6 = $dg->create_user();
        $u7 = $dg->create_user();

        $dg->enrol_user($u1->id, $c1->id);
        $dg->enrol_user($u2->id, $c1->id);
        $dg->enrol_user($u3->id, $c1->id);
        $dg->enrol_user($u4->id, $c1->id);
        $dg->enrol_user($u5->id, $c1->id);
        $dg->enrol_user($u6->id, $c1->id);
        $dg->enrol_user($u7->id, $c1->id);

        // Group 1.
        $g1 = $dg->create_group(['courseid' => $c1->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u2->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u4->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u6->id]);

        // Group 2.
        $g2 = $dg->create_group(['courseid' => $c1->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u1->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u2->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u3->id]);

        // Group 3
        $g3 = $dg->create_group(['courseid' => $c1->id]);
        $dg->create_group_member(['groupid' => $g3->id, 'userid' => $u1->id]);
        $dg->create_group_member(['groupid' => $g3->id, 'userid' => $u2->id]);
        $dg->create_group_member(['groupid' => $g3->id, 'userid' => $u3->id]);
        $dg->create_group_member(['groupid' => $g3->id, 'userid' => $u4->id]);
        $dg->create_group_member(['groupid' => $g3->id, 'userid' => $u5->id]);

        // Group 4.
        $g4 = $dg->create_group(['courseid' => $c1->id]);
        $dg->create_group_member(['groupid' => $g4->id, 'userid' => $u1->id]);
        $dg->create_group_member(['groupid' => $g4->id, 'userid' => $u2->id]);
        $dg->create_group_member(['groupid' => $g4->id, 'userid' => $u3->id]);

        $w1 = $this->get_world($c1->id);
        $s1 = $w1->get_store();

        $s1->set($u1->id, 11);
        $s1->set($u2->id, 22);
        $s1->set($u3->id, 33);
        $s1->set($u4->id, 44);
        $s1->set($u5->id, 55);
        $s1->set($u6->id, 66);
        $s1->set($u7->id, 77);

        $l = new course_group_leaderboard(di::get('db'), $c1->id, ['xp' => 'XP'], $w1->get_levels_info());
        $ranking = $l->get_ranking(new limit(100));
        $this->assertCount(4, $ranking);
        $this->assertEquals(1, $ranking[0]->get_rank());
        $this->assertEquals(11+22+33+44+55, $ranking[0]->get_state()->get_xp());
        $this->assertEquals($g3->id, $ranking[0]->get_state()->get_id());
        $this->assertEquals(2, $ranking[1]->get_rank());
        $this->assertEquals(22+44+66, $ranking[1]->get_state()->get_xp());
        $this->assertEquals($g1->id, $ranking[1]->get_state()->get_id());
        $this->assertEquals(3, $ranking[2]->get_rank());
        $this->assertEquals(11+22+33, $ranking[2]->get_state()->get_xp());
        $this->assertEquals($g2->id, $ranking[2]->get_state()->get_id());
        $this->assertEquals(3, $ranking[3]->get_rank());
        $this->assertEquals(11+22+33, $ranking[3]->get_state()->get_xp());
        $this->assertEquals($g4->id, $ranking[3]->get_state()->get_id());

        $this->assertSame(2, $l->get_rank($g1->id)->get_rank());
        $this->assertSame(3, $l->get_rank($g2->id)->get_rank());
        $this->assertSame(1, $l->get_rank($g3->id)->get_rank());
        $this->assertSame(3, $l->get_rank($g4->id)->get_rank());

        $this->assertSame(1, $l->get_position($g1->id));
        $this->assertSame(2, $l->get_position($g2->id));
        $this->assertSame(0, $l->get_position($g3->id));
        $this->assertSame(3, $l->get_position($g4->id));

        $this->assertSame(4, $l->get_count());
    }
}
