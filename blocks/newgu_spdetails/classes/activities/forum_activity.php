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
 * Concrete implementation for mod_forum.
 *
 * @package    block_newgu_spdetails
 * @copyright  2024 University of Glasgow
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_newgu_spdetails\activities;

use cache;

/**
 * Implementation for a forum activity.
 */
class forum_activity extends base {

    /**
     * @var object $cm
     */
    private $cm;

    /**
     * @var object $forum
     */
    private $forum;

    /**
     * @var constant CACHE_KEY
     */
    const CACHE_KEY = 'studentid_forumduesoon:';

    /**
     * Constructor, set grade itemid.
     *
     * @param int $gradeitemid Grade item id
     * @param int $courseid
     * @param int $groupid
     */
    public function __construct(int $gradeitemid, int $courseid, int $groupid) {
        parent::__construct($gradeitemid, $courseid, $groupid);

        // Get the forum object.
        $this->cm = \local_gugrades\users::get_cm_from_grade_item($gradeitemid, $courseid);
        $this->forum = $this->get_forum($this->cm);
    }

    /**
     * Get forum object.
     *
     * @param object $cm course module
     * @return object
     */
    public function get_forum(object $cm): object {
        global $DB;

        $coursemodulecontext = \context_module::instance($cm->id);
        $forum = $DB->get_record('forum', ['id' => $this->gradeitem->iteminstance], '*', MUST_EXIST);
        $forum->coursemodulecontext = $coursemodulecontext;

        return $forum;
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
        $dateinstance = $this->forum;
        $rawdate = $dateinstance->duedate;

        return $rawdate;
    }

