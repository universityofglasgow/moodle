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
 * Course module name rule.
 *
 * @package    local_xp
 * @copyright  2021 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

use block_xp_rule_base;
use context_course;
use core_text;
use html_writer;

/**
 * Course module name rule.
 *
 * @package    local_xp
 * @copyright  2021 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cm_name extends block_xp_rule_base {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(self::EQ, '');
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
        return get_string('rulecmnamedesc', 'local_xp', (object)array(
            'compare' => get_string('rule:' . $this->compare, 'block_xp'),
            'value' => $this->value
        ));
    }

    /**
     * Returns a form element for this rule.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_form($basename) {
        $output = \block_xp\di::get('renderer');

        $o = parent::get_form($basename);

        $o .= html_writer::start_div('xp-flex xp-gap-1');

        $o .= html_writer::start_div('xp-flex xp-items-center');
        $o .= get_string('rulecmname', 'local_xp');
        $o .= html_writer::end_div();

        $operator = html_writer::select(array(
            self::EQ => get_string('rule:' . self::EQ, 'block_xp'),
            self::CT => get_string('rule:' . self::CT, 'block_xp'),
        ), $basename . '[compare]', $this->compare, '', array('id' => '', 'class' => ''));
        $o .= html_writer::div($operator, 'xp-min-w-px');

        $input = html_writer::empty_tag('input', array('type' => 'text', 'name' => $basename . '[value]',
            'value' => s($this->value), 'class' => 'form-control block_xp-form-control-inline'));
        $helpicon = $output->help_icon('rulecmname', 'local_xp');
        $o .= html_writer::div($input . $helpicon, 'xp-min-w-px xp-max-w-[80%] xp-whitespace-nowrap');

        $o .= html_writer::end_div();

        return $o;
    }

    /**
     * Get the value to use during comparison from the subject.
     *
     * @param mixed $subject The subject.
     * @return mixed The value to use.
     */
    protected function get_subject_value($subject) {
        if (!$subject instanceof \core\event\base) {
            return null;
        } else if (!$subject->courseid) {
            return null;
        } else if ($subject->contextlevel != CONTEXT_MODULE) {
            return null;
        }

        $context = $subject->get_context();
        if (!$context) {
            return null;
        }

        $modinfo = get_fast_modinfo($subject->courseid);
        $cm = $modinfo->get_cm($context->instanceid);
        return core_text::strtolower(trim($cm->name));
    }

    /**
     * Get the value to use during comparison.
     *
     * @return mixed The value to use.
     */
    protected function get_value() {
        $val = parent::get_value();
        return is_string($val) ? core_text::strtolower(trim($val)) : '';
    }

    /**
     * Does the $subject match the rules.
     *
     * @param mixed $subject The subject of the comparison.
     * @return bool Whether or not it matches.
     */
    public function match($subject) {
        $subj = $this->get_subject_value($subject);
        $value = $this->get_value();

        // Skip the values that are to be ignored.
        if ($subj === null || $subj === '') {
            return false;
        } else if ($value === null || $value === '') {
            return false;
        }

        // Bypass parent method fully.
        $method = 'match_' . $this->compare;
        return $this->$method($subj, $value);
    }
}
