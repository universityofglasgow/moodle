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
 * File contains functions for user-category manipulation and storage.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/consts.php');

/**
 * Deletes a specific user-category.
 *
 * @param string user_id The user-id that owns the category.
 * @param string category_id The id of the category that will be deleted.
 */
function delete_category($user_id, $category_id) {
    global $DB;
    // TODO: Batch update? Delegated transactions?
    // No such category in DB, print error
    if(!$DB->record_exists(PLUGIN_DB_TABLE_CATEGORY, 
                           array('id'=>$category_id))) {
        return;
    }
    // Move all existing user-categories 
    $catpos = $DB->get_field(PLUGIN_DB_TABLE_CATEGORY, 
                             'position', array('id'=>$category_id));
    $query = 'UPDATE '.MOODLE_DB_PREFIX.PLUGIN_DB_TABLE_CATEGORY . 
           ' SET position = position - 1' .
           ' WHERE userid = ? AND position > ?';
    $DB->Execute($query, array($user_id, $catpos, $catpos));

    // Delete record from database
    $DB->delete_records(PLUGIN_DB_TABLE_CATEGORY, 
                        array('id'=>$category_id));
    // Update assigned courses
    $assignments = $DB->get_records(PLUGIN_DB_TABLE_COURSETOCAT, 
                                    array('catid' => $category_id));
    foreach ($assignments as $assignment) {
        $assignment->catid = 0;
        $DB->update_record(PLUGIN_DB_TABLE_COURSETOCAT, $assignment);
    }
}

/**
 * Deletes a specific user-category.
 *
 * @param string category_id The id of the category that will be manipulated.
 * @param boolean visible "true" = visible, "false" = invisible.
 */
function set_category_visibility($category_id, $visible) {
    global $DB;
    // TODO: Batch update? Delegated transactions?
    // No such category in DB, print error
    if(!$cat = $DB->get_record(PLUGIN_DB_TABLE_CATEGORY, 
                               array('id' => $category_id))) {
    } 
    $cat->visible = $visible;
    $DB->update_record(PLUGIN_DB_TABLE_CATEGORY, $cat);
}

/**
 * Toggles a specific user-categories state.
 *
 * @param string category_id The id of the category that will be manipulated.
 * @param boolean opened "true" = opened, "false" = closed.
 */
function set_category_state($category_id, $opened) {
    global $DB;
    // TODO: Batch update? Delegated transactions?
    // No such category in DB, print error
    if(!$cat = $DB->get_record(PLUGIN_DB_TABLE_CATEGORY, 
                                array('id' => $category_id))) {
        return;
    } 
    $cat->opened = $opened;
    $DB->update_record(PLUGIN_DB_TABLE_CATEGORY, $cat);
}

/**
 * Moves a specific user-category.
 *
 * @param string user_id The user-id that owns the category.
 * @param string category_id The id of the category that will be manipulated.
 * @param boolean up "true": Move category one place up, 
 * "false": Move category one place down.
 */
function move_category_pos($user_id, $category_id, $up) {
    global $DB;
    // TODO: Batch update? Delegated transactions?
    // No such category in DB, print error
    if(!$cat = $DB->get_record(PLUGIN_DB_TABLE_CATEGORY, 
                               array('id' => $category_id))) {
        return;
    } 
    $oldpos = $cat->position;
    if ($up) {
        if ($cat->position == 0) {
            return;
        }
        $newpos = $cat->position - 1;
    } else {
        $query = 'SELECT MAX(position) from '.
                MOODLE_DB_PREFIX.PLUGIN_DB_TABLE_CATEGORY;
        $max_position = $DB->get_field_sql($query);
        if ($cat->position >= $max_position) {
            return;
        }
        $newpos = $cat->position + 1;
    }
    //Swap courses
    $DB->set_field(PLUGIN_DB_TABLE_CATEGORY, 'position', $oldpos, 
                   array('userid' => $user_id, 'position' => $newpos));
    $DB->set_field(PLUGIN_DB_TABLE_CATEGORY, 'position', $newpos, 
                   array('id' => $category_id));
}