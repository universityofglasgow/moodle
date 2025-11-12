<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace quiz_essaydownload;

use Generator;
use mod_quiz\quiz_attempt;
use quiz_essaydownload_options;
use quiz_essaydownload_report;
use Throwable;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/tests/quiz_question_helper_test_trait.php');
require_once($CFG->dirroot . '/mod/quiz/report/essaydownload/essaydownload_options.php');
require_once($CFG->dirroot . '/mod/quiz/report/essaydownload/report.php');
require_once($CFG->dirroot . '/mod/quiz/report/essaydownload/tests/helper.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');

/**
 * Tests for Essay responses downloader plugin (quiz_essaydownload)
 *
 * @package   quiz_essaydownload
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \quiz_essaydownload_report
 */
final class report_test extends \advanced_testcase {
    use \quiz_question_helper_test_trait;

    /**
     * Call quiz_attempt::process_finish() for Moodle < 5.0 or quiz_attempt::process_submit()
     * and quiz_attempt::process_grade_submission() for Moodle 5.0 and later, because the
     * method process_finish() is deprecated in the context of MDL-68806.
     * Note: We leave out the type hint for the first parameter in order to be compatible
     * accross all branches, as quiz_attempt has different name spaces in Moodle 4.1 than
     * in more recent versions.
     *
     * @param quiz_attempt $attemptobj attempt object used to call the processing method
     * @param int $time timestamp
     * @return void
     */
    private function process_submit_or_finish($attemptobj, int $time): void {
        if (method_exists($attemptobj, 'process_submit')) {
            $attemptobj->process_submit($time, false);
            $attemptobj->process_grade_submission($time);
        } else {
            $attemptobj->process_finish($time, false);
        }
    }

    public function test_quiz_has_essay_questions_when_it_has(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        self::assertTrue($report->quiz_has_essay_questions());
    }

    public function test_quiz_has_essay_questions_when_it_has_random(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with a random question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $this->add_one_random_question($questiongenerator, $quiz);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // If we have a random question, it might resolve to an essay question during the attempt,
        // so we actually expect the function to return true.
        self::assertTrue($report->quiz_has_essay_questions());
    }

    public function test_quiz_has_essay_questions_when_it_has_not(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with two non-essay questions.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $this->add_two_regular_questions($questiongenerator, $quiz);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        self::assertFalse($report->quiz_has_essay_questions());
    }

    public function test_quiz_has_essay_questions_when_it_is_empty(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and an empty quiz.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        self::assertFalse($report->quiz_has_essay_questions());
    }

    public function test_long_names_being_shortened(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $quiz->name = 'ThisQuizHasAnExtremelyLongTitleBecauseLongTitlesAreJustSoCoolToHave';
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz);

        // Add a student with a very long name and create an attempt.
        $student = \phpunit_util::get_data_generator()->create_user(
            [
                'firstname' => 'ExtremelyLongFirstNameForThisVerySpecificPerson',
                'lastname' => 'OneThingIsSureThisLastNameIsNotGoingToEndVerySoon',
            ]
        );
        \phpunit_util::get_data_generator()->enrol_user($student->id, $course->id, 'student');
        $attempt = $this->attempt_quiz($quiz, $student);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();

        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Use reflection to force shortening of names.
        $reflectedreport = new \ReflectionClass($report);
        $reflectedoptions = $reflectedreport->getProperty('options');
        $reflectedoptions->setAccessible(true);
        $options = new quiz_essaydownload_options('essaydownload', $quiz, $cm, $course);
        $options->shortennames = true;
        $reflectedoptions->setValue($report, $options);

        // Fetch the attemps using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);

        // There should be exactly one attempt.
        self::assertCount(1, $fetchedattempts);

        $i = 0;
        foreach ($fetchedattempts as $fetchedid => $fetcheddata) {
            // The attempt is stored in a somewhat obscure way.
            $attemptobj = $attempt[2]->get_attempt();

            $id = $attemptobj->id;
            self::assertEquals($id, $fetchedid);
            self::assertEquals($student->firstname, $fetcheddata['firstname']);
            self::assertEquals($student->lastname, $fetcheddata['lastname']);

            $firstname = clean_filename(str_replace(' ', '_', substr($student->firstname, 0, 40)));
            $lastname = clean_filename(str_replace(' ', '_', substr($student->lastname, 0, 40)));

            $name = $lastname . '_' . $firstname . '_' . $id . '_' . date('Ymd_His', $attemptobj->timefinish);

            // We will not compare the minutes and seconds, because there might be a small difference and
            // we don't really care. If the timestamp is correct up to the hours, we can safely assume the
            // conversion worked.
            self::assertStringStartsWith(substr($name, 0, -4), $fetcheddata['path']);
            $i++;
        }

