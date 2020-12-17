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
 * UofG tasks
 * Flag missing files in turnitin
 *
 * @package    local_gutask
 * @copyright  2020 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gutask\task;

defined('MOODLE_INTERNAL') || die;

class turnitin extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('turnitin', 'local_gutask');
    }

    public function execute() {
        global $DB;

        $errormessage = "File no longer exists";
        $lastmodified = time();
        $sql = 'UPDATE {plagiarism_turnitin_files}
            SET statuscode="error",
            errorcode = 69,
            errormsg = :errormessage,
            lastmodified = :lastmodified
            WHERE identifier NOT IN (
                SELECT pathnamehash FROM {files}
            )
            AND statuscode="queued"';
        $DB->execute($sql, [
            'errormessage' => $errormessage,
            'lastmodified' => $lastmodified,
        ]);
    }

}
