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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * kuraCloud block
 *
 * @copyright 2017 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_kuracloud extends block_base {

    /**
     * Init the block
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_kuracloud');
    }

    /**
     * Get block content
     *
     * @return string|boolean
     */
    public function get_content() {
        // Moodle calls get_content several times for each block, so just compute once.
        if ($this->content !== null) {
            return $this->content;
        }

        global $COURSE, $PAGE;

        if (!isset($COURSE->id)) {
            return '';
        }

        $output = $PAGE->get_renderer('block_kuracloud');

        $mapping = \block_kuracloud\courses::get_mapping($COURSE->id);

        $blockcontent = new \block_kuracloud\output\block_content($mapping);

        $this->content = new \stdClass;
        $this->content->text = $output->render($blockcontent);

        return true;

    }

    /**
     * Only once instance per context
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Only used on course page
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'course' => true,
        );
    }

    /**
     * Has config
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * User can only add block if there's a valid endpoint
     *
     * @param page $PAGE Moodle page object
     * @return boolean
     */
    public function user_can_addto($PAGE) {

        if (empty(\block_kuracloud\endpoints::get_all())) {
            return false;
        }

        return parent::user_can_addto($PAGE);
    }

    /**
     * Un-map all courses and remove LMS enabled flag prior to block removal
     *
     * @return void
     */
    public function before_delete() {

        $courses = new \block_kuracloud\courses();

        $mappings = $courses->get_all_mapped();

        foreach ($mappings as $mapping) {
            $courses->delete_mapping($mapping);
        }

        return true;
    }

    /**
     * Un-map course on instance_delete
     *
     * @return void
     */
    public function instance_delete() {
        global $COURSE;
        if ($mapping = \block_kuracloud\courses::get_mapping($COURSE->id)) {
            $courses = new \block_kuracloud\courses();
            $courses->delete_mapping($mapping);
        }
        return true;
    }
}
