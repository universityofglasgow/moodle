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
 * Contains all functions required to create the content of the block.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/consts.php');
require_once($CFG->libdir . "/coursecatlib.php");

/**
 * Returns the DOM identifier for a given course name.
 *
 * @param string $element_name the elements name that should be converted to 
 * an identifier.
 * @return string The course identifier as a string.
 */
function get_element_identifier($element_name) {
    //return str_replace(' ', '_', strtolower(format_string($course_name)));
    return preg_replace(PLUGIN_LIST_ID_REG, '',  
                        strtolower(format_string($element_name)));
}

/**
 * Find the top level course categories
 * @return array of categories
 */
function get_top_level_categories() {
    global $DB;
    $categories = $DB->get_records('course_categories', array('parent' => 0));
    return $categories;    
}

/**
 * Returns an HTML list containing all top-level moodle course-categories.
 *
 * @return string An HTML list containing all top-level moodle 
 * course-categories or an empty string if the user there are no such 
 * categories.
 */
function get_moodle_category_list() {
    // Get top-level categories
    // If there are no categories, return an empty string
    if (!$categories = get_top_level_categories()) {
        return '';
    } else {
        // Get list-elements/links for each category
        $list = '';
        foreach ($categories as $category) {
            $name = format_string($category->name);
            $url = MOODLE_CATEGORY_URL.'?id='.$category->id;
            $icon = '<img src="'.MOODLE_ICON_NAVIGATION.'" class="icon"'.
                    ' style="position: absolute; font-align: middle;"/>';
            $link = "<a title=\"{$name }\" href=\"{$url}\">{$icon}"."
                    <div style=\"padding-left: 20px;\">{$name}</div></a>";
            // Create html list entry element and append it to the content
            $entryparams = array(
                'id' => get_element_identifier($name),
                'class' => 'qa-listentry qa-category'
            );
            $entry = html_writer::tag('li', $link, $entryparams);
            $list .= $entry;
        }
        // Create HTML-list-tag and return it
        $listparams = array(
            'id' => 'qa-blocklist',
            'class' => 'qa-blocklist unlist'
        );
        return html_writer::tag('ul', $list, $listparams);
    }
}

/**
 * Returns an HTML list containing all courses the user is enrolled in.
 *
 * @return string An HTML list containing all courses the user is enrolled in.
 * If the user is not enrolled in any courses "get_moodle_category_list()" is 
 * returned. 
 */
function get_moodle_course_list() {
    // Get courses the user is enrolled in
    // User is not enroled in any course, return moodle category list
    if (!$courses = enrol_get_my_courses(NULL, 'visible DESC, fullname ASC')) {
        return get_moodle_category_list();
    } else {
        // Get list-elements/links for enrolled course
        $list = '';
        foreach ($courses as $course) {
            $name = format_string($course->fullname);
            $shortname = format_string($course->shortname);
            $url = MOODLE_COURSE_URL.'?id='.$course->id;
            $icon = '<img src="'.MOODLE_ICON_COURSE_SRC.'" class="icon" '.
                    ' style="position: absolute; font-align: middle;"/>';
            $linkcls = $course->visible ? "" : "class=\"dimmed dimmed_text\"";
            $link = "<a title=\"{$shortname}\" href=\"{$url}\"".
                    " {$linkcls}>{$icon}".
                    "<div style=\"padding-left: 20px;\">{$name}</div></a>";

            // Create html list entry element and append it to the content
            $entryparams = array(
                'id' => get_element_identifier($name),
                'class' => 'qa-listentry qa-course'
            );
            $entry = html_writer::tag('li', $link, $entryparams);
            $list .= $entry;
        }
        // Create HTML-list-tag and return it
        $listparams = array(
            'id' => 'qa-blocklist',
            'class' => 'qa-blocklist unlist'
        );
        return html_writer::tag('ul', $list, $listparams);
    }  
}

/**
 * Returns an HTML list containing all courses that were assigned to specific
 * user-category.
 *
 * @param string $user_id The id of the currently logged in user.
 * @param string $cat_id The id of user-category
 * @return string An HTML list containing all courses that were assigned to 
 * specific user-category. If there are no courses for the specified category,
 * an empty list is returned.
 */
