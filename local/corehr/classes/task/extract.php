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
 * Background task to get CoreHR data
 *
 * @package    local_corehr
 * @copyright  2019 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_corehr\task;

class extract extends \core\task\adhoc_task {

    public function execute() {
        global $CFG, $DB;

        // Get custom data (for GUID)
        $data = $this->get_custom_data();
        $guid = $data->guid;
        mtrace('local_corehr: Extracting CoreHR data for GUID ' . $guid);

        // Get user record
        $user = $DB->get_record('user', ['username' => $guid, 'mnethostid'=>$CFG->mnet_localhost_id]);
        mtrace('local_corehr: user record located for ' . fullname($user));

        if ($user) {
            $fullextract = \local_corehr\api::extract($guid);
            if ($fullextract) {
                $extract = \local_corehr\api::store_extract($user->id, $fullextract);
            }
        }
    }
}