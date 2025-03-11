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
        $form[] = [
            'identifier' => 'letter',
            'description' => 'Letter Grade',
            'category' => false,
        ];
        $form[] = [
            'identifier' => 'numerical',
            'description' => 'Numerical Grade',
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
        $form[] = [
            'identifier' => 'warnings',
            'description' => get_string('showwarnings', 'local_gugrades'),
            'category' => false,
        ];

        // Stored preferences
        $preferencename = 'local_gugrades_customaggregationexportselect_' . $gradecategoryid;
        $preferences = get_user_preferences($preferencename);
        if ($preferences) {
            $selected = explode(',', $preferences);
        } else {
            $selected = [];
        }

        // Add 'selected' fields.
        foreach ($form as $key => $record) {
            $identifier = $record['identifier'];
            if (in_array($identifier, $selected)) {
                $form[$key]['selected'] = true;
            } else {
                $form[$key]['selected'] = false;
            }
        }

        return $form;
    }

    /**
     * Does the parent category specify a weighted category?
     * @param int $gradeitemid
     * @return boolean
     */
    protected function is_weighted_category(int $gradeitemid) {
        global $DB, $GUGRADES_WEIGHTED;

        // Cached in global scope (sorry).
        if (isset($GUGRADES_WEIGHTED[$gradeitemid])) {
            return $GUGRADES_WEIGHTED[$gradeitemid];
        }

        $gradeitem = \local_gugrades\grades::get_gradeitem($gradeitemid);
        if ($gradeitem->itemtype == 'category') {
            $gradecategoryid = $gradeitem->iteminstance;
            $gradecategory = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);
            $parentid = $gradecategory->parent;
        } else {
            $parentid = $gradeitem->categoryid;
        }

        $parent = $DB->get_record('grade_categories', ['id' => $parentid], '*', MUST_EXIST);

        $weighted = $parent->aggregation == \GRADE_AGGREGATE_WEIGHTED_MEAN;
        $GUGRADES_WEIGHTED['$gradeitemid'] = $weighted;

        return $weighted;
    }

    /**
     * Process (grade)item for user
     * Can return multiple items (weight etc. if selected)
     * Options are
     * 'weights', 'strategy' and 'released'
     * @param string $identifier
     * @param int $courseid
     * @param int $userid
     * @param array $options
     * @return array
     */
    protected function process_item(string $identifier, int $courseid, int $userid, array $options) {

        // CSV items to return
        $csvitems = [];

        // We need this all over the place.
        $strnodata = get_string('nodata', 'local_gugrades');

        // Identifier should be ITEM_nnnn - we need the number.
        $parts = explode('_', $identifier);
        if (isset($parts[1])) {
            $gradeitemid = $parts[1];
        } else {
            throw new \moodle_exception('Invalid identifier - "' . $identifier . '"');
        }

        // Get the gradeitem
        $gradeitem = \local_gugrades\grades::get_gradeitem($gradeitemid);
        $iscategory = $gradeitem->itemtype == 'category';

        // Is this grade released?
        $isreleased = \local_gugrades\grades::is_grades_released($courseid, $gradeitemid);

        // Is this IN a weighted category?
        $isweighted = $this->is_weighted_category($gradeitemid);

        // If category...
        if ($iscategory) {

            // get the aggregated category grades
            [$category, $released] = \local_gugrades\grades::get_category_grades($gradeitemid, $userid);

            // Add aggregated category if there is one
            if ($category) {
                $csvitems[$identifier] = $category->displaygrade;
            } else {
                $csvitems[$identifier] = $strnodata;
            }

            // Released if there is one and released grades option.
            if ($options['released'] && $isreleased) {
                if ($released) {
                    $csvitems[$identifier . '_released'] = $released->displaygrade;
                } else {
                    $csvitems[$identifier . '_released'] = '';
                }
            }

            // If showing strategy for category
            if ($options['strategy']) {
                $gradecategoryid = \local_gugrades\grades::get_gradecategoryid_from_gradeitemid($gradeitemid);
                $strategy = \local_gugrades\aggregation::get_formatted_strategy($gradecategoryid);
                $csvitems[$identifier . '_strategy'] = $strategy;
            }

            // Warnings (only applies to category)
            if ($isreleased && $options['warnings']) {
                if ($category && $released && ($category->displaygrade != $released->displaygrade)) {
                    $warning = get_string('mismatch', 'local_gugrades');
                } else {
                    $warning = '';
                }
                $csvitems[$identifier . '_warnings'] = $warning;
            }
        } else {

            // Ordinary item.
            if ($provisional = \local_gugrades\grades::get_provisional_from_id($gradeitemid, $userid)) {

                $displaygrade = $provisional->displaygrade;
                $csvitems[$identifier] = $displaygrade;
            } else {
                $csvitems[$identifier] = get_string('nodata', 'local_gugrades');
            }

            // If option for released grades
            if ($options['released'] && $isreleased) {
                $released = \local_gugrades\grades::get_released_grade($courseid, $gradeitemid, $userid);
                if ($provisional) {
                    $csvitems[$identifier . '_released'] = $released->displaygrade;
                } else {
                    $csvitems[$identifier . '_released'] = $strnodata;
                }
            }
        }

        // If showing weight.
        if ($options['weights'] && $isweighted) {

            [ , $alteredweight] = \local_gugrades\grades::get_altered_weight($gradeitemid, $userid);
            $csvitems[$identifier . '_weights'] = number_format(100 * $alteredweight, 2) . '%';
        }

        return $csvitems;
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
     *
     * All the data lines should have the same data
     * @param int $courseid
     * @param int $gradecategoryid
     * @param array $line
     * @return array
     */
    protected function get_heading(int $courseid, int $gradecategoryid, array $line) {

        $originalform = $this->get_form_fields($courseid, $gradecategoryid);

        // Convert original form to simple(r) ident => description
        $descriptions = [];
        foreach ($originalform as $record) {
            $descriptions[$record['identifier']] = $record['description'];
        }

        $headings = [];

        foreach ($line as $ident => $value) {
            if (str_ends_with($ident, '_weights') || str_ends_with($ident, '_strategy') || str_ends_with($ident, '_released') || str_ends_with($ident, '_warnings')) {
                $parts = explode('_', $ident);
                if (count($parts) !== 3) {
                    throw new \moodle_exception('Incorrectly formatted option identifier - "' . $ident . '"');
                }
                $newident = $parts[0] . '_' . $parts[1];
                $option = $parts[2];
            } else {
                $newident = $ident;
                $option = '';
            }

            if (!array_key_exists($newident, $descriptions)) {
                throw new \moodle_exception('Identifier not found in descriptions - "' . $newident . '"');
            }
            $description = $descriptions[$newident];
            if ($option) {
                $description .= ' ' . get_string('option_' . $option, 'local_gugrades');
            }
            $headings[$ident] = $description;
        }

        return $headings;
    }

    /**
     * Save the user selections in user preferences
     * @param int $gradecategoryid
     * @param array $form
     */
    protected function save_preferences(int $gradecategoryid, array $form) {

        // Convert form to a simple array
        $preferences = [];
        foreach ($form as $record) {
            if ($record['selected']) {
                $preferences[] = $record['identifier'];
            }
        }

        // Convert to comma separated list of selected fields.
        $selected = implode(',', $preferences);
        $preferencename = 'local_gugrades_customaggregationexportselect_' . $gradecategoryid;

        // User preferences have some weird limit of 1333 characters.
        if (\core_text::strlen($selected) > 1333) {
            $selected = null;
        }

        set_user_preference($preferencename, $selected);
    }

    /**
     * Separate out the numerical and letter grade after aggregation to show numerical/letter values for additional exported columns.
     * @param object $user
     * @return string
     */
    protected function sanitise_grade($user) {

        // Sanatise the user grade to export scale value only
        if ($user->rawgrade) {
            $grade = $user->displaygrade;

            // Remove the bracketted value
            $parts = explode(' ', $grade);

            return $parts[0];
        }

        return '';
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

        $this->save_preferences($gradecategoryid, $form);

        // Get list of students.
        $users = \local_gugrades\aggregation::get_users($courseid, $gradecategoryid, '', '', $groupid);

        // Aggregate all the users.
        //\local_gugrades\aggregation::aggregate($courseid, $gradecategoryid, $users);
        [$columns] = \local_gugrades\aggregation::get_columns($courseid, $gradecategoryid);
        [$users] = \local_gugrades\aggregation::add_aggregation_fields_to_users($courseid, $gradecategoryid, $users, $columns);

        // Get the non-data settings and create options array.
        $options = [
            'weights' => $this->identifier_enabled('weights', $form),
            'strategy' => $this->identifier_enabled('strategy', $form),
            'released' => $this->identifier_enabled('released', $form),
            'warnings' => $this->identifier_enabled('warnings', $form),
        ];

        // Array holds CSV lines.
        $lines = [];

        // Iterate over users getting requested data.
        foreach ($users as $user) {
            $line = [];

            foreach ($form as $record) {
                $ident = $record['identifier'];
                $selected = $record['selected'];
                if (!$selected) {
                    continue;
                }

                // Ignore the non-data flags
                if ($ident == 'weights') {
                    continue;
                }
                if ($ident == 'strategy') {
                    continue;
                }
                if ($ident == 'released') {
                    continue;
                }
                if ($ident == 'warnings') {
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
                    $itemcsv = $this->process_item($ident, $courseid, $user->id, $options);
                    $line = array_merge($line, $itemcsv);
                } else if ($ident =='letter'){
                    $line[$ident] = $this->sanitise_grade($user);
                } else if ($ident =='numerical'){
                    $line[$ident] = $user->rawgrade;
                }

            }

            $lines[] = $line;
        }

        // Headings.
        // The first line of data will do.
        if (count($lines)) {
            $headings = $this->get_heading($courseid, $gradecategoryid, $lines[0]);
            array_unshift($lines, $headings);
        }

        return $this->convert_csv($lines);
    }
}
