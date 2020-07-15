<?php

namespace mod_coursework\export\csv\cells;

/**
 * Class name_cell
 */
class name_cell extends cell_base {

    /**
     * @param $submission
     * @param $student
     * @param $stage_identifier
     * @return string
     * @throws \coding_exception
     */
    public function get_cell($submission, $student, $stage_identifier){

        if ($this->can_view_hidden()){
            $name = $student->lastname . ' ' . $student->firstname;
        } else {
            $name = get_string('hidden', 'coursework');
        }

        return  $name;
    }

    /**
     * @param $stage
     * @return string
     * @throws \coding_exception
     */
    public function get_header($stage){
        return  get_string('studentname', 'coursework');
    }
}