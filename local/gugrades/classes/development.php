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
 * Debugging and profiling stuff
 *
 * @package    local_gugrades
 * @copyright  2025
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

defined('MOODLE_INTERNAL') || die();

define('XHPROF_PATH', '/profiles/');

class development {

    /**
     * Start XHPROF profile
     * Check included for xhprof being installed (in case we forget)
     */
    public static function xhprof_start() {
        if (function_exists('xhprof_enable')) {
            xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
        }
    }

    /** 
     * Stop XHPROF profiling and save results
     */
    public static function xhprof_stop() {
        if (function_exists('xhprof_disable')) {
            file_put_contents(XHPROF_PATH . time() . '.application.xhprof', serialize(xhprof_disable()));
        }
    }
}