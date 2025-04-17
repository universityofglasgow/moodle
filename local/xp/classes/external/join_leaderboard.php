<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

namespace local_xp\external;

use block_xp\di;
use block_xp\external\external_function_parameters;
use block_xp\external\external_single_structure;
use block_xp\external\external_value;
use moodle_exception;

/**
 * External function.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class join_leaderboard extends external_api {

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT),
        ]);
    }

    /**
     * Execute.
     *
     * @param int $contextid
     * @return array
     */
    public static function execute($contextid) {
        global $CFG, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact('contextid'));
        $contextid = $params['contextid'];

        $world = di::get('context_world_factory')->get_world_from_context(\context::instance_by_id($contextid));
        self::validate_context($world->get_context());
        di::get('addon')->require_activated();
        $world->get_access_permissions()->require_access();

        $service = di::get(\local_xp\local\leaderboard\participation\service_factory::class)
            ->get_for_context($world->get_context());
        $state = $service->get_state($USER->id);
        if ($state->is_state_locked() || $$state->is_participating()) {
            throw new \moodle_exception('invaliddata', 'core_error');
        }

        $service->add_participant($USER->id);
        return ['success' => true];
    }

    /**
     * External function return values.
     *
     * @return external_value
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL),
        ]);
    }

}
