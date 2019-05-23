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
 * Privacy Subsystem implementation for block_myoverview.
 *
 * @package    block_course_overview
 * @copyright  2018 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_overview\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\request\writer;
use \core_privacy\local\metadata\collection;

/**
 * Privacy Subsystem for block_course_overview.
 *
 * @copyright  2018 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\user_preference_provider {

    /**
     * Returns meta-data information about the course_overview block.
     *
     * @param  collection $collection A collection of meta-data.
     * @return collection Return the collection of meta-data.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_user_preference('block_course_overview_number_of_courses', 'privacy:metadata:numberofcourses');
        $collection->add_user_preference('block_course_overview_course_sortorder', 'privacy:metadata:coursesortorder');
        $collection->add_user_preference('block_course_overview_course_order', 'privacy:metadata:courseorder');
        $collection->add_user_preference('block_course_overview_favourites', 'privacy:metadata:favourites');
        $collection->add_user_preference('block_course_overview_sortorder', 'privacy:metadata:sortorder');
        return $collection;
    }

    /**
     * Export all user preferences for the myoverview block
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $preferences = [
            'number_of_courses' => 'numberofcourses',
            'course_sortorder' => 'coursesortorder',
            'course_order' => 'courseorder',
            'favourites' => 'favourites',
            'sortorder' => 'sortorder',
        ];
        foreach ($preferences as $name => $metadata) {
            $preference = get_user_preferences('block_course_overview_' . $name, null, $userid);
            if (isset($preference)) {
                writer::export_user_preference('block_course_overview', 'block_course_overview_' . $name,
                        $preference, get_string('privacy:metadata:' . $metadata, 'block_course_overview'));
            }
        }
    }
}
