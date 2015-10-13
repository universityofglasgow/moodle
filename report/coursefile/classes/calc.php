<?php


class report_coursefile_calc {

    /**
     * Get list of files given (course) context
     * @param object $context
     * @return array list of files
     */
    static function get_filelist($context) {
        global $DB;

        $children = $context->get_child_contexts();

        // store files
        $filelist = array();

        // loop over contexts looking for files
        $contextids = array_keys($children);
        foreach ($contextids as $contextid) {
            if ($files = $DB->get_records('files', array('contextid' => $contextid))) {
                foreach ($files as $file) {
                    if ($file->filename == '.') {
                        continue;
                    }
                    $filelist[] = $file;
                }
            }
        }

        return $filelist;
    }

}
