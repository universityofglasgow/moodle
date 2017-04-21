<?php

/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_gismo extends block_base {

    protected $course;

    public function init() {
        $this->title = get_string('gismo', 'block_gismo');
    }

    public function has_config() {
        return true;
    }

    public function specialization() {
        global $DB, $COURSE;
        $this->course = $DB->get_record('course', array('id' => $COURSE->id));
    }

    public function get_content() {
        global $OUTPUT, $DB, $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        // init content
        $this->content = new stdClass;

        //Get block_gismo settings
        $gismoconfig = get_config('block_gismo');

        if (isset($gismoconfig->student_reporting)) {
            if ($gismoconfig->student_reporting === "false") {
                //check gismo:view capability
                if (!has_capability('block/gismo:view', context_block::instance($this->instance->id))) {
                    // return empty content
                    return $this->content;
                }
            }
        }
        //Check if setting exportlogs exists
        if (empty($gismoconfig->exportlogs)) {
            $this->content->text = html_writer::tag('span', get_string("exportlogs_missing", "block_gismo"));
            $this->content->text .= $OUTPUT->help_icon('gismo', 'block_gismo');
            $this->content->footer = '';
            return $this->content;
        }
        //Check if exportlogs is "course" & there are logs
        if ($gismoconfig->exportlogs == 'course') {
            if (!($last_export_max_log_id = $DB->record_exists("block_gismo_config", array("name" => "last_export_max_log_id_" . $this->course->id))) || $last_export_max_log_id == 0) {
                $this->content->text = html_writer::tag('span', get_string("exportlogs_missingcourselogs", "block_gismo"));
                $this->content->text .= $OUTPUT->help_icon('gismo', 'block_gismo');
                $this->content->footer = '';
                return $this->content;
            }
        }


        // server data
        $data = new stdClass();
        $data->block_instance_id = $this->instance->id;
        $data->course_id = $this->course->id;
        $srv_data_encoded = urlencode(base64_encode(serialize($data)));

        // moodle bug fix
        $fix_style = "line-height: 20px; vertical-align: top;";

        // block content
        $this->content->text = html_writer::empty_tag('img', array('src' => '../blocks/gismo/images/gismo.gif', 'alt' => '', 'style' => $fix_style));
        $this->content->text .= html_writer::tag('span', '&nbsp;');
        if ($this->check_data() === true) {
            /**
             * Modifications: remove 'target' => '_blank' when behat enable
             * @link https://github.com/moodle/moodle/blob/MOODLE_25_STABLE/lib/behat/classes/util.php (is_test_mode_enabled() function)
             * @author Corbière Alain <alain.corbiere@univ-lemans.fr>
             */            
            if (defined('BEHAT_SITE_RUNNING')) {
                $this->content->text .= html_writer::tag('a', get_string("gismo_report_launch", "block_gismo"), array('href' => '../blocks/gismo/main.php?srv_data=' . $srv_data_encoded));
            } else {
                $this->content->text .= html_writer::tag('a', get_string("gismo_report_launch", "block_gismo"), array('href' => '../blocks/gismo/main.php?srv_data=' . $srv_data_encoded, 'target' => '_blank'));
            }
        } else {
            $this->content->text .= html_writer::tag('span', strtoupper(get_string("gismo", "block_gismo")) . ' (disabled)');
            $this->content->text .= $OUTPUT->help_icon('gismo', 'block_gismo');
        }
        $this->content->footer = '';


        // return content
        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function instance_allow_config() {
        return false;
    }

    public function applicable_formats() {
        return array('site' => false, 'course-view' => true);
    }

    public function check_data() {
        global $CFG;
        // FetchStaticDataMoodle instance
        $gismo_static_data = new block_gismo\FetchStaticDataMoodle($this->course->id, "teacher");
        $gismo_static_data->init();
        // check
        return $gismo_static_data->checkData();
    }

    public function cron() {
        //Disabled CRON
        return false;
    }

}

?>
