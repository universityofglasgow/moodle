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
 * This is the external API for local_gugcat.
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugcat;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

class external extends external_api {

    /**
     * display_assessments parameters.
     *
     * @since  Moodle 3.8
     * @return external_function_parameters
     */
    public static function display_assessments_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT)
            )
        );
    }

    /**
     * Toggles the settings of assessment display on student dashboard.
     *
     * @since  Moodle 3.8
     */
    public static function display_assessments($courseid) {
        $params = self::validate_parameters(self::display_assessments_parameters(), ['courseid' => $courseid]);
        return [
            'result' => api::display_assessments($params['courseid'])
        ];
    }

    /**
     * Returns display_assessments result value.
     *
     * @since  Moodle 3.8
     * @return external_value
     */
    public static function display_assessments_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result')
        ]);
    }
}