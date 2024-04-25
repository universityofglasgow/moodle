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
 * Course diagnostic renderer
 *
 * @package    report_coursediagnostic
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic\output;

use plugin_renderer_base;

class renderer extends plugin_renderer_base {
    /**
     * Defer to template.
     *
     * @param index_page $page
     *
     * @return string html for the page
     * @throws \moodle_exception
     */
    public function render_index_page($page): string {
        $data = $page->export_for_template($this);
        return parent::render_from_template('report_coursediagnostic/index_page', $data);
    }
}
