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
 * Concrete implementation for mod_scorm.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

use cache;

/**
 * Implementation for a SCORM activity type.
 */
class scorm_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $scorm
     */
    private $scorm;

    /**
     * @var constant CACHE_KEY
     */
    const CACHE_KEY = 'studentid_scormduesoon:';

    /**
     * For this activity, get just the basic course module info.
     *
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     * @param int $groupid
     */
    public function __construct(int $gradeitemid, int $courseid, int $groupid) {
        parent::__construct($gradeitemid, $courseid, $groupid);

        // Get the scorm object.
        $this->cm = \local_gugrades\users::get_cm_from_grade_item($gradeitemid, $courseid);
        $this->scorm = $this->get_scorm($this->cm);
    }

    /**
     * Get scorm object.
     *
     * @param object $cm course module
     * @return object
     */
    public function get_scorm($cm): object {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/scorm/locallib.php');
        $coursemodulecontext = \context_module::instance($cm->id);
        $scorm = $DB->get_record('scorm', ['id' => $this->gradeitem->iteminstance], '*', MUST_EXIST);
        $scorm->coursemodulecontext = $coursemodulecontext;

        return $scorm;
    }

    /**
     * Return the grade directly from Gradebook.
     *
     * @param int $userid
     * @return mixed object|bool
     */
    public function get_grade(int $userid): object|bool {
        global $DB;

        $activitygrade = new \stdClass();
        $activitygrade->finalgrade = null;
        $activitygrade->rawgrade = null;
        $activitygrade->gradedate = null;

        // If the grade is overridden in the Gradebook then we can
        // revert to the base - i.e., get the grade from the Gradebook.
        if ($grade = $DB->get_record('grade_grades', ['itemid' => $this->gradeitemid, 'hidden' => 0, 'userid' => $userid])) {
            if ($grade->overridden) {
                return parent::get_first_grade($userid);
            }

            // We want access to other properties, hence the returns...
            if ($grade->finalgrade != null && $grade->finalgrade > 0) {
                $activitygrade->finalgrade = $grade->finalgrade;
                $activitygrade->gradedate = $grade->timemodified;
                return $activitygrade;
            }

            if ($grade->rawgrade != null && $grade->rawgrade > 0) {
                $activitygrade->rawgrade = $grade->rawgrade;
                return $activitygrade;
            }
        }

        // SCORM appears similar to Quiz in that we can have 1 to multiple attempts, which means we should check for this
        // in order to determine which grade to return, i.e. the highest, mean etc
        $scormgrade = scorm_get_user_grades($this->scorm, $userid);
        if ($scormgrade) {
            // Yes, we're keying on rawgrade - but I'm treating this as the final grade.
            $activitygrade->finalgrade = $scormgrade[$userid]->rawgrade;
            
            $sql = "SELECT MAX(id)
            FROM {scorm_attempt}
            WHERE userid = ? AND scormid = ?";
            $lastattemptid = $DB->get_field_sql($sql, [$userid, $this->scorm->id]);
            if (empty($lastattemptid)) {
                $lastattemptid = 1;
            }

            // In order to give us the gradedate, jump through some hoops.
            $sql = "SELECT timemodified
            FROM {scorm_scoes_value}
            WHERE attemptid = ? AND value = 'passed'";
            $timemodified = $DB->get_field_sql($sql, [$lastattemptid]);
            if (!empty($timemodified)) {
                $activitygrade->gradedate = $timemodified;
            }

            return $activitygrade;
        }

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
     * Return the due date as the unix timestamp.
     *
     * @return int
     */
    public function get_rawduedate(): int {
        $dateinstance = $this->scorm;
        $rawdate = $dateinstance->timeclose;

        return $rawdate;
    }

    /**
     * Return a formatted date.
     *
     * @param int $unformatteddate
     * @return string
     */
    public function get_formattedduedate(int $unformatteddate = null): string {
        $dateinstance = $this->scorm;
        $rawdate = $dateinstance->timeclose;
        if ($unformatteddate) {
            $rawdate = $unformatteddate;
        }

        if ($rawdate > 0) {
            $duedate = userdate($rawdate, get_string('strftimedate', 'core_langconfig'));
        } else {
            $duedate = 'N/A';
        }

        return $duedate;
    }

    /**
     * Default implementation for returning the status of
     * a SCORM activity.
     *
     * @param int $userid
     * @return object
     */
    public function get_status(int $userid): object {

        $statusobj = new \stdClass();
        $statusobj->assessment_url = $this->get_assessmenturl();
        $allowsubmissionsfromdate = $this->scorm->timeopen;
        $statusobj->grade_status = '';
        $statusobj->status_text = '';
        $statusobj->status_class = '';
        $statusobj->status_link = '';
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->grade_class = false;
        $statusobj->due_date = 'N/A';
        $statusobj->raw_due_date = 0;
        $statusobj->grade_date = '';

        $now = usertime(time());
        if ($allowsubmissionsfromdate > $now) {
            $statusobj->grade_status = get_string('status_submissionnotopen', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submissionnotopen', 'block_newgu_spdetails');
            $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        }

        if ($now > $this->scorm->timeclose) {
            $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
            $statusobj->status_link = '';
            $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
            $statusobj->due_date = $this->scorm->timeclose;
            $statusobj->raw_due_date = $this->get_rawduedate();
        }

        if ($statusobj->grade_status == '') {
            $scormsubmission = scorm_get_last_completed_attempt($this->scorm->id, $userid);

            $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
            $statusobj->status_class = get_string('status_class_notsubmitted', 'block_newgu_spdetails');
            $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
            $statusobj->due_date = $this->scorm->timeclose;
            $statusobj->raw_due_date = $this->scorm->timeclose;

            if (!empty($scormsubmission) && $scormsubmission != '1') {
                $statusobj->grade_status = get_string('status_submitted', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_submitted', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_submitted', 'block_newgu_spdetails');
                $statusobj->status_link = '';
            } else {
                $statusobj->grade_status = get_string('status_submit', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_submit', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_submit', 'block_newgu_spdetails');
                $statusobj->status_link = $statusobj->assessment_url;
            }
        }

        // Formatting this here as the integer format for the date is no longer needed for testing against.
        if ($statusobj->due_date != 0) {
            $statusobj->due_date = $this->get_formattedduedate($statusobj->due_date);
            $statusobj->raw_due_date = $statusobj->due_date;
        } else {
            $statusobj->due_date = 'N/A';
            $statusobj->raw_due_date = 0;
        }

        return $statusobj;
    }

    /**
     * Return the due date of the default activity if it hasn't been submitted.
     *
     * @return array
     */
    public function get_assessmentsdue(): array {
        global $USER, $DB;

        // Cache this query as it's going to get called for each assessment in the course otherwise.
        $cache = cache::make('block_newgu_spdetails', 'scormduequery');
        $now = usertime(time());
        $currenttime = usertime(time());
        $fiveminutes = $currenttime - 300;
        $cachekey = self::CACHE_KEY . $USER->id;
        $cachedata = $cache->get_many([$cachekey]);
        $scormdata = [];

        if (!$cachedata[$cachekey] || $cachedata[$cachekey][0]['updated'] < $fiveminutes) {

            $lastmonth = usertime(mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y')));

            $params = [
                'userid' => $USER->id, 
                'lastmonth' => $lastmonth, 
                'now' => $now
            ];
            $scormsubmissions = $DB->get_records_sql(
                'SELECT scormid, value FROM {scorm_attempt} AS sa INNER JOIN {scorm_scoes_value} AS ssv ON ssv.attemptid = sa.id
                WHERE sa.userid = :userid AND ssv.timemodified BETWEEN :lastmonth AND :now',
                $params);

            $submissionsdata = [
                'updated' => $currenttime,
                'scormsubmissions' => $scormsubmissions,
            ];

            $cachedata = [
                $cachekey => [
                    $submissionsdata,
                ],
            ];
            $cache->set_many($cachedata);
        } else {
            $cachedata = $cache->get_many([$cachekey]);
            $scormsubmissions = $cachedata[$cachekey][0]['scormsubmissions'];
        }

        $scorm = $this->scorm;

        if (!array_key_exists($scorm->id, $scormsubmissions) ||
            (array_key_exists($scorm->id, $scormsubmissions) &&
            (is_object($scormsubmissions[$scorm->id]) &&
            property_exists($scormsubmissions[$scorm->id], 'value') &&
            $scormsubmissions[$scorm->id]->value == 'incomplete'))) {
            if ($scorm->timeopen != 0 && $scorm->timeopen < $now) {
                if ($scorm->timeclose != 0 && $scorm->timeclose > $now) {
                    $obj = new \stdClass();
                    $obj->name = $scorm->name;
                    $obj->duedate = $scorm->timeclose;
                    $scormdata[] = $obj;
                }
            }
        }

        return $scormdata;

    }

}
