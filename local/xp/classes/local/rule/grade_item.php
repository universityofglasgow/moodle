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
 * Grade item rule.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

use base_logger;
use block_xp_rule_base;
use context_course;
use grade_item as core_grade_item;
use html_writer;
use restore_dbops;

/**
 * Grade item rule.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_item extends block_xp_rule_base {

    /** @var config The configuration. */
    protected $config;

    /** @var int The course ID to use as basis for the form. */
    protected $courseid;

    /**
     * Constructor.
     *
     * @param int $courseid The course ID.
     * @param int $gradeitemid The grade item ID.
     */
    public function __construct($courseid = 0, $gradeitemid = 0) {
        global $COURSE;
        $this->courseid = empty($courseid) ? $COURSE->id : $courseid;
        $this->config = \block_xp\di::get('config');
        parent::__construct(self::EQ, $gradeitemid);
    }

    /**
     * Returns a string describing the rule.
     *
     * @return string
     */
    public function get_description() {
        return $this->get_display_name();
    }

    /**
     * Get display name.
     *
     * @return string
     */
    protected function get_display_name() {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');

        if (empty($this->value)) {
            return get_string('errorunknowngradeitem', 'local_xp');
        }

        $gradeitem = core_grade_item::fetch(['id' => $this->value]);
        if (!$gradeitem) {
            return get_string('errorunknowngradeitem', 'local_xp');
        }

        $name = $gradeitem->itemname;
        if ($gradeitem->itemtype === 'course') {
            $name = get_string('coursetotal', 'core_grades');
        } else if ($gradeitem->itemtype === 'category') {
            $category = $gradeitem->get_parent_category();
            $name = get_string('categorytotalfull', 'core_grades', ['category' => $category ? $category->fullname : '?']);
        }

        $str = 'rulegradeitemdesc';
        $strparams = ['gradeitemname' => $name];

        $coursecontext = false;
        if ($this->config->get('context') == CONTEXT_SYSTEM) {
            $coursecontext = context_course::instance($gradeitem->courseid);
        }
        if (!empty($coursecontext)) {
            $str = 'rulegradeitemdescwithcourse';
            $strparams['coursename'] = $coursecontext->get_context_name(false, true);
        }

        return get_string($str, 'local_xp', (object) $strparams);
    }

    /**
     * Returns a form element for this rule.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_form($basename) {
        return $this->get_advanced_form($basename);
    }

    /**
     * Get advanced form.
     *
     * This is used when we're using one block for the whole site,
     * we can't display all modules at once as there would be too many.
     *
     * @param string $basename The base name.
     * @return string
     */
    protected function get_advanced_form($basename) {
        $output = \block_xp\di::get('renderer');
        $hasgi = !empty($this->value);

        $o = parent::get_form($basename);
        $o .= html_writer::start_tag('span', ['class' => 'local_xp-grade-item-rule-widget ' . ($hasgi ? 'has-grade-item' : null)]);
        $o .= html_writer::empty_tag('input', [
            'name' => $basename . '[value]',
            'class' => 'grade-item-rule-itemid',
            'type' => 'hidden',
            'value' => $this->value
        ]);

        if (!$hasgi) {
            // We can only select the grade item once!
            static::init_page_requirements();
            $o .= html_writer::start_tag('span', ['class' => 'grade-item-selection']);
            $o .= html_writer::tag('button', get_string('clicktoselectgradeitem', 'local_xp'), ['class' => 'btn btn-warning']);
            $o .= html_writer::end_tag('span');
        }

        $o .= html_writer::start_tag('span', ['class' => 'grade-item-selected']);
        $o .= $this->get_display_name();
        $o .= html_writer::end_tag('span');

        $o .= $output->help_icon('rulegradeitem', 'local_xp');
        $o .= html_writer::end_tag('span');
        return $o;
    }

    /**
     * Get the value to use during comparison from the subject.
     *
     * @param mixed $subject The subject.
     * @return mixed The item ID.
     */
    protected function get_subject_value($subject) {
        if ($subject instanceof \core\event\user_graded) {
            return $subject->other['itemid'];
        }
        return 0;
    }

    /**
     * Update the rule after a restore.
     *
     * @return void
     */
    public function update_after_restore($restoreid, $courseid, base_logger $logger) {
        if (!empty($this->value)) {
            $newid = restore_dbops::get_backup_ids_record($restoreid, 'grade_item', $this->value);
            if (!$newid || !$newid->newitemid) {
                $logger->process("Could not find mapping for grade_item {$this->value}", backup::LOG_WARNING);
                return;
            }
            $this->value = (int) $newid->newitemid;
        }
    }

    /**
     * Initialise the page requirements.
     *
     * @return void
     */
    protected static function init_page_requirements() {
        global $PAGE, $COURSE;

        static $alreadydone = false;
        if ($alreadydone) {
            return;
        }
        $alreadydone = true;
        $issiteid = $COURSE->id == SITEID;

        $args = [];

        // This currently has no effect as when we use one block per course, the page always has the system context.
        if (!$issiteid) {
            $args[] = array_intersect_key((array) $COURSE, array_flip(['id', 'fullname', 'displayname', 'shortname', 'categoryid']));
        } else {
            $args[] = null;
        }

        $config = \block_xp\di::get('config');
        $args[] = $config->get('context') == CONTEXT_SYSTEM ? true : $issiteid; // If the users messes up and edits the front page.

        $PAGE->requires->js_call_amd('local_xp/grade-item-rule', 'init', $args);
        $PAGE->requires->strings_for_js(['gradeitemselector', 'rulegradeitemdesc', 'rulegradeitemdescwithcourse'], 'local_xp');
    }

}
