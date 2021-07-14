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
 * Graded reason.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\reason;
defined('MOODLE_INTERNAL') || die();

use grade_item;
use moodle_url;
use block_xp\local\reason\reason;

/**
 * Graded reason.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class graded_reason implements reason, reason_with_short_description, reason_with_location {

    /** @var int The grade item ID. */
    protected $itemid;
    /** @var grade_item The grade item. */
    protected $gradeitem;
    /** @var int The related user ID. */
    protected $relateduserid;

    public function __construct($itemid, $relateduserid) {
        $this->itemid = $itemid;
        $this->relateduserid = $relateduserid;
    }

    protected function get_grade_item() {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');
        if ($this->gradeitem === null) {
            $this->gradeitem = grade_item::fetch(['id' => $this->itemid]);
        }
        return $this->gradeitem;
    }

    /**
     * Get the location name.
     *
     * @return string|null
     */
    public function get_location_name() {
        $gradeitem = $this->get_grade_item();
        if (!$gradeitem) {
            return '';
        }
        return $gradeitem->get_name(true);
    }

    /**
     * Get the location URL.
     *
     * @return moodle_url|null
     */
    public function get_location_url() {
        $gradeitem = $this->get_grade_item();
        if (!$gradeitem) {
            return null;
        }
        return new moodle_url('/grade/report/singleview/index.php', [
            'id' => $gradeitem->courseid,
            'itemid' => $gradeitem->id,
            'item' => 'grade'
        ]);
    }

    public function get_short_description() {
        return get_string('gradereceived', 'local_xp');
    }

    public function get_signature() {
        return $this->itemid . ':' . $this->relateduserid;
    }

    public static function get_type() {
        return __CLASS__;
    }

    public static function from_signature($signature) {
        list($itemid, $relateduserid) = explode(':', $signature);
        return new static($itemid, $relateduserid);
    }

    public static function from_event(\core\event\user_graded $e) {
        return new static((int) $e->other['itemid'], $e->relateduserid);
    }

}
