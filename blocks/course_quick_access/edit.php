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
 * Creates the user-configuration page for the course-quick-access block.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

// Include plugin constants, but set the "Page Context" first, 
// to avoid false debug warning messages
$PAGE->set_context(context_system::instance());
require_once(dirname(__FILE__).'/consts.php');

// Include editing-formular classes
require_once(dirname(__FILE__).'/editlib_category.php');
require_once(dirname(__FILE__).'/editlib_category_form.php');
require_once(dirname(__FILE__).'/editlib_delete_category_form.php');
require_once(dirname(__FILE__).'/editlib_category_list_form.php');
require_once(dirname(__FILE__).'/editlib_category_course_list_form.php');

// Set PAGE
$url = new moodle_url(PLUGIN_CONFIG_URL);
//$return_to_url 
$PAGE->set_url($url);
require_login();
$userid = $USER->id;

// Get page parameters & clean them
$actionparam = optional_param(PLUGIN_CONFIG_ACTION_PARAM, null, PARAM_TEXT);
$catparam = optional_param(PLUGIN_CONFIG_CATEGORY_PARAM, 0, PARAM_INT);
$actionparam = clean_param($actionparam, PARAM_TEXT);
$catparam = clean_param($catparam, PARAM_INT);

// Initialize HTML form-parameters for later use
$show_edit_category_form = false;
$show_delete_category_form = false;
$edit_category_form = null;
$category_list_form = null;
$course_list_form = null;

// ===== Print edit category form =====
$edit_category_form = new edit_category_form(null, 
                                             array('id' => $catparam, 
                                                   'userid' => $userid));
if($actionparam == 'edit') {
    $show_edit_category_form = true;
    // Get category DB record & create HTML form
    $qacategory = $DB->get_record(PLUGIN_DB_TABLE_CATEGORY, 
                                  array('id'=>$catparam));
    // Category already in database, set data
    if($qacategory) {
        $data = array();
        $data['id'] = $qacategory->id;
        $data['newcategoryname'] = $qacategory->name;
        $data['visible'] = $qacategory->visible;
        $edit_category_form->set_data($data);
    }
}
// On save -> Store to DB
if (($data = $edit_category_form->get_data()) and 
        ($data->action == 'savesettings')) {
    $obj = new stdClass;
    $obj->userid = $userid;
    $obj->name = $data->newcategoryname;
    $obj->visible = $data->visible;
    $obj->position = 0;
    // New category enty
    if($data->id == 0) {
        // Move all existing user-categories 
        $query = 'UPDATE '.MOODLE_DB_PREFIX.PLUGIN_DB_TABLE_CATEGORY . 
               ' SET position = position + 1' .
               ' WHERE userid = ?';
        $DB->Execute($query, array($userid));
        $DB->insert_record(PLUGIN_DB_TABLE_CATEGORY, $obj, false);
    // Edit existing entry
    } else { 
        $obj->id = $data->id;
        $DB->update_record(PLUGIN_DB_TABLE_CATEGORY, $obj);
    }
}
// ===== Delete category form =====
$courseurl = new moodle_url(PLUGIN_CONFIG_URL, 
                            array('action'=>'deleteconfirm', 
                                  'usercategory'=>$catparam));
$delete_category_form = new delete_category_form($courseurl, 
                                                 array('id' => $catparam, 
                                                       'userid' => $userid));
if($actionparam == 'delete' and $catparam) {
    $show_delete_category_form = true;
    // Set form data
    $targetcategory = $DB->get_record(PLUGIN_DB_TABLE_CATEGORY, 
                                      array('id'=>$catparam));
    $data = array();
    $data['catname'] = '<b>'.$targetcategory->name.'</b>';
    $delete_category_form->set_data($data);

}
// On save -> Store results to DB
if (($data = $delete_category_form->get_data()) and 
        ($data->action == 'deleteconfirm')) {
    // Delete target category
    delete_category($userid, $data->id);
}
// ===== Perform category actions =====
if ($actionparam == 'hide') {
    set_category_visibility($catparam, false);
    $actionparam = null;
} elseif ($actionparam == 'show') {
    set_category_visibility($catparam, true);
    $actionparam = null;
} elseif ($actionparam == 'up') {
    move_category_pos($userid, $catparam, true);
    $actionparam = null;
} elseif ($actionparam == 'down') {
    move_category_pos($userid, $catparam, false);
    $actionparam = null;
}
// ===== Make DB queries =====
$courses = enrol_get_my_courses();
$qacategories = $DB->get_records(PLUGIN_DB_TABLE_CATEGORY, 
                                  array('userid' => $userid),
                                  $sort='position');
$qacatcourses = array();
foreach ($courses as $course) {
    $qacatcourses[] = $DB->get_record(PLUGIN_DB_TABLE_COURSETOCAT, 
                                      array('userid' => $userid,
                                     'courseid' => $course->id));
}
$numcourses = array();
foreach ($qacategories as $cat) {
    $numcourses[] = $DB->count_records(PLUGIN_DB_TABLE_COURSETOCAT, 
                                       array('userid' => $userid,
                                      'catid' => $cat->id));
}
// ===== Print category-list form =====
$createcaturl = $url.'?'.PLUGIN_CONFIG_ACTION_PARAM.'=edit';
$category_list_form = new edit_category_list_form($url, 
                               array('userid' => $userid,
                                     'categories' => $qacategories,
                                     'numcourses' => $numcourses,
                                     'createcaturl' => $createcaturl));
// ===== Print course-list form =====
$course_list_form = new edit_category_course_list_form($url, 
                           array('userid' => $userid,
                                 'courses' => $courses,
                                 'categories' => $qacategories,
                                 'cat_courses' => $qacatcourses));
// On "save"
if (($data = $course_list_form->get_data()) and 
    ($data->action == 'savesettings')) {
    foreach ($courses as $course) { // TODO: Batch update?
        $catid = $data->{'categoryselect'.$course->id};
        //if($catid != 0) {
        $objid = $DB->get_field(PLUGIN_DB_TABLE_COURSETOCAT, 'id', 
                            array('userid' => $userid,
                                  'courseid' => $course->id));
        $obj = new stdClass;
        $obj->userid = $userid;
        $obj->catid = $catid;
        $obj->courseid = $course->id;
        if(!$objid) { // Store new course-to-category assignment in the db
            $DB->insert_record(PLUGIN_DB_TABLE_COURSETOCAT, $obj, false);
        } else { // Update existing course-to-category assignment
            $obj->id = $objid;
            $DB->update_record(PLUGIN_DB_TABLE_COURSETOCAT, $obj);
        }
    }
    // Reload page
    header('Location: '.$url);
    exit;
}
// ===== Output HTML =====
// Print page header
$PAGE->set_title(get_string('configpagetitle', 'block_course_quick_access'));
$PAGE->set_heading(get_string('configpageheading', 
                              'block_course_quick_access'));
$PAGE->navbar->add(get_string('confignavigationtitle', 
                              'block_course_quick_access'));
// Set page layout to display blocks around the content
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('confignavigationtitle', // TODO
                                 'block_course_quick_access'));
if ($show_edit_category_form) {
    $edit_category_form->display();
} elseif ($show_delete_category_form) {
//if ($show_delete_category_form) {
    $delete_category_form->display();
} else {
    if ($category_list_form) {
        echo('<hr />');
        $category_list_form->display();
    }
    if ($qacategories) {
        echo('<hr />');
        if ($course_list_form) {
            $course_list_form->display();
        }
    }    

}
// Print page footer
echo $OUTPUT->footer();
