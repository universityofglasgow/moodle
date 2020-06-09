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
 * User graded rule.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

use block_xp_rule;
use html_writer;

/**
 * User graded rule.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_graded extends block_xp_rule {

    /** Graded. */
    const GRADED = 1;
    /** Graded and passed grade. */
    const GRADED_PASS = 2;
    /** Graded but failed. */
    const GRADED_FAIL = 4;

    /** @var int The mode chosen. */
    protected $mode = 0;
    /** @var renderer_base The renderer. */
    protected $output;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->output = \block_xp\di::get('renderer');
    }

    /**
     * Export the properties and their values.
     *
     * @return array Keys are properties, values are the values.
     */
    public function export() {
        $data = parent::export();
        $data['mode'] = $this->mode;
        return $data;
    }

    /**
     * Returns a string describing the rule.
     *
     * @return string
     */
    public function get_description() {
        return get_string('ruleusergradeddesc', 'local_xp');
    }

    /**
     * Returns a form element for this rule.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_form($basename) {
        $success = static::GRADED_PASS;
        $o = parent::get_form($basename);
        $o .= html_writer::empty_tag('input', ['type' => 'hidden', 'value' => $success, 'name' => $basename . '[mode]']);
        $o .= $this->get_description();
        $o .= $this->output->help_icon('ruleusergraded', 'local_xp');
        return $o;
    }

    /**
     * Does the $subject match the rule?
     *
     * @param mixed $subject The subject of the comparison.
     * @return bool Whether or not it matches.
     */
    public function match($subject) {
        // TODO Offer various other options.

        $event = $subject;
        if (!$event instanceof \core\event\user_graded) {
            return false;
        } else if ($event->is_restored()) {
            // We can't call user_graded::get_grade on restore.
            return false;
        } else if (empty($event->other['itemid'])) {
            // We don't have a grade item ID? Good bye!
            return false;
        } else if ($event->userid <= 0) {
            // We want teachers to initiate this, not the system.
            return false;
        }


        $gradeobject = $event->get_grade();
        if (!$gradeobject) {
            // This should never happen, but <rant>someone decided that it was a good idea
            // to add all these event::create_from_xxx(), and thus allow unintended extra logic.
            // In this case the reference to the grade is added. But, it's not impossible
            // for a developer to bypass create_from_grade, and directly use create(), after all
            // create() is the default way to instantiate an event. So if they do, they probably
            // will not attach the grade object. Events were meant to be so strict that they
            // were 100% reliable, now I have to handle this scenario, which I shouldn't,
            // but if I don't I knowingly risk to let bugs slide through the cracks, so I have to.
            // </rant>.
            $gradeobject = \grade_grade::fetch(['id' => $event->objectid]);
        }

        // Grade pre-checks.
        if (!$gradeobject) {
            // Never trust the gradebook!
            return false;
        } else if ($gradeobject->finalgrade === null) {
            // No idea why that is null, but unlike the completion API, I don't think
            // it's safe to fallback on the rawgrade as we could bypass overridden
            // grades set to null. The finalgrade of the type of items we're looking
            // at at the moment should really never be null if there is rawgrade.
            return false;
        }

        // Filter out what we do not want to deal with. For now we really only want to
        // be dealing with the grades which were given by a module. That's the simplest
        // way to ensure that rewards occur when we expected it.
        $gradeitem = $gradeobject->load_grade_item();
        if (!$gradeitem) {
            // The gradebook can be very broken... this is just in case.
            return false;
        } else if ($gradeitem->gradetype != GRADE_TYPE_VALUE) {
            // We only want to deal with points, no scales, no none, no text.
            return false;
        } else if ($gradeitem->is_outcome_item()) {
            // This shouldn't happen, as outcomes use scales.
            return false;
        } else if ($gradeitem->is_calculated()) {
            // We want manual actions, not aggregations or calculations.
            return false;
        } else if (!$gradeitem->is_external_item()) {
            // An external item is a module. So obvious I had to write it here.
            return false;
        } else if (plugin_supports(FEATURE_RATE, $gradeitem->itemmodule, false)) {
            // Rating usually means that the grade is volatile and changes often.
            // For now we'll skip those as we focus on other types of grades.
            return false;
        }

        // We should be good now.
        $grade = $gradeobject->finalgrade;

        // We need to cast to float, else PHP does weird things.
        $gradepass = (float) $item->gradepass > 0 ? (float) $item->gradepass : null;

        // There is a grade to pass.
        if ($gradepass !== null) {
            // For now we only support passing.
            return $grade >= $gradepass && $this->mode & static::GRADED_PASS;
        }

        // Matches when the mode is graded without a grade to pass.
        return $this->mode & static::GRADED;
    }
}
