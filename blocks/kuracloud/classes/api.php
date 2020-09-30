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

define('HTTP_OK', 200);

defined('MOODLE_INTERNAL') || die();

/**
 * kuraCloud API abstraction
 *
 * @copyright 2017 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /**
     * Transport object
     *
     * @var transport
     */
    private $transport;

    /**
     * Construct api class with the supplied transport
     *
     * @param apitransport $transport
     */
    public function __construct(apitransport $transport) {
        $this->transport = $transport;
    }


    /**
     * Get details about a kuraCloud instance
     *
     * @return \stdClass
     */
    public function get_instance() {

        $data = $this->do_api_get('/v2/instance');

        $instance = new \stdClass;
        $instance->instanceId = clean_param($data->instanceId, PARAM_TEXT);
        $instance->displayName = clean_param($data->displayName, PARAM_TEXT);
        $instance->gradingMode = clean_param($data->gradingMode, PARAM_TEXT);
        $instance->lmsEnabled = clean_param($data->lmsEnabled, PARAM_BOOL);

        return $instance;
    }


    /**
     * Get list of kuraCloud courses
     *
     * @return \stdClass
     */
    public function get_courses() {
        $data = $this->do_api_get('/v2/course');

        $courses = array();

        foreach ($data as $d) {
            $course = new \stdClass;

            $course->courseId = clean_param($d->courseId, PARAM_INT);
            $course->name = clean_param($d->name, PARAM_TEXT);
            $course->courseCode = clean_param($d->courseCode, PARAM_TEXT);
            $course->lmsEnabled = clean_param($d->lmsEnabled, PARAM_BOOL);
            $courses[$course->courseId] = $course;
        }

        return $courses;
    }


    /**
     * Edit a kuraCloud course
     *
     * @param \stdClass $params Properties of the course to edit
     * @return \stdClass
     */
    public function edit_course($params) {
        $courseid = $params['courseId'];
        unset($params['courseId']); // Not needed when editing.

        $data = $this->do_api_put('/v2/course/'.$courseid, $params);

        $course = new \stdClass;

        $course->courseId = clean_param($data->courseId, PARAM_INT);
        $course->name = clean_param($data->name, PARAM_TEXT);
        $course->courseCode = clean_param($data->courseCode, PARAM_TEXT);
        $course->lmsEnabled = clean_param($data->lmsEnabled, PARAM_BOOL);

        return $course;
    }


    /**
     * Undocumented function
     *
     * @param integer $courseid
     * @param boolean $deleted
     * @return \stdClass
     */
    public function get_students($courseid, $deleted=false) {

        $deletedparam = '';
        if ($deleted) {
            $deletedparam = '?deleted=true';
        }
        $results = $this->do_api_get('/v2/course/'.$courseid.'/student'.$deletedparam);

        $students = array();
        foreach ($results as $result) {
            $student = new \stdClass;

            $student->studentId = clean_param($result->studentId, PARAM_INT);
            $student->email = clean_param($result->email, PARAM_EMAIL);
            $student->givenName = clean_param($result->givenName, PARAM_TEXT);
            $student->familyName = clean_param($result->familyName, PARAM_TEXT);
            $student->inviteComplete = clean_param($result->inviteComplete, PARAM_BOOL);
            $student->createdDate = clean_param($result->createdDate, PARAM_INT);
            $student->externalStudentId = clean_param($result->externalStudentId, PARAM_TEXT);
            $student->section = clean_param($result->section, PARAM_TEXT);

            $students[$student->studentId] = $student;
        }

        return $students;
    }


    /**
     * Add students to kuraCloud
     *
     * @param integer $courseid kuraCloud course id
     * @param \stdClass[] $students array of students to add
     * @return \stdClass[]
     */
    public function add_students($courseid, $students) {
        $results = $this->do_api_post('/v2/course/'.$courseid.'/student', $students);
        $students = array();

        if (!is_array($results)) {
            $results = array($results);
        }

        foreach ($results as $result) {
            $student = new \stdClass;

            $student->studentId = clean_param($result->studentId, PARAM_INT);
            $student->email = clean_param($result->email, PARAM_EMAIL);
            $student->givenName = clean_param($result->givenName, PARAM_TEXT);
            $student->familyName = clean_param($result->familyName, PARAM_TEXT);
            $student->inviteComplete = clean_param($result->inviteComplete, PARAM_BOOL);
            $student->createdDate = clean_param($result->createdDate, PARAM_INT);
            $student->externalStudentId = clean_param($result->externalStudentId, PARAM_TEXT);
            $student->section = clean_param($result->section, PARAM_TEXT);

            $students[$student->studentId] = $student;
        }

        return $students;
    }


    /**
     * Edit students in kuraCloud
     *
     * @param integer $courseid kuraCloud course id
     * @param \stdClass[] $students array of students to edit
     * @return \stdClass[]
     */
    public function edit_students($courseid, $students) {
        $results = $this->do_api_post('/v2/course/'.$courseid.'/student/edit', $students);
        $students = array();

        if (!is_array($results)) {
            $results = array($results);
        }

        foreach ($results as $result) {
            $student = new \stdClass;

            $student->studentId = clean_param($result->studentId, PARAM_INT);
            $student->email = clean_param($result->email, PARAM_EMAIL);
            $student->givenName = clean_param($result->givenName, PARAM_TEXT);
            $student->familyName = clean_param($result->familyName, PARAM_TEXT);
            $student->inviteComplete = clean_param($result->inviteComplete, PARAM_BOOL);
            $student->createdDate = clean_param($result->createdDate, PARAM_INT);
            $student->externalStudentId = clean_param($result->externalStudentId, PARAM_TEXT);
            $student->section = clean_param($result->section, PARAM_TEXT);

            $students[$student->studentId] = $student;
        }

        return $students;
    }


    /**
     * Delete students from kuraCloud
     *
     * @param integer $courseid kuraCloud course id
     * @param \stdClass[] $students array of students to delete
     * @return boolean
     */
    public function delete_students($courseid, $students) {
        return $this->do_api_post('/v2/course/'.$courseid.'/student/delete', $students, false);
    }


    /**
     * Restore deleted students in kuraCloud
     *
     * @param integer $courseid kuraCloud course id
     * @param \stdClass[] $students array of students to restore
     * @return boolean
     */
    public function restore_students($courseid, $students) {
        return $this->do_api_post('/v2/course/'.$courseid.'/student/restore', $students, false);
    }


    /**
     * Request grades
     *
     * This method returns a token that can be used to poll the export
     * status and then requests a download the actual data.
     *
     * @param integer $courseid kuraCloud course id
     * @return string
     */
    public function request_grades($courseid) {
        $result = $this->do_api_post('/v2/course/'.$courseid.'/published/exportgrades.json');

        return clean_param($result->taskToken, PARAM_TEXT);
    }

    /**
     * Check if grade export is ready to download
     *
     * @param integer $courseid kuraCloud course id
     * @param string $token Token from request_grades()
     * @return boolean
     */
    public function grades_ready($courseid, $token) {
        $result = $this->do_api_get('/v2/course/'.$courseid.'/published/exportgrades.json/'.$token);

        return $result->status == 'SUCCEEDED' ? true : false;
    }

    /**
     * Get all grade data requested with request_grades()
     *
     * @param integer $courseid kuraCloud course id
     * @param strgin $token Token from request_grades()
     * @return \stdClass[]
     */
    public function get_grades($courseid, $token) {
        $lessons = $this->do_api_get('/v2/course/'.$courseid.'/published/exportgrades.json/'.$token.'/download');

        $return = array();

        foreach ($lessons as $l) {
            $cleanllesson = new \stdClass;
            $cleanllesson->publishedContentId = clean_param($l->publishedContentId, PARAM_INT);
            $cleanllesson->publishedLessonId = clean_param($l->publishedLessonId, PARAM_INT);
            $cleanllesson->publishedLessonTitle = clean_param($l->publishedLessonTitle, PARAM_TEXT);

            foreach ($l->gradedPublishedRevisions as $gr) {
                $cleangraderev = new \stdClass;
                $cleangraderev->publishedRevisionId = clean_param($gr->publishedRevisionId, PARAM_INT);
                $cleangraderev->publishedRevisionTitle = clean_param($gr->publishedRevisionTitle, PARAM_TEXT);

                foreach ($gr->questions as $q) {
                    $cleanquestion = new \stdClass;
                    $cleanquestion->questionId = clean_param($q->questionId, PARAM_TEXT);
                    $cleanquestion->maxGrade = clean_param($q->maxGrade, PARAM_NUMBER);

                    $cleangraderev->questions[] = $cleanquestion;
                }

                foreach ($gr->studentGrades as $sg) {
                    $cleanstudentgrade = new \stdClass;
                    $cleanstudentgrade->studentId = clean_param($sg->studentId, PARAM_INT);
                    if (is_null($sg->gradeOffset)) {
                        $cleanstudentgrade->gradeOffset = null;
                    } else {
                        $cleanstudentgrade->gradeOffset = clean_param($sg->gradeOffset, PARAM_NUMBER);
                    }

                    foreach ($sg->questionGrades as $qg) {
                        $cleanquestiong = new \stdClass;
                        $cleanquestiong->questionId = clean_param($qg->questionId, PARAM_TEXT);
                        if (is_null($qg->questionGrade)) {
                            $cleanquestiong->questionGrade = null;
                        } else {
                            $cleanquestiong->questionGrade = clean_param($qg->questionGrade, PARAM_NUMBER);
                        }

                        $cleanstudentgrade->questionGrades[] = $cleanquestiong;
                    }

                    $cleangraderev->studentGrades[] = $cleanstudentgrade;
                }

                $cleanllesson->gradedPublishedRevisions[] = $cleangraderev;
            }
            $return[] = $cleanllesson;
        }
        return $return;
    }


    /**
     * Do a GET requests to the API
     *
     * @param string $url of the API
     * @throws \Exception From API, transport or unknown source
     * @return stdClass|array
     */
    private function do_api_get($url) {
        list($response, $code, $error) = $this->transport->get($url);

        $responseobj = json_decode($response);

        if ($code != HTTP_OK) {
            if (isset($responseobj->message)) {
                throw new \Exception(get_string('apierrorgeneral', 'block_kuracloud',
                    clean_param($responseobj->message, PARAM_TEXT)));
            } else if (!empty($error)) {
                throw new \Exception(get_string('apierrortransport', 'block_kuracloud', clean_param($error, PARAM_TEXT)));
            } else {
                throw new \Exception(get_string('apierrorunknown', 'block_kuracloud', $code));
            }
        }
        if (is_null($responseobj)) {
            throw new \Exception('Error decoding response');
        }
        return $responseobj;
    }


    /**
     * Do a PUT requests to the API
     *
     * @param string $url of the API
     * @param stdClass $params Parameters to pass to the api
     * @throws \Exception From API, transport or unknown source
     * @return stdClass|array
     */
    private function do_api_put($url, $params) {
        list($response, $code, $error) = $this->transport->put($url, json_encode($params));

        $responseobj = json_decode($response);

        if ($code != HTTP_OK) {
            if (isset($responseobj->message)) {
                throw new \Exception(get_string('apierrorgeneral', 'block_kuracloud',
                    clean_param($responseobj->message, PARAM_TEXT)));
            } else if (!empty($error)) {
                throw new \Exception(get_string('apierrortransport', 'block_kuracloud', clean_param($error, PARAM_TEXT)));
            } else {
                throw new \Exception(get_string('apierrorunknown', 'block_kuracloud', $code));
            }
        }

        if (is_null($responseobj)) {
            throw new \Exception('Error decoding response');
        }
        return $responseobj;
    }


    /**
     * Do a PUT requests to the API
     *
     * @param string $url of the API
     * @param stdClass $params Parameters to pass to the api
     * @param boolean $expectresponse Does the api return a JSON
     * @throws \Exception From API, transport or unknown source
     * @return stdClass|array
     */
    private function do_api_post($url, $params=null, $expectresponse=true) {
        list($response, $code, $error) = $this->transport->post($url, is_null($params) ? '' : json_encode($params));

        $responseobj = json_decode($response);

        if ($code != HTTP_OK) {
            if (isset($responseobj->message)) {
                throw new \Exception(get_string('apierrorgeneral', 'block_kuracloud',
                    clean_param($responseobj->message, PARAM_TEXT)));
            } else if (!empty($error)) {
                throw new \Exception(get_string('apierrortransport', 'block_kuracloud', clean_param($error, PARAM_TEXT)));
            } else {
                throw new \Exception(get_string('apierrorunknown', 'block_kuracloud', $code));
            }
        }

        if ($expectresponse) {
            if (is_null($responseobj)) {
                throw new \Exception('Error decoding response');
            }
            return $responseobj;
        }

        return true;
    }
}