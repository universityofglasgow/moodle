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
 * Cleanup database tables
 *
 * @package    local_corehr
 * @copyright  2025 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_corehr\task;

class cleanup extends \core\task\scheduled_task {

    public function get_name() {
        // Shown in admin screens
        return get_string('cleanuptask', 'local_corehr');
    }

    /**
     * Delete expired logs in local_corehr_boomi_log
     */
    public function execute() {
        global $DB;

        $keeplogsfor = get_config('local_corehr', 'keeplogsfor', 14);
        $cutoff = time() - ($keeplogsfor * 86400);

        $select = 'timestamp < :cutoff';
        $DB->delete_records_select('local_corehr_boomi_log', $select, ['cutoff' => $cutoff]);

        return true;
    }
}
