<?php

namespace mod_coursework\export\csv\cells;
use mod_coursework\models\coursework;
use mod_coursework\models\deadline_extension;
use mod_coursework\grade_judge;
use mod_coursework\models\submission;
use mod_coursework\models\plagiarism_flag;
use gradingform_rubric_instance;

/**
 * Class cell_base
 */
abstract class cell_base implements cell_interface {

    /**
     * @var coursework
     */
    protected $coursework;
    protected $dateformat;
    protected $stages;
    protected $extension;
    protected $plagiarismflag;

    /**
     * @param $coursework
     */
    public function __construct($coursework) {

        $this->coursework = new coursework($coursework->id);
        $this->dateformat = '%a, %d %b %Y, %H:%M';
        $this->stages = $this->coursework->get_max_markers();
        $this->extension = new deadline_extension;
        $this->plagiarismflag = new plagiarism_flag;

    }


    /**
     * Function to check if a user can see real names/usernames even if blind marking is enabled
     * @return bool
     * @throws \coding_exception
     */
    public function can_view_hidden()    {

        $viewanonymous = has_capability('mod/coursework:viewanonymous',$this->coursework->get_context());
        $exportgrade   = has_capability('mod/coursework:canexportfinalgrades',$this->coursework->get_context());

        return (!$this->coursework->blindmarking || $viewanonymous || $exportgrade);
    }


    /**
     * Function to check if the student was given an extension
     * @param $student
     * @return bool
     */
    public function extension_exists($student){

        $extension = $this->extension->get_extension_for_student($student,$this->coursework);

        return ($this->coursework->extensions_enabled() && !empty($extension));
    }


    /**
     * Function to get student's extension date
     * @param $student
     * @return string
     */
    public function get_extension_date_for_csv($student){

        $extension = $this->extension->get_extension_for_student($student,$this->coursework);

        return userdate($extension->extended_deadline, $this->dateformat);
    }


    /**
     * Function to get extra information about student's extension
     * @param $student
     * @return string
     */
    public function get_extension_extra_info_for_csv($student){

        $extension = $this->extension->get_extension_for_student($student,$this->coursework);

        return strip_tags($extension->extra_information_text);
    }


    /**
     * Function to get student's extension pre-defined reason
     * @param $student
     * @return string
     */
    public function get_extension_reason_for_csv($student){

        $extension = $this->extension->get_extension_for_student($student,$this->coursework);
        $extension_reasons = $this->get_extension_predefined_reasons();

        return (!empty($extension_reasons[$extension->pre_defined_reason])) ?
            strip_tags($extension_reasons[$extension->pre_defined_reason]) : "";
    }


    /**
     * Function to get all pre-defined extension reasons
     * @return array
     */
    public function get_extension_predefined_reasons(){
        return $this->coursework->extension_reasons();
    }



    /**
     * Function to check if the plagiarism has been flagged for the given submission
     * @param $submission
     * @return bool
     */
    public function plagiarism_flagged($submission){

        $flag = $this->plagiarismflag->get_plagiarism_flag($submission);

        return ($this->coursework->plagiarism_flagging_enbled() && !empty($flag));
    }

    /**
     * Function to get student's plagiarism status
     * @param $submission
     * @return string
     */
    public function get_plagiarism_flag_status_for_csv($submission){

        $flag = $this->plagiarismflag->get_plagiarism_flag($submission);

        return get_string('plagiarism_'.$flag->status, 'mod_coursework');
    }

    /**
     * Function to get comment about student's plagiarism status
     * @param $submission
     * @return string
     */
    public function get_plagiarism_flag_comment_for_csv($submission){

        $flag = $this->plagiarismflag->get_plagiarism_flag($submission);

        return strip_tags($flag->comment);
    }


    /**
     * Function to get a grade that should be displayed
     * @param $grade
     * @return null
     */
    public function get_actual_grade($grade){

        $judge =  new grade_judge($this->coursework);

        return $judge->grade_to_display($grade);
    }


    /**
     * Function to get assessor's full name
     * @param $assessorid
     * @return string
     */
    public function get_assessor_name($assessorid){
        global $DB;

        $assessor = $DB->get_record('user',array('id'=>$assessorid),'firstname, lastname');

        return $assessor->lastname .' '. $assessor->firstname;
    }


    /**
     * Function to get assessor's username
     * @param $assessorid
     * @return string
     */
    public function get_assessor_username($assessorid)   {
        global $DB;

        $assessor = $DB->get_record('user',array('id'=>$assessorid),'username');

        return $assessor->username;
    }


    /**
     * Function to get a message if submission was made withihn the deadline
     * @param submission $submission
     */
    protected function submission_time($submission){

        if ($submission->is_late() && (!$submission->has_extension() || !$submission->submitted_within_extension())){
            $time =  get_string('late', 'coursework');
        } else {
            $time =  get_string('ontime', 'mod_coursework');
        }

        return $time;
    }

    /**
     * Function to get stage_identifier for the current assessor
     * @param $submission
     * @param $student
     * @return string
     */
    public function get_stage_identifier_for_assessor($submission, $student){
        global $DB, $USER;

        $stageidentifier = '';
        if ($this->coursework->allocation_enabled()){
            $stageidentifier = $this->coursework->get_assessors_stage_identifier($student->id, $USER->id);
        } else if($this->coursework->get_max_markers()>1) {
            // get existing feedback

          $sql = "SELECT * FROM {coursework_feedbacks}
                  WHERE submissionid= $submission->id
                  AND assessorid = $USER->id
                  AND stage_identifier <> 'final_agreed_1'";

            $feedback = $DB->get_record_sql($sql);
            if ($feedback) {
                $stageidentifier = $feedback->stage_identifier;
            }
        } else { // 1 marker only
            $stageidentifier = 'assessor_1';
        }

        return $stageidentifier;
    }

    /**
     * Function to validate cell for the file upload
     * @return mixed
     */
    public function validate_cell($value,$submissions,$stage_dentifier='', $uploadedgradecells = array()){
        return true;
    }


    /**
     * @param $grade
     * @param $gradedata
     */
    public function get_rubric_scores_gradedata($grade, &$gradedata){

        if($grade) {

            $controller = $this->coursework->get_advanced_grading_active_controller();
            $gradinginstance = $controller->get_or_create_instance(0, $grade->assessorid, $grade->id);
            /**
             * @var gradingform_rubric_instance $grade
             */
            $rubric_marks = $gradinginstance->get_rubric_filling();

            foreach ($rubric_marks['criteria'] as $id => $record) {
                $gradedata[] = $controller->get_definition()->rubric_criteria[$id]['levels'][$record['levelid']]['score'];
                $gradedata[] = $record['remark'];

            }
        } else {
            $criterias = $this->coursework->get_rubric_criteria();
            foreach ($criterias as $criteria) { // if no marks we need same amount of empty holders
                $gradedata[] = '';
                $gradedata[] = '';
            }
        }
    }


}