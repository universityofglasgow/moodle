<?php


class report_coursefile_calc {

    static $filesum = 0;

    /**
     * Get list of files given (course) context
     * @param object $context
     * @return array list of files
     */
    function get_filelist($context) {
        global $DB;

        $children = $context->get_child_contexts();

        // loop over contexts looking for files
        $contextids = array_keys($children);
        foreach ($contextids as $contextid) {
            $files = $DB->get_records('files', array('contextid' => $contextid));
            foreach ($files as $file) {
                if ($file->filename == '.') {
                    continue;
                }
                $filesum += $file->filesize;
                $filelist[] = $file;
            }
        }

        return $filelist;
    }

    /**
     * Get total
     */
    public function get_filesum() {
        return $filesum;
    }

}
