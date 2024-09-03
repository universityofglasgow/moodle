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
 * Aggregation functions
 *
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

defined('MOODLE_INTERNAL') || die();

/**
 * Ideal number of characters for shortening grade category
 * and grade item names.
 */
define('SHORTNAME_LENGTH', 20);

require_once($CFG->dirroot . '/grade/lib.php');

/**
 * Class to store and manipulate grade structures for course
 */
class aggregation {

    /**
     * Factory for aggregation rule set
     * @param int $courseid
     * @param string $atype
     * @return \local_gugrades\aggregation\base
     */
    public static function aggregation_factory(int $courseid, string $atype) {

        // Just base at the moment, but other variations could exist.
        $aggregation = new \local_gugrades\aggregation\base($courseid, $atype);

        return $aggregation;
    }

    /**
     * Get aggregation strategy formatted for display
     * @param int $gradecategoryid
     * @param int $level
     * @return string
     *
     */
    public static function get_formatted_strategy(int $gradecategoryid) {
        global $DB;

        $gcat = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);
        $agg = $gcat->aggregation;

        // The 'level' we are at is the depth in the grade_items table minus 1
        // (depth = 1 is the course level).
        $level = $gcat->depth - 1;

        // If level >=2 then check for droplow. It's not supported at level 1.
        if (($level >= 2) && ($gcat->droplow > 0)) {
            $droplow = " (Drop lowest {$gcat->droplow})";
        } else {
            $droplow = '';
        }

        // Array translates aggregation id.
        $lookup = [
            \GRADE_AGGREGATE_MEAN => get_string('aggregatemean', 'grades'),
            \GRADE_AGGREGATE_MEDIAN => get_string('aggregatemedian', 'grades'),
            \GRADE_AGGREGATE_MIN => get_string('aggregatemin', 'grades'),
            \GRADE_AGGREGATE_MAX => get_string('aggregatemax', 'grades'),
            \GRADE_AGGREGATE_MODE => get_string('aggregatemode', 'grades'),
            \GRADE_AGGREGATE_WEIGHTED_MEAN => get_string('aggregateweightedmean', 'grades'),
            \GRADE_AGGREGATE_WEIGHTED_MEAN2 => get_string('aggregateweightedmean2', 'grades'),
            \GRADE_AGGREGATE_EXTRACREDIT_MEAN => get_string('unsupportedweight', 'local_gugrades'),
            \GRADE_AGGREGATE_SUM => get_string('aggregatesum', 'grades'),
        ];

