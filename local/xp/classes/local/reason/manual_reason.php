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
 * Manual reason.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\reason;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\reason\reason;

/**
 * Manual reason.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manual_reason implements reason, reason_with_short_description {

    /** @var int The author of the manual reward. */
    protected $userid;

    /**
     * Constructor.
     *
     * @param int $userid The user id offering the reward.
     */
    public function __construct($userid) {
        $this->userid = $userid;
    }

    public function get_signature() {
        return $this->userid;
    }

    public function get_short_description() {
        return get_string('manuallyawarded', 'local_xp');
    }

    public static function get_type() {
        return __CLASS__;
    }

    public static function from_signature($signature) {
        return new static($signature);
    }

}
