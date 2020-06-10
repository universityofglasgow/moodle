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
 * Course rule.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

use block_xp_rule;
use block_xp_rule_property;
use context_course;
use html_writer;

/**
 * Course rule.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course extends block_xp_rule_property {

    /** @var renderer_base The renderer. */
    protected $output;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(self::EQ, null, 'courseid');
        $this->output = \block_xp\di::get('renderer');
    }

    /**
     * Get a course name.
     *
     * @param int $courseid The course ID.
     * @return str|null
     */
    protected function get_course_name($courseid) {
        $context = context_course::instance((int) $courseid, IGNORE_MISSING);
        return $context ? $context->get_context_name(false, true) : null;
    }

    /**
     * Returns a string describing the rule.
     *
     * @return string
     */
    public function get_description() {
        $prefix = '';
        $config = \block_xp\di::get('config');
        if ($config->get('context') != CONTEXT_SYSTEM) {
            $prefix = '[' . get_string('ineffective', 'block_xp') . '] ';
        }

        $string = 'rulecoursedesc';
        $name = $this->get_course_name($this->value);
        if (!$name) {
            $name = get_string('errorunknowncourse', 'local_xp');
        }

        return $prefix . get_string($string, 'local_xp', $name);
    }

    /**
     * Returns a form element for this rule.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_form($basename) {
        static::init_page_requirements();

        $config = \block_xp\di::get('config');
        $coursename = $this->get_course_name($this->value);
        $hascourse = !empty($coursename);

        $o = block_xp_rule::get_form($basename);
        $o .= html_writer::start_tag('span', ['class' => 'local_xp-course-rule-widget ' . ($hascourse ? 'has-course' : null)]);
        $o .= html_writer::empty_tag('input', [
            'name' => $basename . '[value]',
            'class' => 'course-rule-courseid',
            'type' => 'hidden',
            'value' => $this->value
        ]);

        if ($config->get('context') != CONTEXT_SYSTEM) {
            $o .= '[' . get_string('ineffective', 'block_xp') . '] ';
        }

        if (!$hascourse) {
            // We can only select the course once!
            $o .= html_writer::start_tag('span', ['class' => 'course-selection']);
            $o .= html_writer::tag('button', get_string('clicktoselectcourse', 'local_xp'), ['class' => 'btn btn-warning']);
            $o .= html_writer::end_tag('span');
        }

        $o .= html_writer::start_tag('span', ['class' => 'course-selected']);
        $o .= get_string('rulecoursedesc', 'local_xp', $coursename);
        $o .= html_writer::end_tag('span');

        $o .= $this->output->help_icon('rulecourse', 'local_xp');
        $o .= html_writer::end_tag('span');
        return $o;
    }

    /**
     * Initialise the page requirements.
     *
     * @return void
     */
    protected static function init_page_requirements() {
        global $PAGE;

        static $alreadydone = false;
        if ($alreadydone) {
            return;
        }
        $alreadydone = true;

        $PAGE->requires->js_call_amd('local_xp/course-rule', 'init', []);
        $PAGE->requires->strings_for_js(['courseselector', 'rulecoursedesc'], 'local_xp');
    }

}
