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
 * Concrete implementation for mod_scheduler.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

/**
 * Implementation for a scheduler activity type.
 */
class scheduler_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $scheduler
     */
    private $scheduler;

    /**
     * For this activity, get just the basic course module info.
     *
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     * @param int $groupid
     */
    public function __construct(int $gradeitemid, int $courseid, int $groupid) {
        parent::__construct($gradeitemid, $courseid, $groupid);

        // Get the course module object.
        $this->cm = \local_gugrades\users::get_cm_from_grade_item($gradeitemid, $courseid);
        $this->scheduler = $this->get_scheduler($this->cm);
    }

    /**
     * Return a scheduler object.
     *
     * @param object $cm course module
     * @return object
     */
    public function get_scheduler($cm): object {
        global $DB;

        $coursemodulecontext = \context_module::instance($cm->id);
        $scheduler = $DB->get_record('scheduler', ['id' => $this->gradeitem->iteminstance], '*', MUST_EXIST);
        $scheduler->coursemodulecontext = $coursemodulecontext;

        return $scheduler;
    }

    /**
     * A scheduler activity doesn't appear to end up in the Gradebook.
     * Simply return false for now.
     *
     * @param int $userid
     * @return mixed object|bool
     */
    public function get_grade(int $userid): object|bool {
        return false;
    }

    /**
     * Return the Moodle URL to the item.
     *
     * @return string
     */
    public function get_assessmenturl(): string {
        return $this->get_itemurl() . $this->cm->id;
    }

    /**
     * Return 0 as this activity doesn't have any kind of due date.
     *
     * @return int
     */
    public function get_rawduedate(): int {
        return 0;
    }

    /**
     * Return N/A as this activity doesn't have any kind of due date.
     *
     * @return string
     */
    public function get_formattedduedate(): string {
        return 'N/A';
    }

    /**
     * This activity only needs to return mostly empty data, as it isn't graded per se.
     *
     * @param int $userid
     * @return object
     */
    public function get_status(int $userid): object {
        global $DB;

        $statusobj = new \stdClass();
        $statusobj->assessment_url = $this->get_assessmenturl();
        $statusobj->grade_status = get_string('status_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->status_text = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->status_class = get_string('status_class_notsubmitted', 'block_newgu_spdetails');
        $statusobj->status_link = '';
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->due_date = 'N/A';
        $statusobj->raw_due_date = 0;
        $statusobj->grade_date = '';
        $statusobj->grade_class = false;

        return $statusobj;
    }

    /**
     * Returns an empty array here as this activity type has no submission tables.
     *
     * @return array
     */
    public function get_assessmentsdue(): array {
        return [];

    }

}
