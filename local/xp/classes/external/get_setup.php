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
 * External function.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\external;

use block_xp\di;
use block_xp\external\external_function_parameters;
use block_xp\external\external_single_structure;
use block_xp\external\external_value;
use local_xp\local\config\default_course_world_config;
use moodle_exception;

/**
 * External function.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_setup extends external_api {

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function execute($courseid) {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact('courseid'));
        extract($params); // @codingStandardsIgnoreLine

        $adminconfig = di::get('config');
        $worldfactory = di::get('course_world_factory');
        $currencyfactory = di::get('course_currency_factory');
        $bifinder = di::get('course_world_block_any_instance_finder_in_context');

        $world = $worldfactory->get_world($courseid);
        $courseid = $world->get_courseid(); // Ensure that we get the real course ID.
        self::validate_context($world->get_context());
        di::get('addon')->require_activated();

        // Find the block instance, we use the course context because the front page is a course.
        $bi = $bifinder->get_any_instance_in_context('xp', $world->get_context());
        $perms = $world->get_access_permissions();
        $publicdata = [
            'contextmode' => $adminconfig->get('context'),
            'perms' => [
                'canaccess' => $perms->can_access(),
                'canmanage' => $perms->can_manage(),
            ],
            'block' => [
                'visible' => false,
            ],
        ];

        if (!$perms->can_access() || isguestuser() || !$USER->id) {
            // Early bail if the user cannot see anything.
            return $publicdata;
        } else if (!$bi) {
            // We could not find the block, so let's skip it.
            return $publicdata;
        }

        $blockconfig = self::make_block_config($bi);
        $config = $world->get_config();
        $currency = $currencyfactory->get_currency($courseid);

        // Description.
        // TODO Remove hardcoded indicator and notice name.
        $indicator = \block_xp\di::get('user_notice_indicator');
        $introname = 'block_intro_' . $courseid;
        $description = '';
        if ($perms->can_manage() || !$indicator->user_has_flag($USER->id, $introname)) {
            $description = self::format_string($blockconfig->get('description'), $world->get_context());
        }

        // Phew, we're done. Note that this is NOT a recursive merge.
        return [
            'block' => [
                'title' => self::format_string($blockconfig->get('title'), $world->get_context()),
                'description' => $description,
                'recentactivity' => $blockconfig->get('recentactivity'),
                // TODO Implement better visibility check. E.g. we should look at the block_position instead, if any.
                'visible' => true,
            ],
            'config' => [
                'enableinfos' => $config->get('enableinfos'),
                'enableladder' => $config->get('enableladder'),
                'enablegroupladder' => $config->get('enablegroupladder') != default_course_world_config::GROUP_LADDER_NONE,
            ],
            'visuals' => [
                'currency' => self::serialize_currency($currency),
            ],
        ] + $publicdata;
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure([
            'contextmode' => new external_value(PARAM_INT),
            'perms' => new external_single_structure([
                'canaccess' => new external_value(PARAM_BOOL),
                'canmanage' => new external_value(PARAM_BOOL),
            ]),
            'block' => new external_single_structure([
                'title' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
                'description' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
                'recentactivity' => new external_value(PARAM_INT, 0, VALUE_OPTIONAL),
                'visible' => new external_value(PARAM_BOOL),
            ]),
            'config' => new external_single_structure([
                'enableinfos' => new external_value(PARAM_BOOL),
                'enableladder' => new external_value(PARAM_BOOL),
                'enablegroupladder' => new external_value(PARAM_INT),
            ], '', VALUE_OPTIONAL),
            'visuals' => new external_single_structure([
                'currency' => self::currency_description(),
            ], '', VALUE_OPTIONAL),
        ]);
    }

}
