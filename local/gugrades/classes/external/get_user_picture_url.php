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
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace local_gugrades\external;

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class get_user_picture_url extends \external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID'),
        ]);
    }

    public static function execute(int $userid) {

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), [
            'userid' => $userid,
        ]);
        $context = \context_user::instance($userid);
        self::validate_context($context);

        // NB. this returns a moodle_url (not a string)
        $url = \local_gugrades\api::get_user_picture_url($userid);

        return ['url' => $url->out(false)];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'url' => new external_value(PARAM_URL, 'Picture URL'),
        ]);
    }
}