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

use format_cards\section_break;

/**
 * Testing data generator.
 *
 * @package    format_cards
 * @copyright  2024 University of Essex
 * @author     John Maydew {@email jdmayd@essex.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class format_cards_generator extends testing_module_generator {

    /**
     * Creates a new section break
     *
     * @param array $data
     * @return void
     */
    public function create_break(array $data): void {

        if (!isset($data['courseid'])) {
            throw new coding_exception("Missing required property courseid");
        }

        if (!isset($data['section'])) {
            throw new coding_exception("Missing required property section");
        }

        if (!isset($data['name'])) {
            $data['name'] = '';
        }

        $break = new section_break(0, (object) $data);
        $break->save();
    }

}
