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
 * Helper for Essay responses downloader plugin tests (quiz_essaydownload)
 *
 * @package   quiz_essaydownload
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_essaydownload;

use advanced_testcase;

defined('MOODLE_INTERNAL') || die();

// This work-around is required until Moodle 4.2 is the lowest version we support.
if (class_exists('\mod_quiz\quiz_settings')) {
    class_alias('\mod_quiz\quiz_settings', '\quiz_essaydownload_quiz_settings_alias');
} else {
    require_once($CFG->dirroot . '/mod/quiz/classes/plugininfo/quiz.php');
    class_alias('\quiz', '\quiz_essaydownload_quiz_settings_alias');
}

/**
 * Helper class providing some useful methods for Essay responses downloader plugin unit
 * tests (quiz_essaydownload).
 *
 * @package   quiz_essaydownload
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_essaydownload_test_helper {
    /**
     * Helper method: Store a test file with a given name and contents in a
     * draft file area.
     *
     * @param int $usercontextid user context id.
     * @param int $draftitemid draft item id.
     * @param string $filename filename.
     * @param string $contents file contents.
     */
    public static function save_file_to_draft_area($usercontextid, $draftitemid, $filename, $contents) {
        $fs = get_file_storage();

        $filerecord = new \stdClass();
        $filerecord->contextid = $usercontextid;
        $filerecord->component = 'user';
        $filerecord->filearea = 'draft';
        $filerecord->itemid = $draftitemid;
        $filerecord->filepath = '/';
        $filerecord->filename = $filename;
        $fs->create_file_from_string($filerecord, $contents);
    }

    /**
     * Create a test quiz for the specified course, setting the grade method to a custom value.
     *
     * @param \stdClass $course
     * @param int $grademethod
     * @return  \stdClass
     */
    public static function add_quiz_with_grademethod(\stdClass $course, int $grademethod = QUIZ_GRADEHIGHEST): \stdClass {
        $quizgenerator = \phpunit_util::get_data_generator()->get_plugin_generator('mod_quiz');

        return $quizgenerator->create_instance([
            'course' => $course->id,
            'questionsperpage' => 0,
            'grade' => 100.0,
            'sumgrades' => 2,
            'grademethod' => $grademethod,
        ]);
    }

    /**
     * Helper method to add an essay question in quiz.
     *
     * @param component_generator_base $questiongenerator
     * @param \stdClass $quiz
     * @param array $override
     */
    public static function add_essay_question($questiongenerator, \stdClass $quiz, $override = []): void {
        $cat = $questiongenerator->create_question_category($override);
        $question = $questiongenerator->create_question('essay', null, ['category' => $cat->id] + $override);
        quiz_add_quiz_question($question->id, $quiz);
    }

    /**
     * Helper method to add a random question to quiz that will not resolve to an essay question.
     *
     * @param component_generator_base $questiongenerator
     * @param \stdClass $quiz
     * @param int $page
     * @param array $override
     */
    public static function add_random_nonessay_question($questiongenerator, \stdClass $quiz, int $page = 1, $override = []): void {
        $cat = $questiongenerator->create_question_category($override);
        $questiongenerator->create_question('shortanswer', null, ['category' => $cat->id]);
        $questiongenerator->create_question('shortanswer', null, ['category' => $cat->id]);

        if (class_exists('\qbank_managecategories\category_condition')) {
            $quizobj = \quiz_essaydownload_quiz_settings_alias::create($quiz->id);
            $structure = $quizobj->get_structure();
            $filtercondition = [
                'filter' => [
                    'category' => [
                        'jointype' => \qbank_managecategories\category_condition::JOINTYPE_DEFAULT,
                        'values' => [$cat->id],
                        'filteroptions' => ['includesubcategories' => false],
                    ],
                ],
            ];
            $structure->add_random_questions($page, 1, $filtercondition);
        } else {
            quiz_add_random_questions($quiz, $page, $cat->id, 1, false);
        }
    }

    /**
     * Helper method to add a random question to quiz that will surely resolve to an essay question.
     *
     * @param component_generator_base $questiongenerator
     * @param \stdClass $quiz
     * @param int $page
     * @param array $override
     */
    public static function add_random_essay_question($questiongenerator, \stdClass $quiz, int $page = 1, $override = []): void {
        $cat = $questiongenerator->create_question_category($override);
        $questiongenerator->create_question('essay', null, ['category' => $cat->id]);
        $questiongenerator->create_question('essay', null, ['category' => $cat->id]);

        if (class_exists('\qbank_managecategories\category_condition')) {
            $quizobj = \quiz_essaydownload_quiz_settings_alias::create($quiz->id);
            $structure = $quizobj->get_structure();
            $filtercondition = [
                'filter' => [
                    'category' => [
                        'jointype' => \qbank_managecategories\category_condition::JOINTYPE_DEFAULT,
                        'values' => [$cat->id],
                        'filteroptions' => ['includesubcategories' => false],
                    ],
                ],
            ];
            $structure->add_random_questions($page, 1, $filtercondition);
        } else {
            quiz_add_random_questions($quiz, $page, $cat->id, 1, false);
        }
    }

    /**
     * Helper method to add a few students to a course.
     *
     * @param \stdClass $course
     * @return \stdClass[] the generated students
     */
    public static function add_students(\stdClass $course): array {
        $names = [
            ['firstname' => 'John L.', 'lastname' => 'Doe'],
            ['firstname' => 'Jean', 'lastname' => 'D\'La Fontaine'],
            ['firstname' => 'Hans-Peter', 'lastname' => 'Müller'],
            ['firstname' => 'Little', 'lastname' => 'Bobby/Tables'],
        ];
        $students = [];
        foreach ($names as $i => $name) {
            $student = \phpunit_util::get_data_generator()->create_user($name);
            \phpunit_util::get_data_generator()->enrol_user($student->id, $course->id, 'student');
            $students[] = $student;
        }
        return $students;
    }

    /**
     * Start an attempt at a quiz for a user.
     *
     * @param \stdClass $quiz Quiz to attempt.
     * @param \stdClass $user A user to attempt the quiz.
     * @param int $attemptnumber
     * @return array
     */
    public static function start_attempt_at_quiz(\stdClass $quiz, \stdClass $user, $attemptnumber = 1): array {
        advanced_testcase::setUser($user);

        $starttime = time();
        $quizobj = \quiz_essaydownload_quiz_settings_alias::create($quiz->id, $user->id);

        $quba = \question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        // Start the attempt.
        $attempt = quiz_create_attempt($quizobj, $attemptnumber, null, $starttime, false, $user->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, $attemptnumber, $starttime);
        quiz_attempt_save_started($quizobj, $quba, $attempt);
        $attemptobj = \quiz_essaydownload_quiz_attempt_alias::create($attempt->id);

        advanced_testcase::setUser();

        return [$quizobj, $quba, $attemptobj];
    }
}
