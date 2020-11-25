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
 * Index file.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot. '/grade/querylib.php');
require_once($CFG->libdir.'/gradelib.php');

global $DB;

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/gugcat/'));
$PAGE->set_title(get_string('gugcat', 'local_gugcat'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('navname', 'local_gugcat'), new moodle_url('/local/gugcat'));
$PAGE->requires->css('/local/gugcat/styles/gcsa.css');

//testing course id = 1
$courseid = optional_param('id', 1, PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}
$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);
$context_course = context_course::instance($course->id);
$students = get_role_users(5 , $context_course);
$modinfo = get_fast_modinfo($courseid);
$mods = $modinfo->get_cms();

$ass_no_one = null;
$ass_no_two = null;
$arr_of_students = array();
$arr_of_students_with_assignments = array();

foreach($students as $keys => $student){
    // Store the fetched data to the array of arr_of_students
    $arr_of_students[$student->id] = (integer)$student->id;
}

$implode_user_id = implode(",", $arr_of_students);


$first_assigmnent_sql = $DB->get_records_sql("SELECT DISTINCT assignment FROM `mdl_assign_grades` WHERE userid IN('$implode_user_id') LIMIT 1 OFFSET 0");
$second_assigment_sql = $DB->get_records_sql("SELECT DISTINCT assignment FROM `mdl_assign_grades` WHERE userid IN('$implode_user_id') LIMIT 1 OFFSET 1");

foreach($first_assigmnent_sql as $ass_1){
    foreach($ass_1 as $ass_value){
        $ass_no_one = $ass_value;
    }
}

foreach($second_assigment_sql as $ass_2){
    foreach($ass_2 as $ass_value){
        $ass_no_two = $ass_value;
    }
}

// Assignment no 1.
$assign_one_grading_info = grade_get_grades($course->id, 'mod', 'assign', (integer)$ass_no_one, array_keys($arr_of_students));
// Assignment no 2.
$assign_two_grading_info = grade_get_grades($course->id, 'mod', 'assign', (integer)$ass_no_two, array_keys($arr_of_students));
// Exam no 1.
$exam_one_grading_info = grade_get_grades($course->id, 'mod', 'quiz', 1, array_keys($arr_of_students));
// Exam no 2.
$exam_two_grading_info = grade_get_grades($course->id, 'mod', 'quiz', 2, array_keys($arr_of_students));


foreach($students as $student_key => $student_value){
    if(sizeof($student) >= 1){
        $result = grade_get_course_grades($course->id, $student_value->id);

        $arr_of_students_with_assignments[$student_key] = (object) [
            "id"            => $student_value->id,
            "forename"      => $student_value->firstname,
            "surname"       => $student_value->lastname
        ];

        foreach($assign_one_grading_info->items[0] as $ass1_value){
            if(sizeof($ass1_value) >= 1){
                $arr_of_students_with_assignments[$student_key] = (object) [
                    "id"               => $student_value->id,
                    "forename"         => $student_value->firstname,
                    "surname"          => $student_value->lastname,
                    "assignment1"      => $ass1_value[$student_value->id]->grade,
                    "aggregate_grade"  => $result->grades[$student_value->id]->str_grade
                ];

                foreach($assign_two_grading_info->items[0] as $ass2_value){
                    if(sizeof($ass2_value) >= 1){
                        $arr_of_students_with_assignments[$student_key] = (object) [
                            "id"               => $student_value->id,
                            "forename"         => $student_value->firstname,
                            "surname"          => $student_value->lastname,
                            "assignment1"      => $ass1_value[$student_value->id]->grade,
                            "assignment2"      => $ass2_value[$student_value->id]->grade,
                            "aggregate_grade"  => $result->grades[$student_value->id]->str_grade
                        ];

                        foreach($exam_one_grading_info->items[0] as $exam1_value){
                            if(sizeof($exam1_value) >= 1){
                                $arr_of_students_with_assignments[$student_key] = (object) [
                                    "id"               => $student_value->id,
                                    "forename"         => $student_value->firstname,
                                    "surname"          => $student_value->lastname,
                                    "assignment1"      => $ass1_value[$student_value->id]->grade,
                                    "assignment2"      => $ass2_value[$student_value->id]->grade,
                                    "exam1"            => $exam1_value[$student_value->id]->grade,
                                    "aggregate_grade"  => $result->grades[$student_value->id]->str_grade
                                ];
                                
                                foreach($exam_two_grading_info->items[0] as $exam2_value){
                                    if(sizeof($exam2_value) >= 1){
                                        $arr_of_students_with_assignments[$student_key] = (object) [
                                            "id"               => $student_value->id,
                                            "forename"         => $student_value->firstname,
                                            "surname"          => $student_value->lastname,
                                            "assignment1"      => $ass1_value[$student_value->id]->grade,
                                            "assignment2"      => $ass2_value[$student_value->id]->grad,
                                            "exam1"            => $exam1_value[$student_value->id]->grade,
                                            "exam2"            => $exam2_value[$student_value->id]->grade,
                                            "aggregate_grade"  => $result->grades[$student_value->id]->str_grade
                                        ];
                                    }
                                }

                            }
                        }
                    }
                }      
            }
        }
    }
}

$templatecontext = (object)[
    'title' =>get_string('title', 'local_gugcat'),
    'assessmenttabstr' =>get_string('assessmentlvlscore', 'local_gugcat'),
    'overviewtabstr' =>get_string('overviewaggregrade', 'local_gugcat'),
    'addsavebtnstr' =>get_string('saveallgrade', 'local_gugcat'),
    'approvebtnstr' =>get_string('approvegrades', 'local_gugcat'),
    'students' => array_values($arr_of_students_with_assignments),
    'activities' => array_values($mods)
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_gugcat/index', $templatecontext);
echo $OUTPUT->footer();