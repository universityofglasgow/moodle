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

// Audit types
define('LOCAL_GUGRADES_AUDIT_ERROR', 'error');
define('LOCAL_GUGRADES_AUDIT_WARNING', 'warning');
define('LOCAL_GUGRADES_AUDIT_INFO', 'info');

abstract class base {

    private int $courseid;

    private int $userid;

    private int $gradeitemid;

    private int $timecreated;

    protected string $type;

    protected string $level;

    protected string $message;

    /**
     * Constructor
     * @param int $courseid
     * @param int $gradeitemid (if 0 then it's just null in db)
     */
    public function __construct(int $courseid, int $gradeitemid) {
        global $USER;

        $this->courseid = $courseid;
        $this->userid = $USER->id;
        $this->gradeitemid = $gradeitemid;
        $this->timecreated = time();
    }

    /**
     * Save the audit information to database
     */
    public function save() {
        global $DB;

        $audit = new \stdClass;
        $audit->courseid = $this->courseid;
        $audit->userid = $this->userid;
        $audit->gradeitemid = empty($this->gradeitemid) ? null : $this->gradeitemid;
        $audit->timecreated = $this->timecreated;
        $audit->type = $this->type;
        $audit->level = $this->level;
        $audit->message = $this->message;
        $DB->insert_record('local_gugrades_audit', $audit);
    }

}