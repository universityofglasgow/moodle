<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

namespace local_xp;

use block_xp\di;
use block_xp\local\division\all_division;
use block_xp\local\division\group_division;
use block_xp\local\sql\limit;
use local_xp\local\config\default_course_world_config;
use local_xp\local\factory\world_leaderboard_factory;
use local_xp\tests\base_testcase;


/**
 * Test case.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class world_leaderboard_factory_test extends base_testcase {

    /**
     * Test nothing.
     *
     * @covers \block_xp\di
     */
    public function test_nothing(): void {
        $this->assertTrue(true);
    }

    /**
     * Test.
     *
     * @covers \local_xp\local\factory\world_leaderboard_factory
     */
    public function test_with_leaderboard_participation_forced(): void {
        $db = di::get('db');
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();

        $world = $this->get_world($c1->id);
        $world->get_config()->set('ladderparticipation', default_course_world_config::LEADERBOARD_PARTICIPATION_FORCED);
        $contextid = $world->get_context()->id;
        $store = $world->get_store();
        $store->set($u1->id, 100);
        $store->set($u2->id, 120);
        $store->set($u3->id, 130);
        $store->set($u4->id, 140);

        $expected = [
            [$u4, 1],
            [$u3, 2],
            [$u2, 3],
            [$u1, 4],
        ];

        // Test regular.
        $factory = new world_leaderboard_factory($db, $world);
        $lb = $factory->get_leaderboard();
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), $expected);

        // Test with division.
        $lb = $factory->get_leaderboard_for_division(new all_division());
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), $expected);

        // Test with flags.
        $db->insert_record('local_xp_user_flag', ['contextid' => $contextid, 'userid' => $u1->id, 'ladderparticipation' => null]);
        $db->insert_record('local_xp_user_flag', ['contextid' => $contextid, 'userid' => $u2->id, 'ladderparticipation' => 0]);
        $db->insert_record('local_xp_user_flag', ['contextid' => $contextid, 'userid' => $u3->id, 'ladderparticipation' => 1]);

        $lb = $factory->get_leaderboard();
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), $expected);

        $lb = $factory->get_leaderboard_for_division(new all_division());
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), $expected);
    }

    /**
     * Test.
     *
     * @covers \local_xp\local\factory\world_leaderboard_factory
     */
    public function test_with_leaderboard_participation_optout(): void {
        $db = di::get('db');
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();

        $dg->enrol_user($u1->id, $c1->id);
        $dg->enrol_user($u2->id, $c1->id);
        $dg->enrol_user($u3->id, $c1->id);
        $dg->enrol_user($u4->id, $c1->id);

        $g1 = $dg->create_group(['courseid' => $c1->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u1->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u2->id]);

        $g2 = $dg->create_group(['courseid' => $c1->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u3->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u4->id]);

        $world = $this->get_world($c1->id);
        $world->get_config()->set('ladderparticipation', default_course_world_config::LEADERBOARD_PARTICIPATION_OPTOUT);
        $contextid = $world->get_context()->id;
        $store = $world->get_store();
        $store->set($u1->id, 100);
        $store->set($u2->id, 120);
        $store->set($u3->id, 130);
        $store->set($u4->id, 140);

        $everyone = [
            [$u4, 1],
            [$u3, 2],
            [$u2, 3],
            [$u1, 4],
        ];

        // Test regular.
        $factory = new world_leaderboard_factory($db, $world);
        $lb = $factory->get_leaderboard();
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), $everyone);

        // Test with division.
        $lb = $factory->get_leaderboard_for_division(new all_division());
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), $everyone);

        // Add flags.
        $db->insert_record('local_xp_user_flag', ['contextid' => $contextid, 'userid' => $u1->id, 'ladderparticipation' => null]);
        $db->insert_record('local_xp_user_flag', ['contextid' => $contextid, 'userid' => $u2->id, 'ladderparticipation' => 0]);
        $db->insert_record('local_xp_user_flag', ['contextid' => $contextid, 'userid' => $u3->id, 'ladderparticipation' => 1]);

        $lb = $factory->get_leaderboard();
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), [
            [$u4, 1],
            [$u3, 2],
            [$u1, 3],
        ]);

        $lb = $factory->get_leaderboard_for_division(new all_division());
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), [
            [$u4, 1],
            [$u3, 2],
            [$u1, 3],
        ]);

        // Combine with group divisions.
        $lb = $factory->get_leaderboard_for_division(new group_division($g1->id));
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), [
            [$u1, 1],
        ]);

        $lb = $factory->get_leaderboard_for_division(new group_division($g2->id));
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), [
            [$u4, 1],
            [$u3, 2],
        ]);
    }

    /**
     * Test.
     *
     * @covers \local_xp\local\factory\world_leaderboard_factory
     */
    public function test_with_leaderboard_participation_optin(): void {
        $db = di::get('db');
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $u4 = $dg->create_user();

        $dg->enrol_user($u1->id, $c1->id);
        $dg->enrol_user($u2->id, $c1->id);
        $dg->enrol_user($u3->id, $c1->id);
        $dg->enrol_user($u4->id, $c1->id);

        $g1 = $dg->create_group(['courseid' => $c1->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u1->id]);
        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u2->id]);

        $g2 = $dg->create_group(['courseid' => $c1->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u3->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u4->id]);

        $world = $this->get_world($c1->id);
        $world->get_config()->set('ladderparticipation', default_course_world_config::LEADERBOARD_PARTICIPATION_OPTIN);
        $contextid = $world->get_context()->id;
        $store = $world->get_store();
        $store->set($u1->id, 100);
        $store->set($u2->id, 120);
        $store->set($u3->id, 130);
        $store->set($u4->id, 140);

        // Test regular.
        $factory = new world_leaderboard_factory($db, $world);
        $lb = $factory->get_leaderboard();
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), []);

        // Test with division.
        $lb = $factory->get_leaderboard_for_division(new all_division());
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), []);

        // Add flags.
        $db->insert_record('local_xp_user_flag', ['contextid' => $contextid, 'userid' => $u1->id, 'ladderparticipation' => null]);
        $db->insert_record('local_xp_user_flag', ['contextid' => $contextid, 'userid' => $u2->id, 'ladderparticipation' => 0]);
        $db->insert_record('local_xp_user_flag', ['contextid' => $contextid, 'userid' => $u3->id, 'ladderparticipation' => 1]);

        $lb = $factory->get_leaderboard();
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), [
            [$u3, 1],
        ]);

        $lb = $factory->get_leaderboard_for_division(new all_division());
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), [
            [$u3, 1],
        ]);

        // Set u1, and u2 flags to 1.
        $db->set_field('local_xp_user_flag', 'ladderparticipation', 1, []);

        $lb = $factory->get_leaderboard();
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), [
            [$u3, 1],
            [$u2, 2],
            [$u1, 3],
        ]);

        $lb = $factory->get_leaderboard_for_division(new all_division());
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), [
            [$u3, 1],
            [$u2, 2],
            [$u1, 3],
        ]);

        // Combine with group divisions.
        $lb = $factory->get_leaderboard_for_division(new group_division($g1->id));
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), [
            [$u2, 1],
            [$u1, 2],
        ]);

        $lb = $factory->get_leaderboard_for_division(new group_division($g2->id));
        $this->assert_ranking($lb->get_ranking(new limit(0, 0)), [
            [$u3, 1],
        ]);
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