        // Fetch details for first attempt and test whether the prefix ist Q_1 instead of Question_1.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);
        self::assertCount(1, $details);
        self::assertStringStartsWith('Q_1_-_', array_keys($details)[0]);
    }

    public function test_custom_name_order(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $quiz->name = 'ThisQuizHasAnExtremelyLongTitleBecauseLongTitlesAreJustSoCoolToHave';
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz);

        // Add a student and an attempt.
        $student = \phpunit_util::get_data_generator()->create_user(['firstname' => 'First', 'lastname' => 'Last']);
        \phpunit_util::get_data_generator()->enrol_user($student->id, $course->id, 'student');
        $attempt = $this->attempt_quiz($quiz, $student);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();

        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Use reflection to force other name format.
        $reflectedreport = new \ReflectionClass($report);
        $reflectedoptions = $reflectedreport->getProperty('options');
        $reflectedoptions->setAccessible(true);
        $options = new quiz_essaydownload_options('essaydownload', $quiz, $cm, $course);
        $options->nameordering = 'firstlast';
        $reflectedoptions->setValue($report, $options);

        // Fetch the attemps using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);

        // There should be exactly one attempt.
        self::assertCount(1, $fetchedattempts);

        $i = 0;
        foreach ($fetchedattempts as $fetchedid => $fetcheddata) {
            // The attempt is stored in a somewhat obscure way.
            $attemptobj = $attempt[2]->get_attempt();

            $id = $attemptobj->id;
            self::assertEquals($id, $fetchedid);
            self::assertEquals($student->firstname, $fetcheddata['firstname']);
            self::assertEquals($student->lastname, $fetcheddata['lastname']);

            $firstname = clean_filename(str_replace(' ', '_', $student->firstname));
            $lastname = clean_filename(str_replace(' ', '_', $student->lastname));

            $name = $firstname . '_' . $lastname . '_' . $id . '_' . date('Ymd_His', $attemptobj->timefinish);

            // We will not compare the minutes and seconds, because there might be a small difference and
            // we don't really care. If the timestamp is correct up to the hours, we can safely assume the
            // conversion worked.
            self::assertStringStartsWith(substr($name, 0, -4), $fetcheddata['path']);
            $i++;
        }
    }

    /**
     * Provide data to test filtering the first/last/best attempt.
     *
     * @return Generator
     */
    public static function provide_grademethods(): Generator {
        yield ['firstattempt', QUIZ_GRADEHIGHEST];
        yield ['', QUIZ_GRADEAVERAGE];
        yield ['firstattempt', QUIZ_ATTEMPTFIRST];
        yield ['secondattempt', QUIZ_ATTEMPTLAST];
    }

    /**
     * Test filtering of the first/last/best attempt.
     *
     * @dataProvider provide_grademethods
     *
     * @param string $expectedattempt which attempt should be fetched
     * @param string $grademethod quiz grading method, e.g. highest grade, first attempt
     * @return void
     */
    public function test_get_best_attempt($expectedattempt, $grademethod): void {
        $this->resetAfterTest();

        // Create a course and a quiz with an essay and a shortanswer (frog/toad) question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = quiz_essaydownload_test_helper::add_quiz_with_grademethod($course, $grademethod);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz);
        $questioncategory = $questiongenerator->create_question_category();
        $saquestion = $questiongenerator->create_question('shortanswer', null, ['category' => $questioncategory->id]);
        quiz_add_quiz_question($saquestion->id, $quiz);

        // Add one student and some attempts.
        $student = \phpunit_util::get_data_generator()->create_user(['firstname' => 'John', 'lastname' => 'Doe']);
        \phpunit_util::get_data_generator()->enrol_user($student->id, $course->id, 'student');

        // Returned attempt will be an array [$quizobj, $quba, $attemptobj].
        $firstattempt = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);
        $timenow = time();
        $tosubmit = [
            1 => ['answer' => 'foobar', 'answerformat' => FORMAT_HTML],
            2 => ['answer' => 'frog', 'answerformat' => FORMAT_PLAIN],
        ];
        $firstattempt[2]->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($firstattempt[2], $timenow);

        $secondattempt = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student, 2);
        $tosubmit = [
            1 => ['answer' => 'foobar', 'answerformat' => FORMAT_HTML],
            2 => ['answer' => 'wrong answer', 'answerformat' => FORMAT_PLAIN],
        ];
        $secondattempt[2]->process_submitted_actions($timenow + 10, false, $tosubmit);
        $this->process_submit_or_finish($secondattempt[2], $timenow);

        // Init report and fetch the attemps.
        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);

        // If no filtering is set, we should get both attempts.
        self::assertCount(2, $fetchedattempts);

        // Use reflection to force text source to plain (i. e. summary).
        $reflectedreport = new \ReflectionClass($report);
        $reflectedoptions = $reflectedreport->getProperty('options');
        $reflectedoptions->setAccessible(true);
        $options = new quiz_essaydownload_options('essaydownload', $quiz, $cm, $course);
        $options->onlyone = true;
        $reflectedoptions->setValue($report, $options);

        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        if (empty($expectedattempt)) {
            self::assertCount(2, $fetchedattempts);
        } else {
            self::assertCount(1, $fetchedattempts);
            self::assertEquals(array_keys($fetchedattempts)[0], ${$expectedattempt}[2]->get_attemptid());
        }
    }

    public function test_get_attempts_and_names_without_groups(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz);

        // Add some students and attempts.
        $students = quiz_essaydownload_test_helper::add_students($course);
        foreach ($students as $student) {
            $attempts[] = $this->attempt_quiz($quiz, $student);
        }

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attemps using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);

        // Every student has made just one attempt, so the counts should match.
        self::assertCount(count($students), $fetchedattempts);

        $i = 0;
        foreach ($fetchedattempts as $fetchedid => $fetcheddata) {
            // The attempt is stored in a somewhat obscure way.
            $attemptobj = $attempts[$i][2]->get_attempt();

            $id = $attemptobj->id;
            self::assertEquals($id, $fetchedid);
            self::assertEquals($students[$i]->firstname, $fetcheddata['firstname']);
            self::assertEquals($students[$i]->lastname, $fetcheddata['lastname']);

            $firstname = clean_filename(str_replace(' ', '_', $students[$i]->firstname));
            $lastname = clean_filename(str_replace(' ', '_', $students[$i]->lastname));

            $name = $lastname . '_' . $firstname . '_' . $id . '_' . date('Ymd_His', $attemptobj->timefinish);

            // We will not compare the minutes and seconds, because there might be a small difference and
            // we don't really care. If the timestamp is correct up to the hours, we can safely assume the
            // conversion worked.
            self::assertStringStartsWith(substr($name, 0, -4), $fetcheddata['path']);
            $i++;
        }
    }

    public function test_get_attempts_and_names_with_separated_groups(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with an essay question. The quiz is configured to have
        // separate groups.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quizgenerator = $generator->get_plugin_generator('mod_quiz');
        $quiz = $quizgenerator->create_instance(['course' => $course->id, 'sumgrades' => 2, 'groupmode' => SEPARATEGROUPS]);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz);

        // Add some students and attempts.
        $students = quiz_essaydownload_test_helper::add_students($course);
        foreach ($students as $student) {
            $attempts[] = $this->attempt_quiz($quiz, $student);
        }

        // Add the students to different groups. Taking the second student for group 1 and the
        // others for group 2.
        $group1 = $generator->create_group(['courseid' => $course->id]);
        $group2 = $generator->create_group(['courseid' => $course->id]);
        $generator->create_group_member(['groupid' => $group1->id, 'userid' => $students[1]->id]);
        for ($i = 0; $i < count($students); $i++) {
            if ($i == 1) {
                continue;
            }
            $generator->create_group_member(['groupid' => $group2->id, 'userid' => $students[$i]->id]);
        }

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attemps using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);

        // The first group is automatically active and has only one student.
        self::assertCount(1, $fetchedattempts);

        // The attempt is stored in a somewhat obscure way.
        $attemptobj = $attempts[1][2]->get_attempt();

        $id = $attemptobj->id;
        self::assertEquals($id, array_keys($fetchedattempts)[0]);

        // Comparing to the second student.
        self::assertEquals($students[1]->firstname, $fetchedattempts[$id]['firstname']);
        self::assertEquals($students[1]->lastname, $fetchedattempts[$id]['lastname']);
        $firstname = clean_filename(str_replace(' ', '_', $students[1]->firstname));
        $lastname = clean_filename(str_replace(' ', '_', $students[1]->lastname));
        $name = $lastname . '_' . $firstname . '_' . $id . '_' . date('Ymd_His', $attemptobj->timefinish);

        // We will not compare the minutes and seconds, because there might be a small difference and
        // we don't really care. If the timestamp is correct up to the hours, we can safely assume the
        // conversion worked.
        self::assertStringStartsWith(substr($name, 0, -4), $fetchedattempts[$id]['path']);

        // Now, add one more student to group 1 and refetch. We'll just check the count.
        $generator->create_group_member(['groupid' => $group1->id, 'userid' => $students[0]->id]);
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(2, $fetchedattempts);

        // Finally, adding a (non-editing) teacher to group 2 with the three students. After
        // re-initialisation of the report, we should now get 3 attempts.
        $teacher = $generator->create_user();
        $generator->enrol_user($teacher->id, $course->id, 'teacher');
        $generator->create_group_member(['groupid' => $group2->id, 'userid' => $teacher->id]);
        $this->setUser($teacher);
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(3, $fetchedattempts);
    }

    public function test_get_attempts_and_names_with_unfinished_attempt(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz);

        // Add some students and attempts. The first student only *starts* their attempt.
        $students = quiz_essaydownload_test_helper::add_students($course);
        foreach ($students as $i => $student) {
            if ($i == 0) {
                quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);
                continue;
            }
            $attempts[] = $this->attempt_quiz($quiz, $student);
        }

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attemps using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);

        // Every student has made just one attempt, so the counts should match. We don't test the
        // rest, because that's already covered by other tests.
        self::assertCount(count($students) - 1, $fetchedattempts);
    }

    public function test_get_details_for_attempt_with_single_essay_question(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz, [
            'name' => 'My Question Title / Test',
            'questiontext' => ['text' => 'Go write your stuff!', 'format' => FORMAT_PLAIN],
        ]);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a response and finish the attempt.
        $timenow = time();
        $tosubmit = [1 => ['answer' => 'Here we go.', 'answerformat' => FORMAT_PLAIN]];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);

        // We expect the result to be an array with one element. The first key should be
        // a unique label for the (only) question, containing the question number and its title.
        // The value should be another array consisting of three keys (questiontext, responsetext, attachments).
        // There are no attachments in this situation, so this will be an empty array. The other
        // two are strings. The response text should not contain any HTML tags anymore.
        self::assertCount(1, $details);
        foreach ($details as $label => $detail) {
            self::assertEquals('Question_1_-_My_Question_Title__Test', $label);
            self::assertEquals('Go write your stuff!', trim($detail['questiontext']));
            self::assertEquals('Here we go.', trim($detail['responsetext']));
            self::assertEmpty($detail['attachments']);
        }
    }

    public function test_get_details_for_attempt_with_two_essay_questions(): void {
        $this->resetAfterTest();

        // Prepare some data...
        $questionsandanswers = [
            1 => ['name' => 'My Question Title / Test', 'text' => 'Go write your stuff!', 'response' => 'First answer.'],
            2 => ['name' => 'Second Question', 'text' => 'Write more!', 'response' => 'Second answer.'],
        ];

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        foreach ($questionsandanswers as $data) {
            quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz, [
                'name' => $data['name'],
                'questiontext' => ['text' => $data['text'], 'format' => FORMAT_PLAIN],
            ]);
        }

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a response and finish the attempt.
        $timenow = time();
        $tosubmit = [
            1 => ['answer' => $questionsandanswers[1]['response'], 'answerformat' => FORMAT_PLAIN],
            2 => ['answer' => $questionsandanswers[2]['response'], 'answerformat' => FORMAT_PLAIN],
        ];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);

        // We expect the result to be an array with one element. The first key should be
        // a unique label for the (only) question, containing the question number and its title.
        // The value should be another array consisting of three keys (questiontext, responsetext, attachments).
        // There are no attachments in this situation, so this will be an empty array. The other
        // two are strings. The response text should not contain any HTML tags anymore.
        self::assertCount(2, $details);
        $i = 1;
        foreach ($details as $label => $detail) {
            $cleanedname = clean_filename(str_replace(' ', '_', $questionsandanswers[$i]['name']));
            self::assertEquals('Question_' . $i . '_-_' . $cleanedname, $label);
            self::assertEquals($questionsandanswers[$i]['text'], $detail['questiontext']);
            self::assertEquals($questionsandanswers[$i]['response'], trim($detail['responsetext']));
            self::assertEmpty($detail['attachments']);
            $i++;
        }
    }

    public function test_get_details_for_attempt_with_one_essay_and_two_other_questions(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with an essay question and another one.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $this->add_two_regular_questions($questiongenerator, $quiz);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz, [
            'name' => 'My Question Title / Test',
            'questiontext' => ['text' => 'Go write your stuff!', 'format' => FORMAT_PLAIN],
        ]);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a response and finish the attempt. Note that the first question will be shortanswer
        // asking for an amphibian and second question is numerical asking for pi to two decimal places.
        $timenow = time();
        $tosubmit = [
            1 => ['answer' => 'frog'],
            2 => ['answer' => '3.14'],
            3 => ['answer' => 'Here we go.', 'answerformat' => FORMAT_PLAIN],
        ];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);

        // We expect the result to be an array with one element. The first key should be
        // a unique label for the (only) question, containing the question number and its title.
        // The value should be another array consisting of three keys (questiontext, responsetext, attachments).
        // There are no attachments in this situation, so this will be an empty array. The other
        // two are strings. The response text should not contain any HTML tags anymore.
        self::assertCount(1, $details);
        foreach ($details as $label => $detail) {
            self::assertEquals('Question_3_-_My_Question_Title__Test', $label);
            self::assertEquals('Go write your stuff!', trim($detail['questiontext']));
            self::assertEquals('Here we go.', trim($detail['responsetext']));
            self::assertEmpty($detail['attachments']);
        }
    }

    public function test_get_details_for_attempt_with_random_nonessay_question(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with an essay question and another one.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_random_nonessay_question($questiongenerator, $quiz);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a response and finish the attempt. Note that the random question will surely
        // resolve to a shortanswer question.
        $timenow = time();
        $tosubmit = [
            1 => ['answer' => 'frog'],
        ];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // The quiz contains a random question, so the rough first check should return true.
        self::assertTrue($report->quiz_has_essay_questions());

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details. The result should be empty, because the attempt does not
        // contain any essay questions.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);
        self::assertEmpty($details);
    }

    public function test_get_details_for_attempt_with_random_essay_question(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with an essay question and another one.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_random_essay_question($questiongenerator, $quiz);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a response and finish the attempt. Note that the random question will surely
        // resolve to a shortanswer question.
        $timenow = time();
        $tosubmit = [
            1 => ['answer' => 'Foo Bar Quak.', 'answerformat' => FORMAT_PLAIN],
        ];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details. The array should contain one row. The 'attachments' sub-array should
        // be empty.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);
        self::assertCount(1, $details);
        foreach ($details as $label => $detail) {
            self::assertEquals('Question_1_-_Essay_question_(HTML_editor)', $label);
            self::assertEquals('Please write a story about a frog.', trim($detail['questiontext']));
            self::assertEquals('Foo Bar Quak.', trim($detail['responsetext']));
            self::assertEmpty($detail['attachments']);
        }
    }

    public function test_get_details_for_attempt_with_text_and_attachment(): void {
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz, [
            'name' => 'My Question Title / Test',
            'questiontext' => ['text' => 'Go write your stuff!', 'format' => FORMAT_PLAIN],
            'responseformat' => 'editorfilepicker',
            'attachments' => 2,
        ]);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a first response.
        $this->setUser($student);
        $usercontextid = \context_user::instance($USER->id)->id;

        $attachementsdraftid = file_get_unused_draft_itemid();
        quiz_essaydownload_test_helper::save_file_to_draft_area($usercontextid, $attachementsdraftid, 'greeting.txt', 'Foobar');
        $timenow = time();
        $tosubmit = [1 => [
            'answer' => 'Foo.',
            'answerformat' => FORMAT_PLAIN,
            'answer:itemid' => 1,
            'attachments' => $attachementsdraftid,
        ]];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);

        // Submit a second response.
        $attachementsdraftid = file_get_unused_draft_itemid();
        quiz_essaydownload_test_helper::save_file_to_draft_area(
            $usercontextid,
            $attachementsdraftid,
            'greeting.txt',
            'Hello world!'
        );

        // Submit a response and finish the attempt.
        $timenow = time();
        $tosubmit = [1 => [
            'answer' => 'Here we go.',
            'answerformat' => FORMAT_PLAIN,
            'answer:itemid' => 1,
            'attachments' => $attachementsdraftid,
        ]];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Use reflection to force text source to plain (i. e. summary).
        $reflectedreport = new \ReflectionClass($report);
        $reflectedoptions = $reflectedreport->getProperty('options');
        $reflectedoptions->setAccessible(true);
        $options = new quiz_essaydownload_options('essaydownload', $quiz, $cm, $course);
        $options->source = 'plain';
        $reflectedoptions->setValue($report, $options);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);

        // We expect the result to be an array with one element. The data should match the
        // second response.
        self::assertCount(1, $details);
        foreach ($details as $label => $detail) {
            self::assertEquals('Question_1_-_My_Question_Title__Test', $label);
            self::assertEquals('Go write your stuff!', trim($detail['questiontext']));
            self::assertStringStartsWith('Here we go.', $detail['responsetext']);
            // Note the non-breaking space between the digit and 'bytes'.
            self::assertStringEndsWith("Attachments: greeting.txt (12\xc2\xa0bytes)", $detail['responsetext']);

            // There should be one attachment.
            self::assertCount(1, $detail['attachments']);

            // Fetch the file and compare the contents.
            $fs = get_file_storage();
            foreach ($detail['attachments'] as $hash => $storedfile) {
                $file = $fs->get_file_by_hash($hash);
                self::assertEquals('Hello world!', $file->get_content());
            }
        }
    }

    public function test_get_details_for_attempt_with_unanswered_question(): void {
        $this->resetAfterTest();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz, [
            'name' => 'My Question Title / Test',
            'questiontext' => ['text' => 'Go write your stuff!', 'format' => FORMAT_PLAIN],
        ]);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Finish the attempt without submitting an answer.
        $timenow = time();
        $tosubmit = [1 => ['answer' => '', 'answerformat' => FORMAT_PLAIN]];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);

        // We expect the result to be an array with one element. The first key should be
        // a unique label for the (only) question, containing the question number and its title.
        // The value should be another array consisting of three keys (questiontext, responsetext, attachments).
        // There are no attachments in this situation, so this will be an empty array. The other
        // two are strings. The response text should not contain any HTML tags anymore.
        self::assertCount(1, $details);
        foreach ($details as $label => $detail) {
            self::assertEquals('Question_1_-_My_Question_Title__Test', $label);
            self::assertEquals('Go write your stuff!', trim($detail['questiontext']));
            self::assertIsString($detail['responsetext']);
            self::assertEquals('', $detail['responsetext']);
            self::assertEmpty($detail['attachments']);
        }
    }

    public function test_pdf_from_summary_when_input_is_html(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz, [
            'name' => 'My Question Title / Test',
            'questiontext' => ['text' => '<p>Go write <strong>your</strong> stuff!</p>', 'format' => FORMAT_HTML],
        ]);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a response and finish the attempt.
        $timenow = time();
        $tosubmit = [1 => ['answer' => '<p>Here<br>we<br>go.</p><p>Foo</p><div>Bar</div>', 'answerformat' => FORMAT_HTML]];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Use reflection to force options.
        $reflectedreport = new \ReflectionClass($report);
        $reflectedoptions = $reflectedreport->getProperty('options');
        $reflectedoptions->setAccessible(true);
        $options = new quiz_essaydownload_options('essaydownload', $quiz, $cm, $course);
        $options->source = 'plain';
        $reflectedoptions->setValue($report, $options);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);

        // We expect the result to be an array with one element. The data should match the
        // second response.
        self::assertCount(1, $details);
        foreach ($details as $label => $detail) {
            self::assertEquals('Question_1_-_My_Question_Title__Test', $label);
            self::assertEquals('Go write YOUR stuff!<br />', trim($detail['questiontext']));
            self::assertStringStartsWith(
                "Here<br />\nwe<br />\ngo.<br />\n<br />\nFoo<br />\n<br />\nBar",
                $detail['responsetext']
            );
            self::assertCount(0, $detail['attachments']);
        }
    }

    public function test_pdf_from_html_when_input_is_html(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz, [
            'name' => 'My Question Title / Test',
            'questiontext' => ['text' => '<p>Go write <strong>your</strong> stuff!</p>', 'format' => FORMAT_HTML],
        ]);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a response and finish the attempt.
        $timenow = time();
        $tosubmit = [1 => ['answer' => '<p>Here <strong>we</strong> go.</p>', 'answerformat' => FORMAT_HTML]];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);

        // We expect the result to be an array with one element. The data should match the
        // second response.
        self::assertCount(1, $details);
        foreach ($details as $label => $detail) {
            self::assertEquals('Question_1_-_My_Question_Title__Test', $label);
            self::assertEquals('<p>Go write <strong>your</strong> stuff!</p>', trim($detail['questiontext']));
            self::assertStringStartsWith('<p>Here <strong>we</strong> go.</p>', $detail['responsetext']);
            self::assertCount(0, $detail['attachments']);
        }
    }

    public function test_pdf_from_html_when_input_is_plaintext_with_newlines(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz, [
            'name' => 'My Question Title / Test',
            'questiontext' => ['text' => '<p>Go write <strong>your</strong> stuff!</p>', 'format' => FORMAT_HTML],
        ]);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a response and finish the attempt.
        $timenow = time();
        $tosubmit = [1 => ['answer' => "Here\nwe\ngo.", 'answerformat' => FORMAT_PLAIN]];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);

        // We expect the result to be an array with one element. The data should match the
        // second response.
        self::assertCount(1, $details);
        foreach ($details as $label => $detail) {
            self::assertEquals('Question_1_-_My_Question_Title__Test', $label);
            self::assertEquals('<p>Go write <strong>your</strong> stuff!</p>', trim($detail['questiontext']));
            self::assertStringStartsWith("Here<br />\nwe<br />\ngo.", $detail['responsetext']);
            self::assertCount(0, $detail['attachments']);
        }
    }

    public function test_pdf_from_html_when_questiontext_is_forced_summary(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz, [
            'name' => 'My Question Title / Test',
            'questiontext' => ['text' => '<p>Go write <strong>your</strong> stuff!</p>', 'format' => FORMAT_HTML],
        ]);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a response and finish the attempt.
        $timenow = time();
        $tosubmit = [1 => ['answer' => '<p>Here <strong>we</strong> go.</p>', 'answerformat' => FORMAT_HTML]];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Use reflection to force options.
        $reflectedreport = new \ReflectionClass($report);
        $reflectedoptions = $reflectedreport->getProperty('options');
        $reflectedoptions->setAccessible(true);
        $options = new quiz_essaydownload_options('essaydownload', $quiz, $cm, $course);
        $options->forceqtsummary = true;
        $reflectedoptions->setValue($report, $options);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);

        // We expect the result to be an array with one element. The data should match the
        // second response.
        self::assertCount(1, $details);
        foreach ($details as $label => $detail) {
            self::assertEquals('Question_1_-_My_Question_Title__Test', $label);
            self::assertEquals('Go write YOUR stuff!<br />', trim($detail['questiontext']));
            self::assertStringStartsWith('<p>Here <strong>we</strong> go.</p>', $detail['responsetext']);
            self::assertCount(0, $detail['attachments']);
        }
    }

    public function test_txt_when_input_is_html(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        quiz_essaydownload_test_helper::add_essay_question($questiongenerator, $quiz, [
            'name' => 'My Question Title / Test',
            'questiontext' => ['text' => '<p>Go write <strong>your</strong> stuff!</p>', 'format' => FORMAT_HTML],
        ]);

        // Add a student and start an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);

        // Submit a response and finish the attempt.
        $timenow = time();
        $tosubmit = [1 => ['answer' => '<p>Here <strong>we</strong> go.</p>', 'answerformat' => FORMAT_HTML]];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Use reflection to force options.
        $reflectedreport = new \ReflectionClass($report);
        $reflectedoptions = $reflectedreport->getProperty('options');
        $reflectedoptions->setAccessible(true);
        $options = new quiz_essaydownload_options('essaydownload', $quiz, $cm, $course);
        $options->fileformat = 'txt';
        $options->source = 'plain';
        $reflectedoptions->setValue($report, $options);

        // Fetch the attemp using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        self::assertCount(1, $fetchedattempts);

        // Fetch the details.
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);

        // We expect the result to be an array with one element. The data should match the
        // second response.
        self::assertCount(1, $details);
        foreach ($details as $label => $detail) {
            self::assertEquals('Question_1_-_My_Question_Title__Test', $label);
            self::assertEquals('Go write YOUR stuff!', trim($detail['questiontext']));
            self::assertStringStartsWith('Here WE go.', $detail['responsetext']);
            self::assertCount(0, $detail['attachments']);
        }
    }

    /**
     * Provide data to test the Atto workaround. First line is the expected output,
     * second line is the input.
     *
     * @return Generator
     */
    public static function provide_texts_with_rem_font_span(): Generator {
        yield 'nothing' => [
                'foo bar',
                'foo bar',
        ];
        yield 'one span not font-size' => [
                    'foo <span>bli</span> bar',
                    'foo <span>bli</span> bar',
        ];
        yield 'one span with font-size not rem' => [
                'foo <span style="font-size: 15px;">bli</span> bar',
                'foo <span style="font-size: 15px;">bli</span> bar',
        ];
        yield 'one span with font-size rem' => [
                'foo <span style="font-size: 90%;">bli</span> bar',
                'foo <span style="font-size: 0.9rem;">bli</span> bar',
        ];
        yield 'one span with font-size rem and other attributes' => [
                'foo <span strangeattribute="yes" style="font-size: 90%;" anotherthing>bli</span> bar',
                'foo <span strangeattribute="yes" style="font-size: 0.9rem;" anotherthing>bli</span> bar',
        ];
        yield 'one span with font-size rem and other properties' => [
                'foo <span style="text-align: left; font-size: 90%; some-obscure-property: true;">bli</span> bar',
                'foo <span style="text-align: left; font-size: 0.9rem; some-obscure-property: true;">bli</span> bar',
        ];
        yield 'one span with font-size rem, uppercase' => [
                'foo <SPAN style="font-size: 90%;">bli</SPAN> bar',
                'foo <SPAN style="font-size: 0.9rem;">bli</SPAN> bar',
        ];
        yield 'one span with font-size rem, single quote' => [
                "foo <span style='font-size: 90%;'>bli</span> bar",
                "foo <span style='font-size: 0.9rem;'>bli</span> bar",
        ];
        yield 'two spans with font-size rem' => [
                'foo <span style="font-size: 90%;">bli</span> goo <span style="font-size: 75%;">dip</span> bar',
                'foo <span style="font-size: 0.9rem;">bli</span> goo <span style="font-size: 0.75rem;">dip</span> bar',
        ];
        yield 'one span font-size rem with whitespace' => [
                'foo <span style = "font-size:    90%;">bli</span> bar',
                'foo <span style = "font-size:    0.9   rem;">bli</span> bar',
        ];
    }

    /**
     * Test relative font-size conversion from rem to percent.
     *
     * @dataProvider provide_texts_with_rem_font_span
     *
     * @param string $expected expected output after conversion
     * @param string $input input
     * @return void
     */
    public function test_workaround_atto_font_size_issue(string $expected, string $input): void {
        $report = new quiz_essaydownload_report();

        self::assertEquals(
            $expected,
            $report->workaround_atto_font_size_issue($input)
        );
    }

    public function test_image_in_questiontext(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a quiz with an essay question.
        $generator = $this->getDataGenerator();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $generator->create_course();
        $quiz = $this->create_test_quiz($course);
        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('essay', null, [
            'category' => $cat->id,
            'name' => 'My Question Title / Test',
            'questiontext' => ['text' => '<p><img src="@@PLUGINFILE@@/image.png"</p>', 'format' => FORMAT_HTML],
        ]);
        quiz_add_quiz_question($question->id, $quiz);

        // Prepare image.
        $fs = get_file_storage();
        $fileinfo = [
            'contextid' => $cat->contextid,
            'component' => 'question',
            'filearea' => 'questiontext',
            'itemid' => $question->id,
            'filepath' => '/',
            'filename' => 'image.png',
        ];
        $file = $fs->create_file_from_pathname(
            $fileinfo,
            $CFG->dirroot . '/mod/quiz/report/essaydownload/tests/fixtures/image.png'
        );

        // Add a student submit an attempt.
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        [$quizobj, $quba, $attemptobj] = quiz_essaydownload_test_helper::start_attempt_at_quiz($quiz, $student);
        $timenow = time();
        $tosubmit = [1 => ['answer' => '<p>Here <strong>we</strong> go.</p>', 'answerformat' => FORMAT_HTML]];
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);
        $this->process_submit_or_finish($attemptobj, $timenow);

        // Initialize report.
        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $report = new quiz_essaydownload_report();
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            $report->init('essaydownload', 'quiz_essaydownload_form', $quiz, $cm, $course);

        // Fetch the attempt and details using the report's API.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);
        self::assertCount(1, $details);
        $questiontext = reset($details)['questiontext'];

        // Try to create a PDF from the question text.
        $e = null;
        try {
            $doc = new customTCPDF('P', 'mm', 'A4');
            $doc->AddPage();
            $doc->writeHTML($questiontext);
            $pdfoutput = $doc->Output('', 'S');
        } catch (Throwable $e) {
            $pdfoutput = '';
        }
        // There should be no error and the PDF should be larger than the image file itself.
        self::assertNull($e);
        $pdfsize = strlen($pdfoutput);
        self::assertGreaterThan($file->get_filesize(), $pdfsize);

        // Now, let's physically remove the file from the data directory. Normally, this is a very bad thing,
        // because it leads to inconsistencies. But in this case, we want to see what happens, when things break.
        // Also, we are at the end of the test, so a reset is going to happen just after this.
        $localpath = $fs->get_file_system()->get_local_path_from_storedfile($file);
        unlink($localpath);

        // Refetch.
        $fetchedattempts = $report->get_attempts_and_names($groupstudentjoins);
        $details = $report->get_details_for_attempt(array_keys($fetchedattempts)[0]);
        self::assertCount(1, $details);
        $questiontext = reset($details)['questiontext'];

        // Trying to generate a PDF again. There should be no error, but the image should be replaced by
        // [image.png], so the file size must be smaller.
        try {
            $doc = new customTCPDF('P', 'mm', 'A4');
            $doc->AddPage();
            $doc->writeHTML($questiontext);
            $pdfoutput = $doc->Output('', 'S');
        } catch (\Throwable $e) {
            $pdfoutput = '';
        }
        self::assertNull($e);
        self::assertLessThan($pdfsize, 0.8 * strlen($pdfoutput));
    }
}
