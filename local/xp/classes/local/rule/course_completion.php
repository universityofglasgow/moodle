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
 * Course completion rule.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

use block_xp_rule;
use block_xp_rule_property;
use html_writer;

/**
 * Course completion rule.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_completion extends block_xp_rule_property {

    /** @var renderer_base The renderer. */
    protected $output;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(self::EQ, '\core\event\course_completed', 'eventname');
        $this->output = \block_xp\di::get('renderer');
    }

    /**
     * Returns a string describing the rule.
     *
     * @return string
     */
    public function get_description() {
        $config = \block_xp\di::get('config');
        if ($config->get('context') != CONTEXT_SYSTEM) {
            return get_string('rulecoursecompletioncoursemodedesc', 'local_xp');
        }
        return get_string('rulecoursecompletiondesc', 'local_xp');
    }

    /**
     * Returns a form element for this rule.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_form($basename) {
        $o = block_xp_rule::get_form($basename);
        $o .= $this->get_description();
        $o .= $this->output->help_icon('rulecoursecompletion', 'local_xp');
        return $o;
    }

}
