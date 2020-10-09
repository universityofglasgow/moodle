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
 * Course completion reason.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\reason;
defined('MOODLE_INTERNAL') || die();

use context_course;
use block_xp\local\reason\reason;

/**
 * Course completion reason.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_completed_reason implements reason, reason_with_short_description, reason_with_location {

    protected $courseid;
    protected $context;

    public function __construct($courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Get the context.
     *
     * @return context|null
     */
    protected function get_context() {
        if (!isset($this->context)) {
            $this->context = context_course::instance($this->courseid, IGNORE_MISSING);
        }
        return !empty($this->context) ? $this->context : null;
    }

    /**
     * Get the location name.
     *
     * @return string|null
     */
    public function get_location_name() {
        $context = $this->get_context();
        return $context ? $context->get_context_name(false, true) : null;
    }

    /**
     * Get the location URL.
     *
     * @return moodle_url|null
     */
    public function get_location_url() {
        $context = $this->get_context();
        return $context ? $context->get_url() : null;
    }

    public function get_short_description() {
        return get_string('eventcoursecompleted', 'core_completion');
    }

    public function get_signature() {
        return $this->courseid;
    }

    public static function get_type() {
        return __CLASS__;
    }

    public static function from_signature($signature) {
        return new static($signature);
    }

    public static function from_event(\core\event\course_completed $e) {
        return new static($e->courseid);
    }

}
