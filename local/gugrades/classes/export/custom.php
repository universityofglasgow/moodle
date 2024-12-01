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

    /**
     * Process (grade)item for user
     * @param string $identifier
     * @param int $userid
     */
    protected function process_item(string $identifier, int $userid) {

        // Identifier should be ITEM_nnnn - we need the number.
        $parts = explode('_', $identifier);
        if (isset($parts[1])) {
            $gradeitemid = $parts[1];
        } else {
            throw new \moodle_exception('Invalid identifier - "' . $identifier . '"');
        }

        // First get the provisional grade
        if ($provisional = \local_gugrades\grades::get_provisional_from_id($gradeitemid, $userid)) {

            $displaygrade = $provisional->displaygrade;

            return $displaygrade;
        } else {
            return get_string('nodata', 'local_gugrades');
        }
    }

    /**
     * Work out if an identifier is enabled in $form
     * @param string $identifier
     * @param array $form
     * @return boolean
     */
    private function identifier_enabled(string $identifier, array $form) {
        foreach ($form as $record) {
            $ident = $record['identifier'];
            if (($ident == $identifier) && $record['selected']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get headings line
     * @param int $courseid
     * @param int $gradecategoryid
     * @param array $form
     * @return array
     */
    protected function get_heading(int $courseid, int $gradecategoryid, array $form) {

        $originalform = $this->get_form_fields($courseid, $gradecategoryid);
        $headings = [];
        foreach ($originalform as $record) {
            $ident = $record['identifier'];
            if ($this->identifier_enabled($ident, $form)) {
                $headings[$ident] = $record['description'];
            }
        }

        return $headings;
    }

    /**
     * Return data for CSV export
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $groupid
     * @param array $form
     * @return array
     */
    public function get_form_data(int $courseid, int $gradecategoryid, int $groupid, array $form) {

        set_time_limit(0);

        // Get list of students.
        $users = \local_gugrades\aggregation::get_users($courseid, $gradecategoryid, '', '', $groupid);

        // Aggregate all the users.
        \local_gugrades\aggregation::aggregate($courseid, $gradecategoryid, $users);

        // Array holds CSV lines.
        $lines = [];

        // Headings
        $lines[] = $this->get_heading($courseid, $gradecategoryid, $form);

        // Iterate over users getting requested data.
        foreach ($users as $user) {
            $line = [];

            foreach ($form as $record) {
                $ident = $record['identifier'];
                $selected = $record['selected'];
                if (!$selected) {
                    continue;
                }

                // Deal with basic data
                if ($ident == 'studentname') {
                    $line[$ident] = $user->displayname;
                } else if ($ident == 'idnumber') {
                    $line[$ident] = $user->idnumber;
                } else if ($ident == 'email') {
                    $line[$ident] = $user->email;
                } else if ($ident == 'resitrequired') {
                    $line[$ident] = $user->resitrequired ? get_string('yes') : get_string('no');
                } else if ($ident == 'completed') {
                    $line[$ident] = $user->completed;
                } else if (str_starts_with($ident, 'ITEM_')) {
                    $line[$ident] = $this->process_item($ident, $user->id);
                }

            }

            $lines[] = $line;
        }

        return $this->convert_csv($lines);
    }
}