function get_categorised_courses($userid, $catid, $visible=true) {
    global $DB;
    // Query the DB courses that the user is enrolled in, and have are 
    // assigned to the given user-category
    $query = 'SELECT '.MOODLE_DB_PREFIX.'course.id, visible, shortname, fullname FROM '.MOODLE_DB_PREFIX.PLUGIN_DB_TABLE_COURSETOCAT.
    ', '.MOODLE_DB_PREFIX.MOODLE_DB_TABLE_COURSE.
    ' WHERE ' . MOODLE_DB_PREFIX.PLUGIN_DB_TABLE_COURSETOCAT.'.userid = ?'.
    ' AND '.MOODLE_DB_PREFIX.PLUGIN_DB_TABLE_COURSETOCAT.'.catid = ?'.
    ' AND '.MOODLE_DB_PREFIX.PLUGIN_DB_TABLE_COURSETOCAT.'.courseid = '.
    MOODLE_DB_PREFIX.MOODLE_DB_TABLE_COURSE.'.id'.
    ' ORDER BY '.MOODLE_DB_PREFIX.MOODLE_DB_TABLE_COURSE.'.fullname ASC';
    // There are no courses assigned to that category, return empty string
    if (!$courses = $DB->get_records_sql($query, array($userid, $catid))) {
        return ''; 
    }
    // Get list-elements/links for enrolled course
    $element_list = '';
    $icon = '<img src="'.MOODLE_ICON_COURSE_SRC.'" class="icon" '.
            'style="position: absolute; font-align: middle;"/>';
    foreach ($courses as $course) {
        $name = format_string($course->fullname);
        $shortname = format_string($course->shortname);
        $url = MOODLE_COURSE_URL.'?id='.$course->id;

        $linkclasses = $course->visible ? "" : "class=\"dimmed dimmed_text\"";
        $link = "<a title=\"{$shortname}\" href=\"{$url}\" {$linkclasses}> ".
                "{$icon}<div style=\"padding-left: 20px;\">{$name}</div></a>";
        // Create HTML-list-entry-tag and append it to the content
        $entryparams = array(
            'id' => get_element_identifier($name),
            'class' => 'qa-listentry qa-course'
        );
        $entry = html_writer::tag('li', $link, $entryparams);
        $element_list .= $entry;
    } 
    // Create HTML-list-tag and return it
    $list_params = array(
        'id' => 'qa-courselist',
        'class' => 'qa-courselist unlist',
        'style' => 'padding-left: 18px;'
    );
    if ($visible == false) {
            $list_params['class'] .= ' qa-hidden';
    }
    return html_writer::tag('ul', $element_list, $list_params);
}

/**
 * Returns an HTML list containing all created user-categories for a specific 
 * user and their corresponding courses.
 *
 * @param string $user_id The id of the currently logged in user.
 * @return string An HTML list containing all created user-categories for a 
 * specific user and their corresponding courses.
 */
function get_custom_course_list($userid) {
    global $CFG, $DB;
    // Get all user-created categories created by the given user
    // Return the moodle course-category list if the user has not created any 
    // user-categories yet.
    if (!($categories = $DB->get_records(PLUGIN_DB_TABLE_CATEGORY, 
                                         array('userid' => $userid), 
                                         $sort='position'))) {
        return get_moodle_course_list();
    }
    // Append each user-category to the element list
    $list = '';
    foreach ($categories as $cat) {
        // Get courses that were assigned to that category by the user
        $courselist = get_categorised_courses($userid, $cat->id, $cat->opened);
        // Set status (opened/closed) icon visibility
        if ($cat->opened == true) {
            $iconopen = '<img src="'.PLUGIN_ICON_USERFOLDEROPENED_SRC.
                    '" class="icon qa-icon qa-icon-opened" alt="" />';
            $iconclosed = '<img src="'.PLUGIN_ICON_USERFOLDERCLOSED_SRC.
                    '" class="icon qa-icon qa-icon-closed qa-hidden"'.
                    ' style="display: none;"/>';
            $toggleclasses = 'qa-toggle qa-toggle-opened';
            //$toggletag = '<div id="qa-toggle-'.$cat->id.
            //        '" class="qa-toggle qa-toggle-opened">';
        } else {
            $iconopen = '<img src="'.PLUGIN_ICON_USERFOLDEROPENED_SRC.
                    '" class="icon qa-icon qa-icon-opened qa-hidden"'.
                    ' style="display: none;"/>';
            $iconclosed = '<img src="'.PLUGIN_ICON_USERFOLDERCLOSED_SRC.
                    '" class="icon qa-icon qa-icon-closed"/>';
            $toggleclasses = 'qa-toggle qa-toggle-closed';
            //$toggletag = '<div id="qa-toggle-'.$cat->id.
            //        '" class="qa-toggle qa-toggle-closed">';
        }

        // Create list entry and return it
        $listentryparams = array(
            'id' => get_element_identifier($cat->name),
            'class' => 'qa-listentry qa-usercategory'
        );
        // Hide category if it is invisible
        if ($cat->visible == false or $courselist == '') {
            $listentryparams['class'] .= ' qa-hidden';
            $listentryparams['style'] = 'display: none';
            $toggletag = '<div id="qa-toggle-'.$cat->id.
                    '" class="'.$toggleclasses.' dimmed dimmed_text">';
        } else {
            $toggletag = '<div id="qa-toggle-'.$cat->id.
                    '" class="'.$toggleclasses.'">';
        }
        $listentry = html_writer::tag('li', 
                                      $toggletag.$iconopen.$iconclosed.
                                      $cat->name.'</div>'.$courselist,
                                      $listentryparams);
        $list .= $listentry;
    }
    // Append all un-assigned courses
    $name = get_string('coursenotcategorized', 
                           'block_course_quick_access');
    $courselist = get_categorised_courses($userid, 0);
    // List un-assinged category, only when it has child elements
    if ($courselist and count($courselist) > 0) {
        $listentryparams = array(
            'id' => get_element_identifier(get_string('coursenotcategorized', 
                                                'block_course_quick_access')),
            //'class' => 'qa-listentry qa-usercategory qa-hidden',
            //'style' => 'display: none'
            'class' => 'qa-listentry qa-usercategory'
        );
        $icon = '<img src="'.MOODLE_ICON_FOLDEROPENED_SRC.
                '" class="icon" alt="" />';
        $iconopen = '<img src="'.MOODLE_ICON_FOLDEROPENED_SRC.
                '" class="icon qa-icon qa-icon-opened" alt="" />';
        $iconclosed = '<img src="'.MOODLE_ICON_FOLDERCLOSED_SRC.
                '" class="icon qa-icon qa-icon-closed qa-hidden"'.
                ' style="display: none;"/>';
        $listentry = html_writer::tag('li', 
                                      '<div class="qa-toggle">'.
                                      $iconopen.$iconclosed.
                                      $name.'</div>'.$courselist, 
                                      $listentryparams);
        $list .= $listentry;
    }
    // No content => return empty string
    if($list == '') {
        return $list;
    } else {
        // Create HTML-list-tag and return it
        $listparams = array(
            'id' => 'qa-blocklist',
            'class' => 'qa-blocklist unlist'
        );
        return html_writer::tag('ul', $list, $listparams); 
    }

}

