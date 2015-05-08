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
 * Contains administration settings options.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$settings->add(new admin_setting_configcheckbox(
        'course_quick_access/Use_Javascript',
        get_string('usejavascript', 'block_course_quick_access'),
        get_string('descusejavascript', 'block_course_quick_access'),
        1));
        
$settings->add(new admin_setting_configcheckbox(
        'course_quick_access/Show_Search_Submit_Button',
        get_string('showsearchsubmitbutton', 'block_course_quick_access'),
        get_string('descshowsearchsubmitbutton', 'block_course_quick_access'),
        1));

$settings->add(new admin_setting_configcheckbox(
        'course_quick_access/Search_Submit_Button_2_Line',
        get_string('searchsubmitbutton2line', 'block_course_quick_access'),
        get_string('descsearchsubmitbutton2line', 'block_course_quick_access'),
        1));

