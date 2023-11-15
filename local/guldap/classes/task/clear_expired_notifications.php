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
 * @package    local_guldap
 * @copyright  2023 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_guldap\task;

defined('MOODLE_INTERNAL') || die;

class clear_expired_notifications extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('clearexpirednotifications', 'local_guldap');
    }

    public function execute() {
        global $DB;

        // Set emailstop where user has had notifications bounce for
        // the maximum retry count.
        $sql = "update {user}
            set emailstop=1
            where id in (select distinct userid from {task_adhoc}
            where component='mod_forum'
            and faildelay=86400)";
        $DB->execute($sql);

        // Delete the adhoc_task entries flagged above.
        $sql = "delete from {task_adhoc}
            where component='mod_forum'
            and faildelay=86400";
        $DB->execute($sql);
    }

}
