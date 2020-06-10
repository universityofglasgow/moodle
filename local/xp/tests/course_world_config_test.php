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
 * Course world config test.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/base_testcase.php');

use block_xp\di;

/**
 * Course world config testcase.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_course_world_config_testcase extends local_xp_base_testcase {

    protected function get_world($courseid) {
        return di::get('course_world_factory')->get_world($courseid);
    }

    public function test_default_config() {
        global $DB;

        $config = di::get('config');
        $dg = $this->getDataGenerator();
        $c1 = $dg->create_course();
        $c2 = $dg->create_course();

        $defaultadmin = new block_xp\local\config\config_stack([
            new local_xp\local\config\default_admin_config(),
            new block_xp\local\config\default_admin_config(),
        ]);
        $defaultcourse = new block_xp\local\config\config_stack([
            new local_xp\local\config\default_course_world_config(),
            new block_xp\local\config\default_course_world_config(),
        ]);
        $inheritable = array_keys(array_intersect_key(
            $defaultadmin->get_all(),
            $defaultcourse->get_all()
        ));

        // Validate that all keys match the admin value.
        $cfg1 = $this->get_world($c1->id)->get_config();
        foreach ($inheritable as $key) {
            $this->assertEquals($cfg1->get($key), $config->get($key));;
        }

        // Validate that changing an admin value is populated in the course.
        $this->assertNotEquals(9, $config->get('neighbours'));
        $this->assertNotEquals(99, $config->get('timeformaxpoints'));
        $config->set('neighbours', 9);
        $config->set('timeformaxpoints', 99);
        $this->assertContains('neighbours', $inheritable);
        $this->assertContains('timeformaxpoints', $inheritable);
        $cfg2 = $this->get_world($c2->id)->get_config();
        foreach ($inheritable as $key) {
            $this->assertEquals($cfg2->get($key), $config->get($key));;
        }

        // After saving the configuration, any more changes to the admin won't have an impact.
        $this->assertEquals($config->get('neighbours'), $cfg2->get('neighbours'));
        $this->assertEquals($config->get('timeformaxpoints'), $cfg2->get('timeformaxpoints'));
        $cfg2->set('neighbours', 7);
        $cfg2->set('timeformaxpoints', 77);
        $config->set('neighbours', 6);
        $config->set('timeformaxpoints', 66);
        $this->assertNotEquals($config->get('neighbours'), $cfg2->get('neighbours'));
        $this->assertNotEquals($config->get('timeformaxpoints'), $cfg2->get('timeformaxpoints'));
    }

}
