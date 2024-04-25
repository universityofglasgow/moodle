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
 * Section completion reason.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\reason;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\reason\reason;
use context_course;

require_once($CFG->dirroot . '/course/lib.php');

/**
 * Section completion reason.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_completion_reason implements reason, reason_with_short_description, reason_with_location {

    protected $courseid;
    protected $context;
    protected $sectionnum;

    public function __construct($courseid, $sectionnum) {
        $this->courseid = $courseid;
        $this->sectionnum = $sectionnum;
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
     * Get modinfo.
     *
     * @return \course_modinfo|null Null if the course no longer exists.
     */
    protected function get_modinfo() {
        global $CFG;
        require_once($CFG->dirroot . '/course/lib.php');
        try {
            return get_fast_modinfo($this->courseid);
        } catch (\moodle_exception $e) {
            return null;
        }
    }

    /**
     * Get the location name.
     *
     * @return string
     */
    public function get_location_name() {
        $modinfo = $this->get_modinfo();
        $name = $modinfo ? get_section_name($modinfo->courseid, $this->sectionnum) : '';
        return $name !== '' ? $name : get_string('unknownsection', 'local_xp', $this->sectionnum);
    }

    /**
     * Get the location URL.
     *
     * @return moodle_url|null
     */
    public function get_location_url() {
        $modinfo = $this->get_modinfo();
        return $modinfo ? course_get_url($modinfo->courseid, $this->sectionnum) : null;
    }

    public function get_signature() {
        return $this->courseid . ':' . $this->sectionnum;
    }

    public function get_short_description() {
        return get_string('sectioncompleted', 'local_xp');
    }

    public static function get_type() {
        return __CLASS__;
    }

    public static function from_signature($signature) {
        list($courseid, $sectionnum) = explode(':', $signature);
        return new static($courseid, $sectionnum);
    }

    public static function from_event(\local_xp\event\section_completed $e) {
        return new static($e->courseid, $e->other['sectionnum']);
    }

}
