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
 * @package    enrol_gudatabase
 * @copyright  2023 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_gudatabase\task;

defined('MOODLE_INTERNAL') || die;

class clear_unused_codes extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('clearunusedcodes', 'enrol_gudatabase');
    }

    public function execute() {
        global $DB;

        $sql = "DELETE FROM {enrol_gudatabase_codes}
            WHERE id IN
                (SELECT * FROM (SELECT gc.id FROM {enrol_gudatabase_codes} gc
                LEFT JOIN {enrol} er ON er.id = gc.instanceid
                WHERE er.id IS NULL
                AND location='plugin') AS cid)";
        $DB->execute($sql);
    }

}
