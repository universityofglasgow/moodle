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
 * Abstract class for audit trail.
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\audit;

defined('MOODLE_INTERNAL') || die();

class import_grades_users extends base {

    /**
     * Constructor
     * @param int $courseid
     */
    public function __construct(int $courseid, $gradeitemid) {
        parent::__construct($courseid);

        $this->type = LOCAL_GUGRADES_AUDIT_INFO;
        $this->level = 0;
        $this->message = get_string('importgradesusers', 'local_gugrades', $gradeitemid);
    }
    
}