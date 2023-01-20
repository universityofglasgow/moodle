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
 * guenrol report rendrer
 *
 * @package    block_course_overview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_guenrol\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

/**
 * guenrol report rendrer
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render menu
     * @param menu $menu
     */
    public function render_menu(menu $menu) {
        return $this->render_from_template('report_guenrol/menu', $menu->export_for_template($this));
    }

    /**
     * Render userlist
     * @param userlist $userlist
     */
    public function render_userlist(userlist $userlist) {
        return $this->render_from_template('report_guenrol/userlist', $userlist->export_for_template($this));
    }
}
