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

namespace local_xp\local\xp;

use block_xp\local\utils\user_utils;
use block_xp\local\xp\state_with_subject;
use block_xp\local\xp\state_with_user;
use block_xp\local\xp\user_state;
use local_xp\tests\base_testcase;
use moodle_url;

/**
 * Tests.
 *
 * @package    local_xp
 * @category   test
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_xp\local\xp\firstname_initial_lastname_anonymiser
 */
final class firstname_initial_lastname_anonymiser_test extends base_testcase {

    /**
     * Test user state.
     */
    public function test_user_state_anonymisation(): void {
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'John', 'lastname' => 'Doe']);
        $world = $this->get_world($course->id);

        $state = new user_state($user, 100, $world->get_levels_info(), $course->id);
        $this->assertEquals($user->id, $state->get_id());
        $this->assertEquals(fullname($user), $state->get_name());
        $this->assertEquals(user_utils::user_picture($user)->out(), $state->get_picture()->out());
        $this->assertNotNull($state->get_link());
        $this->assertEquals($user, $state->get_user());

        $anonymiser = new firstname_initial_lastname_anonymiser([$user->id]);
        $anonstate = $anonymiser->anonymise_state($state);
        $this->assertSame($state, $anonstate);

        $anonymiser = new firstname_initial_lastname_anonymiser();
        $anonstate = $anonymiser->anonymise_state($state);
        $this->assertNotSame($state, $anonstate);

        // The ID is preserved, we should investigate whether that's something we want or not.
        // $this->assertNotEquals($user->id, $anonstate->get_id());
        // $this->assertEquals($anonuser->id, $anonstate->get_id());

        if ($anonstate instanceof state_with_subject) {
            $this->assertEquals('John D.', $anonstate->get_name());
            $this->assertEquals(user_utils::default_picture()->out(), $anonstate->get_picture()->out());
            $this->assertNull($anonstate->get_link());
        }
    }

    /**
     * Test levelless state.
     */
    public function test_levelless_state_anonymisation(): void {
        $state = new levelless_state(100, 42, 'Test User');
        $this->assertEquals(42, $state->get_id());
        $this->assertEquals('Test User', $state->get_name());
        $this->assertNull($state->get_picture());
        $this->assertNull($state->get_link());

        $anonymiser = new firstname_initial_lastname_anonymiser([42]);
        $anonstate = $anonymiser->anonymise_state($state);
        $this->assertSame($state, $anonstate);

        $anonymiser = new firstname_initial_lastname_anonymiser();
        $anonstate = $anonymiser->anonymise_state($state);
        $this->assertNotSame($state, $anonstate);

        // The ID is preserved, we should investigate whether that's something we want or not.
        // $this->assertNotEquals(42, $anonstate->get_id());
        if ($anonstate instanceof state_with_subject) {
            $this->assertEquals('Someone else', $anonstate->get_name());
            $this->assertEquals(user_utils::default_picture(), $anonstate->get_picture());
            $this->assertNull($anonstate->get_link());
        }
    }

    /**
     * Test state with user.
     */
    public function test_state_with_user_anonymisation(): void {
        $user = $this->getDataGenerator()->create_user(['firstname' => 'John', 'lastname' => 'Doe']);

        // Create a state_with_user that also contains state_with_subject to confirm their anonymity.
        $state = new class ($user) implements state_with_subject, state_with_user {
            protected $user;

            public function __construct($user) {
                $this->user = $user;
            }

            public function get_id() {
                return $this->user->id;
            }

            public function get_level() {
                return new static_level(1, 0);
            }

            public function get_link() {
                return new moodle_url('user.php', ['id' => $this->user->id]);
            }

            public function get_name() {
                return fullname($this->user);
            }

            public function get_picture() {
                return new moodle_url('pic.php', ['id' => $this->user->id]);
            }

            public function get_ratio_in_level() {
                return 0;
            }

            public function get_total_xp_in_level() {
                return 100;
            }

            public function get_user() {
                return $this->user;
            }

            public function get_xp() {
                return 0;
            }

            public function get_xp_in_level() {
                return 0;
            }
        };

        $this->assertEquals($user->id, $state->get_id());
        $this->assertEquals(fullname($user), $state->get_name());
        $this->assertNotNull($state->get_link());
        $this->assertEquals($user, $state->get_user());

        $anonymiser = new firstname_initial_lastname_anonymiser([$user->id]);
        $anonstate = $anonymiser->anonymise_state($state);
        $this->assertSame($state, $anonstate);

        $anonymiser = new firstname_initial_lastname_anonymiser();
        $anonstate = $anonymiser->anonymise_state($state);
        $this->assertNotSame($state, $anonstate);

        // The ID is preserved, we should investigate whether that's something we want or not.
        // $this->assertNotEquals($user->id, $anonstate->get_id());
        // $this->assertEquals($anonuser->id, $anonstate->get_id());

        if ($anonstate instanceof state_with_subject) {
            $this->assertEquals('John D.', $anonstate->get_name());
            $this->assertNotEquals($state->get_picture(), $anonstate->get_picture()->out());
            $this->assertEquals(user_utils::default_picture()->out(), $anonstate->get_picture()->out());
            $this->assertNotEquals($state->get_link(), $anonstate->get_link());
            $this->assertNull($anonstate->get_link());
        }
    }
}