/**
 * Fixes the course-to-category assignments for a specific user.
 *
 * Makes sure all newly enrolled-in courses are stored in the "not-assigned"
 * category and that un-enrolled courses are removed.
 *
 * @param string $user_id The id of the currently logged in user.
 */
function fix_course_assignments($userid) {
    global $CFG, $DB;
    $courses = enrol_get_my_courses();
    $courseassignment = array();
    foreach ($courses as $course) {
        $catid = $DB->get_field(PLUGIN_DB_TABLE_COURSETOCAT, 'catid',
                                             array('userid' => $userid,
                                                   'courseid' => $course->id));
        if (!$catid) {
            $courseassignment[] = 0;
        } else {
            $courseassignment[] = $catid;
        }
    }
    $DB->delete_records(PLUGIN_DB_TABLE_COURSETOCAT, 
                        array('userid' => $userid));
    $i = 0;
    foreach ($courses as $course) {
        $obj = new stdClass;
        $obj->userid = $userid;
        $obj->courseid = $course->id;
        $obj->catid = $courseassignment[$i];
        $DB->insert_record(PLUGIN_DB_TABLE_COURSETOCAT, $obj, false);
        $i++;
    }
}

/**
 * Returns a HTML-div containing a list of elements (courses and categories)
 * as a string. TODO
 *
 * @return string An HTML div element as a string.
 * 
 */
function get_element_list() {
    global $CFG, $USER;
    $headline = '';
    $list = ''; 
    // User is not logged in, return top-level moodle category list
    if (!isloggedin() or empty($USER->id)) {
        $list = get_moodle_category_list();
    } else {
        fix_course_assignments($USER->id);
        $list = get_custom_course_list($USER->id);
        // Element list still empty, show enroled course list
        if ($list == '') {
            $headline = get_string('usernotenrolledincourses', 
                                   'block_course_quick_access');
            $list = get_moodle_course_list();
        }
    }
    // Create headline element
    $headlineparams = array(
        'id' => 'qa-blockcontenthead',
        'class' => 'qa-blockcontenthead'
    );
    if ($headline == '') {
        $headlineparams['class'] .= ' qa-hidden';
        $headlineparams['style'] = 'display: none';
    }
    $head = html_writer::tag('p', $headline, $headlineparams);
    // Return div element
    $contentparams = array(
        'id' => 'qa-blockcontent',
        'class' => 'qa-blockcontent'
    );
    return html_writer::tag('div', $head.$list, $contentparams);
}

