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
 * Gugcat library phpunit tests.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/gugcat/lib.php');

class local_gugcat_lib_testcase extends advanced_testcase {

    public function setUp() {
        $this->resetAfterTest();

        $this->user = $this->getDataGenerator()->create_user();
        $this->course = $this->getDataGenerator()->create_course();
        $this->coursecontext = context_course::instance($this->course->id);
        $modulecontext = context_module::instance($assign->cmid);
        $assign = new assign($modulecontext, false, false);
        $this->cm = $assign->get_course_module();
    }

    public function test_local_gugcat_extend_navigation_course(){
        global $COURSE;

        $node = new navigation_node(array('text'=>"test"));
        $node2 = new navigation_node(array('text'=>"test"));

        $url = new moodle_url('/local/gugcat/index.php', array('id' => $COURSE->id));        
        $gugcat = get_string('navname', 'local_gugcat');
        $icon = new pix_icon('t/grades', '');

        $node2->get('home');
        $node2->add($gugcat, $url, navigation_node::NODETYPE_LEAF, $gugcat, 'gugcat', $icon)->showinflatnavigation = true;
        local_gugcat_extend_navigation($node);
        $this->assertEquals($node2, $node);
    }

    public function test_local_gugcat_extend_navigation(){
        $node = new navigation_node(array('text'=>"test"));
        $node2 = new navigation_node(array('text'=>"test"));

        $url = new moodle_url('/local/gugcat/index.php', array('id' => 3));        
        $gugcat = get_string('navname', 'local_gugcat');
        $icon = new pix_icon('t/grades', '');

        $node2->add($gugcat, $url, navigation_node::NODETYPE_LEAF, $gugcat, 'gugcat', $icon);
        local_gugcat_extend_navigation_course($node, (object) array('id'=> 3 ), null);
        $this->assertEquals($node2, $node);
    }
}