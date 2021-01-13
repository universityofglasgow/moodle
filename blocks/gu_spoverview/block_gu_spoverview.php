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
 * Contains the class for the UofG Assessments Overview block.
 *
 * @package    block_gu_spoverview
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
define('SPOVERVIEW_STRINGS', 'block_gu_spoverview');
require_once($CFG->dirroot . '/blocks/gu_spdetails/block_gu_spdetails.php');

class block_gu_spoverview extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_gu_spoverview');
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $USER, $DB, $PAGE, $OUTPUT;

        $PAGE->requires->css('/blocks/gu_spoverview/styles.css');
        $userid = $USER->id;

        $courses = enrol_get_all_users_courses($userid, true);
        $courseids = array_column($courses, 'id');

        $assessments = block_gu_spdetails::return_assessments($courseids, $userid);
        $count = self::return_assessments_count($assessments);

        // Set singular/plural strings for Assessments submitted and Assessments marked
        $submitted_str = ($count->submitted == 1) ? get_string('assessment', SPOVERVIEW_STRINGS) :
                                                    get_string('assessments', SPOVERVIEW_STRINGS);
        $marked_str = ($count->marked == 1) ? get_string('assessment', SPOVERVIEW_STRINGS) :
                                              get_string('assessments', SPOVERVIEW_STRINGS);

        $assessments_submitted_icon = $OUTPUT->image_url('assessments_submitted', 'theme');
        $assessments_tosubmit_icon = $OUTPUT->image_url('assessments_tosubmit', 'theme');
        $assessments_overdue_icon = $OUTPUT->image_url('assessments_overdue', 'theme');
        $assessments_marked_icon = $OUTPUT->image_url('assessments_marked', 'theme');

        $templatecontext = (object)[
            'assessments_submitted'        => $count->submitted,
            'assessments_tosubmit'         => $count->tosubmit,
            'assessments_overdue'          => $count->overdue,
            'assessments_marked'           => $count->marked,
            'assessments_submitted_icon'   => $assessments_submitted_icon,
            'assessments_tosubmit_icon'    => $assessments_tosubmit_icon,
            'assessments_overdue_icon'     => $assessments_overdue_icon,
            'assessments_marked_icon'      => $assessments_marked_icon,
            'assessments_submitted_str'    => $submitted_str.get_string('submitted', SPOVERVIEW_STRINGS),
            'assessments_tosubmit_str'     => get_string('tobesubmitted', SPOVERVIEW_STRINGS),
            'assessments_overdue_str'      => get_string('overdue', SPOVERVIEW_STRINGS),
            'assessments_marked_str'       => $marked_str.get_string('marked', SPOVERVIEW_STRINGS),
        ];

        $this->content = new stdClass;
        $this->content->text = $OUTPUT->render_from_template('block_gu_spoverview/spoverview', $templatecontext);

        return $this->content;
    }

    /**
     * 
     * @param array $assessments
     * @return stdClass $counter Object containing count of Assessment records
     */
    public static function return_assessments_count($assessments) {
        $counter = new stdClass;
        $counter->submitted = 0;
        $counter->tosubmit = 0;
        $counter->overdue = 0;
        $counter->marked = 0;

        if (!empty($assessments)) {
            foreach($assessments as $assessment) {
                switch($assessment->status->suffix) {
                    case 'graded':
                        $counter->marked++;
                        $counter->submitted++;
                        break;
                    case 'overdue':
                        $counter->overdue++;
                        break;
                    case 'overduelinked':
                        $counter->tosubmit++;
                        $counter->overdue++;
                        break;
                    case 'submit':
                        $counter->tosubmit++;
                        break;
                    case 'submitted':
                        $counter->submitted++;
                        break;
                }
            }
        }

        return $counter;
    }
}