        if (array_key_exists($agg, $lookup)) {
            return $lookup[$agg] . $droplow;
        } else {
            throw new \moodle_exception('Unknown aggregation strategy - ' . $agg);
        }
    }

    /**
     * Get aggregation table columns for supplied gradecategoryid
     * @param int $courseid
     * @param int $gradecategoryid
     * @return [$columns, $atype, $warnings]
     */
    public static function get_columns(int $courseid, int $gradecategoryid) {
        global $DB;

        // Accumulate any warnings.
        $warnings = [];

        // Get list of grade categories.
        $sql = "SELECT * FROM {grade_categories}
            WHERE courseid = :courseid
            AND parent = :parent
            AND hidden = :hidden";
        $rawcats = $DB->get_records_sql($sql, [
            'courseid' => $courseid,
            'parent' => $gradecategoryid,
            'hidden' => 0,
        ]);

        // Run over above and fetch enhanced category information from (hopefully) cache.
        $gradecategories = [];
        foreach ($rawcats as $rawcat) {
            $gradecategories[] = self::get_enhanced_grade_category($courseid, $rawcat->id);
        }

        $sql = "SELECT * FROM {grade_items}
            WHERE (itemtype = 'mod' OR itemtype = 'manual')
            AND courseid = :courseid
            AND categoryid = :categoryid";
        $gradeitems = $DB->get_records_sql($sql, [
            'courseid' => $courseid,
            'categoryid' => $gradecategoryid,
        ]);

        // Short names for items.
        $gradeitems = array_map(function($gi) {
            $gi->shortname = shorten_text($gi->itemname, SHORTNAME_LENGTH);
            $gi->weight = $gi->aggregationcoef;
            $gi->gradeitemid = $gi->id;
            return $gi;
        }, $gradeitems);

        // Columns are a mix of grade categories and items.
        $columns = [];
        foreach ($gradecategories as $gradecategory) {
            $columns[] = (object)[
                'fieldname' => 'AGG_' . $gradecategory->itemid,
                'gradeitemid' => $gradecategory->itemid,
                'categoryid' => $gradecategory->categoryid,
                'shortname' => $gradecategory->shortname,
                'fullname' => $gradecategory->name,
                'gradetype' => $gradecategory->gradetype,
                'grademax' => $gradecategory->grademax,
                'isscale' => $gradecategory->isscale,
                'schedule' => $gradecategory->schedule,
                'strategy' => self::get_formatted_strategy($gradecategory->categoryid),
                'strategyid' => $gradecategory->aggregation,

                // TODO - may not be so simple.
                'weight' => round($gradecategory->weight * 100),
            ];
        }
        foreach ($gradeitems as $gradeitem) {
            $mapping = \local_gugrades\grades::mapping_factory($courseid, $gradeitem->gradeitemid);
            $columns[] = (object)[
                'fieldname' => 'AGG_' . $gradeitem->gradeitemid,
                'gradeitemid' => $gradeitem->gradeitemid,
                'categoryid' => 0,
                'shortname' => $gradeitem->shortname,
                'fullname' => $gradeitem->itemname,
                'gradetype' => $mapping->name(),
                'grademax' => $mapping->get_maximum_grade(),
                'isscale' => $mapping->is_scale(),
                'schedule' => $mapping->get_schedule(),
                'strategy' => '',
                'strategyid' => 0,

                // TODO - may not be so simple.
                'weight' => round($gradeitem->weight * 100),
            ];
        }

        // Get aggregation type for these columns (i.e. this grade category).
        [$atype, $warnings] = self::get_aggregation_type($columns, $gradecategoryid);

        return [$columns, $atype, $warnings];
    }

    /**
     * Is resit required?
     * @param int $courseid
     * @param int $userid
     * @return boolean
     */
    protected static function is_resit_required(int $courseid, int $userid) {
        global $DB;

        return $DB->record_exists('local_gugrades_resitrequired', ['courseid' => $courseid, 'userid' => $userid]);
    }

    /**
     * Get single user for aggregation.
     * @param int $courseid
     * @param int $userid
     * @return object
     */
    public static function get_user(int $courseid, int $userid) {
        $context = \context_course::instance($courseid);
        $user = \local_gugrades\users::get_gradeable_user($context, $userid);
        $user->displayname = fullname($user);
        $user->resitrequired = self::is_resit_required($courseid, $userid);

        $user = \local_gugrades\users::add_picture_and_profile_to_user_record($courseid, $user);

        return $user;
    }

    /**
     * Get students - with some filtering
     * $firstname and $lastname are single initial character only.
     * @param int $courseid
     * @param string $firstname
     * @param string $lastname
     * @param int $groupid
     * @return array
     */
    public static function get_users(int $courseid, string $firstname, string $lastname, int $groupid) {
        $context = \context_course::instance($courseid);
        $users = \local_gugrades\users::get_gradeable_users($context, $firstname,
            $lastname, $groupid);

        // Add aditional fields.
        foreach ($users as $user) {
            $user->displayname = fullname($user);
            $user->resitrequired = self::is_resit_required($courseid, $user->id);

            // These get overwritten by actual total data.
            $user->total = get_string('gradesmissing', 'local_gugrades');
            $user->completed = 0;
            $user->error = get_string('gradesmissing', 'local_gugrades');
        }

        // Pictures.
        $users = \local_gugrades\users::add_pictures_and_profiles_to_user_records($courseid, $users);

        return array_values($users);
    }

    /**
     * Is grade hidden in MyGrades?
     * @param int $gradeitemid
     * @param int $userid
     */
    private static function is_grade_hidden(int $gradeitemid, int $userid) {
        global $DB;

        return $DB->record_exists('local_gugrades_hidden', ['gradeitemid' => $gradeitemid, 'userid' => $userid]);
    }

    /**
     * Add aggregation data for a single user
     * @param int $courseid
     * @param int gradecategoryid
     * @param object $user
     * @param array $columns
     * @return object
     */
    public static function add_aggregation_fields_to_user(int $courseid, int $gradecategoryid, object $user, array $columns) {
        global $DB;

        // We're assuming that this user is fully aggregated and no further checks are required.

        // Get the grade item corresponding to this category.
        $gcat = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);
        $gradecatitem = $DB->get_record('grade_items',
            ['itemtype' => 'category', 'iteminstance' => $gradecategoryid], '*', MUST_EXIST);

        $fields = [];
        $items = [];
        foreach ($columns as $column) {

            // Basic fields.
            $fieldname = 'AGG_' . $column->gradeitemid;
            $data = [
                'fieldname' => $fieldname, // Required by WS.
                'itemname' => $column->shortname, // Required by WS.
                'display' => '', // Required by WS.
                'schedule' => $column->schedule,
                'weight' => $column->weight,
                'grademissing' => true,
                'isscale' => $column->isscale,
                'dropped' => false,
                'isadmin' => false,
                'hidden' => self::is_grade_hidden($column->gradeitemid, $user->id),
                'overridden' => false,
            ];

            // Field identifier based on gradeitemid (which is unique even for categories).
            $provisional = \local_gugrades\grades::get_provisional_from_id($column->gradeitemid, $user->id);
            if ($provisional) {
                $data['rawgrade'] = $provisional->rawgrade;
                $data['display'] = $provisional->displaygrade;
                $data['grademissing'] = is_null($provisional->rawgrade);
                $data['admingrade'] = $provisional->admingrade;
                $data['dropped'] = $provisional->dropped;
                $data['isadmin'] = !empty($provisional->admingrade);
                $data['overridden'] = $provisional->catoverride;
            } else {
                $data['display'] = get_string('nodata', 'local_gugrades');
            }

            $fields[] = $data;
            $items[] = (object)$data;
        }

        $user->fields = $fields;

        // Get atype and aggregation rules.
        // This is why we needed items - array of array vs. array of objects.
        [$atype, $warnings] = self::get_aggregation_type($items, $gradecategoryid);
        $aggregation = self::aggregation_factory($courseid, $atype);

        // Read "top level" category for user info
        // This is needed if no aggregation is performed.
        $item = $DB->get_record('local_gugrades_grade',
            ['gradeitemid' => $gradecatitem->id, 'gradetype' => 'CATEGORY', 'userid' => $user->id, 'iscurrent' => 1],
            '*', MUST_EXIST);
        $user->rawgrade = $item->rawgrade;
        $user->total = $item->convertedgrade;
        $user->displaygrade = $item->displaygrade;
        $weighted = $aggregation->is_strategy_weighted($gcat->aggregation);
        $user->completed = $aggregation->completion($items, $weighted);
        $user->error = $item->auditcomment;
        $user->overridden = $item->catoverride;

        return $user;
    }

    /**
     * Add aggregation data to users.
     * Each user record contains list based on columns
     * Formatted to survive web services (will need reformatted for EasyDataTable)
     * @param int $courseid
     * @param int $gradecategoryid
     * @param array $users
     * @param array $columns
     * @return array
     */
    public static function add_aggregation_fields_to_users(int $courseid, int $gradecategoryid, array $users, array $columns) {
        global $DB;

        $gcat = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);

        // Get the grad eitem corresponding to this category.
        $gradecatitem = $DB->get_record('grade_items',
            ['itemtype' => 'category', 'iteminstance' => $gradecategoryid], '*', MUST_EXIST);

        // debugging stuff.
        $userhelpercount = 0;

        foreach ($users as $id => $user) {

            // The agregated 'CATEGORY' field should already be in the grades table.
            // If it's not, we need to aggregate this user
            if (!$DB->record_exists('local_gugrades_grade', ['gradeitemid' => $gradecatitem->id, 'gradetype' => 'CATEGORY', 'userid' => $user->id, 'iscurrent' => 1])) {
                self::aggregate_user_helper($courseid, $gradecategoryid, $user->id);
                $userhelpercount++;
            }

            $users[$id] = self::add_aggregation_fields_to_user($courseid, $gradecategoryid, $user, $columns);
        }

        // Debug stuff.
        $debug = [];
        $debug[]['line'] = "$userhelpercount User helper calls count.";

        return [$users, $debug];
    }

    /**
     * Get "breadcrumb" trail for given gradecategoryid
     * Return array of ['id' => ..., 'shortname' => ...]
     * @param int $gradecategoryid
     * @return array
     */
    public static function get_breadcrumb(int $gradecategoryid) {
        global $DB;

        $category = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);
        $path = explode('/', trim($category->path, '/'));
        array_shift($path);

        if ($path) {
            $breadcrumb = [];
            foreach ($path as $id) {
                $pathcat = $DB->get_record('grade_categories', ['id' => $id], '*', MUST_EXIST);
                $breadcrumb[] = [
                    'id' => $id,
                    'shortname' => shorten_text($pathcat->fullname, SHORTNAME_LENGTH),
                ];
            }

            return $breadcrumb;
        } else {
            return [];
        }
    }

    /**
     * Is this a "top level" category?
     * Table layout is slightly different at the toppermost level
     * @param int $gradecategoryid
     * @return bool
     */
    public static function is_top_level(int $gradecategoryid) {
        global $DB;

        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);

        return $gradecategory->depth == 2;
    }

    /**
     * Determine type of aggregated result for a given grade category. Possibilities
     * 1. If all columns are points then result is points
     * 2. If there are a mix of points and scales then it's an error
     * 3. If all Schedule A then result is Schedule A
     * 4. If all Schedule B then result is Schedule B
     * 5. If mix of Schedule A/B then Schedule A if >=50% by weight is Sched A, otherwise Sched B (see MGU-812)
     * 6. If sum of weights is zro then its an error
     * TODO: More finely grained error control.
     * Also checks for some possible error conditions
     * a. Error in any child
     * b. All weights are zero
     * c. mixture of points and scales (mixture of scales ok)
     * @param array $items
     * @param int $gradecategoryid
     * @return [$atype, $warnings]
     */
    public static function get_aggregation_type(array $items, $gradecategoryid) {
        global $DB;

        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);

        $sumofweights = 0;
        $sumscheduleaweights = 0;
        $sumschedulebweights = 0;
        $countpoints = 0;
        $warnings = [];
        $atype = null;
        foreach ($items as $item) {

            // If any item schedule is an error, then the result is as well.
            if ($item->schedule == 'E') {
                $atype = \local_gugrades\GRADETYPE_ERROR;
                $warnings[] = ['message' => get_string('childerror', 'local_gugrades')];
            }
            $sumofweights += $item->weight;
            if ($item->schedule == 'A') {
                $sumscheduleaweights += $item->weight;
            } else if ($item->schedule == 'B') {
                $sumschedulebweights += $item->weight;
            } else if ($item->schedule == 'P') {
                $countpoints++;
            }
        }

        // ONLY if weighted mean aggregation...
        // If sumofweights is zero, we're going to get divide-by-zero
        // errors down the line.
        if ($sumofweights == 0 && ($gradecategory->aggregation == \GRADE_AGGREGATE_WEIGHTED_MEAN)) {
            $atype = \local_gugrades\GRADETYPE_ERROR;
            $warnings[] = ['message' => get_string('weightszero', 'local_gugrades')];
        }

        // Points must be all items or no items.
        if (($countpoints != 0) && ($countpoints != count($items))) {
            $atype = \local_gugrades\GRADETYPE_ERROR;
            $warnings[] = ['message' => get_string('mixture', 'local_gugrades')];
        }

        // If we have found an error by this point then give up.
        if ($atype == \local_gugrades\GRADETYPE_ERROR) {
            return [$atype, $warnings];
        }

        // Now work out what we have.
        if ($countpoints == count($items)) {
            $atype = \local_gugrades\GRADETYPE_POINTS;
        } else if ($sumscheduleaweights >= ($sumofweights / 2)) {
            $atype = \local_gugrades\GRADETYPE_SCHEDULEA;
        } else if ($sumscheduleaweights < ($sumofweights / 2)) {
            $atype = \local_gugrades\GRADETYPE_SCHEDULEB;
        } else {
            throw new \moodle_exception('Cannot evaluate aggregation type');
        }

        // If we have decided it's points but a conversion map has been applied,
        // then it's whatever that map says
        if (($atype == \local_gugrades\GRADETYPE_POINTS) && ($mapitem = $DB->get_record('local_gugrades_map_item', ['gradecategoryid' => $gradecategoryid]))) {
            $mapid = $mapitem->mapid;
            $map = $DB->get_record('local_gugrades_map', ['id' => $mapid], '*', MUST_EXIST);
            $atype = $map->scale == 'schedulea' ? \local_gugrades\GRADETYPE_SCHEDULEA : \local_gugrades\GRADETYPE_SCHEDULEB;
        }

        return [$atype, []];
    }

    /**
     * Translate atype (A, B, P and so on)
     * Not all strings need translated
     * @param string $atype
     * @return string
     */
    public static function translate_atype(string $atype) {
        if ($atype == 'A') {
            return 'Schedule A';
        } else if ($atype == 'B') {
            return 'Schedule B';
        } else if ($atype == 'P') {
            return get_string('points', 'local_gugrades');
        } else if ($atype == 'E') {
            return get_string('error', 'local_gugrades');
        } else {
            throw new \moodle_exception('Unrecognised $atype - ' . $atype);
        }

    }

    /**
     * Invalidate the cache data
     * @param int courseid
     */
    public static function invalidate_cache(int $courseid) {
        global $DB;

        $cache = \cache::make('local_gugrades', 'gradeitems');

        // Get all grade category ids for this course
        $gradecats = $DB->get_records('grade_categories', ['courseid' => $courseid]);

        foreach ($gradecats as $gradecat) {
            $cachetag = 'CATEGORY_' . $courseid . '_' . $gradecat->id;
            $cache->delete($cachetag);
        }
    }

    /**
     * Recursive helper to build grade-item tree
     * force==true, disregard caches and build new structure (and cache)
     * @param int $courseid
     * @param int $gradecategoryid
     * @param bool $force
     * @return object
     */
    public static function recurse_tree(int $courseid, int $gradecategoryid, bool $force = false) {
        global $DB;

        // Cache the data if possible.
        // Construct a reasonably unique tag for this categoryid.
        $cache = \cache::make('local_gugrades', 'gradeitems');
        $cachetag = 'CATEGORY_' . $courseid . '_' . $gradecategoryid;

        // If this category is already stored in the cache then there's nothing to do.
        // (assuming recalculation is not forced)
        if (!$force && ($categorynode = $cache->get($cachetag))) {
            return $categorynode;
        }

        // Get the category and corresponding instance.
        $gcat = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);
        $gradeitem = $DB->get_record('grade_items',
            ['iteminstance' => $gradecategoryid, 'itemtype' => 'category'], '*', MUST_EXIST);

        $categorynode = (object)[
            'iscategory' => true,
            'categoryid' => $gradecategoryid,
            'itemid' => $gradeitem->id,
            'name' => $gcat->fullname,
            'shortname' => shorten_text($gcat->fullname, SHORTNAME_LENGTH),
            'keephigh' => (int)$gcat->keephigh,
            'droplow' => (int)$gcat->droplow,
            'aggregation' => (int)$gcat->aggregation,
            'weight' => (float)$gradeitem->aggregationcoef,
            'grademax' => 0.0, // Calculated further down.
            'isscale' => false,  // Calculated further down.
            'children' => [],
        ];

        // Get any categories at this level (and recurse into them).
        // Categories are stored in the grade_items table but (for some reason)
        // the (parent) categoryid field is null. So...
        $childcategories = $DB->get_records('grade_categories', ['parent' => $gradecategoryid]);
        foreach ($childcategories as $childcategory) {
            $categorynode->children[] = self::recurse_tree($courseid, $childcategory->id, $force);
        }

        // Get grade items in this grade category.
        $items = $DB->get_records('grade_items', ['categoryid' => $gradecategoryid]);
        foreach ($items as $item) {

            // Get the conversion object, so we can tell what sort of grade we're dealing with.
            if (!($node = $cache->get($item->id)) || $force) {
                $mapping = \local_gugrades\grades::mapping_factory($courseid, $item->id);
                $node = (object)[
                    'itemid' => $item->id,
                    'name' => $item->itemname,
                    'iscategory' => false,
                    'isscale' => $mapping->is_scale(),
                    'schedule' => $mapping->get_schedule(),
                    'weight' => $item->aggregationcoef,
                    'grademax' => $mapping->get_grademax(),
                ];
            }

            $categorynode->children[] = $node;
        }

        // Process $categorynode->children such that we know what the category's
        // aggregation type is (Schedule A, B, POINTS).
        [$atype, $warnings] = self::get_aggregation_type($categorynode->children, $gradecategoryid);
        $categorynode->atype = $atype;
        $categorynode->schedule = $atype;
        $categorynode->isscale = ($atype == 'A') || ($atype == 'B');
        $categorynode->warnings = $warnings;
        $categorynode->grademax = ($atype == 'A') || ($atype == 'B') ? 22.0 : 100;

        // Human name of whatever grade type this contains.
        $categorynode->gradetype = self::translate_atype($atype);

        // Write the completed node to the cache
        $cache->set($cachetag, $categorynode);

        return $categorynode;
    }

    /**
     * Get grade category with enhanced info
     * Any grade category depends on looking at its children (recursively),
     * so this either gets the cached grade category or (if it doesn't exist)
     * it calls recurse_tree to get it.
     * @param int $courseid
     * @param int $gradecategoryid
     * @return object
     */
    public static function get_enhanced_grade_category(int $courseid, int $gradecategoryid) {
        global $DB;

        // The cache uses the corresponding grade item id.
        $gradeitem = $DB->get_record('grade_items',
            ['iteminstance' => $gradecategoryid, 'itemtype' => 'category'], '*', MUST_EXIST);

        // Get cache instance.
        $cache = \cache::make('local_gugrades', 'gradeitems');
        $cachetag = 'CATEGORY_' . $courseid . '_';

        // Is the category in the cache. If not (re)build
        // (anc cache) that part of the category tree.
        if ($gradecategory = $cache->get($cachetag . $gradeitem->id)) {
            return $gradecategory;
        } else {
            return self::recurse_tree($courseid, $gradecategoryid, false);
        }
    }

    /**
     * Record dropped items in grade table
     * @param int $userid
     * @param array $items
     */
    private static function flag_dropped_items(array $items, int $userid) {
        global $DB;

        foreach ($items as $item) {
            $itemid = $item->itemid;

            // There can be multiple reasons (which we don't know here), so we'll just mark them
            // all to make our lives easier.
            $grades = $DB->get_records('local_gugrades_grade', ['gradeitemid' => $itemid, 'userid' => $userid, 'iscurrent' => 1]);
            foreach ($grades as $grade) {
                $grade->dropped = 1;
                $DB->update_record('local_gugrades_grade', $grade);
            }
        }
    }

    /**
     * Use the array of items for a given gradecategory and produce
     * an aggregated grade (or not).
     * The category object is provided to identify aggregation settings
     * and so on
     * Note that this will be for one gradecategory for one user, only.
     * Return array has the following...
     * - parent grade value (See MGU-821)
     * - raw aggregated grade
     * - display grade (e.g. scale)
     * - completion %
     * - error
     * @param int $courseid
     * @param object $category
     * @param array $items
     * @param int $level
     * @param int $userid
     * @return array ['rounded' grade, grade val, admingrade, grade disp, completion, error]
     */
    protected static function aggregate_user_category(int $courseid, object $category, array $items, int $level, int $userid) {

        // Get basic data about aggregation
        // (this is also a check that it actually exists).
        $keephigh = $category->keephigh;
        $droplow = $category->droplow;
        $aggmethod = $category->aggregation;
        $atype = $category->atype;
        $itemid = $category->itemid;

        // Get appropriate aggregation 'rule' set.
        $aggregation = self::aggregation_factory($courseid, $category->atype);

        // 0 based keys, please.
        $items = array_values($items);

        // If level 1 then calculate completion %age.
        // This can be calculated even though we can't run rest of aggregation (incomplete).
        $completion = 0;
        if ($level == 1) {
            $weighted = $aggregation->is_strategy_weighted($aggmethod);
            $completion = $aggregation->completion($items, $weighted);
        }

        // Get the correct aggregation function.
        $aggfunction = $aggregation->strategy_factory($aggmethod);

        // Quick check - all items must have a grade.
        foreach ($items as $item) {
            if ($item->grademissing) {
                return [null, null, '', null, $completion, get_string('gradesmissing', 'local_gugrades')];
            }
        }

        // Pre-process.
        $items = $aggregation->pre_process_items($items);

        // "drop lowest" items.
        // NOTE: droplow is NOT supported for level 1
        if (($droplow > 0) && ($level > 1)) {
            [$items, $droppeditems] = $aggregation->droplow($items, $droplow);
            self::flag_dropped_items($droppeditems, $userid);
        }

        // Need to have a valid aggregation type to actually do the aggregation.
        // OR, we've ended up with no items left after droplow.
        if (($category->atype == \local_gugrades\GRADETYPE_ERROR) || (count($items) == 0)) {
            return [null, null, '', null, $completion, get_string('cannotaggregate', 'local_gugrades')];
        } else {

            // If >=level2 then check for admin grades (see MGU-726).
            if ($level >= 2) {
                if ($admingrade = $aggregation->admin_grades_level2($items)) {
                    return [0, 0, $admingrade, $admingrade, $completion, ''];
                }
            }

            // If level = 1 then check admin grades for 'top' level. TODO - Ticket number?
            if ($level == 1) {
                if ($admingrade = $aggregation->admin_grades_level1($items)) {
                    return [0, 0, $admingrade, $admingrade, $completion, ''];
                }
            }

            // Now call the appropriate aggregation function to do the sums.
            $aggregatedgrade = call_user_func([$aggregation, $aggfunction], $items);

            // If this is a scale convert the numeric grade to the appropriate.
            if (($atype == \local_gugrades\GRADETYPE_SCHEDULEA) || ($atype == \local_gugrades\GRADETYPE_SCHEDULEB)) {
                [$convertedgrade, $convertedgradevalue] = $aggregation->convert($aggregatedgrade, $atype);

                // Should we pass back convertedgradevalue or aggregatedgrade (see MGU-821).
                $parentgrade = $aggregation->get_grade_for_parent($aggregatedgrade, $convertedgradevalue);

                // How do we want to display this?
                $displaygrade = $aggregation->format_displaygrade(
                    $convertedgrade, $aggregatedgrade, $convertedgradevalue, $completion, $level);

                return [$parentgrade, $aggregatedgrade, '', $displaygrade, $completion, ''];
            }

            // Return points grades.
            return [$aggregatedgrade, $aggregatedgrade, '', $aggregatedgrade, $completion, ''];
        }

        throw new \moodle_exception('Should never be here');
    }

    /**
     * Write aggregated category into gugrades_grades table
     * ONLY write if it hasn't changed (otherwise table just fills up)
     * TODO - need to handle errors
     * @param int $courseid
     * @param int $userid
     * @param object $category
     */
    protected static function write_aggregated_category(int $courseid, int $userid, object $category) {
        global $DB;

        // Aggregation function returns null in error .
        if (is_null($category->grade)) {
            $grade = null;
            $rawgrade = null;
            if (!$category->error) {
                throw new \moodle_exception('No error text when grade=null');
            }
            $iserror = true;
            $displaygrade = $category->error;
        } else {
            $iserror = false;
            $displaygrade = $category->displaygrade; // TODO ?
            $grade = $category->grade;
            $rawgrade = $category->rawgrade;
        }

        // NOTE: If category grade has been overridden then we cannot update it. It's 'sticky'.
        \local_gugrades\grades::write_grade(
            courseid:       $courseid,
            gradeitemid:    $category->itemid,
            userid:         $userid,
            admingrade:     $category->admingrade,
            rawgrade:       $rawgrade, // Grade before rounding or lookup.
            convertedgrade: $grade, // Grade for ongoing aggregation.
            displaygrade:   $displaygrade, // As displayed to the user.
            weightedgrade:  0,
            gradetype:      'CATEGORY',
            other:          '',
            iscurrent:      true,
            iserror:        $iserror,
            auditcomment:   $category->error,  // Hide the error message here
            ispoints:       !$category->isscale,
            overwrite:      true,
        );
    }

    /**
     * Set droplow flag to zero (not dropped)
     * @param int $gradeitemid
     * @param int $userid
     */
    private static function clear_droplow(int $gradeitemid, int $userid) {
        global $DB;

        $sql = "UPDATE {local_gugrades_grade}
            SET dropped = 0
            WHERE userid = :userid
            AND gradeitemid = :gradeitemid
            AND iscurrent = 1";
        $DB->execute($sql, [
            'userid' => $userid,
            'gradeitemid' => $gradeitemid,
        ]);
    }

    /**
     * Get overidden category (or not)
     * @param int $itemid
     * @param int $userid
     * @return object | false
     */
    protected static function get_overridden_category(int $itemid, int $userid) {
        global $DB;

        if ($grade = $DB->get_record('local_gugrades_grade', ['gradeitemid' => $itemid, 'userid' => $userid, 'catoverride' => 1])) {
            return [
                $grade->rawgrade,
                $grade->rawgrade,
                $grade->admingrade,
                $grade->displaygrade,
                0,
                '',
            ];
        } else {
            return false;
        }
    }

    /**
     * Aggregate user data recursively
     * (starting with current category)
     * Returning array of category totals for that user
     * $allitems 'collects' the various totals to display on the aggregation table
     * Returns aggregated total or null if data is incomplete
     * @param int $courseid
     * @param object $category
     * @param int $userid
     * @param int $level
     * @return array [total, $rawgrade, $displaygrade, completion, error]
     */
    protected static function aggregate_user(
        int $courseid,
        object $category,
        int $userid,
        int $level
        ) {

        // Information about the category is in the param
        // The field 'children' holds all the sub-items and sub-categories that
        // we need to 'add up'.
        // Get array of data to aggregate for this 'level' and then send off to
        // the aggregation function.
        $children = $category->children;
        $items = [];
        foreach ($children as $child) {

            // Clear droplow flag. We'll put it back later if required
            self::clear_droplow($child->itemid, $userid);

            // If this is itself a grade category then we need to recurse to get the aggregated total
            // of this category (and any error). Call with the 'child' segment of the category tree.
            if ($child->iscategory) {

                // Is the category overridden? Nothing more to do if it is.
                if ($overriddencategory = self::get_overridden_category($child->itemid, $userid)) {
                    [$childcategorytotal, $rawgrade, $admingrade, $display, $completion, $error] = $overriddencategory;
                } else {
                    [$childcategorytotal, $rawgrade, $admingrade, $display, $completion, $error] = self::aggregate_user(
                        $courseid, $child, $userid, $level + 1
                    );
                }
                $item = (object)[
                    'itemid' => $child->itemid,
                    'categoryid' => $child->categoryid,
                    'iscategory' => true,
                    'isscale' => $child->isscale,
                    'grademissing' => !is_numeric($childcategorytotal),
                    'grade' => $childcategorytotal,
                    'rawgrade' => $rawgrade,
                    'displaygrade' => $display,
                    'admingrade' => $admingrade,
                    'grademax' => $child->grademax,
                    'weight' => $child->weight,
                    'error' => $error,
                ];
            } else {

                // Is there a grade (in MyGrades) for this user?
                // Provisional will be null if nothing has been imported.
                $usercapture = new \local_gugrades\usercapture($courseid, $child->itemid, $userid);
                $provisional = $usercapture->get_provisional();
                if ($provisional) {
                    $item = (object)[
                        'itemid' => $child->itemid,
                        'iscategory' => false,
                        'grademissing' => false,
                        'grade' => $provisional->convertedgrade,
                        'admingrade' => $provisional->admingrade,
                        'grademax' => $child->grademax,
                        'weight' => $child->weight,
                        'displaygrade' => $provisional->displaygrade,
                        'isscale' => $child->isscale,
                    ];
                } else {
                    $item = (object)[
                        'itemid' => $child->itemid,
                        'iscategory' => false,
                        'grademissing' => true,
                        'weight' => $child->weight,
                    ];
                }
            }

            // Construct items array for aggregation
            $items[$child->itemid] = $item;
        }

        // List of items should hold list for this gradecategory only, ready
        // to aggregate.
        [$total, $rawgrade, $admingrade, $display, $completion, $error] =
            self::aggregate_user_category($courseid, $category, $items, $level, $userid);

        // If this is a points grade, level 2 or deeper, a grade is returned and a map exists then
        // we need to deal with this as a converted grade
        if (($level >= 2) && ($mapid = \local_gugrades\conversion::get_mapid_for_category($category->categoryid)) && $rawgrade) {
            [$display, $total] = \local_gugrades\conversion::aggregation_conversion($rawgrade, $category->grademax, $mapid);

        }

        // Write the aggregated category to the gugrades_grades table.
        $aggregatedcategory = (object)[
            'itemid' => $category->itemid, // TODO mapped itemid of category
            'categoryid' => $category->categoryid,
            'iscategory' => true,
            'isscale' => $category->isscale, // TODO is this right?
            'grademissing' => !is_numeric($total),
            'grade' => $total,
            'rawgrade' => $rawgrade,
            'displaygrade' => $display,
            'admingrade' => $admingrade,
            'grademax' => $category->grademax, // TODO need gradeitem
            'weight' => $category->weight, // TODO need gradeitem
            'error' => $error,
        ];
        self::write_aggregated_category($courseid, $userid, $aggregatedcategory);

        return [$total, $rawgrade, $admingrade, $display, $completion, $error];
    }

    /**
     * Helper function to aggregate a single user when updating any user grades.
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $userid
     * @param bool $force
     */
    public static function aggregate_user_helper(int $courseid, int $gradecategoryid, int $userid, bool $force = false) {

        // As $gradecategoryid could be second level + then we first need to find the 1st level
        // categoryid (as we're aggregating everything).
        $level1categoryid = \local_gugrades\grades::get_level_one_parent($gradecategoryid);

        // We need the recursed category tree for this categoryid. Hopefully, this should be cached.
        $toplevel = self::recurse_tree($courseid, $level1categoryid, $force);

        // Basic user object.
        $user = self::get_user($courseid, $userid);

        // Aggregate this user.
        self::aggregate_user($courseid, $toplevel, $userid, 1);
    }

    /**
     * Entry point for calculating complete aggregations of array of users.
     * @param int $courseid
     * @param int $gradecategoryid
     * @param array $users
     * @return array
     */
    public static function aggregate(int $courseid, int $gradecategoryid, array $users) {
        global $DB;

        // As $gradecategoryid could be second level + then we first need to find the 1st level
        // categoryid (as we're aggregating everything).
        $level1categoryid = \local_gugrades\grades::get_level_one_parent($gradecategoryid);

        // We need the recursed category tree for this categoryid. Hopefully, this should be cached.
        $toplevel = self::recurse_tree($courseid, $level1categoryid, true);

        // Run through each user and aggregate their grades.
        foreach ($users as $user) {

            // 1 = level 1 (we need to know what level we're at). Level is incremented
            // as call recurses.
            self::aggregate_user($courseid, $toplevel, $user->id, 1);
        }
    }

}
