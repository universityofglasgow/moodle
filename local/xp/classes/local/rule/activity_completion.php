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
 * Activity completion rule.
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
 * Activity completion rule.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_completion extends block_xp_rule {

    /** Completed. */
    const COMPLETED = 1;
    /** Completed and passed grade. */
    const COMPLETED_PASS = 2;
    /** Completed but failed. */
    const COMPLETED_FAIL = 4;

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
        return get_string('ruleactivitycompletiondesc', 'local_xp');
    }

    /**
     * Returns a form element for this rule.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_form($basename) {
        $success = static::COMPLETED | static::COMPLETED_PASS;
        $o = parent::get_form($basename);
        $o .= html_writer::empty_tag('input', ['type' => 'hidden', 'value' => $success, 'name' => $basename . '[mode]']);
        $o .= get_string('ruleactivitycompletiondesc', 'local_xp');
        $o .= $this->output->help_icon('ruleactivitycompletion', 'local_xp');
        return $o;
    }

    /**
     * Does the $subject match the rule?
     *
     * @param mixed $subject The subject of the comparison.
     * @return bool Whether or not it matches.
     */
    public function match($subject) {
        if (!$subject instanceof \core\event\course_module_completion_updated) {
            return false;
        }

        $event = $subject;
        $data = $event->get_record_snapshot('course_modules_completion', $event->objectid);
        $state = $data->completionstate;

        if (($state == COMPLETION_COMPLETE && $this->mode & self::COMPLETED)
                || ($state == COMPLETION_COMPLETE_PASS && $this->mode & self::COMPLETED_PASS)) {
            return true;
        }

        return false;
    }

}
