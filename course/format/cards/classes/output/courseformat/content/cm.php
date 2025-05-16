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
 * Class for rendering a course module within the format
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_cards\output\courseformat\content;

use core_courseformat\output\local\content\cm as cm_base;
use renderer_base;
use stdClass;

/**
 * Extended class for rendering a course module within the course
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cm extends cm_base {

    /**
     * Appends the indentation level of a course module to the template
     *
     * @param stdClass $data
     * @param array $haspartials
     * @param renderer_base $output
     * @return bool
     * @see cm_base::add_format_data
     */
    protected function add_format_data(stdClass &$data, array $haspartials, renderer_base $output): bool {
        $result = parent::add_format_data($data, $haspartials, $output);

        if (!empty($this->mod->indent)) {
            $data->indent = min($this->mod->indent, 7);
            $result = true;
        }

        return $result;
    }


}
