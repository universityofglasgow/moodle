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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_kuracloud\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Cron task to check the state of the kuraCloud API connection
 *
 * @copyright 2017 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_coursemappings extends \core\task\scheduled_task {

    /**
     * Get name of task
     *
     * @return void
     */
    public function get_name() {
        return get_string('checkcoursemappings', 'block_kuracloud');
    }

    /**
     * Execute task
     *
     * @return void
     */
    public function execute() {
        global $DB;

        $courses = new \block_kuracloud\courses;
        $mappings = $courses->get_all_mapped(true);

        foreach ($mappings as $mapping) {
            $DB->update_record('block_kuracloud_courses', $mapping);
        }

        return true;
    }
}