    /**
     * Return a formatted date.
     *
     * @param int $unformatteddate
     * @return string
     */
    public function get_formattedduedate(int $unformatteddate = null): string {
        $dateinstance = $this->forum;
        $rawdate = $dateinstance->duedate;
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
     * Method to return the current status of the assessment item.
     *
     * @param int $userid
     * @return object
     */
    public function get_status(int $userid): object {
        global $DB;

        $statusobj = new \stdClass();
        $statusobj->assessment_url = $this->get_assessmenturl();
        $foruminstance = $this->forum;
        $statusobj->grade_status = '';
        $statusobj->status_text = '';
        $statusobj->status_class = '';
        $statusobj->status_link = '';
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');
        $statusobj->grade_class = false;
        $statusobj->due_date = $foruminstance->duedate;
        $statusobj->raw_due_date = $foruminstance->duedate;
        $statusobj->cutoff_date = $foruminstance->cutoffdate;

        $forumposts = [];
        // We need the discussionid for the forum in order to determine if any posts have been made.
        if ($discussion = $DB->get_record('forum_discussions', ['forum' => $foruminstance->id])) {
            $forumposts = $DB->count_records('forum_posts', ['discussion' => $discussion->id, 'userid' => $userid]);
        }

        // Begin with the easy step. If the student has not made a forum post yet.
        if (empty($forumposts)) {
            $this->set_displaystate($statusobj);
        } else {
            // Not sure it's as easy as this when it comes to this activity, but lets go with it for now.
            $statusobj->grade_status = get_string('status_submitted', 'block_newgu_spdetails');
            $statusobj->status_class = get_string('status_class_submitted', 'block_newgu_spdetails');
            $statusobj->status_text = get_string('status_text_submitted', 'block_newgu_spdetails');
            $statusobj->status_link = '';
        }

        // Formatting this here as the integer format for the date is no longer needed for testing against.
        if ($statusobj->due_date != 0) {
            $statusobj->due_date = $this->get_formattedduedate($statusobj->due_date);
            $statusobj->raw_due_date = $this->get_rawduedate();
        } else {
            $statusobj->due_date = 'N/A';
            $statusobj->raw_due_date = 0;
        }

        return $statusobj;
    }

    /**
     * This method takes the $statusobj object and sets the display values for the grade status.
     *
     * @param object $statusobj
     * @return object
     */
    private function set_displaystate(object $statusobj): object {

        // Start by saying the student is still able to make a submission.
        $statusobj->grade_status = get_string('status_submit', 'block_newgu_spdetails');
        $statusobj->status_text = get_string('status_text_submit', 'block_newgu_spdetails');
        $statusobj->status_class = get_string('status_class_submit', 'block_newgu_spdetails');
        $statusobj->status_link = $statusobj->assessment_url;
        $statusobj->grade_to_display = get_string('status_text_tobeconfirmed', 'block_newgu_spdetails');

        // Cut-off date is the more 'finite' state - exceed this and you're not allowed to submit at all.
        if ($statusobj->cutoff_date > 0) {
            // The student can still post to the forum if they have exceeded the due date at this point.
            if ($statusobj->due_date != 0 && time() > $statusobj->due_date) {
                $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
                $statusobj->status_link = $statusobj->assessment_url;
            }
            // If the student has exceeded the cut-off date then we can no longer post anything.
            if (time() > $statusobj->cutoff_date) {
                $statusobj->grade_status = get_string('status_notsubmitted', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_notsubmitted', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_notsubmitted', 'block_newgu_spdetails');
                $statusobj->status_link = '';
            }
        } else {
            // The student can still post to the forum if they have exceeded only the due date at this point.
            if ($statusobj->due_date != 0 && time() > $statusobj->due_date) {
                $statusobj->grade_status = get_string('status_overdue', 'block_newgu_spdetails');
                $statusobj->status_text = get_string('status_text_overdue', 'block_newgu_spdetails');
                $statusobj->status_class = get_string('status_class_overdue', 'block_newgu_spdetails');
                $statusobj->status_link = $statusobj->assessment_url;
            }
        }

        return $statusobj;
    }

    /**
     * Forum as an activity can have a requirement that posts are made by a due date,
     * or a cutoff date. We therefore need to check for a forum due date and then see
     * if any posts have been made by the due date.
     * @return array $assignmentdata
     */
    public function get_assessmentsdue(): array {
        global $USER, $DB;

        // Cache this query as it's going to get called for each activity in the course otherwise.
        $cache = cache::make('block_newgu_spdetails', 'forumduequery');
        $now = mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
        $currenttime = time();
        $fiveminutes = $currenttime - 300;
        $cachekey = self::CACHE_KEY . $USER->id;
        $cachedata = $cache->get_many([$cachekey]);
        $forumdata = [];

        if (!$cachedata[$cachekey] || $cachedata[$cachekey][0]['updated'] < $fiveminutes) {
            $lastmonth = mktime(date('H'), date('i'), date('s'), date('m') - 1, date('d'), date('Y'));

            $params = [
                'lastmonth' => $lastmonth,
                'now' => $now,
                'tlastmonth' => $lastmonth,
                'tnow' => $now,
            ];

            $forumsubmissions = $DB->get_records_sql(
                'SELECT f.id FROM {forum_posts} AS fp INNER JOIN {forum_discussions} AS fd ON fd.id = fp.discussion INNER JOIN
                {forum} AS f ON f.id = fd.forum WHERE ((fp.created BETWEEN :lastmonth AND :now) OR (fp.modified BETWEEN
                :tlastmonth AND :tnow))',
                $params);

            $submissionsdata = [
                'updated' => time(),
                'forumsubmissions' => $forumsubmissions,
            ];

            $cachedata = [
                $cachekey => [
                    $submissionsdata,
                ],
            ];
            $cache->set_many($cachedata);
        } else {
            $cachedata = $cache->get_many([$cachekey]);
            $forumsubmissions = $cachedata[$cachekey][0]['forumsubmissions'];
        }

        $forum = $this->forum;

        if (!array_key_exists($forum->id, $forumsubmissions)) {
            if ($forum->duedate != 0 && $forum->duedate > $now) {
                // If we don't have the optional cutoff date set.
                if ($forum->cutoffdate == 0) {
                    $obj = new \stdClass();
                    $obj->name = $forum->name;
                    $obj->duedate = $forum->duedate;
                    $forumdata[] = $obj;
                } elseif ($forum->cutoffdate != 0 && $forum->cutoffdate > $now) {
                    $obj = new \stdClass();
                    $obj->name = $forum->name;
                    $obj->duedate = $forum->duedate;
                    $forumdata[] = $obj;
                }
            }
        }

        return $forumdata;

    }

}
