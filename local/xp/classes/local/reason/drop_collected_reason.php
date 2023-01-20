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
 * Drop collected reason.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\reason;

use block_xp\local\reason\reason;

defined('MOODLE_INTERNAL') || die();

/**
 * Drop collected reason.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class drop_collected_reason implements reason, reason_with_short_description {

    /** @var int The drop ID. */
    protected $dropid;

    /**
     * Constructor.
     *
     * @param int $dropid The id of the drop.
     */
    public function __construct($dropid) {
        $this->dropid = $dropid;
    }

    public function get_short_description() {
        return get_string('dropcollected', 'local_xp');
    }

    public function get_signature() {
        return $this->dropid;
    }

    public static function get_type() {
        return __CLASS__;
    }

    public static function from_signature($signature) {
        list($dropid) = $signature;
        return new static($dropid);
    }

}