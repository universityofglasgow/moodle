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
 * Renderer for UofG Hillhead 4.0 theme features
 *
 * @package
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_hillhead40_core_renderer extends core_renderer {

    /**
     * @param custom_menu $menu
     * @return mixed
     */
    protected function render_custom_menu(custom_menu $menu) {
        if (isloggedin()) {
            $usesaccessibilitytools = get_user_preferences('theme_hillhead40_accessibility', false);
            $varg = 'clear';
            $spantext = 'Hide';
            if ($usesaccessibilitytools === false) {
                $varg = 'on';
                $spantext = 'Show';
            }
            $branchlabel = $spantext . ' Accessibility Tools';
            $script = '/theme/hillhead40/accessibility.php';
            $args = '?o=theme_hillhead_accessibility&v=' . $varg;
            $branchurl = new moodle_url($CFG->wwwroot . $script . $args);
            $branchtitle = $branchlabel;
            $branchsort  = 10000;
            $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
        }

        return parent::render_custom_menu($menu);
    }

    /**
     * @return bool
     */
    public function firstview_fakeblocks() {
    }

}

/** @var stdClass $CFG */
global $CFG;
include_once($CFG->dirroot . "/course/renderer.php");
include_once($CFG->dirroot . "/course/classes/management_renderer.php");

/**
 *
 * This core course renderer has been overwritten to inject additional links to /local/template/index.php at all locations where 'Add a new course' text exists.
 *
 * Renderer for use with the course section and all the goodness that falls
 * within it.
 *
 * This renderer should contain methods useful to courses, and categories.
 *
 * @package   theme_hillhead40
 * @copyright 2023 Glasgow University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The core course renderer
 *
 * Can be retrieved with the following:
 * $renderer = $PAGE->get_renderer('core','course');
 */
class theme_hillhead40_core_course_renderer extends core_course_renderer {

    /**
     *
     * Override core course renderer function to add additional button (if /local/template is present)
     * The original function should be monitored for changes.
     *
     * Renders HTML to display particular course category - list of it's subcategories and courses
     *
     * Invoked from /course/index.php
     *
     * @param int|stdClass|core_course_category $category
     */
    public function course_category($category) {
        if (!theme_hillhead40_exists_template_plugin()) {
            return parent::course_category($category);
        }
        $addnewcoursecategoryactionbar = get_config('local_template', 'addnewcoursecategoryactionbar');
        if (!$addnewcoursecategoryactionbar) {
            return parent::course_category($category);
        }

        global $CFG;
        $usertop = core_course_category::user_top();
        if (empty($category)) {
            $coursecat = $usertop;
        } else if (is_object($category) && $category instanceof core_course_category) {
            $coursecat = $category;
        } else {
            $coursecat = core_course_category::get(is_object($category) ? $category->id : $category);
        }
        $site = get_site();
        $actionbar = new \theme_hillhead40\output\theme_hillhead40_category_action_bar($this->page, $coursecat);
        $output = $this->render_from_template('core_course/category_actionbar', $actionbar->export_for_template($this));

        if (core_course_category::is_simple_site()) {
            // There is only one category in the system, do not display link to it.
            $strfulllistofcourses = get_string('fulllistofcourses');
            $this->page->set_title("$site->shortname: $strfulllistofcourses");
        } else if (!$coursecat->id || !$coursecat->is_uservisible()) {
            $strcategories = get_string('categories');
            $this->page->set_title("$site->shortname: $strcategories");
        } else {
            $strfulllistofcourses = get_string('fulllistofcourses');
            $this->page->set_title("$site->shortname: $strfulllistofcourses");
        }

        // Print current category description
        $chelper = new coursecat_helper();
        if ($description = $chelper->get_category_formatted_description($coursecat)) {
            $output .= $this->box($description, array('class' => 'generalbox info'));
        }

        // Prepare parameters for courses and categories lists in the tree
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_AUTO)
            ->set_attributes(array('class' => 'category-browse category-browse-'.$coursecat->id));

        $coursedisplayoptions = array();
        $catdisplayoptions = array();
        $browse = optional_param('browse', null, PARAM_ALPHA);
        $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $baseurl = new moodle_url('/course/index.php');
        if ($coursecat->id) {
            $baseurl->param('categoryid', $coursecat->id);
        }
        if ($perpage != $CFG->coursesperpage) {
            $baseurl->param('perpage', $perpage);
        }
        $coursedisplayoptions['limit'] = $perpage;
        $catdisplayoptions['limit'] = $perpage;
        if ($browse === 'courses' || !$coursecat->get_children_count()) {
            $coursedisplayoptions['offset'] = $page * $perpage;
            $coursedisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $catdisplayoptions['nodisplay'] = true;
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $catdisplayoptions['viewmoretext'] = new lang_string('viewallsubcategories');
        } else if ($browse === 'categories' || !$coursecat->get_courses_count()) {
            $coursedisplayoptions['nodisplay'] = true;
            $catdisplayoptions['offset'] = $page * $perpage;
            $catdisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $coursedisplayoptions['viewmoretext'] = new lang_string('viewallcourses');
        } else {
            // we have a category that has both subcategories and courses, display pagination separately
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses', 'page' => 1));
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories', 'page' => 1));
        }
        $chelper->set_courses_display_options($coursedisplayoptions)->set_categories_display_options($catdisplayoptions);

        // Display course category tree.
        $output .= $this->coursecat_tree($chelper, $coursecat);

        return $output;
    }

}


