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
 * Course-Quick-Access block main file.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/consts.php');
require_once(dirname(__FILE__).'/blocklib_content_search.php');
require_once(dirname(__FILE__).'/blocklib_content_list.php');
require_once(dirname(__FILE__).'/blocklib_footer.php');

/**
 *
 */
class block_course_quick_access extends block_base {

    /**
    * Assign initial plugin properties.
    */
    function init() {
        $this->title = get_string('pluginname', 'block_course_quick_access');
        $this->content_type = BLOCK_TYPE_TEXT;
    }

    /**
    * This method returns a boolean value that denotes whether the block 
    * wants to present a configuration interface to site admins or not.
    *
    * @return bool Returns true
    */
    function has_config() {
        return true;
    }
    
    /*
    * This method returns a boolean value, indicating whether the block is
    * allowed to have multiple instances in the same page or not.
    *
    * @return bool Returns false
    */
    function instance_allow_multiple() {
        return false;
    }

    /**
     * Set the applicable formats for this plugin.
     *
     * @return array Array of booleans for each format that is allowed or not
     */
    function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Determins wether the plugin can be docked to the sidebar.
     *
     * @return boolean Returns true
     */
    function instance_can_be_docked() {
        return true;
    }

    /**
     * Loads the required javascript files.
     */
    function load_required_javascript() {
        global $USER;
        $loggedin = 'false';
        $js_module = array(
            'name' => 'block_course_quick_access',
            'fullpath' => PLUGIN_JS_PATH.'module.js',
            'requires' => array('base', 'io', 'node', 'json')
        );
        if (isloggedin()) {
            $loggedin = 'true';
        }
        $js_init_params = array(
            'categorytoggleurl' => PLUGIN_TOOGLE_CAT_URL,
            'hiddencourses' => get_string('hiddencourses', 
                                          'block_course_quick_access'),
            'hiddencoursesnotloggedin' => get_string('hiddencoursesnotloggedin', 
                                         'block_course_quick_access'),
            'loggedin' => $loggedin
        );
        $this->page->requires->js_init_call(
                                    'M.block_course_quick_access.init', 
                                    $js_init_params,
                                    false,
                                    $js_module
        );
    }
    
    /**
    * Populates the "$this->content" & "content->footer" variable of the block.
    */
    function get_content() {

        // Content has been computed before -> return content
        if ($this->content !== null) {
            return $this->content;
        }
    
        // Get admin-configuration settings
        $use_javascript = get_config('course_quick_access', 'Use_Javascript');
        $show_search_submit_button = get_config('course_quick_access', 
                                                'Show_Search_Submit_Button');
        $search_submit_2_line = get_config('course_quick_access', 
                                           'Search_Submit_Button_2_Line');

        // Create new block content (text & footer) --
        $this->content = new stdClass;
        $this->content->text = get_search_form($show_search_submit_button, 
                                               $search_submit_2_line);
        $this->content->text .= '<br />'.get_element_list();
        $this->content->footer = get_footer();

        if($use_javascript) {
            $this->load_required_javascript();
        }

        return $this->content;
    }
    
}