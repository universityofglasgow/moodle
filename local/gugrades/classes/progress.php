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
 * Progress bar drivers
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

defined('MOODLE_INTERNAL') || die();

class progress {

    /**
     * Create progress cache tag
     * @param int $courseid
     * @param int $uniqueid
     * @param string $progresstype
     * @param int $staffuserid
     * @return string
     */
    private static function get_tag(int $courseid, int $uniqueid, string $progresstype, $staffuserid = 0) {
        global $USER;

        $uniquestring = empty($uniqueid) ? '' : '_' . $uniqueid;
        if (!$staffuserid) {
            $staffuserid = $USER->id;
        }

        return $progresstype . '_' . $staffuserid . $uniquestring;
    }

    /**
     * Initialise progress bar
     * Fancy way to set it to zero and create the record
     * @param int $courseid
     * @param int $uniqueid
     * @param string $progresstype
     */
    public static function initialise(int $courseid, int $uniqueid, string $progresstype) {

        $cache = \cache::make('local_gugrades', 'progress');
        $tag = self::get_tag($courseid, $uniqueid, $progresstype);

        $cache->set($tag, 0);       
    }

    /**
     * Record current progress using the progress cache
     * $uniqueid is something that identifies this progress thing from any other (for this user)
     * $progresstype can be something like 'csvimport', 'recursiveimport' and so forth
     * @param int $courseid
     * @param int $uniqueid
     * @param string $progresstype
     * @param int $progress
     */
    public static function record(int $courseid, int $uniqueid, string $progresstype, int $progress) {

        $cache = \cache::make('local_gugrades', 'progress');
        $tag = self::get_tag($courseid, $uniqueid, $progresstype);

        $cache->set($tag, $progress);
    }

    /**
     * Get the current progress
     * If there is no record then return -1
     * @param int $courseid
     * @param int $uniqueid
     * @param string $progresstype
     * @param int $staffuserid
     * @return int
     */
    public static function get(int $courseid, int $uniqueid, string $progresstype, int $staffuserid = 0) {
 
        $cache = \cache::make('local_gugrades', 'progress');
        $tag = self::get_tag($courseid, $uniqueid, $progresstype, $staffuserid);

        if ($progress = $cache->get($tag)) {
            return $progress;
        } else {
            return -1;
        }
    }

    /**
     * Terminate progress
     * @param int $courseid
     * @param int $uniqueid
     * @param string $progresstype
     */
    public static function terminate(int $courseid, int $uniqueid, string $progresstype) {

        $cache = \cache::make('local_gugrades', 'progress');
        $tag = self::get_tag($courseid, $uniqueid, $progresstype);
        
        $cache->delete($tag);
    }
}