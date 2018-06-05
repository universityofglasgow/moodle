<?php

namespace mod_coursework\plagiarism_helpers;

/**
 * Class turnitin
 * @package mod_coursework\plagiarism_helpers
 */
class urkund extends base {

    /**
     * @return string
     */
    public function file_submission_instructions() {
        return 'Urkund is only able to process certain kinds of files.';
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function enabled() {
        global $DB, $CFG;

        if ($CFG->enableplagiarism) {
            $plagiarismsettings = (array)get_config('plagiarism');
            if (!empty($plagiarismsettings['urkund_use'])) {
                $params = array(
                    'cm' => $this->get_coursework()->get_course_module()->id,
                    'name' => 'use_urkund',
                    'value' => 1
                );
                if ($DB->record_exists('plagiarism_urkund_config', $params)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function human_readable_name() {
        return get_string('urkund', 'plagiarism_urkund');
    }
}