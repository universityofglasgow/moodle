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
use block_xp\local\userfilter\group_members;
use block_xp\local\userfilter\everyone;
use block_xp\local\userfilter\nobody;
use context_course;
use context_system;
use local_xp\local\config\default_course_world_config;
use local_xp\local\userfilter\leaderboard_participants;
use local_xp\local\userfilter\stack;
use local_xp\tests\base_testcase;


/**
 * Test case.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class user_filter_test extends base_testcase {

    /**
     * Test.
     *
     * @covers \local_xp\local\userfilter\leaderboard_participants
     */
    public function test_leaderboard_participants_forced(): void {
        $dg = $this->getDataGenerator();

        $c1 = $dg->create_course();
        $c2 = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();

        $sysctx = context_system::instance();
        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

        $db = di::get('db');
        $db->insert_record('local_xp_user_flag', ['contextid' => $sysctx->id, 'userid' => $u1->id, 'ladderparticipation' => 0]);
        $db->insert_record('local_xp_user_flag', ['contextid' => $sysctx->id, 'userid' => $u2->id, 'ladderparticipation' => 1]);
        $db->insert_record('local_xp_user_flag', ['contextid' => $c1ctx->id, 'userid' => $u2->id, 'ladderparticipation' => 0]);
        $db->insert_record('local_xp_user_flag', ['contextid' => $c2ctx->id, 'userid' => $u3->id, 'ladderparticipation' => 1]);

        $mode = default_course_world_config::LEADERBOARD_PARTICIPATION_FORCED;
        $userfilter = new leaderboard_participants($sysctx->id, $mode);

        foreach ([$sysctx, $c1ctx, $c2ctx] as $context) {
            [$insql, $inparams] = $userfilter->get_sql('id');
            $userids = $db->get_fieldset_sql("SELECT id FROM {user} WHERE $insql AND id IN ($u1->id, $u2->id, $u3->id)", $inparams);
            sort($userids);
            $this->assertEquals([$u1->id, $u2->id, $u3->id], $userids, "Testing with context " . get_class($context));

            // Test with column aliases.
            [$insql, $inparams] = $userfilter->get_sql('u.id');
            $userids = $db->get_fieldset_sql("SELECT u.id FROM {user} u WHERE $insql AND u.id IN ($u1->id, $u2->id, $u3->id)",
                $inparams
            );
            sort($userids);
            $this->assertEquals([$u1->id, $u2->id, $u3->id], $userids, "Testing with context " . get_class($context));
        }
    }

    /**
     * Provider.
     *
     * @return array
     */
    public static function leaderboard_participants_optin_provider(): array {
        return [
            'no users have flags' => [
                'contextid' => 'sys',
                'flags' => [],
                'expected' => [],
            ],
            'single user opted in' => [
                'contextid' => 'c1',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'c1', 'ladderparticipation' => 1],
                ],
                'expected' => ['u1'],
            ],
            'multiple users opted in' => [
                'contextid' => 'c2',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'c2', 'ladderparticipation' => 1],
                    ['userid' => 'u2', 'contextid' => 'c2', 'ladderparticipation' => 1],
                    ['userid' => 'u3', 'contextid' => 'c2', 'ladderparticipation' => 1],
                ],
                'expected' => ['u1', 'u2', 'u3'],
            ],
            'multiple users opted in other context' => [
                'contextid' => 'c1',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'c2', 'ladderparticipation' => 1],
                    ['userid' => 'u2', 'contextid' => 'c2', 'ladderparticipation' => 1],
                    ['userid' => 'u3', 'contextid' => 'c2', 'ladderparticipation' => 1],
                ],
                'expected' => [],
            ],
            'mixed participation flags' => [
                'contextid' => 'sys',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'sys', 'ladderparticipation' => 1],
                    ['userid' => 'u2', 'contextid' => 'sys', 'ladderparticipation' => 0],
                    ['userid' => 'u3', 'contextid' => 'sys', 'ladderparticipation' => 1],
                ],
                'expected' => ['u1', 'u3'],
            ],
            'different context flags' => [
                'contextid' => 'c1',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'c1', 'ladderparticipation' => 1],
                    ['userid' => 'u2', 'contextid' => 'c2', 'ladderparticipation' => 1],
                    ['userid' => 'u3', 'contextid' => 'c1', 'ladderparticipation' => 1],
                ],
                'expected' => ['u1', 'u3'],
            ],
            'null or missing participation flags' => [
                'contextid' => 'c2',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'c2', 'ladderparticipation' => 1],
                    ['userid' => 'u2', 'contextid' => 'c2', 'ladderparticipation' => null],
                ],
                'expected' => ['u1'],
            ],
        ];
    }

    /**
     * Test.
     *
     * @covers \local_xp\local\userfilter\leaderboard_participants
     * @dataProvider leaderboard_participants_optin_provider
     * @param string $contextid
     * @param array $flags
     * @param array $expected
     */
    public function test_leaderboard_participants_optin(string $contextid, array $flags, array $expected): void {
        $dg = $this->getDataGenerator();

        $c1 = $dg->create_course();
        $c2 = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $users = compact('u1', 'u2', 'u3');

        $sysctx = context_system::instance();
        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);
        $contexts = ['sys' => $sysctx, 'c1' => $c1ctx, 'c2' => $c2ctx];
        $contextid = $contexts[$contextid]->id;

        $db = di::get('db');
        foreach ($flags as $flag) {
            $flag['contextid'] = $contexts[$flag['contextid']]->id;
            $flag['userid'] = $users[$flag['userid']]->id;
            $db->insert_record('local_xp_user_flag', $flag);
        }

        $mode = default_course_world_config::LEADERBOARD_PARTICIPATION_OPTIN;
        $userfilter = new leaderboard_participants($contextid, $mode);
        $expected = array_map(function ($u) use ($users) {
            return $users[$u]->id;
        }, $expected);
        sort($expected);

        [$insql, $inparams] = $userfilter->get_sql('id');
        $userids = $db->get_fieldset_sql("SELECT id FROM {user} WHERE $insql AND id IN ($u1->id, $u2->id, $u3->id)", $inparams);
        sort($userids);
        $this->assertEquals($expected, $userids);

        // Test with column aliases.
        [$insql, $inparams] = $userfilter->get_sql('u.id');
        $userids = $db->get_fieldset_sql("SELECT u.id FROM {user} u WHERE $insql AND u.id IN ($u1->id, $u2->id, $u3->id)",
            $inparams
        );
        sort($userids);
        $this->assertEquals($expected, $userids);
    }

    /**
     * Provider.
     *
     * @return array
     */
    public static function leaderboard_participants_optout_provider(): array {
        return [
            'no users have flags' => [
                'contextid' => 'sys',
                'flags' => [],
                'expected' => ['u1', 'u2', 'u3'],
            ],
            'single user opted out' => [
                'contextid' => 'c1',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'c1', 'ladderparticipation' => 0],
                ],
                'expected' => ['u2', 'u3'],
            ],
            'multiple users opted out' => [
                'contextid' => 'c2',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'c2', 'ladderparticipation' => 0],
                    ['userid' => 'u2', 'contextid' => 'c2', 'ladderparticipation' => 0],
                    ['userid' => 'u3', 'contextid' => 'c2', 'ladderparticipation' => 0],
                ],
                'expected' => [],
            ],
            'multiple users opted out other context' => [
                'contextid' => 'c1',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'c2', 'ladderparticipation' => 0],
                    ['userid' => 'u2', 'contextid' => 'c2', 'ladderparticipation' => 0],
                    ['userid' => 'u3', 'contextid' => 'c2', 'ladderparticipation' => 0],
                ],
                'expected' => ['u1', 'u2', 'u3'],
            ],
            'mixed participation flags' => [
                'contextid' => 'sys',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'sys', 'ladderparticipation' => 1],
                    ['userid' => 'u2', 'contextid' => 'sys', 'ladderparticipation' => 0],
                    ['userid' => 'u3', 'contextid' => 'sys', 'ladderparticipation' => null],
                ],
                'expected' => ['u1', 'u3'],
            ],
            'different context flags' => [
                'contextid' => 'c1',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'c1', 'ladderparticipation' => 0],
                    ['userid' => 'u2', 'contextid' => 'c2', 'ladderparticipation' => 0],
                    ['userid' => 'u3', 'contextid' => 'c1', 'ladderparticipation' => 0],
                ],
                'expected' => ['u2'],
            ],
            'null or missing participation flags' => [
                'contextid' => 'c2',
                'flags' => [
                    ['userid' => 'u1', 'contextid' => 'c2', 'ladderparticipation' => 0],
                    ['userid' => 'u2', 'contextid' => 'c2', 'ladderparticipation' => null],
                ],
                'expected' => ['u2', 'u3'],
            ],
        ];
    }

    /**
     * Test.
     *
     * @covers \local_xp\local\userfilter\leaderboard_participants
     * @dataProvider leaderboard_participants_optout_provider
     * @param string $contextid
     * @param array $flags
     * @param array $expected
     */
    public function test_leaderboard_participants_optout(string $contextid, array $flags, array $expected): void {
        $dg = $this->getDataGenerator();

        $c1 = $dg->create_course();
        $c2 = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();
        $users = compact('u1', 'u2', 'u3');

        $sysctx = context_system::instance();
        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);
        $contexts = ['sys' => $sysctx, 'c1' => $c1ctx, 'c2' => $c2ctx];
        $contextid = $contexts[$contextid]->id;

        $db = di::get('db');
        foreach ($flags as $flag) {
            $flag['contextid'] = $contexts[$flag['contextid']]->id;
            $flag['userid'] = $users[$flag['userid']]->id;
            $db->insert_record('local_xp_user_flag', $flag);
        }

        $mode = default_course_world_config::LEADERBOARD_PARTICIPATION_OPTOUT;
        $userfilter = new leaderboard_participants($contextid, $mode);
        $expected = array_map(function ($u) use ($users) {
            return $users[$u]->id;
        }, $expected);
        sort($expected);

        [$insql, $inparams] = $userfilter->get_sql('id');
        $userids = $db->get_fieldset_sql("SELECT id FROM {user} WHERE $insql AND id IN ($u1->id, $u2->id, $u3->id)", $inparams);
        sort($userids);
        $this->assertEquals($expected, $userids);

        // Test with column aliases.
        [$insql, $inparams] = $userfilter->get_sql('u.id');
        $userids = $db->get_fieldset_sql("SELECT u.id FROM {user} u WHERE $insql AND u.id IN ($u1->id, $u2->id, $u3->id)",
            $inparams
        );
        sort($userids);
        $this->assertEquals($expected, $userids);
    }

    /**
     * Test.
     *
     * @covers \local_xp\local\userfilter\stack
     */
    public function test_stack(): void {
        $dg = $this->getDataGenerator();

        $c1 = $dg->create_course();
        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();

        $dg->enrol_user($u1->id, $c1->id);
        $dg->enrol_user($u2->id, $c1->id);
        $dg->enrol_user($u3->id, $c1->id);

        $g1 = $dg->create_group(['courseid' => $c1->id]);
        $g2 = $dg->create_group(['courseid' => $c1->id]);

        $dg->create_group_member(['groupid' => $g1->id, 'userid' => $u1->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u1->id]);
        $dg->create_group_member(['groupid' => $g2->id, 'userid' => $u2->id]);
        $db = di::get('db');

        // Fetching everyone.
        $expected = [$u1->id, $u2->id, $u3->id];
        $userfilter = new stack([new everyone(), new everyone()], true);
        [$insql, $inparams] = $userfilter->get_sql('id');
        $actual = $db->get_fieldset_select('user', 'id', "id IN ($u1->id, $u2->id, $u3->id) AND $insql", $inparams);
        sort($actual);
        $this->assertSame($expected, $actual);

        // Fetching everyone and nobody.
        $expected = [];
        $userfilter = new stack([new everyone(), new nobody()], true);
        [$insql, $inparams] = $userfilter->get_sql('id');
        $actual = $db->get_fieldset_select('user', 'id', "id IN ($u1->id, $u2->id, $u3->id) AND $insql", $inparams);
        sort($actual);
        $this->assertSame($expected, $actual);

        // Fetching everyone or nobody.
        $expected = [$u1->id, $u2->id, $u3->id];
        $userfilter = new stack([new everyone(), new nobody()], false);
        [$insql, $inparams] = $userfilter->get_sql('id');
        $actual = $db->get_fieldset_select('user', 'id', "id IN ($u1->id, $u2->id, $u3->id) AND $insql", $inparams);
        sort($actual);
        $this->assertSame($expected, $actual);

        // Fetching nobody or nobody.
        $expected = [];
        $userfilter = new stack([new nobody(), new nobody()], false);
        [$insql, $inparams] = $userfilter->get_sql('id');
        $actual = $db->get_fieldset_select('user', 'id', "id IN ($u1->id, $u2->id, $u3->id) AND $insql", $inparams);
        sort($actual);
        $this->assertSame($expected, $actual);

        // Fetching g1 or g2.
        $expected = [$u1->id, $u2->id];
        $userfilter = new stack([new group_members($g1->id), new group_members($g2->id)], false);
        [$insql, $inparams] = $userfilter->get_sql('id');
        $actual = $db->get_fieldset_select('user', 'id', "id IN ($u1->id, $u2->id, $u3->id) AND $insql", $inparams);
        sort($actual);
        $this->assertSame($expected, $actual);

        // Fetching g1 and g2.
        $expected = [$u1->id];
        $userfilter = new stack([new group_members($g1->id), new group_members($g2->id)], true);
        [$insql, $inparams] = $userfilter->get_sql('id');
        $actual = $db->get_fieldset_select('user', 'id', "id IN ($u1->id, $u2->id, $u3->id) AND $insql", $inparams);
        sort($actual);
        $this->assertSame($expected, $actual);
    }
}
