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
 * Contains the formular-class for the user-category listing.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/consts.php');
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/formslib.php');

class edit_category_list_form extends moodleform {

    function definition() {
        $mform = $this->_form;
        $custom = $this->_customdata;
        // Get form custom data
        $userid = $custom['userid'];
        $categories = $custom['categories'];
        $numcourses = $custom['numcourses'];
        $createcaturl = $custom['createcaturl'];
        // Add hidden fields
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT); 
        $mform->addElement('hidden', 'action', 'createcategory');
        $mform->setType('action', PARAM_TEXT);
        // Add form header
        $mform->addElement('header', 'categorylistheadline',
            get_string('categorylistheadline', 'block_course_quick_access'));
        // Add form elements
        $mform->addElement('html', '<br>');
        // If no user-category is present, show an info-message
        if (!$categories) {
            $mform->addElement('html', '<p>' .
                '<img src="'.MOODLE_ICON_INFO_SRC . 
                '" class="icon" alt="" /> ' .
                get_string('nocatcreatedtext', 'block_course_quick_access').
                '</p>');     
        // Create a table containing all user-categories & quick-edit options
        } else {
            $mform->addElement('html', '<p>' .
                '<img src="'.MOODLE_ICON_INFO_SRC . 
                '" class="icon" alt="" /> ' .
                get_string('categorylistdesc', 'block_course_quick_access').
                '</p>');
            $mform->addElement('html', 
                        $this->get_category_table($categories, $numcourses));
            // Create more categories-info-text
            $mform->addElement('html', '<br>');
            $mform->addElement('html', '<p>' .
                '<img src="'.MOODLE_ICON_INFO_SRC . 
                '" class="icon" alt="" /> ' .
                get_string('createmorecattext', 'block_course_quick_access').
                '</p>');   
        }
        $createcategorylink = '<a href="'.$createcaturl.'">'.
                              '<img src="'.PLUGIN_ICON_USERFOLDEROPENED_SRC . 
                              '" class="icon" alt="" />'.
                               get_string('createnewcategory', 
                                          'block_course_quick_access').
                               '</a>';
        $mform->addElement('html', $createcategorylink); 
    }

    /**
     * Creates a table containing all user-categories & quick-edit options
     *
     * @param array $categories An array containing all user-categories
     * to list.
     * @param array $categories An array containing the course count for each
     * category.
     * @return string HTML table element as a string.
     */
    protected function get_category_table(array $categories, 
                                          array $numcourses) {
        // Set icon HTML tags
        $editicon = '<img src="'.MOODLE_ICON_EDIT_SRC.
               '" class="icon" alt="' . 
               get_string('cateditlinktext', 'block_course_quick_access') . 
               '" />';
        $deleteicon = '<img src="'.MOODLE_ICON_DELETE_SRC.
               '" class="icon" alt="' . 
               get_string('catdeletelinktext', 'block_course_quick_access') . 
               '" />';
        $hideicon = '<img src="'.MOODLE_ICON_HIDE_SRC.
               '" class="icon" alt="' . 
               get_string('cathidelinktext', 'block_course_quick_access') . 
               '" />';
        $showicon = '<img src="'.MOODLE_ICON_SHOW_SRC.
               '" class="icon" alt="' . 
               get_string('catshowlinktext', 'block_course_quick_access') . 
               '" />';
        $upicon = '<img src="'.MOODLE_ICON_UP_SRC.
               '" class="icon" alt="' . 
               get_string('catmoveup', 'block_course_quick_access') .
               '" />';
        $downicon = '<img src="'.MOODLE_ICON_DOWN_SRC.
               '" class="icon" alt="' . 
               get_string('catmovedown', 'block_course_quick_access') .
               '" />';
        // Create category HTML table tag
        $table = new html_table();
        // Set table headlines
        $table->head = array(
            get_string('categorynameheadline', 'block_course_quick_access'),
            get_string('courseamountheadline', 'block_course_quick_access'),
            get_string('categoryeditheadline', 'block_course_quick_access'),
            get_string('categorymoveheadline', 'block_course_quick_access')
        );
        // Fill table with category-data
        $table->data = array();
        $i = 0; 
        foreach ($categories as $cat) {
            // Create custom course-editing-icons
            $editlink = "<a href=\"" ."?".PLUGIN_CONFIG_CATEGORY_PARAM . 
                    "=".$cat->id."&".PLUGIN_CONFIG_ACTION_PARAM . 
                    "=edit\">".$editicon.'</a>';
            $deletelink = "<a href=\"?".PLUGIN_CONFIG_CATEGORY_PARAM . 
                    "=".$cat->id."&".PLUGIN_CONFIG_ACTION_PARAM . 
                    "=delete\">".$deleteicon. "</a>";
            $hidelink = "<a href=\"?".PLUGIN_CONFIG_CATEGORY_PARAM . 
                    "=".$cat->id."&".PLUGIN_CONFIG_ACTION_PARAM . 
                    "=hide\">".$hideicon. "</a>";
            $showlink = "<a href=\"?".PLUGIN_CONFIG_CATEGORY_PARAM . 
                    "=".$cat->id."&".PLUGIN_CONFIG_ACTION_PARAM . 
                    "=show\">".$showicon. "</a>";
            if ($cat->visible) {
                $visiblecatlink = $hidelink;
            } else {
                $visiblecatlink = $showlink;
            }
            $moveuplink = "<a href=\"?".PLUGIN_CONFIG_CATEGORY_PARAM . 
                    "=".$cat->id."&".PLUGIN_CONFIG_ACTION_PARAM . 
                    "=up\">".$upicon .'</a>';
            $movedownlink = "<a href=\"?".PLUGIN_CONFIG_CATEGORY_PARAM . 
                    "=".$cat->id."&".PLUGIN_CONFIG_ACTION_PARAM . 
                    "=down\">".$downicon .'</a>';
            $table->data[] = array(
                $cat->name,
                $numcourses[$i], //$numcourses[]'',
                ($editlink.' '.$visiblecatlink.' '.$deletelink) ,
                ($moveuplink.' '.$movedownlink)
            );
            $i++;
        }
        // Create and return table
        $table->attributes  = array(
            'class' => 'generaltable',
            'style' => 'text-align: center;'
        );
        return html_writer::table($table);
    }

}
