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
 * Version file.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_gugcat_extend_navigation_course($parentnode, $course, $context) {
    $url = new moodle_url('/local/gugcat/index.php', array('id' => $course->id));
    $gugcat = get_string('navname', 'local_gugcat');
    $icon = new pix_icon('my-media', '', 'local_mymedia');
    $main_node = $parentnode->add($gugcat, $url, navigation_node::TYPE_CONTAINER, $gugcat, 'gugcat', $icon);
}

function local_gugcat_extend_navigation($navigation){
    global $COURSE;

    if (empty($COURSE->id)) {
        return;
    }    
    
    // Check the current page context.  If the context is not of a course or module then we are in another area of Moodle and return void.
    $context = context::instance_by_id($COURSE->id);
    $isvalidcontext = ($context instanceof context_course || $context instanceof context_module) ? true : false;
    if (!$isvalidcontext) {
        return;
    }

    // If the context is a module then get the parent context.
    $coursecontext = null;
    if ($context instanceof context_module) {
        $coursecontext = $context->get_course_context();
    } else {
        $coursecontext = $context;
    }

    $url = new moodle_url('/local/gugcat/index.php', array('id' => $COURSE->id));
    $gugcat = get_string('navname', 'local_gugcat');
    $icon = new pix_icon('t/grades', '');

    $currentCourseNode = $navigation->find('currentcourse', $navigation::TYPE_ROOTNODE);
    if (isNodeNotEmpty($currentCourseNode)) {
        // we have a 'current course' node, add the link to it.
        $currentCourseNode->add($gugcat, $url, navigation_node::NODETYPE_LEAF, $gugcat, 'gugcat', $icon);
    }    

    $main_node = $navigation->add($gugcat, $url, navigation_node::TYPE_CONTAINER, $gugcat, 'gugcat', $icon);
    $main_node->showinflatnavigation = true;
}