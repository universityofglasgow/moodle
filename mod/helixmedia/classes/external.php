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
 * helixmedia external API
 *
 * @package    mod_helixmedia
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>, 2017 Streaming LTD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * helixmedia external functions
 *
 * @package    mod_helixmedia
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>, 2017 Streaming LTD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_helixmedia_external extends external_api {

    public static function get_launch_data_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Moodle module id'),
                'user' => new external_value(PARAM_INT, 'Moodle user id'),
                'course' => new external_value(PARAM_INT, 'Moodle course id')
            )
        );
    }

    public static function get_launch_data($id, $userid, $course) {
        global $DB, $USER;
        $warnings = array();
        $token = self::random_code(40);

        $tokenid = $DB->insert_record("helixmedia_mobile", array(
            'instance' => $id,
            'user' => $userid,
            'course' => $course,
            'token' => $token,
            'timecreated' => time())
        );

        $result = array(
            'id' => $tokenid,
            'token' => $token,
            'warnings' => $warnings
        );

        return $result;
    }

    private static function random_code($length) {
        $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $clen = strlen( $chars ) - 1;
        $id = '';

        for ($i = 0; $i < $length; $i++) {
            $id .= $chars[mt_rand(0, $clen)];
        }
        return ($id);
    }

    public static function get_launch_data_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Launch token id'),
                'token' => new external_value(PARAM_TEXT, 'Launch token'),
                'warnings' => new external_warnings()
            )
        );
    }
}
