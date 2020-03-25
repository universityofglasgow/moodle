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
 * @package    theme_hillhead
 * @copyright  2018 Alex Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_hillhead\privacy;

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
        $collection->add_user_preference('theme_hillhead_font', 'privacy:metadata:font');
        $collection->add_user_preference('theme_hillhead_size', 'privacy:metadata:size');
        $collection->add_user_preference('theme_hillhead_contrast', 'privacy:metadata:contrast');
        $collection->add_user_preference('theme_hillhead_bold', 'privacy:metadata:bold');
        $collection->add_user_preference('theme_hillhead_spacing', 'privacy:metadata:spacing');
        $collection->add_user_preference('theme_hillhead_readtome', 'privacy:metadata:readtome');
        $collection->add_user_preference('theme_hillhead_readalert', 'privacy:metadata:readalert');
        $collection->add_user_preference('theme_hillhead_stripstyles', 'privacy:metadata:stripstyles');
        $collection->add_user_preference('theme_hillhead_accessibility', 'privacy:metadata:accessibility');
        return $collection;
    }

    /**
     * Export all user preferences for the myoverview block
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $preferences = [
            'font' => 'font',
            'size' => 'size',
            'contrast' => 'contrast',
            'bold' => 'bold',
            'spacing' => 'spacing',
            'readtome' => 'readtome',
            'readalert' => 'readalert',
            'stripstyles' => 'stripstyles',
            'accessibility' => 'accessibility'
        ];
        foreach ($preferences as $name => $metadata) {
            $preference = get_user_preferences('theme_hillhead_' . $name, null, $userid);
            if (isset($preference)) {
                writer::export_user_preference('theme_hillhead', 'theme_hillhead_' . $name,
                        $preference, get_string('privacy:metadata:' . $metadata, 'theme_hillhead'));
            }
        }
    }
}
