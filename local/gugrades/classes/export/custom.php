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
 * Custom aggregation export
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\export;

/**
 * Access data in course activities
 *
 */
class custom extends base {

    /**
     * Define name of export
     * @return string
     */
    public function get_name() {
        return get_string('customexport', 'local_gugrades');
    }

    /**
     * Does the plugin define optional fields?
     * @return boolean
     */
    public function defines_optional_fields() {
        return true;
    }

    /**
     * Walk aggregation tree to get a flat list of grade items
     * and categories.
     * @param object $segment
     * @param array $list
     * @param array $prefixes
     * @return array
     */
    protected static function walk_tree(object $segment, array $list, array $prefixes = []) {

        $prefixes[] = $segment->name;

        // Add top level of segment to list
        $list[] = [
            'description' => implode(' > ', $prefixes),
            'identifier' => 'ITEM_' . $segment->itemid,
            'category' => $segment->iscategory,
        ];

        // If this is a category then we can iterate through its children.
        if ($segment->iscategory) {
            foreach ($segment->children as $child) {
                $list = self::walk_tree($child, $list, $prefixes);
            }
        }

        return $list;
    }

    /**
     * Return list of fields for form
     * (called if defines_optional_fields() is true)
     * @param int $courseid
     * @param int $gradecategoryid
     * @return array
     */
    public function get_form_fields(int $courseid, int $gradecategoryid) {

        $form = [];

        // Following fields are fixed.
        $form[] = [
            'identifier' => 'studentname',
            'description' => get_string('studentname', 'local_gugrades'),
            'category' => false,
        ];
        $form[] = [
            'identifier' => 'idnumber',
            'description' => get_string('idnumber', 'local_gugrades'),
            'category' => false,
        ];
        $form[] = [
            'identifier' => 'email',
            'description' => get_string('email', 'local_gugrades'),
            'category' => false,
        ];
        $form[] = [
            'identifier' => 'resitrequired',
            'description' => get_string('resitrequired', 'local_gugrades'),
            'category' => false,
        ];
        $form[] = [
            'identifier' => 'completed',
            'description' => get_string('completed'),
            'category' => false,
        ];

        // Get tree from aggregation tab.
        $tree = \local_gugrades\aggregation::recurse_tree($courseid, $gradecategoryid);
        $form = self::walk_tree($tree, $form);

        // Add additional options
        $form[] = [
            'identifier' => 'weights',
            'description' => get_string('showweights', 'local_gugrades'),
            'category' => false,
        ];
        $form[] = [
            'identifier' => 'released',
            'description' => get_string('showreleased', 'local_gugrades'),
            'category' => false,
        ];
        $form[] = [
            'identifier' => 'strategy',
            'description' => get_string('showstrategy', 'local_gugrades'),
            'category' => false,
        ];

        // Add 'selected' field
        foreach ($form as $key => $record) {
            $form[$key]['selected'] = false;
        }

        return $form;
    }
}
