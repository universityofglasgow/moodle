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
 * wiforms
 *
 * @package   block
 * @subpackage wiforms
 * @copyright 2013 Howard Miller
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_wiforms extends block_list {

    /** 
     * set the block name and version number
     */
    public function init() {
        $this->title = get_string('blockname', 'block_wiforms');
    }

    /*
     * This has a global config screen
     */
    public function has_config() {
        return true;
    }

    /*
     * Get the contents of the block
     */
    public function get_content() {
        global $USER, $CFG, $COURSE, $OUTPUT;

        if ($this->content != null) {
            return $this->content;
        }

        // Set up content.
        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->items = array();
        $this->content->icons = array();

        // Check capability.
        $context = context_course::instance($COURSE->id);
        if (!has_capability('block/wiforms:access', $context  )) {
            return $this->content;
        }

        // Course id.
        $id = $COURSE->id;

        // Add links to forms.
        $enlargelink = new moodle_url('/blocks/wiforms/email.php', array('id'=>$id, 'form'=>'enlargement'));
        $this->content->items[] = '<a href="'.$enlargelink.'">'.get_string('noticeenlargement', 'block_wiforms').'</a>';
        $this->content->icons[] = '<img src="'.$OUTPUT->pix_url('f/text').'" height="16" width="16" alt="icon" />';

        $formationlink = new moodle_url('/blocks/wiforms/email.php', array('id'=>$id, 'form'=>'formation'));
        $this->content->items[] = '<a href="'.$formationlink.'">'.get_string('noticeformation', 'block_wiforms').'</a>';
        $this->content->icons[] = '<img src="'.$OUTPUT->pix_url('f/text').'" height="16" width="16" alt="icon" />';

        $formationlink = new moodle_url('/blocks/wiforms/email.php', array('id'=>$id, 'form'=>'reformation'));
        $this->content->items[] = '<a href="'.$formationlink.'">'.get_string('noticereformation', 'block_wiforms').'</a>';
        $this->content->icons[] = '<img src="'.$OUTPUT->pix_url('f/text').'" height="16" width="16" alt="icon" />';

        $suspensionlink = new moodle_url('/blocks/wiforms/email.php', array('id'=>$id, 'form'=>'suspension'));
        $this->content->items[] = '<a href="'.$suspensionlink.'">'.get_string('noticesuspension', 'block_wiforms').'</a>';
        $this->content->icons[] = '<img src="'.$OUTPUT->pix_url('f/text').'" height="16" width="16" alt="icon" />';

        return $this->content;
    }

}