/**
 *
 _ This core course management renderer has been overwritten to inject additional links to /local/template/index.php at all locations where 'Create new course' text exists.
 *
 * Override core course management renderer to add additional button (if /local/template is present)
 * The original function should be monitored for changes.
 *
 * Main renderer for the course management pages.
 *
 * @package   theme_hillhead40
 * @copyright 2023 Glasgow University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_hillhead40_core_course_management_renderer extends core_course_management_renderer {

    /**
     *
     * Override core course management function to add additional button (if /local/template is present)
     * The original function should be monitored for changes.
     *
     * Renderers actions for the course listing.
     *
     * Not to be confused with course_listitem_actions which renderers the actions for individual courses.
     *
     * @param core_course_category $category
     * @param core_course_list_element $course The currently selected course.
     * @param int $perpage
     * @return string
     */
    public function course_listing_actions(core_course_category $category, core_course_list_element $course = null, $perpage = 20) {
        if (!theme_hillhead40_exists_template_plugin()) {
            return parent::course_listing_actions($category, $course, $perpage);
        }
        $addnewcoursecoursemanagement = get_config('local_template', 'addnewcoursecoursemanagement');
        if (!$addnewcoursecoursemanagement) {
            return parent::course_listing_actions($category, $course, $perpage);
        }

        $actions = array();
        if ($category->can_create_course()) {
            $url = new moodle_url('/course/edit.php', array('category' => $category->id, 'returnto' => 'catmanage'));
            $actions[] = html_writer::link($url, get_string('createnewcourse'), array('class' => 'btn btn-secondary'));

            $url = new moodle_url('/local/template/index.php', array('category' => $category->id, 'returnto' => 'catmanage'));
            $actions[] = html_writer::link($url, get_string('addnewcourseviatemplate', 'local_template'), array('class' => 'btn btn-secondary'));
        }
        if ($category->can_request_course()) {
            // Request a new course.
            $url = new moodle_url('/course/request.php', array('category' => $category->id, 'return' => 'management'));
            $actions[] = html_writer::link($url, get_string('requestcourse'));
        }
        if ($category->can_resort_courses()) {
            $params = $this->page->url->params();
            $params['action'] = 'resortcourses';
            $params['sesskey'] = sesskey();
            $baseurl = new moodle_url('/course/management.php', $params);
            $fullnameurl = new moodle_url($baseurl, array('resort' => 'fullname'));
            $fullnameurldesc = new moodle_url($baseurl, array('resort' => 'fullnamedesc'));
            $shortnameurl = new moodle_url($baseurl, array('resort' => 'shortname'));
            $shortnameurldesc = new moodle_url($baseurl, array('resort' => 'shortnamedesc'));
            $idnumberurl = new moodle_url($baseurl, array('resort' => 'idnumber'));
            $idnumberdescurl = new moodle_url($baseurl, array('resort' => 'idnumberdesc'));
            $timecreatedurl = new moodle_url($baseurl, array('resort' => 'timecreated'));
            $timecreateddescurl = new moodle_url($baseurl, array('resort' => 'timecreateddesc'));
            $menu = new action_menu(array(
                new action_menu_link_secondary($fullnameurl,
                    null,
                    get_string('sortbyx', 'moodle', get_string('fullnamecourse'))),
                new action_menu_link_secondary($fullnameurldesc,
                    null,
                    get_string('sortbyxreverse', 'moodle', get_string('fullnamecourse'))),
                new action_menu_link_secondary($shortnameurl,
                    null,
                    get_string('sortbyx', 'moodle', get_string('shortnamecourse'))),
                new action_menu_link_secondary($shortnameurldesc,
                    null,
                    get_string('sortbyxreverse', 'moodle', get_string('shortnamecourse'))),
                new action_menu_link_secondary($idnumberurl,
                    null,
                    get_string('sortbyx', 'moodle', get_string('idnumbercourse'))),
                new action_menu_link_secondary($idnumberdescurl,
                    null,
                    get_string('sortbyxreverse', 'moodle', get_string('idnumbercourse'))),
                new action_menu_link_secondary($timecreatedurl,
                    null,
                    get_string('sortbyx', 'moodle', get_string('timecreatedcourse'))),
                new action_menu_link_secondary($timecreateddescurl,
                    null,
                    get_string('sortbyxreverse', 'moodle', get_string('timecreatedcourse')))
            ));
            $menu->set_menu_trigger(get_string('resortcourses'));
            $actions[] = $this->render($menu);
        }
        $strall = get_string('all');
        $menu = new action_menu(array(
            new action_menu_link_secondary(new moodle_url($this->page->url, array('perpage' => 5)), null, 5),
            new action_menu_link_secondary(new moodle_url($this->page->url, array('perpage' => 10)), null, 10),
            new action_menu_link_secondary(new moodle_url($this->page->url, array('perpage' => 20)), null, 20),
            new action_menu_link_secondary(new moodle_url($this->page->url, array('perpage' => 50)), null, 50),
            new action_menu_link_secondary(new moodle_url($this->page->url, array('perpage' => 100)), null, 100),
            new action_menu_link_secondary(new moodle_url($this->page->url, array('perpage' => 999)), null, $strall),
        ));
        if ((int)$perpage === 999) {
            $perpage = $strall;
        }
        $menu->attributes['class'] .= ' courses-per-page';
        $menu->set_menu_trigger(get_string('perpagea', 'moodle', $perpage));
        $actions[] = $this->render($menu);
        return html_writer::div(join(' ', $actions), 'listing-actions course-listing-actions');
    }


}
