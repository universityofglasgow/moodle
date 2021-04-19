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
 * User state course store.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\xp;
defined('MOODLE_INTERNAL') || die();

use context_helper;
use moodle_database;
use stdClass;
use user_picture;
use block_xp\local\xp\levels_info;
use block_xp\local\logger\reason_collection_logger;
use block_xp\local\observer\level_up_state_store_observer;

/**
 * User state course store.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_user_state_store extends \block_xp\local\xp\course_user_state_store {

    /** @var Closure The user state factory. */
    protected $userstatefactory;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param levels_info $levelsinfo The levels info.
     * @param int $courseid The course ID.
     * @param reason_collection_logger $logger The reason logger.
     * @param Closure $userstatefactory The user state factory, if any.
     * @param level_up_state_store_observer $observer The observer.
     */
    public function __construct(moodle_database $db, levels_info $levelsinfo, $courseid, reason_collection_logger $logger,
            $userstatefactory = null, level_up_state_store_observer $observer = null) {

        parent::__construct($db, $levelsinfo, $courseid, $logger, $observer);
        $this->userstatefactory = $userstatefactory;
    }

    /**
     * Make a user_state from the record.
     *
     * @param stdClass $record The row.
     * @param string $useridfield The user ID field.
     * @return user_state
     */
    public function make_state_from_record(stdClass $record, $useridfield = 'userid') {
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
