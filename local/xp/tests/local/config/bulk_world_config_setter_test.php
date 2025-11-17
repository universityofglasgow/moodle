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

namespace local_xp\local\config;

use block_xp\di;
use block_xp\local\config\static_config;
use local_xp\tests\base_testcase;

/**
 * Tests.
 *
 * @package    local_xp
 * @category   test
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_xp\local\config\bulk_world_config_setter
 */
final class bulk_world_config_setter_test extends base_testcase {

    /**
     * Test bulk override.
     */
    public function test_bulk_override(): void {
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();

        $cfg1 = $this->get_world($c1->id)->get_config();
        $cfg2 = $this->get_world($c2->id)->get_config();

        // Mix of block_xp and local_xp settings.
        $cfg1->set('maxactionspertime', 123);
        $cfg2->set('maxactionspertime', 456);
        $cfg1->set('timeformaxactions', 1800);
        $cfg2->set('timeformaxactions', 3600);
        $cfg1->set('levelsdata', '{}');
        $cfg2->set('levelsdata', '{"invalid": true}');
        $cfg1->set('maxpointspertime', 4800);
        $cfg2->set('maxpointspertime', 4800);
        $cfg1->set('timeformaxpoints', 6400);
        $cfg2->set('timeformaxpoints', 9000);
        $cfg1->set('enablegroupladder', 1);
        $cfg2->set('enablegroupladder', 0);
        $cfg1->set('badgetheme', 'badge1');
        $cfg2->set('badgetheme', 'badge2');
        $cfg1->set('currencystate', 0);
        $cfg2->set('currencystate', 1);
        $cfg1->set('currencytheme', 'theme1');
        $cfg2->set('currencytheme', 'theme2');

        di::get('bulk_world_config_setter')->set_from(new static_config([
            'enablegroupladder' => 1,
            'maxactionspertime' => 100,
            'timeformaxactions' => 200,
            'maxpointspertime' => 300,
            'timeformaxpoints' => 400,
            'badgetheme' => 'badge3',
            'currencystate' => 0,
            'currencytheme' => 'theme3',
        ]));

        $this->reset_container();
        $cfg1 = $this->get_world($c1->id)->get_config();
        $cfg2 = $this->get_world($c2->id)->get_config();

        $this->assertEquals(1, $cfg1->get('enablegroupladder'));
        $this->assertEquals(1, $cfg2->get('enablegroupladder'));
        $this->assertEquals(100, $cfg1->get('maxactionspertime'));
        $this->assertEquals(100, $cfg2->get('maxactionspertime'));
        $this->assertEquals(200, $cfg1->get('timeformaxactions'));
        $this->assertEquals(200, $cfg2->get('timeformaxactions'));
        $this->assertEquals(300, $cfg1->get('maxpointspertime'));
        $this->assertEquals(300, $cfg2->get('maxpointspertime'));
        $this->assertEquals(400, $cfg1->get('timeformaxpoints'));
        $this->assertEquals(400, $cfg2->get('timeformaxpoints'));
        $this->assertEquals('badge3', $cfg1->get('badgetheme'));
        $this->assertEquals('badge3', $cfg2->get('badgetheme'));
        $this->assertEquals(0, $cfg1->get('currencystate'));
        $this->assertEquals(0, $cfg2->get('currencystate'));
        $this->assertEquals('theme3', $cfg1->get('currencytheme'));
        $this->assertEquals('theme3', $cfg2->get('currencytheme'));
    }

    /**
     * Test bulk override with invalid keys.
     */
    public function test_bulk_override_with_invalid_keys(): void {
        $c1 = $this->getDataGenerator()->create_course();

        $cfg1 = $this->get_world($c1->id)->get_config();
        $data = $cfg1->get_all();

        di::get('bulk_world_config_setter')->set_from(new static_config([
            'blah' => 1,
            'foo' => 'abc',
            'id' => 9876,
            'courseid' => 1234,
            'contextid' => 3456,
        ]));

        $this->reset_container();
        $cfg1 = $this->get_world($c1->id)->get_config();
        $this->assertEquals($data, $cfg1->get_all());
    }

    /**
     * Test bulk override from admin defaults.
     */
    public function test_bulk_override_from_admin_defaults(): void {
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();

        $cfg1 = $this->get_world($c1->id)->get_config();
        $cfg2 = $this->get_world($c2->id)->get_config();

        // Mix of block_xp and local_xp settings.
        $cfg1->set('maxactionspertime', 123);
        $cfg2->set('maxactionspertime', 456);
        $cfg1->set('timeformaxactions', 1800);
        $cfg2->set('timeformaxactions', 3600);
        $cfg1->set('levelsdata', '{}');
        $cfg2->set('levelsdata', '{"invalid": true}');
        $cfg1->set('maxpointspertime', 4800);
        $cfg2->set('maxpointspertime', 4800);
        $cfg1->set('timeformaxpoints', 6400);
        $cfg2->set('timeformaxpoints', 9000);
        $cfg1->set('enablegroupladder', 1);
        $cfg2->set('enablegroupladder', 0);
        $cfg1->set('badgetheme', 'badge1');
        $cfg2->set('badgetheme', 'badge2');
        $cfg1->set('currencystate', 0);
        $cfg2->set('currencystate', 1);
        $cfg1->set('currencytheme', 'theme1');
        $cfg2->set('currencytheme', 'theme2');

        di::get('bulk_world_config_setter')->set_from_admin_defaults(new static_config([
            'enablegroupladder' => 1,
            'maxactionspertime' => 100,
            'timeformaxactions' => 200,
            'maxpointspertime' => 300,
            'timeformaxpoints' => 400,
            'badgetheme' => 'badge3',
            'currencystate' => 0,
            'currencytheme' => 'theme3',
        ]));

        $this->reset_container();
        $cfg1 = $this->get_world($c1->id)->get_config();
        $cfg2 = $this->get_world($c2->id)->get_config();

        $this->assertEquals(1, $cfg1->get('enablegroupladder'));
        $this->assertEquals(1, $cfg2->get('enablegroupladder'));
        $this->assertEquals(100, $cfg1->get('maxactionspertime'));
        $this->assertEquals(100, $cfg2->get('maxactionspertime'));
        $this->assertEquals(200, $cfg1->get('timeformaxactions'));
        $this->assertEquals(200, $cfg2->get('timeformaxactions'));
        $this->assertEquals(300, $cfg1->get('maxpointspertime'));
        $this->assertEquals(300, $cfg2->get('maxpointspertime'));
        $this->assertEquals(400, $cfg1->get('timeformaxpoints'));
        $this->assertEquals(400, $cfg2->get('timeformaxpoints'));
        $this->assertEquals('badge1', $cfg1->get('badgetheme'));
        $this->assertEquals('badge2', $cfg2->get('badgetheme'));
        $this->assertEquals(0, $cfg1->get('currencystate'));
        $this->assertEquals(1, $cfg2->get('currencystate'));
        $this->assertEquals('theme1', $cfg1->get('currencytheme'));
        $this->assertEquals('theme2', $cfg2->get('currencytheme'));
    }

}
