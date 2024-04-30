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
 * External function.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\external;

use block_xp\di;
use block_xp\external\external_function_parameters;
use block_xp\external\external_multiple_structure;
use block_xp\external\external_single_structure;
use block_xp\external\external_value;
use core_text;

/**
 * External function.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_grade_items extends external_api {

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'query' => new external_value(PARAM_RAW),
        ]);
    }

    /**
     * Search grade items.
     *
     * @param int $courseid The course ID.
     * @param string $query The query.
     * @return array
     */
    public static function execute($courseid, $query) {
        global $CFG;

        $params = self::validate_parameters(self::execute_parameters(), compact('courseid', 'query'));
        $courseid = $params['courseid'];
        $query = core_text::strtolower(trim($params['query']));

        $world = di::get('course_world_factory')->get_world($courseid);
        self::validate_context($world->get_context());
        di::get('addon')->require_activated();
        $world->get_access_permissions()->require_manage();

        // Iterate over the tree, reduce the items, fitler out the null values,
        // then reduce in a flattened array.
        require_once($CFG->libdir . '/gradelib.php');
        $tree = \grade_category::fetch_course_tree($courseid, true);
        $data = array_reduce(array_filter(array_map(function($treeitem) use ($query) {
            return static::grade_item_tree_reducer($treeitem, $query);
        }, $tree['children'])), function($carry, $items) {
            return array_merge($carry, !is_array($items) ? [$items] : $items);
        }, []);

        if (empty($data)) {
            return [];
        }

        $firstresult = array_shift($data);
        if ($firstresult->type == 'course') {
            $data[] = $firstresult;
        }
        return $data;
    }

    /**
     * External function return values.
     *
     * @return external_value
     */
    public static function execute_returns() {
        $item = new external_single_structure([
            'id' => new external_value(PARAM_INT, 'The grade item id'),
            'type' => new external_value(PARAM_ALPHANUMEXT, 'The grade item type, see grade_item::$itemtype.'),
            'name' => new external_value(PARAM_RAW, 'The name of this item.'),
            'module' => new external_value(PARAM_ALPHANUMEXT, 'The module of this item, if any.'),
            'min' => new external_value(PARAM_FLOAT, 'The min value of this item.'),
            'max' => new external_value(PARAM_FLOAT, 'The max value of this item.'),
            'categories' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The grade category ID'),
                'name' => new external_value(PARAM_RAW, 'The grade category name'),
            ]), 'The list of parent categories ordered by depth ascending.'),
        ]);
        return new external_multiple_structure($item);
    }

    /**
     * Reduces a tree of grade items in a flat list.
     *
     * @param array $treeitem Containing what is returned from grade_category::fetch_course_tree.
     * @param string $query A string to filter items on, or '*'.
     * @param object[] $categories A list of parent categories, containing id and name.
     * @return object|array|null Null when nothing matches.
     */
    public static function grade_item_tree_reducer($treeitem, $query, $categories = []) {
        $item = $treeitem['object'];
        $iscategory = in_array($treeitem['type'], ['category']);

        if (!$iscategory && in_array($item->gradetype, [GRADE_TYPE_NONE, GRADE_TYPE_TEXT])) {
            return null;
        }

        if ($iscategory) {
            $children = $treeitem['children'];

            // Place the total last.
            $categorytotal = array_shift($children);
            $children[] = $categorytotal;

            $categories[] = (object) [
                'id' => $item->id,
                'name' => $item->fullname,
            ];
            return array_reduce($children, function($carry, $child) use ($query, $categories) {
                $child = static::grade_item_tree_reducer($child, $query, $categories);
                if ($child === null) {
                    return $carry;
                } else if (!is_array($child)) {
                    $child = [$child];
                }
                return array_merge($carry, $child);
            }, []);
        }

        $data = (object) ['id' => $item->id];
        $data->type = $item->itemtype;
        $data->name = $item->itemname;
        $data->module = $item->itemmodule;
        $data->modulename = $item->itemmodule;
        $data->min = $item->grademin;
        $data->max = $item->grademax;
        $data->categories = $categories;

        // Not all of them have a name.
        if ($item->itemtype === 'course') {
            $data->name = get_string('coursetotal', 'core_grades');
        } else if ($item->itemtype === 'category') {
            $data->name = get_string('categorytotal', 'core_grades');
        }

        if ($query === '*') {
            return $data;
        }

        $candidates = [core_text::strtolower($data->name)];
        if ($item->itemtype === 'course' || $item->itemtype === 'category') {
            $lastcategory = end($categories);
            if ($lastcategory) {
                $candidates[] = core_text::strtolower($lastcategory->name);
            }
        }

        $matchesquery = array_reduce($candidates, function($carry, $candidate) use ($query) {
            return $carry || strpos($candidate, $query) !== false;
        }, false);
        if (!$matchesquery) {
            return null;
        }

        return $data;
    }

}
