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
 * Contains the class for the UofG Assessments Details block.
 *
 * @package    block_gu_spdetails
 * @copyright  2021 Accenture
 * @author     Franco Louie Magpusao, Jose Maria Abreu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

class block_gu_spdetails extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_gu_spdetails');
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $USER, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        if (!empty(assessments_details::return_enrolledcourses($USER->id))) {
            $link = new moodle_url('/blocks/gu_spdetails/index.php');
            $this->content->text = $OUTPUT->render_from_template('block_gu_spdetails/block', [
                'link' => $link,
            ]);
        }

        return $this->content;
    }

    public function has_config() {
        return true;
    }

}
