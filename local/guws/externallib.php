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
 * Export custom web services used by UofG systems
 *
 * @package    local_guws
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
 
class local_myplugin_external extends external_api {

    /**
     * Parameter definition for ams_searchassign
     * @return external_funtion_parameters
     */
    public static function ams_searchassign_parameters() {
        return new external_function_parameters([
            'code' => new external_value(PARAM_TEXT, 'Substring to search for in Assignment names'),
            'date' => new external_value(PARAM_ALPHANUM, 'Target date in YYYYMMDD format. Will only return Assignments that where active on this date', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Return definition for amd_searchassign
     * @returns external_multiple_structure
     */
    public static function ams_searchassign_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Assignment id'),
                'cm' => new external_value(PARAM_INT, 'Assignment course module id'),
                'name' => new external_value(PARAM_TEXT, 'Assignment name in full'),
            ])
        );
    }

    public static function ams_searchassign($code, $date) {
        global $CFG, $DB;

        // Check params
        $params = self::validate_params(self::ams_searchassign_parameters(), ['code' => $code, 'date' => $date]);
    }

}
