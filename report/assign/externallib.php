assign/gradingtable.php:53:    /** @var boolean $hasgrantextension - Only do the capability check once for the entire table */
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
 * External report_assign API
 *
 * @package    report_assign
 * @copyright  2020 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Assign functions
 * @copyright 2012 Paul Charsley
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_assign_external extends external_api {

    /**
     * Describes the parameters for save_user_extensions
     * @return external_function_parameters
     * @since  Moodle 2.6
     */
    public static function save_user_extensions_parameters() {
        return new external_function_parameters(
            array(
                'assignmentid' => new external_value(PARAM_INT, 'The assignment id to operate on'),
                'userids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'user id'),
                    '1 or more user ids',
                    VALUE_REQUIRED),
                'dates' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'dates'),
                    '1 or more extension dates (timestamp)',
                    VALUE_REQUIRED),
            )
        );
    }

    /**
     * Grant extension dates to students for an assignment.
     *
     * @param int $assignmentid The id of the assignment
     * @param array $userids Array of user ids to grant extensions to
     * @param array $dates Array of extension dates
     * @return array of warnings for each extension date that could not be granted
     * @since Moodle 2.6
     */
    public static function save_user_extensions($assignmentid, $userids, $dates) {
        global $CFG;

        $params = self::validate_parameters(self::save_user_extensions_parameters(),
                        array('assignmentid' => $assignmentid,
                              'userids' => $userids,
                              'dates' => $dates));

        if (count($params['userids']) != count($params['dates'])) {
            $detail = 'Length of userids and dates parameters differ.';
            $warnings[] = self::generate_warning($params['assignmentid'],
                                                 'invalidparameters',
                                                 $detail);

            return $warnings;
        }

        list($assignment, $course, $cm, $context) = self::validate_assign($params['assignmentid']);

        $warnings = array();
        foreach ($params['userids'] as $idx => $userid) {
            $duedate = $params['dates'][$idx];
            if (!$assignment->save_user_extension($userid, $duedate)) {
                $detail = 'User id: ' . $userid . ', Assignment id: ' . $params['assignmentid'] . ', Extension date: ' . $duedate;
                $warnings[] = self::generate_warning($params['assignmentid'],
                                                     'couldnotgrantextensions',
                                                     $detail);
            }
        }

        return $warnings;
    }

    /**
     * Describes the return value for save_user_extensions
     *
     * @return external_single_structure
     * @since Moodle 2.6
     */
    public static function save_user_extensions_returns() {
        return new external_warnings();
    }

}