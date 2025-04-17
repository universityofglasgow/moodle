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
 * Quiz Filedownloader report version information.
 *
 * @package   quiz_filedownloader
 * @copyright 2019 ETH Zurich
 * @author    Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/report/filedownloader/report.php');

/**
 * Tests for the filedownloader report.
 */
class quiz_filedownloader_report_testcase extends advanced_testcase {

    public function test_filedownloader_get_configqtypes() {

        $this->resetAfterTest();

        $report = new quiz_filedownloader_report();

        set_config('acceptedqtypes', 'essay, fileresponse', 'quiz_filedownloader');

        $this->assertEquals(2, count($report->filedownloader_get_config_qtypes()));
        $this->assertEquals('essay', $report->filedownloader_get_config_qtypes()[0]);
        $this->assertEquals('fileresponse', $report->filedownloader_get_config_qtypes()[1]);

        set_config('acceptedqtypes', '      essay  ,   fileresponse    ', 'quiz_filedownloader');

        $this->assertEquals(2, count($report->filedownloader_get_config_qtypes()));
        $this->assertEquals('essay', $report->filedownloader_get_config_qtypes()[0]);
        $this->assertEquals('fileresponse', $report->filedownloader_get_config_qtypes()[1]);

        set_config('acceptedqtypes', ', ', 'quiz_filedownloader');

        $this->assertEquals(2, count($report->filedownloader_get_config_qtypes()));
        $this->assertEquals('', $report->filedownloader_get_config_qtypes()[0]);
        $this->assertEquals('', $report->filedownloader_get_config_qtypes()[1]);
    }

    public function test_filedownloader_get_config_fileareass() {

        $this->resetAfterTest();

        $report = new quiz_filedownloader_report();

        set_config('qtypefileareas', 'attachments, answer', 'quiz_filedownloader');
        $qtypes = array(0 => 'essay', 1 => 'fileresponse');

        $this->assertEquals(2, count($report->filedownloader_get_config_fileareas($qtypes)));
        $this->assertEquals(array(0 => 'essay', 1 => 'fileresponse'),
            array_keys($report->filedownloader_get_config_fileareas($qtypes)));
        $this->assertEquals('attachments', array_values($report->filedownloader_get_config_fileareas($qtypes))[0]);
        $this->assertEquals('answer', array_values($report->filedownloader_get_config_fileareas($qtypes))[1]);

        set_config('acceptedqtypes', '      attachments  ,   answer    ', 'quiz_filedownloader');
        $this->assertEquals(2, count($report->filedownloader_get_config_fileareas($qtypes)));
        $this->assertEquals(array(0 => 'essay', 1 => 'fileresponse'),
            array_keys($report->filedownloader_get_config_fileareas($qtypes)));
        $this->assertEquals('attachments', array_values($report->filedownloader_get_config_fileareas($qtypes))[0]);
        $this->assertEquals('answer', array_values($report->filedownloader_get_config_fileareas($qtypes))[1]);

        set_config('qtypefileareas', ', ', 'quiz_filedownloader');
        $this->assertEquals(2, count($report->filedownloader_get_config_fileareas($qtypes)));
        $this->assertEquals(array(0 => 'essay', 1 => 'fileresponse'),
            array_keys($report->filedownloader_get_config_fileareas($qtypes)));
        $this->assertEquals('', array_values($report->filedownloader_get_config_fileareas($qtypes))[0]);
        $this->assertEquals('', array_values($report->filedownloader_get_config_fileareas($qtypes))[1]);

        set_config('qtypefileareas', 'attachments', 'quiz_filedownloader');
        $this->assertEquals(0, count($report->filedownloader_get_config_fileareas($qtypes)));
    }

