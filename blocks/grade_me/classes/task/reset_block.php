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
 * @package    block_grade_me
 * @copyright  2017 Derek Henderson {@link http://www.remote-learner.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_grade_me\task;

defined('MOODLE_INTERNAL') || die();


class reset_block extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('pluginname-reset', 'block_grade_me');
    }

    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/blocks/grade_me/lib.php');
        block_grade_me_cache_reset();
    }
}
