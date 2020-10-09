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
 * Course user leaderboard.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\leaderboard;
defined('MOODLE_INTERNAL') || die();

use context_helper;
use moodle_database;
use stdClass;
use user_picture;
use block_xp\local\leaderboard\ranker;
use block_xp\local\xp\levels_info;

/**
 * Course user leaderboard.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_user_leaderboard extends \block_xp\local\leaderboard\course_user_leaderboard {

    /** @var Closure The user state factory. */
    protected $userstatefactory;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param levels_info $levelsinfo The levels info.
     * @param int $courseid The course ID.
     * @param string[] $columns The name of the columns.
     * @param ranker $ranker An alternative ranker.
     * @param int $groupid The group ID.
     * @param Closure $userstatefactory The user state factory.
     */
    public function __construct(
            moodle_database $db,
            levels_info $levelsinfo,
            $courseid,
            array $columns,
            ranker $ranker = null,
            $groupid = 0,
            $userstatefactory = null) {

        parent::__construct($db, $levelsinfo, $courseid, $columns, $ranker, $groupid);
        $this->userstatefactory = $userstatefactory;
    }

    /**
     * Make a user_state from the record.
     *
     * @param stdClass $record The row.
     * @param string $useridfield The user ID field.
     * @return user_state
     */
    protected function make_state_from_record(stdClass $record, $useridfield = 'userid') {
        if (!empty($this->userstatefactory)) {
            $user = user_picture::unalias($record, null, $useridfield);
            context_helper::preload_from_record($record);
            $xp = !empty($record->xp) ? $record->xp : 0;
            $cb = $this->userstatefactory;
            return $cb($user, $xp);
        }
        return parent::make_state_from_record($record, $useridfield);
    }

}
