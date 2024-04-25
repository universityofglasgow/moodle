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
 * Activity completion reason.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\reason;
defined('MOODLE_INTERNAL') || die();

use context_module;
use block_xp\local\reason\reason;

/**
 * Activity completion reason.
 *
 * We also store the state in which the completion happened, in case we want
 * to reward for going from failing to completing, etc...
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_completion_reason implements reason, reason_with_short_description, reason_with_location {

    protected $cmid;
    protected $context;
    protected $state;

    public function __construct($cmid, $state) {
        $this->cmid = $cmid;
        $this->state = $state;
    }


    /**
     * Get the context.
     *
     * @return context|null
     */
    protected function get_context() {
        if (!isset($this->context)) {
            $this->context = context_module::instance($this->cmid, IGNORE_MISSING);
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
        return $context ? $context->get_context_name(true) : null;
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

    public function get_signature() {
        return $this->cmid . ':' . $this->state;
    }

    public function get_short_description() {
        return get_string('activitycompleted', 'local_xp');
    }

    public static function get_type() {
        return __CLASS__;
    }

    public static function from_signature($signature) {
        list($cmid, $state) = explode(':', $signature);
        return new static($cmid, $state);
    }

    public static function from_event(\core\event\course_module_completion_updated $e) {
        $data = $e->get_record_snapshot('course_modules_completion', $e->objectid);
        $state = $data->completionstate;
        $cmid = $e->get_context()->instanceid;
        return new static($cmid, $state);
    }

}
