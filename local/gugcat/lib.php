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
 * Library file.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_gugcat_extend_navigation_course($parentnode, $course, $context) {
    $url = new moodle_url('/local/gugcat/index.php', array('id' => $course->id));
    $gugcat = get_string('navname', 'local_gugcat');
    $icon = new pix_icon('t/grades', '');
    $currentCourseNode = $parentnode->add($gugcat, $url, navigation_node::NODETYPE_LEAF, $gugcat, 'gugcat', $icon);
}

function local_gugcat_extend_navigation($navigation){
    global $USER, $COURSE;

    if (empty($USER->id)) {
        return;
    }

    if ($COURSE->id < 2) {
        return;
    }    

    $nodehome = $navigation->get('home');
    if (empty($nodehome)){
        $nodehome = $navigation;
    }
    
    $gugcat = get_string('navname', 'local_gugcat');
    $icon = new pix_icon('t/grades', '');
    $currentCourseNode = $nodehome->add($gugcat, new moodle_url('/local/gugcat/index.php', array('id' => $COURSE->id)), navigation_node::NODETYPE_LEAF, $gugcat, 'gugcat', $icon);
    $currentCourseNode->showinflatnavigation = true;    
}
