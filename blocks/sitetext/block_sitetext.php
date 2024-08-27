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

use core_external\util as external_util;

/**
 * Form for editing HTML block instances.
 *
 * @package   block_sitetext
 * @copyright Howard Miller
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_sitetext extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_sitetext');
    }

    function has_config() {
        return true;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function instance_allow_multiple() {
        return false;
    }

    function specialization() {
        $title = get_config('block_sitetext', 'title');
        $this->title = $title;
    }

    function get_content() {
        global $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        // Get the content
        $sitecontent = get_config('block_sitetext', 'content');

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        $filteropt->noclean = true;

        $this->content = new stdClass;
        $this->content->text = format_text($sitecontent, FORMAT_HTML, $filteropt);

        return $this->content;
    }


}
