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

/**
 * Get the data associated with a grade item
 */
class get_grade_item extends \external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'itemid' => new external_value(PARAM_INT, 'Grade item id'),
        ]);
    }

    public static function execute($itemid) {
        global $DB;

        // Security.
        $params = self::validate_parameters(self::execute_parameters(), ['itemid' => $itemid]);

        // Get item (if it exists)
        $item = $DB->get_record('grade_items', ['id' => $itemid], '*', MUST_EXIST);

        // More security
        $courseid = $item->courseid;
        $context = \context_course::instance($courseid);
        self::validate_context($context);

        return \local_gugrades\api::get_grade_item($itemid);
    }

    public static function execute_returns() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Grade item ID'),
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'categoryid' => new external_value(PARAM_INT, 'Grade category ID'),
            'itemname' => new external_value(PARAM_TEXT, 'Name of grade item'),
            'itemtype' => new external_value(PARAM_ALPHA, 'course / mod / category / manual'),
            'itemmodule' => new external_value(PARAM_ALPHA, 'Module type (if module)'),
            'iteminstance' => new external_value(PARAM_INT, 'Module instance ID'),
        ]);
    }

}