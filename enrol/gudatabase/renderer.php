<?php

class enrol_gudatabase_renderer extends plugin_renderer_base {

    /**
     * Print tabs for edit page
     * @param int $courseid
     * @param string $selected selected tab
     */
    public function print_tabs($courseid, $selected) {
        $rows = array();
        $rows[] = new tabobject(
            'config',
            new moodle_url('/enrol/gudatabase/edit.php', array('courseid' => $courseid, 'tab' => 'config')),
            get_string('config', 'enrol_gudatabase')
        );
        $rows[] = new tabobject(
            'codes',
            new moodle_url('/enrol/gudatabase/edit.php', array('courseid' => $courseid, 'tab' => 'codes')),
            get_string('codes', 'enrol_gudatabase')
        );
        $rows[] = new tabobject(
            'groups',
            new moodle_url('/enrol/gudatabase/edit.php', array('courseid' => $courseid, 'tab' => 'groups')),
            get_string('groups', 'enrol_gudatabase')
        );
        return $this->output->tabtree($rows, $selected) . '<p></p>';
    }

    /**
     * Print legacy codes
     */
    public function print_codes($courseid, $codes) {
        global $DB;

        $html = '<div class="alert alert-info">';
        if ($codes) {
            $html .= "<p><b>" . get_string('legacycodes', 'enrol_gudatabase') . "</b></p>";
            $html .= "<ul>";
            foreach ($codes as $code) {
                if ($codeinfo = $DB->get_record('enrol_gudatabase_codes', array('courseid'=>$courseid, 'code'=>$code))) {
                    $courseinfo = "{$codeinfo->subjectname} > {$codeinfo->coursename}";
                } else {
                    $courseinfo = get_string('nocourseinfo', 'enrol_gudatabase');
                }
	$html .= "<li><b>$code</b>&nbsp;&nbsp;&nbsp; ($courseinfo)</li>";
            }
            $html .= "</ul>";
        } else {
            $html .= '<p class="alert alert-warning">' . get_string('nolegacycodes', 'enrol_gudatabase') . '</p>';
        }

        $html .= '</div>';

        return $html;
    }
}