    public function test_filedownloader_get_valid_qtypes() {

        $this->resetAfterTest();

        $report             = new quiz_filedownloader_report();

        $generator          = $this->getDataGenerator();
        $course             = $generator->create_course();
        $quizgenerator      = $generator->get_plugin_generator('mod_quiz');
        $quiz               = $quizgenerator->create_instance(array('course'            => $course->id,
                                                            'visible'           => true,
                                                            'questionsperpage'  => 0,
                                                            'grade'             => 100.0,
                                                            'sumgrades'         => 2));

        $questiongenerator  = $generator->get_plugin_generator('core_question');
        $category           = $questiongenerator->create_question_category();
        $question1          = $questiongenerator->create_question('essay', 'plain', array('category' => $category->id));
        $question2          = $questiongenerator->create_question('essay', 'plain', array('category' => $category->id));

        $questions          = array ();
        $configqtypes       = array(0 => 'essay', 1 => 'fileresponse');
        $configfileareas    = array('essay' => 'attachments', 'fileresponse' => 'attachments');
        $qtypes = $report->filedownloader_get_valid_qtypes($questions, $configfileareas, $configqtypes);

        $this->assertEquals(0, count($qtypes['valid']));
        $this->assertEquals(array(0 => get_string('response_noquestions', 'quiz_filedownloader')),
            $qtypes['errors']);

        array_push($questions, $question1);
        array_push($questions, $question2);

        $configqtypes = array(0 => 'fileresponse');
        $configfileareas = array('fileresponse' => 'attachments');
        $qtypes = $report->filedownloader_get_valid_qtypes($questions, $configfileareas, $configqtypes);

        $this->assertEquals(0, count($qtypes['valid']));
        $this->assertEquals(array(0 => get_string('response_noconfigqtypes', 'quiz_filedownloader')),
            $qtypes['errors']);

        $configqtypes = array(0 => 'fileresponse', 1 => 'essay');
        $configfileareas = array();
        $qtypes = $report->filedownloader_get_valid_qtypes($questions, $configfileareas, $configqtypes);

        $this->assertEquals(0, count($qtypes['valid']));
        $this->assertEquals(array(0 => get_string('response_noconfigfileareas', 'quiz_filedownloader')),
            $qtypes['errors']);

        $configqtypes = array(0 => 'fileresponse', 1 => 'essay');
        $configfileareas = array('fileresponse' => 'attachments', 'notessay' => 'attachments');
        $qtypes = $report->filedownloader_get_valid_qtypes($questions, $configfileareas, $configqtypes);

        $this->assertEquals(0, count($qtypes['valid']));
        $this->assertEquals(array(0 => get_string('response_nofilearea', 'quiz_filedownloader') . 'essay'),
            $qtypes['errors']);

        $questions = array(
            0 => (object) array('qtype' => 'essay'),
            1 => (object) array('qtype' => 'notinstalledqtype'));
            $configqtypes = array(0 => 'notinstalledqtype');
            $configfileareas = array('notinstalledqtype' => 'attachments');
        $qtypes = $report->filedownloader_get_valid_qtypes($questions, $configfileareas, $configqtypes);

        $this->assertEquals(0, count($qtypes['valid']));
        $this->assertEquals(array(0 => get_string('response_nosuchqtype', 'quiz_filedownloader') . 'notinstalledqtype'),
            $qtypes['errors']);

        $configqtypes = array(0 => 'essay');
        $configfileareas = array('essay' => 'invalidfileareavalue');
        $qtypes = $report->filedownloader_get_valid_qtypes($questions, $configfileareas, $configqtypes);

        $this->assertEquals(0, count($qtypes['valid']));
        $this->assertEquals(array(0 => get_string('response_invalidfilearea', 'quiz_filedownloader') . 'essay'),
            $qtypes['errors']);

        $configqtypes = array(0 => 'essay', 1 => 'fileresponse');
        $configfileareas = array('essay' => 'attachments', 'fileresponse' => 'attachments');
        $qtypes = $report->filedownloader_get_valid_qtypes($questions, $configfileareas, $configqtypes);

        $this->assertEquals(0, count($qtypes['errors']));
        $this->assertEquals(1, count($qtypes['valid']));
    }

