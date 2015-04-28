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
 * Provides funtionality for asnynchron javascript category-tree toggeling.
 * 
 * When the url of this file is called by the client with a specific 
 * category-id and a category-state (open/close), this state is stored in the
 * db. 
 * Example:
 *   "[path_to_file]/category_tree_toggle.php?id=28&state=close"
 *   stores the state of the category with "id" 28 as closed. 
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
// Include plugin constants, but set the "Page Context" first, 
// to avoid false debug warning messages
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
require_once(dirname(dirname(__FILE__)).'/consts.php');
require_once(dirname(dirname(__FILE__)).'/editlib_category.php');

$catidparam = optional_param('id', 0, PARAM_INT);
$catidparam = clean_param($catidparam, PARAM_INT);
$toggleparam = optional_param('state', 'open', PARAM_TEXT);
$toggleparam = clean_param($toggleparam, PARAM_TEXT);

$catid = $DB->get_field(PLUGIN_DB_TABLE_CATEGORY, 'id', 
                        array('id'=>$catidparam));
if($catid) {
    if($toggleparam == 'open') {
        set_category_state($catid, 1);
    } else if ($toggleparam == 'close') {
        set_category_state($catid, 0);
    }
}
