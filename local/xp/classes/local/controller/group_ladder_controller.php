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
 * Group ladder controller.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;
defined('MOODLE_INTERNAL') || die();

use moodle_exception;
use local_xp\local\config\default_course_world_config;

/**
 * Group ladder controller class.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group_ladder_controller extends \block_xp\local\controller\page_controller {

    protected $requiremanage = false;
    protected $supportsgroups = false;
    protected $routename = 'group_ladder';

    protected function permissions_checks() {
        parent::permissions_checks();
        $config = \block_xp\di::get('config');
        if ($this->world->get_config()->get('enablegroupladder') == default_course_world_config::GROUP_LADDER_NONE) {
            throw new moodle_exception('nopermissions', '', '', 'view_group_ladder_page');
        }
    }

    protected function page_setup() {
        global $PAGE;
        parent::page_setup();
        $PAGE->add_body_class('block_xp-ladder');
        $PAGE->add_body_class('block_xp-group-ladder');
    }

    protected function get_highlighted_ids() {
        global $USER;
        $helper = \block_xp\di::get('grouped_leaderboard_helper');
        return $helper->get_user_group_ids($USER, $this->world);
    }

    protected function get_leaderboard() {
        $factory = \block_xp\di::get('course_world_grouped_leaderboard_factory');
        return $factory->get_course_grouped_leaderboard($this->world);
    }

    protected function get_table() {
        global $USER;
        $table = new \local_xp\output\group_leaderboard_table(
            $this->get_leaderboard(),
            $this->get_renderer(),
            $this->get_highlighted_ids()
        );
        $table->define_baseurl($this->pageurl);
        return $table;
    }

    protected function get_page_html_head_title() {
        return get_string('groupladder', 'local_xp');
    }

    protected function get_page_heading() {
        return get_string('groupladder', 'local_xp');
    }

    protected function page_content() {
        echo $this->get_table()->out(20, false);
    }

}
