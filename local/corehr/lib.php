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
 *
 * @package    local_corehr
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_corehr_extend_navigation_course($parentnode, $course, $context) {
    if (!has_capability('local/corehr:config', $context)) {
        return;
    }
    $url = new moodle_url('/local/corehr/config.php', ['id' => $course->id]);
    $name = get_string('pluginname', 'local_corehr');
    $icon = new pix_icon('t/completion_complete', '');
    $parentnode->add($name, $url, navigation_node::NODETYPE_LEAF, 'corehr', null, $icon);
    $parentnode->make_active();
}