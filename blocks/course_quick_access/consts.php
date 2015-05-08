<?php
// This file is part of course-quick-access plugin for Moodle
// http://moodle.org/
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
 * Contains project constants for the course-quick-access block.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG, $OUTPUT;

define('MOODLE_COURSE_SEARCH_URL', $CFG->wwwroot.'/course/search.php'); 

define('MOODLE_ALL_COURSES_URL', $CFG->wwwroot.'/course/index.php');
define('MOODLE_USER_COURSES_URL', $CFG->wwwroot.'/my/index.php');
define('MOODLE_CATEGORY_URL', $CFG->wwwroot.'/course/category.php');
define('MOODLE_COURSE_URL', $CFG->wwwroot.'/course/view.php');

define('PLUGIN_CONFIG_URL', $CFG->wwwroot . 
		'/blocks/course_quick_access/edit.php');
define('PLUGIN_TOOGLE_CAT_URL', $CFG->wwwroot . 
		'/blocks/course_quick_access/ajax/category_tree_toggle.php');
define('PLUGIN_CONFIG_CATEGORY_PARAM', 'usercategory');
define('PLUGIN_CONFIG_ACTION_PARAM', 'action');

// ===== Database Tables =====
define('MOODLE_DB_PREFIX', $CFG->prefix);
define('MOODLE_DB_TABLE_USER', 'user');
define('MOODLE_DB_TABLE_COURSE', 'course');
define('MOODLE_DB_TABLE_CONTEXT', 'context');
define('MOODLE_DB_TABLE_ROLE_ASSIGN', 'role_assignments');
define('PLUGIN_DB_TABLE_CATEGORY', 'block_qacategory');
define('PLUGIN_DB_TABLE_COURSETOCAT', 'block_qacategory_course');

define('PLUGIN_JS_PATH', '/blocks/course_quick_access/js/');
define('PLUGIN_LIST_ID_REG', '/[^A-Za-z0-9]/');


define('MOODLE_ICON_INFO_SRC', $OUTPUT->pix_url('i/info'));
define('MOODLE_ICON_ATTENTION_SRC', $OUTPUT->pix_url('i/risk_personal'));

define('MOODLE_ICON_EDIT_SRC', $OUTPUT->pix_url('t/edit'));
define('MOODLE_ICON_DELETE_SRC', $OUTPUT->pix_url('t/delete'));
define('MOODLE_ICON_HIDE_SRC', $OUTPUT->pix_url('t/hide'));
define('MOODLE_ICON_SHOW_SRC', $OUTPUT->pix_url('t/show'));

define('MOODLE_ICON_COLLAPSED_SRC', $OUTPUT->pix_url('t/switch_plus'));
define('MOODLE_ICON_EXPANDED_SRC', $OUTPUT->pix_url('i/closed'));

define('MOODLE_ICON_UP_SRC', $OUTPUT->pix_url('t/up'));
define('MOODLE_ICON_DOWN_SRC', $OUTPUT->pix_url('t/down'));

define('MOODLE_ICON_NAVIGATION', $OUTPUT->pix_url('i/navigationitem'));
define('MOODLE_ICON_COURSE_SRC', $OUTPUT->pix_url('i/course'));
define('MOODLE_ICON_CATEGORY_SRC', $OUTPUT->pix_url('i/course'));
define('PLUGIN_ICON_USERCATEGORY_SRC', $OUTPUT->pix_url('i/open'));
define('PLUGIN_ICON_USERCATEGORYCLOSED_SRC', $OUTPUT->pix_url('i/closed'));

define('MOODLE_ICON_FOLDERCLOSED_SRC', $CFG->wwwroot.
		'/blocks/course_quick_access/pix/folder_closed.png');
define('MOODLE_ICON_FOLDEROPENED_SRC', $CFG->wwwroot.
		'/blocks/course_quick_access/pix/folder_opened.png');
define('PLUGIN_ICON_USERFOLDERCLOSED_SRC', $CFG->wwwroot.
		'/blocks/course_quick_access/pix/user_folder_closed.png');
define('PLUGIN_ICON_USERFOLDEROPENED_SRC', $CFG->wwwroot.'
		/blocks/course_quick_access/pix/user_folder_opened.png');
