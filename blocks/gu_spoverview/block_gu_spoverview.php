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
 * Contains the class for the UofG Assessments Overview block.
 *
 * @package    block_gu_spoverview
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_gu_spoverview extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_gu_spoverview');
    }

    public function hide_header() {
        return TRUE;
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     * @todo Create a renderer for assessments block.
     *       See myoverview plugin for reference.
     */
    public function get_content() {
        if ($this->content !== null) {
          return $this->content;
        }

        $this->content         = new stdClass();
        $this->content->text   = get_string('sample_text', 'block_gu_spoverview');
     
        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }
}