    public function test_filedownloader_get_userattempts() {

        $this->resetAfterTest();

        $report = new quiz_filedownloader_report();

        // Setup course, students, quiz and question.
        $generator          = $this->getDataGenerator();
        $course             = $generator->create_course();

        $students = array();
        for ($i = 0; $i < 5; $i++) {
            $students[$i] = $generator->create_user();
            $generator->enrol_user($students[$i]->id, $course->id);
        }

        $quizgenerator      = $generator->get_plugin_generator('mod_quiz');
        $quiz1              = $quizgenerator->create_instance(array('course'            => $course->id,
                                                                    'visible'           => true,
                                                                    'questionsperpage'  => 0,
                                                                    'grade'             => 100.0,
                                                                    'sumgrades'         => 2));

        $quiz2              = $quizgenerator->create_instance(array('course'            => $course->id,
                                                                    'visible'           => true,
                                                                    'questionsperpage'  => 0,
                                                                    'grade'             => 100.0,
                                                                    'sumgrades'         => 2));

        $questiongenerator  = $generator->get_plugin_generator('core_question');
        $category           = $questiongenerator->create_question_category();
        $question1          = $questiongenerator->create_question('essay', 'plain', array('category' => $category->id));

        quiz_add_quiz_question($question1->id, $quiz1);
        quiz_add_quiz_question($question1->id, $quiz2);

        $validqtypes        = array(0 => 'essay');

        $this->assertEquals(0, count($report->filedownloader_get_userattempts($quiz1->id, $validqtypes)));

        // Create 5 user attempts in quiz 1.
        for ($i = 0; $i < 5; $i++) {
            $quizobj    = quiz::create($quiz1->id, $students[$i]->id);
            $quba       = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
            $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

            $timenow    = time();
            $attempt    = quiz_create_attempt($quizobj, 1, false, $timenow, false, $students[$i]->id);

            quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
            quiz_attempt_save_started($quizobj, $quba, $attempt);
        }

        // Create an user attempt in non-relevant quiz 2.
        $quizobj    = quiz::create($quiz2->id, $students[0]->id);
        $quba       = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow    = time();
        $attempt    = quiz_create_attempt($quizobj, 1, false, $timenow, false, $students[0]->id);

        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $this->assertEquals(5, count($report->filedownloader_get_userattempts($quiz1->id, $validqtypes)));

        // Create a 2nd user attempt with student0 in quiz 1.
        $quizobj    = quiz::create($quiz1->id, $students[0]->id);
        $quba       = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow    = time();
        $attempt    = quiz_create_attempt($quizobj, 2, false, $timenow, false, $students[0]->id);

        quiz_start_new_attempt($quizobj, $quba, $attempt, 2, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $this->assertEquals(6, count($report->filedownloader_get_userattempts($quiz1->id, $validqtypes)));
    }

    public function test_filedownloader_create_pathes() {

        $this->resetAfterTest(true);

        $report = new quiz_filedownloader_report();

        $data = array(
            'id'                => 2,
            'mode'              => 'filedownloader',
            'zip_inonefolder'   => 0,
            'downloadfiles'     => 'Herunterladen'
        );

        // Setup course, students, quiz and question.
        $generator          = $this->getDataGenerator();
        $course             = $generator->create_course();
        $student1           = $generator->create_user(array('firstname' => 'Herbert',
                                                            'lastname'  => 'West',
                                                            'username'  => 'hwest',
                                                            'idnumber' => 'u_00001'));
        $student2           = $generator->create_user(array('firstname' => 'Keziah',
                                                            'lastname'  => 'Mason',
                                                            'username'  => 'nahab'));

        $generator->enrol_user($student1->id, $course->id);
        $generator->enrol_user($student2->id, $course->id);

        $quizgenerator      = $generator->get_plugin_generator('mod_quiz');
        $quiz               = $quizgenerator->create_instance(array('course'            => $course->id,
                                                                    'visible'           => true,
                                                                    'questionsperpage'  => 0,
                                                                    'grade'             => 100.0,
                                                                    'sumgrades'         => 2));

        $questiongenerator  = $generator->get_plugin_generator('core_question');
        $category           = $questiongenerator->create_question_category();
        $question1          = $questiongenerator->create_question('essay', 'plain', array('category' => $category->id));

        quiz_add_quiz_question($question1->id, $quiz);

        // Create student 1 - attempt 1.
        $quizobj    = quiz::create($quiz->id, $student1->id);
        $quba       = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow    = time();
        $attempt    = quiz_create_attempt($quizobj, 1, false, $timenow, false, $student1->id);

        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Create student 1 - attempt 2.
        $quizobj    = quiz::create($quiz->id, $student1->id);
        $quba       = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow    = time();
        $attempt    = quiz_create_attempt($quizobj, 2, false, $timenow, false, $student1->id);

        quiz_start_new_attempt($quizobj, $quba, $attempt, 2, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Create student 1 - attempt 3.
        $quizobj    = quiz::create($quiz->id, $student1->id);
        $quba       = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow    = time();
        $attempt    = quiz_create_attempt($quizobj, 3, false, $timenow, false, $student1->id);

        quiz_start_new_attempt($quizobj, $quba, $attempt, 3, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Create student 2 - attempt 1.
        $quizobj    = quiz::create($quiz->id, $student2->id);
        $quba       = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow    = time();
        $attempt    = quiz_create_attempt($quizobj, 1, false, $timenow, false, $student2->id);

        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $structure  = $quizobj->get_structure();

        $validqtypes        = array(0 => 'essay');

        // Get userattempts.
        $studentattempts    = $report->filedownloader_get_userattempts($quiz->id, $validqtypes);

        // Create pathes.
        foreach ($studentattempts as $key => $studentattempt) {
            $questionattempt = $quba->get_question_attempt($studentattempt->slot);
            $questionnumber = $structure->get_displayed_number_for_slot($studentattempt->slot);
            $path = $report->filedownloader_create_pathes($data, $studentattempt, $questionnumber);

            if ($studentattempt->username == "hwest" && $studentattempt->num == 1) {
                $this->assertEquals('Question 1 - Essay question with filepicker and attachments/', $path[0]);
                $this->assertRegexp('/u_00001\(.+?\)\sHerbert\sWest/', $path[1]);
                $this->assertEquals('/', $path[2]);
                $this->assertEquals('Attempt 1/', $path[3]);
            } else if ($studentattempt->username == "hwest" && $studentattempt->num == 2) {
                $this->assertEquals('Question 1 - Essay question with filepicker and attachments/', $path[0]);
                $this->assertRegexp('/u_00001\(.+?\)\sHerbert\sWest/', $path[1]);
                $this->assertEquals('/', $path[2]);
                $this->assertEquals('Attempt 2/', $path[3]);
            } else if ($studentattempt->username == "hwest" && $studentattempt->num == 3) {
                $this->assertEquals('Question 1 - Essay question with filepicker and attachments/', $path[0]);
                $this->assertRegexp('/u_00001\(.+?\)\sHerbert\sWest/', $path[1]);
                $this->assertEquals('/', $path[2]);
                $this->assertEquals('Attempt 3/', $path[3]);
            } else if ($studentattempt->username == "nahab" && $studentattempt->num == 1) {
                $this->assertEquals('Question 1 - Essay question with filepicker and attachments/', $path[0]);
                $this->assertRegexp('/xxxxxx\(.+?\)\sKeziah\sMason/', $path[1]);
                $this->assertEquals('/', $path[2]);
                $this->assertEquals('', $path[3]);
            }
        }
    }

    public function test_filedownloader_create_txtfile() {

        global $CFG;

        $this->resetAfterTest();

        $report = new quiz_filedownloader_report();

        // Setup course, students, quiz and question.
        $generator          = $this->getDataGenerator();
        $course             = $generator->create_course();
        $student1           = $generator->create_user(array('firstname' => 'Randolph',
                                                            'lastname'  => 'Carter',
                                                            'username'  => 'rcarter'));
        $generator->enrol_user($student1->id, $course->id);

        $quizgenerator      = $generator->get_plugin_generator('mod_quiz');
        $quiz               = $quizgenerator->create_instance(array('course'            => $course->id,
                                                                    'visible'           => true,
                                                                    'questionsperpage'  => 0,
                                                                    'grade'             => 100.0,
                                                                    'sumgrades'         => 2));

        $questiongenerator  = $generator->get_plugin_generator('core_question');
        $category           = $questiongenerator->create_question_category();
        $question1          = $questiongenerator->create_question('essay', 'plain', array('category' => $category->id));

        quiz_add_quiz_question($question1->id, $quiz);

        // Create student attempt.
        $quizobj    = quiz::create($quiz->id, $student1->id);
        $quba       = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow    = time();
        $attempt    = quiz_create_attempt($quizobj, 1, false, $timenow, false, $student1->id);

        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $validqtypes = array(0 => 'essay');

        // Test functionality.
        $studentattempts = $report->filedownloader_get_userattempts($quiz->id, $validqtypes);
        $context = context_course::instance($course->id);
        $data = (object) array(
            "chooseableanonymization" => 0
        );

        foreach ($studentattempts as $student) {
            $questionattempt    = $quba->get_question_attempt($student->slot);

            $content            = $report->filedownloader_create_txtfile($context->id,
                                                                        $student,
                                                                        $course->fullname,
                                                                        $course->id,
                                                                        $questionattempt->get_question()->name,
                                                                        $questionattempt->get_question()->id,
                                                                        $data);
            $content = $content->get_content();

            $this->assertRegexp('/User:     Randolph Carter \(Student ID: -not available-, User ID:/', $content);
            $this->assertRegexp('/E-Mail:   rcarter@example.com/', $content);
            $this->assertRegexp('/Question: Essay question with filepicker and attachments \(Question ID: /', $content);
            $this->assertRegexp('/Course:   Test course 1 \(Course ID: /', $content);
        }
    }
}
