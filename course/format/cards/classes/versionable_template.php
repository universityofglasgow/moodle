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

namespace format_cards;

use cache;
use cache_store;
use core\persistent;
use dml_exception;
use section_info;
use stdClass;

/**
 * Can be used to add variables to template contexts to switch templates depending on
 * the current moodle version
 *
 * @package     format_cards
 * @copyright   2025 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait versionable_template {

    /**
     * Adds version variables to a context template
     *
     * @param stdClass $data
     * @return void
     */
    protected function add_version_variables(stdClass $data): void {
        global $CFG;

        $data->moodle500orlater = $CFG->version >= 2025040100;
        $data->moodle405orlater = $CFG->version >= 2024100700;
        $data->moodle404orlater = $CFG->version >= 2024042200;
        $data->moodle403orlater = $CFG->version >= 2023100900;
    }

}
