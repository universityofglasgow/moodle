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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_kuracloud;

defined('MOODLE_INTERNAL') || die();

/**
 * A mapped kuraCloud course
 *
 * @copyright 2017 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course {

    /**
     * Moodle courseid
     *
     * @var integer
     */
    public $courseid;

    /**
     * kuraCloud instance id
     *
     * @var string
     */
    public $remoteinstanceid;

    /**
     * kuraCloud course id
     *
     * @var integer
     */
    public $remotecourseid;

    /**
     * kuraCloud course name
     *
     * @var string
     */
    public $remotename;

    /**
     * Status of the connection between Moodle and kuraCloud
     *
     * @var boolean
     */
    public $statusok;

    /**
     * Failure message of $statusok is false
     *
     * @var string
     */
    public $statusmessage;

    /**
     * API endpoint for this course
     *
     * @var endpoint
     */
    private $endpoint;


    /**
     * Construct a course object from the supplied course data
     *
     * @param stdClass $course Course data
     * @param endpoint $endpoint of the API
     */
    public function __construct($course, endpoint $endpoint) {

        $this->courseid = $course->courseid;
        $this->remote_instanceid = $course->remote_instanceid;
        $this->remote_courseid = $course->remote_courseid;
        $this->remote_name = $course->remote_name;
        $this->status_ok = $course->status_ok;
        $this->status_message = $course->status_message;

        $this->endpoint = $endpoint;

    }

    /**
     * Check if the course has been deleted
     *
     * @return boolean
     */
    public function is_deleted() {

        $courses = $this->endpoint->api->get_courses();

        if (isset($courses[$this->remote_courseid])) {
            return false;
        }
        return true;
    }

    /**
     * Get users from the kuraCloud course
     *
     * @return \stdClass[]
     */
    public function get_users() {
        return $this->endpoint->api->get_students($this->remote_courseid);
    }

    /**
     * Get deleted users form the kuraCloud course
     *
     * @return \stdClass[]
     */
    public function get_deleted_users() {
        return $this->endpoint->api->get_students($this->remote_courseid, true);
    }

    /**
     * Add users to kuraCloud
     *
     * @param \stdClass[] $users to add to the kuraCloud course
     * @return \stdClass[]
     */
    public function add_users($users) {
        return $this->endpoint->api->add_students($this->remote_courseid, $users);
    }

    /**
     * Update users in the kuraCloud course
     *
     * @param \stdClass[] $users to edit in the kuraCloud course
     * @return \stdClass[]
     */
    public function update_users($users) {
        return $this->endpoint->api->edit_students($this->remote_courseid, $users);
    }

    /**
     * Delete users in the kuraCloud course
     *
     * @param \stdClass[] $users to delete from the kuraCloud course
     * @return boolean
     */
    public function delete_users($users) {
        return $this->endpoint->api->delete_students($this->remote_courseid, $users);
    }

    /**
     * Restore users in the kuraCloud course
     *
     * @param \stdClass[] $users to restore in the kuraCloud course
     * @return boolean
     */
    public function restore_users($users) {
        return $this->endpoint->api->restore_students($this->remote_courseid, $users);
    }

    /**
     * Compute a set of differences between Moodle and kuraCloud course enrolments
     *
     * @param boolean $fordisplay return data ready for presentation or for further API calls.
     * @return array[]
     */
    public function get_usersync_changes($fordisplay=true) {
        global $DB;

        $todelete = array();
        $toupdate = array();
        $torestore = array();
        $toadd = array();

        $context = \context_course::instance($this->courseid);

        $remoteusers = $this->get_users();
        $remotedeletedusers = $this->get_deleted_users();
        $localusers = get_enrolled_users($context, 'block/kuracloud:participate');
        $lookupid = $DB->get_records_menu('block_kuracloud_users', array(
            'remote_courseid' => $this->remote_courseid,
            'remote_instanceid' => $this->remote_instanceid), null, 'userid, remote_studentid');

        // Get group membership of all users in course.
        $sql = "SELECT gm.id, gm.userid, g.name
                FROM {groups_members} gm
                    INNER JOIN {groups} g ON gm.groupid = g.id
                WHERE g.courseid = :courseid
                ORDER BY gm.timeadded DESC";

        $groups = $DB->get_records_sql($sql, array('courseid' => $this->courseid));

        $lookupgroup = array();

        foreach ($groups as $group) {
            $lookupgroup[$group->userid] = $group->name;
        }

        // Create lookup maps.
        $lookupemail = array();
        foreach ($remoteusers as $id => $r) {
            $lookupemail[strtolower($r->email)] = $id;
        }

        $lookupdeletedemail = array();
        foreach ($remotedeletedusers as $id => $r) {
            $lookupdeletedemail[strtolower($r->email)] = $id;
        }

        // Iterate through local users and figure out the remote action to take.
        foreach ($localusers as $key => $local) {

            $local->group = isset($lookupgroup[$local->id]) ? $lookupgroup[$local->id] : "";

            $remoteuser = false;

            // Check for a known mapping - check active and deleted remote users.
            if (isset($lookupid[$local->id])) {

                if (isset($remoteusers[$lookupid[$local->id]])) {
                    $remoteuser = $remoteusers[$lookupid[$local->id]];
                } else if (isset($remotedeletedusers[$lookupid[$local->id]])) {
                    $remoteuser = $remotedeletedusers[$lookupid[$local->id]];

                    if (!$fordisplay) {
                        $restoreuser = new \stdClass;
                        $restoreuser->studentId = $remoteuser->studentId;
                        $torestore[] = $restoreuser;
                    } else {
                        $torestore[] = $this->format_remoteuser($remoteuser);
                    }
                } else {
                    // Invalid mapping record in our DB.
                    $DB->delete_records('block_kuracloud_users', array(
                        'userid' => $local->id,
                        'remote_instanceid' => $this->remote_instanceid,
                        'remote_courseid' => $this->remote_courseid));

                    unset($lookupid[$local->id]);
                }

                // Check for an email match - check active and deleted remote users
                // Save the mapping locally if found.
            } else if (isset($lookupemail[strtolower($local->email)])) {
                $remoteuser = $remoteusers[$lookupemail[strtolower($local->email)]];

                $mapping = new \stdClass;
                $mapping->userid = $local->id;
                $mapping->remote_studentid = $remoteuser->studentId;
                $mapping->remote_instanceid = $this->remote_instanceid;
                $mapping->remote_courseid = $this->remote_courseid;

                $DB->insert_record('block_kuracloud_users', $mapping);

            } else if (isset($lookupdeletedemail[strtolower($local->email)])) {
                $remoteuser = $remotedeletedusers[$lookupdeletedemail[strtolower($local->email)]];
                if (!$fordisplay) {

                    $torestore[] = $remoteuser->studentId;

                    $mapping = new \stdClass;
                    $mapping->userid = $local->id;
                    $mapping->remote_studentid = $remoteuser->studentId;
                    $mapping->remote_instanceid = $this->remote_instanceid;
                    $mapping->remote_courseid = $this->remote_courseid;

                    $DB->insert_record('block_kuracloud_users', $mapping);
                } else {
                    $torestore[] = $this->format_remoteuser($remoteuser);
                }
            }

            if ($remoteuser) {

                // See if user needs to be updated.
                if ($local->firstname != $remoteuser->givenName
                    || $local->lastname != $remoteuser->familyName
                    || $local->idnumber != $remoteuser->externalStudentId
                    || $local->group != $remoteuser->section) {

                        $remoteuser->givenName = $local->firstname;
                        $remoteuser->familyName = $local->lastname;
                        $remoteuser->externalStudentId = $local->idnumber;
                        $remoteuser->section = $local->group;

                    if (!$fordisplay) {
                        $toupdate[] = $remoteuser;
                    } else {
                        $toupdate[] = $this->format_remoteuser($remoteuser);
                    }
                }
            } else {

                $newuser = new \stdClass;

                $newuser->givenName = $local->firstname;
                $newuser->familyName = $local->lastname;
                $newuser->externalStudentId = $local->idnumber;
                $newuser->section = $local->group;
                $newuser->email = $local->email;

                if (!$fordisplay) {
                    $toadd[] = $newuser;
                } else {
                    $toadd[] = $this->format_remoteuser($newuser);
                }
            }

            if ($remoteuser) {
                if (isset($remoteusers[$remoteuser->studentId])) {
                    unset($remoteusers[$remoteuser->studentId]);
                }
            }
        }

        // Remaining remote users should be deleted.
        foreach ($remoteusers as $remoteuser) {
            $deleteuser = new \stdClass;
            $deleteuser->studentId = $remoteuser->studentId;
            if (!$fordisplay) {
                $todelete[] = $deleteuser;
            } else {
                $todelete[] = $this->format_remoteuser($remoteuser);
            }
        }

        unset($remoteusers);

        return array($toadd, $toupdate, $todelete, $torestore);
    }

    /**
     * Get grades from kuraCloud for the course
     *
     * @param boolean|function $progresscallback function to call when polling the download ready status
     * @return \stdClass[]
     */
    public function get_grades($progresscallback=false) {
        global $DB;

        $polldelay = 0.5; // Seconds.
        $backoffdelay = 0.5; // Seconds.
        $maxdelay = 5; // Seconds.

        $token = $this->endpoint->api->request_grades($this->remote_courseid);

        $loops = 0;

        while (!$this->endpoint->api->grades_ready($this->remote_courseid, $token)) {
            $loops++;

            if (is_callable($progresscallback)) {
                $progresscallback($loops);
            }

            usleep($polldelay * 1000000);

            if ($polldelay < $maxdelay) {
                $polldelay += $backoffdelay;
            }
        }

        $lessons = $this->endpoint->api->get_grades($this->remote_courseid, $token);

        $studentmap = $DB->get_records_menu('block_kuracloud_users', array(
            'remote_instanceid' => $this->remote_instanceid,
            'remote_courseid' => $this->remote_courseid), '', 'remote_studentid, userid');

        $gradeitems = array();

        foreach ($lessons as $l) {
            if (isset($l->gradedPublishedRevisions)) {
                foreach ($l->gradedPublishedRevisions as $gr) {
                    $gradeitem = new \stdClass;
                    $a = new \stdClass;
                    $a->title = $gr->publishedRevisionTitle;
                    $a->lessonid = $l->publishedLessonId;
                    $a->revisionid = $gr->publishedRevisionId;

                    $gradeitem->itemname = get_string('gradeitemname', 'block_kuracloud', $a);
                    $gradeitem->idnumber = $l->publishedLessonId.'-'.$gr->publishedRevisionId;
                    $gradeitem->grademin = 0;
                    $gradeitem->grademax = 0;

                    foreach ($gr->questions as $q) {
                        $gradeitem->grademax += $q->maxGrade;
                    }

                    $gradeitem->grades = array();
                    if (isset($gr->studentGrades)) {
                        foreach ($gr->studentGrades as $sg) {

                            $grade = $gradeitem->grademin;

                            $allnull = true;
                            foreach ($sg->questionGrades as $qg) {

                                if ($allnull && !is_null($qg->questionGrade)) {
                                    $allnull = false;
                                }
                                $grade += $qg->questionGrade;
                            }

                            // If all grades are NULL, and the offset the student doesn't need a grade.
                            if ($allnull && (is_null($sg->gradeOffset) || $sg->gradeOffset == 0)) {
                                continue;
                            }

                            $grade += $sg->gradeOffset;

                            if ($grade > $gradeitem->grademax) {
                                $grade = $gradeitem->grademax;
                            } else if ($grade < $gradeitem->grademin) {
                                $grade = $gradeitem->grademin;
                            }
                            $gradeitem->grades[$studentmap[$sg->studentId]] = $grade;
                        }

                        if (!empty($gradeitem->grades)) {
                            $gradeitems[$gradeitem->idnumber] = $gradeitem;
                        }
                    }
                }
            }
        }

        return ($gradeitems);
    }

    /**
     * Format kuraCloud user info according to Moodle display settings
     *
     * @param \stdClass $remoteuser kuraCloud user data
     * @return string
     */
    private function format_remoteuser($remoteuser) {
        $u = new \stdClass;
        $u->firstname = $remoteuser->givenName;
        $u->lastname = $remoteuser->familyName;
        $u->firstnamephonetic = '';
        $u->lastnamephonetic = '';
        $u->middlename = '';
        $u->alternatename = '';

        return fullname($u);
    }
}