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
 * Gradebook functions
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/grade/lib.php');

/**
 * Class to store and manipulate grade structures for course
 */
class grades {

    /**
     * Get a grade item.
     * As we can constantly look up the same grade item over and over
     * @param int $gradeitemid
     * @return object
     */
    public static function get_gradeitem(int $gradeitemid) {
        global $DB, $GRADEITEMS;

        // Just bypassign this, for the moment, as it seems to cause
        // issues in tests.
        // (presumably, data is changing)

        return $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);

        if (empty($GRADEITEMS)) {
            $GRADEITEMS = [];
        }

        if (array_key_exists($gradeitemid, $GRADEITEMS)) {
            return $GRADEITEMS[$gradeitemid];
        }

        $gradeitem = $DB->get_record('grade_items', ['id' => $gradeitemid], '*', MUST_EXIST);
        $GRADEITEMS[$gradeitemid] = $gradeitem;

        return $gradeitem;
    }

    /**
     * Get item name from gradeitemid
     * @param int $gradeitemid
     * @return string
     */
    public static function get_item_name_from_itemid(int $gradeitemid) {
        global $DB;

        if ($gradeitem = $DB->get_record('grade_items', ['id' => $gradeitemid])) {
            return $gradeitem->itemname;
        }

        return '';
    }

    /**
     * Get gradecategoryid from gradeitemid
     * @param int $gradeitemid
     * @return int
     */
    public static function get_gradecategoryid_from_gradeitemid(int $gradeitemid) {
        global $DB;

        $gradeitem = self::get_gradeitem($gradeitemid);
        if ($gradeitem->itemtype == 'category') {
            return $gradeitem->iteminstance;
        } else {
            return $gradeitem->categoryid;
        }
    }

    /**
     * Recursively search child categories for one or more grade items
     * We just care that one exists
     * @param int $categoryid
     * @return boolean
     */
    private static function find_child_item(int $categoryid) {
        global $DB;

        // Search for any grade items at this level (that's the end of it).
        if ($DB->record_exists('grade_items', ['categoryid' => $categoryid])) {
            return true;
        }

        // Failing that, search any child categories.
        if ($childcats = $DB->get_records('grade_categories', ['parent' => $categoryid])) {
            foreach ($childcats as $childcat) {
                if (self::find_child_item($childcat->id)) {
                    return true;
                }
            }
        }

        // Failing all of that, there can't be any.
        return false;
    }

    /**
     * Is a grade category gradetype = GRADE_TYPE_NONE
     * The grade type is stored in the record of the associated grade_item
     * @param object $gradecategory
     * @return boolean
     */
    public static function is_gradecategory_grade_type_none(object $gradecategory) {
        global $DB;

        // If there's no associated grade_item (which probably isn't good), just assume it's no grade.
        if (!$gradeitem = $DB->get_record('grade_items', ['iteminstance' => $gradecategory->id, 'itemtype' => 'category'])) {
            return true;
        }

        return $gradeitem->gradetype == GRADE_TYPE_NONE;
    }

    /**
     * Is a grade item the *equivalent* of none.
     * Currently, that's GRADE_TYPE_NONE or GRADE_TYPE_TEXT
     * (Setting grade = none in Assignment sets GRADE_TYPE_TEXT in the db)
     * @param object $gradeitem
     * @return boolean
     */
    public static function is_gradeitem_grade_type_none(object $gradeitem) {

        return ($gradeitem->gradetype == GRADE_TYPE_NONE) || ($gradeitem->gradetype == GRADE_TYPE_TEXT);
    }

    /**
     * Get first level categories (should be summative / formative and so on)
     * Actually depth==2 in the database (1 == top level)
     * @param int $courseid
     * @return array
     */
    public static function get_firstlevel(int $courseid) {
        global $DB;

        // First level is depth 2. Depth 1 is the course.
        $gradecategories = $DB->get_records('grade_categories', [
            'courseid' => $courseid,
            'hidden' => 0,
            'depth' => 2,
        ]);

        // We're only interested in categories that have some grade items
        // Somewhere in their tree, and are not gradetype = none.
        foreach ($gradecategories as $category) {
            if (!self::find_child_item($category->id)) {
                unset($gradecategories[$category->id]);
            } else if (self::is_gradecategory_grade_type_none($category)) {
                unset($gradecategories[$category->id]);
            }
        }

        return $gradecategories;
    }

    /**
     * Get first level (summative / formative etc) category id for given category id
     * Depth == 2 for the first level
     * @param int $gradecategoryid
     * @return bool
     */
    public static function get_level_one_parent(int $gradecategoryid) {
        global $DB;

        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);
        $cats = explode('/', trim($gradecategory->path, '/'));
        if (isset($cats[1])) {
            return $cats[1];
        } else {
            throw new \moodle_exception('Top level category not found. Grade Category ID = ' . $gradecategoryid);
        }
    }

    /**
     * Get level for grade category
     * @param int $gradecategoryid
     * @return int
     */
    public static function get_category_level(int $gradecategoryid) {
        global $DB;

        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);

        // OUR level is one less than the level in the grade_categories table.
        return $gradecategory->depth - 1;
    }

    /**
     * Get the parent category id of the give grade item id.
     * @param int $itemid
     * @return int
     */
    public static function get_parent_grade_category(int $itemid) {
        global $DB;

        $gradeitem = self::get_gradeitem($itemid);

        // If this is a category then we need to look there for parent.
        if ($gradeitem->itemtype == 'category') {
            $gradecategory = $DB->get_record('grade_categories', ['id' => $gradeitem->iteminstance], '*', MUST_EXIST);

            return $gradecategory->parent;
        } else {

            return $gradeitem->categoryid;
        }
    }

    /**
     * Save resit item
     * MGU-1351
     * @param int $courseid
     * @param int $itemid
     * @param bool $set
     */
    public static function save_resit_item(int $courseid, int $itemid, bool $set) {
        global $DB;

        // Get parent category.
        $parentcategoryid = self::get_parent_grade_category($itemid);

        // If it's not set then just delete the records.
        if (!$set) {
            $DB->delete_records('local_gugrades_resit', ['gradecategoryid' => $parentcategoryid]);
        } else {
            if ($resit = $DB->get_record('local_gugrades_resit', ['gradecategoryid' => $parentcategoryid])) {
                $resit->gradeitemid = $itemid;
                $DB->update_record('local_gugrades_resit', $resit);
            } else {
                $resit = (object)[
                    'courseid' => $courseid,
                    'gradecategoryid' => $parentcategoryid,
                    'gradeitemid' => $itemid,
                ];
                $DB->insert_record('local_gugrades_resit', $resit);
            }
        }
    }

    /**
     * Has a grade category been configured as having a resit?
     * That is, does it have an entry in local_gugrades_resit?
     * @param int $gradecategoryid
     * @return boolean
     */
    public static function is_resit_category(int $gradecategoryid) {
        global $DB;

        return $DB->record_exists('local_gugrades_resit', ['gradecategoryid' => $gradecategoryid]);
    }

    /**
     * Is this grade item flagged as a resit?
     * That is, does it have an entry in local_gugrades_resit (for gradeitemid)?
     * @param int $gradeitemid
     * @return boolean
     */
    public static function is_resit_gradeitem(int $gradeitemid) {
        global $DB;

        return $DB->record_exists('local_gugrades_resit', ['gradeitemid' => $gradeitemid]);
    }

    /**
     * Get the gradeitemid given the gradecategoryid
     * @param int $gradecategoryid
     * @return int
     */
    public static function get_gradeitemid_from_gradecategoryid(int $gradecategoryid) {
        global $DB;

        $gradeitem = $DB->get_record('grade_items', ['itemtype' => 'category', 'iteminstance' => $gradecategoryid], '*', MUST_EXIST);

        return $gradeitem->id;
    }

    /**
     * Check if grade category is a candidate for being a resit category
     * MGU-1351
     * Must have two and only two 'children'
     * @param int $gradecategoryid
     * @return boolean
     */
    public static function is_resit_category_candidate(int $gradecategoryid) {
        global $DB;

        // First get count of grade items in this category. 
        $gradeitemscount = $DB->count_records('grade_items', ['categoryid' => $gradecategoryid]);

        // If this is more than two already then we're done here.
        if ($gradeitemscount > 2) {
            return false;
        }

        // Now get the count of child categories. 
        $childcategorycount = $DB->count_records('grade_categories', ['parent' => $gradecategoryid]);

        // The total of both must be exactly 2
        $iscandidate = ($gradeitemscount + $childcategorycount) == 2;

        // If it's NOT a candidate, then we should delete any resit records in gugrades_resits
        // (gradebook structure must have changed)
        if (!$iscandidate) {
            $DB->delete_records('local_gugrades_resit', ['gradecategoryid' => $gradecategoryid]);
        }

        return $iscandidate;
    }

    /**
     * If grade category is a candidate for resits then does it actually have
     * a resit defined. Note that there's can be only none or one resit items.
     * MGU-1351
     * @param int $gradecategoryid
     * @return int|false
     */
    public static function get_resit_itemid(int $gradecategoryid) {
        global $DB;

        if ($resit = $DB->get_record('local_gugrades_resit', ['gradecategoryid' => $gradecategoryid])) {
            return $resit->gradeitemid;
        } else {
            return false;
        }
    }

    /**
     * Get the category/item tree beneath the selected depth==2 category.
     * @param int $courseid
     * @param int $categoryid
     * @return object
     */
    public static function get_activitytree(int $courseid, int $categoryid) {
        global $DB;

        $category = $DB->get_record('grade_categories', ['id' => $categoryid], '*', MUST_EXIST);
        if (self::is_gradecategory_grade_type_none($category)) {
            throw new \moodle_exception('Attempting to open GRADE_TYPE_NONE category');
        }
        $gradeitems = $DB->get_records('grade_items', ['courseid' => $courseid]);

        // Remove any grade type non like items.
        foreach ($gradeitems as $id => $gradeitem) {
            if (self::is_gradeitem_grade_type_none($gradeitem)) {
                unset($gradeitems[$id]);
                continue;
            }
        }

        $gradecategories = $DB->get_records('grade_categories', [
            'courseid' => $courseid,
        ]);

        // Note if there were *any* resit category candidates.
        $anyresitcandidates = false;

        // Remove any GRADE_TYPE_NONE categories.
        $even = false;
        foreach ($gradecategories as $id => $gradecategory) {
            if (self::is_gradecategory_grade_type_none($gradecategory)) {
                unset($gradecategories[$id]);
                continue;
            }

            // Add gradeitemid.
            $gradecategories[$id]->itemid = self::get_gradeitemid_from_gradecategoryid($id);

            // Add aggregation strategy.
            $gradecategories[$id]->strategy = \local_gugrades\aggregation::get_formatted_strategy($id);

            // Add reset candidate.
            $resitcandidate = self::is_resit_category_candidate($id);
            $gradecategories[$id]->resitcandidate = $resitcandidate;
            $gradecategories[$id]->resititemid = self::get_resit_itemid($id);
            if ($resitcandidate) {
                $anyresitcandidates = true;
            }

            // Add odd/even for style to second level only.
            $level = self::get_category_level($id);
            if ($level == 2) {
                $gradecategories[$id]->even = $even;
                $even = !$even;
            } else {
                $gradecategories[$id]->even = false;
            }
        }
        $categorytree = self::recurse_activitytree($category, $gradeitems, $gradecategories);

        // Note availability of resit candidates to root node.
        $categorytree->anyresitcandidates = $anyresitcandidates;

        return $categorytree;
    }

    /**
     * Recursive routine to build activity tree
     * Tree consists of both sub-categories and grade items
     * {
     *     category -> current category
     *     items -> array of grade items in this category
     *     categories -> array of grade categories, children of this category (recursive)
     * }
     * @param object $category
     * @param array $gradeitems
     * @param array $gradecategories
     * @return object
     */
    private static function recurse_activitytree($category, $gradeitems, $gradecategories) {
        global $OUTPUT;

        $tree = [];

        // First find any grade items attached to the current category.
        $items = [];
        foreach ($gradeitems as $item) {
            if ($item->categoryid == $category->id) {
                $item->info = (object) \local_gugrades\api::get_grade_item($item->id);
                $item->icon = $OUTPUT->image_url('monologo', $item->itemmodule)->out();
                $items[$item->id] = $item;
            }
        }

        // Next find any sub-categories of this category.
        $categories = [];
        foreach ($gradecategories as $gradecategory) {
            if ($gradecategory->parent == $category->id) {
                $categories[$gradecategory->id] = self::recurse_activitytree($gradecategory, $gradeitems, $gradecategories);
            }
        }

        // Add this all up
        // (array_values() to prevent arrays beening encoded as objects in JSON).
        $record = new \stdClass();
        $record->category = $category;
        $record->items = array_values($items);
        $record->categories = array_values($categories);

        return $record;
    }

    /**
     * Given gradecatoryid - get all the items in that category
     * and in an child categories (recursively)
     * (a bit like recurse_activitytree but only items)
     * We don't actually recurse - just use the path
     * @param object $gradecategory
     * @return array (of grade_items)
     */
    public static function get_gradeitems_recursive(object $gradecategory) {
        global $DB;

        // Whatever path this has will be the start of all other paths that we want.
        $path = $gradecategory->path;
        $sql = "SELECT gi.* from {grade_items} gi
            JOIN {grade_categories} gc ON gi.categoryid = gc.id
            WHERE gc.path LIKE :path";
        $items = $DB->get_records_sql($sql, ['path' => $path . '%']);

        return $items;
    }

    /**
     * Given gradetcategoryid - get all included gradecategories
     * and child categories.
     * Not actually recursive - just uses the path
     * @param int $gradecategoryid
     * @return array
     */
    public static function get_gradecategories_recursive(int $gradecategoryid) {
        global $DB;

        // All the paths will start with the same start path as this one.
        $category = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);
        $path = $category->path;
        $sql = "SELECT * FROM {grade_categories}
            WHERE path LIKE :path";
        $categories = $DB->get_records_sql($sql, ['path' => $path . '%']);

        return $categories;
    }

    /**
     * Check that all grades are the same for a potential recursive import
     * For a given gradeitemid, we're looking at that items *peers* and any
     * children thereof. So we want to start with the parent category of the
     * supplied gradeitemid.
     * ALSO, check that all gradestypes are valid
     * @param int $gradeitemid
     * @return array(recursiveavailable, recursivematch, allgradesvalid)
     */
    public static function recursive_import_match(int $gradeitemid) {
        global $DB;

        $gradeitem = self::get_gradeitem($gradeitemid);
        $courseid = $gradeitem->courseid;
        $categoryid = $gradeitem->categoryid;

        $recursiveavailable = false;
        $recursivematch = false;
        $allgradesvalid = true;

        // This MUST be a 'second level' category. Which is actually the 3rd one down.
        // SO it will have a path field like /a/b/c/ or longer.
        // If not, recursive import is not available.
        // MGU-1103 - now available at any level
        $gradecategory = $DB->get_record('grade_categories', ['id' => $categoryid], '*', MUST_EXIST);

        // Trim to remove leading and trailing /, otherwise you get two extra empty fields.
        $pathcats = explode('/', trim($gradecategory->path, '/'));
        if (count($pathcats) > 1) {
            $recursiveavailable = true;

            // Get grade items.
            if ($items = self::get_gradeitems_recursive($gradecategory)) {

                // Check for any items with invalid grade types.
                foreach ($items as $item) {
                    if (!self::is_grade_supported($item->id)) {

                        // Recursive is technically available but a grade is invalid.
                        return [true, false, false];
                    }
                }

                // As a basic check grade min, max and scale type need to match.
                $first = array_shift($items);
                $recursivematch = true;
                foreach ($items as $item) {
                    if (
                        ($first->grademax != $item->grademax) ||
                        ($first->grademin != $item->grademin) ||
                        ($first->scaleid != $item->scaleid)
                    ) {
                        $recursivematch = false;
                    }
                }
            }
        }

        return [
            $recursiveavailable,
            $recursivematch,
            $allgradesvalid,
        ];
    }

    /**
     * Get the grade column record for the gradetype and (optionally)
     * 'other' text
     * @param int $courseid
     * @param int $gradeitemid
     * @param string $gradetype
     * @param string $other
     * @param bool $points
     * @return object
     */
    public static function get_column(int $courseid, int $gradeitemid, string $gradetype, string $other, bool $points) {
        global $DB;

        // Check 'other' text is valid.
        $other = trim($other);
        if (($gradetype != 'OTHER') && !empty($other)) {
            throw new \moodle_exception('Other text provided for non-other gradetype');
        }
        if (($gradetype == 'OTHER') && empty($other)) {
            throw new \moodle_exception('No other text provided for other gradetype');
        }

        // Does record exist?
        if (!$other) {
            if ($column = $DB->get_record('local_gugrades_column', ['gradeitemid' => $gradeitemid, 'gradetype' => $gradetype])) {
                return $column;
            }
        } else {

            // If other text, due to sql_compare_text it all gets a bit more complicated.
            $compareother = $DB->sql_compare_text('other');
            $sql = "SELECT * FROM {local_gugrades_column}
                WHERE gradeitemid = :gradeitemid
                AND gradetype = :gradetype
                AND $compareother = :other";
            if ($column = $DB->get_record_sql($sql,
                ['gradeitemid' => $gradeitemid, 'gradetype' => $gradetype, 'other' => $other])) {
                return $column;
            }
        }

        // Failing the above, we need a new column record.
        $column = new \stdClass;
        $column->courseid = $courseid;
        $column->gradeitemid = $gradeitemid;
        $column->gradetype = $gradetype;
        $column->other = $other;
        $column->points = $points;
        $column->id = $DB->insert_record('local_gugrades_column', $column);

        return $column;
    }

    /**
     * Unpack OTHER
     * Where we have OTHER_xxx
     * @param int $courseid
     * @param string $other
     * @return $string
     */
    public static function unpack_other(int $courseid, string $other) {
        global $DB;

        $parts = explode('_', $other);
        if (count($parts) != 2) {
            throw new \moodle_exception('Invalid OTHER_ code - "' . $other . '"');
        }
        $columnid = $parts[1];
        if (!$column = $DB->get_record('local_gugrades_column', ['courseid' => $courseid, 'id' => $columnid])) {
            throw new \moodle_exception('Column not found (or not valid for course) - ' . $columnid);
        }
        if ($column->gradetype != 'OTHER') {
            throw new \moodle_exception('Column is not an OTHER column - ' . $columnid);
        }

        return $column->other;
    }

    /**
     * Write grade to local_gugrades_grade table
     * NOTE: $overwrite means that we don't make multiple copies (for aggregated categories)
     *
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @param string $admingrade
     * @param float|null $rawgrade
     * @param float|null $convertedgrade
     * @param string $displaygrade
     * @param float $weightedgrade
     * @param string $gradetype
     * @param string $other
     * @param bool $iscurrent
     * @param bool $iserror
     * @param string $auditcomment
     * @param bool $ispoints
     * @param bool $overwrite
     * @param bool $catoverride
     * @param bool $notavailable (only applicable to categories)
     */
    public static function write_grade(
        int $courseid,
        int $gradeitemid,
        int $userid,
        string $admingrade,
        float|null $rawgrade,
        float|null $convertedgrade,
        string $displaygrade,
        float $weightedgrade,
        string $gradetype,
        string $other,
        bool $iscurrent,
        bool $iserror,
        string $auditcomment,
        bool $ispoints,
        bool $overwrite = false,
        bool $catoverride = false,
        bool $notavailable = false,
    ) {
        global $DB, $USER;

        // Must have editgrade capability to change anything at all
        $context = \context_course::instance($courseid);
        if (!has_capability('local/gugrades:editgrades', $context)) {
            return;
        }

        // Sanity
        if ($catoverride && ($gradetype != 'CATEGORY')) {
            throw new \moodle_exception('catoverride true when gradetype sis not CATEGORY');
        }

        // Admingrade Sanity.
        if ($admingrade) {
            \local_gugrades\admingrades::validate_admingrade($admingrade);
        }

        // Invalidate provisionalgrade cache
        self::invalidate_provisionalgrade_cache($gradeitemid, $userid);

        // Get/create the column entry.
        $column = self::get_column($courseid, $gradeitemid, $gradetype, $other, $ispoints);

        // If this is CATEGORY and there's a corresponding 'catoverride' record then we don't
        // attempt to write anything new.
        if ($gradetype == 'CATEGORY') {
            if ($DB->record_exists('local_gugrades_grade', ['gradeitemid' => $gradeitemid, 'userid' => $userid, 'columnid' => $column->id, 'catoverride' => 1])) {
                return;
            }
        }

        // Does this already exist?
        // The plan is not to touch catoverride that already exists.
        // Don't touch CATEGORY grades as these are made not current elsewhere.
        if (!$overwrite) {
            $gradetypecompare = $DB->sql_compare_text('gradetype');
            $sql = 'SELECT * FROM {local_gugrades_grade}
                WHERE courseid = :courseid
                AND gradeitemid = :gradeitemid
                AND userid = :userid
                AND iscurrent = :iscurrent
                AND columnid = :columnid
                AND catoverride = 0
                AND ' . $gradetypecompare . ' = :gradetype';
            if ($oldgrades = $DB->get_records_sql($sql, [
                'courseid' => $courseid,
                'gradeitemid' => $gradeitemid,
                'userid' => $userid,
                'iscurrent' => true,
                'columnid' => $column->id,
                'gradetype' => $gradetype,
            ])) {
                foreach ($oldgrades as $oldgrade) {

                    // It's not current any more.
                    $oldgrade->iscurrent = false;
                    $DB->update_record('local_gugrades_grade', $oldgrade);
                }
            }
        }

        // Are we overwriting an existing grade (probably CATEGORY)?
        if ($overwrite) {

            // Find the existing entry - if not, create a new one anyway
            if ($gugrade = $DB->get_record('local_gugrades_grade',
                ['courseid' => $courseid, 'gradeitemid' => $gradeitemid, 'userid' => $userid, 'columnid' => $column->id, 'iscurrent' => 1])) {
                $gugrade->rawgrade = $rawgrade;
                $gugrade->admingrade = $admingrade;
                $gugrade->convertedgrade = $convertedgrade;
                $gugrade->displaygrade = $displaygrade;
                $gugrade->weightedgrade = $weightedgrade;
                $gugrade->gradetype = $gradetype;
                $gugrade->other = $other;
                $gugrade->iscurrent = true;
                $gugrade->iserror = $iserror;
                $gugrade->notavailable = $notavailable;
                $gugrade->auditby = $USER->id;
                $gugrade->audittimecreated = time();
                $gugrade->auditcomment = $auditcomment;
                $gugrade->points = $ispoints;
                $gugrade->catoverride = $catoverride;

                $DB->update_record('local_gugrades_grade', $gugrade);

                return;
            }
        }

        $gugrade = new \stdClass;
        $gugrade->courseid = $courseid;
        $gugrade->gradeitemid = $gradeitemid;
        $gugrade->userid = $userid;
        $gugrade->rawgrade = $rawgrade;
        $gugrade->admingrade = $admingrade;
        $gugrade->convertedgrade = $convertedgrade;
        $gugrade->displaygrade = $displaygrade;
        $gugrade->weightedgrade = $weightedgrade;
        $gugrade->gradetype = $gradetype;
        $gugrade->other = $other;
        $gugrade->iscurrent = true;
        $gugrade->iserror = $iserror;
        $gugrade->notavailable = $notavailable;
        $gugrade->columnid = $column->id;
        $gugrade->auditby = $USER->id;
        $gugrade->audittimecreated = time();
        $gugrade->auditcomment = $auditcomment;
        $gugrade->points = $ispoints;
        $gugrade->catoverride = $catoverride;
        $DB->insert_record('local_gugrades_grade', $gugrade);
    }

    /**
     * Get grade from array by reason
     * @param array $grades
     * @param string $reason
     * @return object
     */
    private static function get_grade_by_reason(array $grades, string $reason) {
        $grade = array_column($grades, null, 'reasonshortname')[$reason] ?? false;

        return $grade->grade;
    }

    /**
     * Work out provisional grade
     * TODO: This is just a 'dummy' - needs lots more logic
     * @param array $grades
     * @return float
     */
    private static function get_provisional_grade($grades) {

        // ATM provision grade is the same as FIRST grade.
        if ($grade = self::get_grade_by_reason($grades, 'FIRST')) {
            return $grade->grade;
        }

        return false;
    }

    /**
     * Create tag for provisionalgrade cache
     * @param int $gradeitemid
     * @param int $userid
     * @return string
     */
    public static function get_provisionalgrade_cachetag(int $gradeitemid, int $userid) {

        return 'PROVISIONAL_' . $gradeitemid . '_' . $userid;
    }

    /**
     * Invalidate provisionalgrade cache entry
     * @param int $gradeitemid
     * @param int $userid
     */
    public static function invalidate_provisionalgrade_cache(int $gradeitemid, int $userid) {
        $cache = \cache::make('local_gugrades', 'provisionalgrade');
        $tag = self::get_provisionalgrade_cachetag($gradeitemid, $userid);
        $cache->delete($tag);
    }

    /**
     * Get the provisional/released grade from the
     * gradeitemid / userid
     * @param int $gradeitemid
     * @param int $userid
     * @return oject|bool
     */
    public static function get_provisional_from_id(int $gradeitemid, int $userid) {
        global $DB;

        // Is this cached?
        $cache = \cache::make('local_gugrades', 'provisionalgrade');
        $tag = self::get_provisionalgrade_cachetag($gradeitemid, $userid);
        if ($grade = $cache->get($tag)) {
            return $grade;
        }

        // ...id is a proxy for time added.
        // Cannot use the timestamp as the unit tests write the test grades all in the
        // same second (potentially).
        $sql = 'SELECT * FROM {local_gugrades_grade}
            WHERE id = (SELECT max(id) FROM {local_gugrades_grade}
                WHERE gradeitemid = :gradeitemid
                AND userid = :userid
                AND gradetype<>"RELEASED"
                AND iscurrent = 1)';
        $grade = $DB->get_record_sql($sql, [
            'gradeitemid' => $gradeitemid,
            'userid' => $userid,
        ]);

        // Cache grade.
        $cache->set($tag, $grade);

        return $grade;
    }

    /**
     * Create (global/cached) array of provisional grades for
     */

    /**
     * Add grades to user records for capture page
     * @param int $courseid
     * @param int $gradeitemid
     * @param array $users
     * @param bool $gradehidden
     * @return array
     */
    public static function add_grades_to_user_records(int $courseid, int $gradeitemid, array $users, bool $gradehidden) {
        foreach ($users as $id => $user) {
            $users[$id] = self::add_grades_for_user($courseid, $gradeitemid, $user, $gradehidden);
        }

        return $users;
    }

    /**
     * Add grades to single user record
     * @param int $courseid
     * @param int $gradeitemid
     * @param object $user
     * @param bool $gradehidden
     * @return array
     */
    public static function add_grades_for_user(int $courseid, int $gradeitemid, object $user, bool $gradehidden = false) {
        //$usercapture = new usercapture($courseid, $gradeitemid, $user->id);
        $usercapture = \local_gugrades\usercapture::create($courseid, $gradeitemid, $user->id);
        $user->grades = $usercapture->get_grades();
        $user->alert = $usercapture->alert();

        // If the parent grade is hidden, then the individual items are assumed to be.
        // This is (I hope) what Moodle does (hidden flag is on and greyed out).
        // MGU-1233
        if ($gradehidden) {
            $user->gradebookhidden = true;
        } else {
            $user->gradebookhidden = $usercapture->is_gradebookhidden();
        }

        return $user;
    }

    /**
     * Is grade supported (e.g. scale with no scale mapping)
     * @param int $gradeitemid
     * @return bool
     */
    public static function is_grade_supported(int $gradeitemid) {
        global $DB;

        $gradeitem = self::get_gradeitem($gradeitemid);
        $gradetype = $gradeitem->gradetype;

        // Grade type "none" is technically supported as we will deal with it elsewhere. 
        // None means that the grade is ignored completely. Text is a proxy for None in some activities (just text)
        if (($gradetype == GRADE_TYPE_NONE) || ($gradetype == GRADE_TYPE_TEXT)) {
            return true;
        }
        if ($gradetype == GRADE_TYPE_SCALE) {
            $scaleid = $gradeitem->scaleid;
            if (!$DB->record_exists_sql('select * from {local_gugrades_scalevalue} where scaleid=:scaleid',
                ['scaleid' => $scaleid])) {
                return false;
            }

            // If it's a valid scale, is it configured to work with MyGrades?
            if (!$scaletype = $DB->get_record('local_gugrades_scaletype', ['scaleid' => $scaleid])) {
                return false;
            } else {
                if (($scaletype->type != 'schedulea') && ($scaletype->type != 'scheduleb')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if grade item is hidden or locked
     * @param int $gradeitemid
     * @return array [$hidden, $locked]
     */
    public static function is_grade_hidden_locked(int $gradeitemid) {
        global $DB;

        // Hidden and locked fields either hold 0/1 or a date.
        // To save madness, ignore the date. May not survive.
        $gradeitem = self::get_gradeitem($gradeitemid);

        return [
            $gradeitem->hidden == 1 ? true : false,
            $gradeitem->locked == 1 ? true : false,
        ];
    }

    /**
     * Analyse grade item. Is it...
     * - is it valid at all
     * - an aggregated category?
     * - points value
     * - if so, is the max points 22 (proxy for 22 point scale)
     * - scale
     * - if so, is it the 22 point scale
     * TODO: Need to look at Schedule B
     *
     * Returns ['scale' | 'scale22' | 'value' | false, $gradeitem] or [false, false]
     * @param int $gradeitemid
     * @return array
     */
    public static function analyse_gradeitem(int $gradeitemid) {
        global $DB;

        // Is it valid at all?
        if (!self::is_grade_supported($gradeitemid)) {
            return [false, false];
        }

        $gradeitem = self::get_gradeitem($gradeitemid);

        // Could be an (aggregated) category.
        // In this case, the grade details are determined by aggregation.
        if ($gradeitem->itemtype == 'category') {
            return ['category', $gradeitem];
        }

        $gradetype = $gradeitem->gradetype;
        if ($gradetype == GRADE_TYPE_VALUE) {
            if ($gradeitem->grademax == 22) {

                // TODO: May change but to get it working.
                //return ['value', $gradeitem];
                return ['scale22', $gradeitem];
            } else {
                return ['value', $gradeitem];
            }
        } else if ($gradetype == GRADE_TYPE_SCALE) {
            //if (($gradeitem->grademin == 1) && ($gradeitem->grademax == 23)) {
            //    return ['scale22', $gradeitem];
            //} else {
                return ['scale', $gradeitem];
            //}
        }

        throw new \moodle_exception('Invalid gradeitem encountered in grades::analyse_gradeitem');
    }

    /**
     * Have any grades already been imported for gradeitem
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $groupid
     * @return bool
     */
    public static function is_grades_imported(int $courseid, int $gradeitemid, int $groupid = 0) {
        global $DB;

        if ($groupid) {
            $sql = "SELECT * FROM {local_gugrades_grade} gg
                JOIN {groups_members} gm ON gm.userid = gg.userid
                WHERE gg.gradeitemid = :gradeitemid
                AND gm.groupid = :groupid";
                $params = ['gradeitemid' => $gradeitemid, 'groupid' => $groupid];
        } else {
            $sql = 'select * from {local_gugrades_grade} where gradeitemid=:gradeitemid';
            $params = ['gradeitemid' => $gradeitemid];
        }

        return $DB->record_exists_sql($sql, $params);
    }

    /**
     * Check if gradeitem / user combo has any imported/added grades
     * @param int $gradeitemid
     * @param int $userid
     * @return bool
     */
    public static function user_has_grades(int $gradeitemid, int $userid) {
        global $DB;

        return $DB->record_exists('local_gugrades_grade', ['gradeitemid' => $gradeitemid, 'userid' => $userid]);
    }

    /**
     * Cleanup empty columns
     * If a column no longer has active grades it can be removed
     * @param int $gradeitemid
     */
    public static function cleanup_empty_columns(int $gradeitemid) {
        global $DB;

        $columns = $DB->get_records('local_gugrades_column', ['gradeitemid' => $gradeitemid]);
        foreach ($columns as $column) {
            if (!$DB->record_exists('local_gugrades_grade',
                ['gradeitemid' => $gradeitemid, 'gradetype' => $column->gradetype, 'iscurrent' => 1])) {
                $DB->delete_records('local_gugrades_column', ['id' => $column->id]);
            }
        }
    }

    /**
     * Get grade capture columns
     * Get the different grade types used for this capture
     * Each gradetype == OTHER with distinct 'other' text is considered a different column
     * @param int $courseid
     * @param int $gradeitemid
     * @return array
     */
    public static function get_grade_capture_columns(int $courseid, int $gradeitemid) {
        global $DB;

        if ($columns = $DB->get_records('local_gugrades_column', ['gradeitemid' => $gradeitemid])) {

            // As there is at least one column then there must be a provisional
            // But it has to go at the end.
            $provisionalcolumn = self::get_column($courseid, $gradeitemid, 'PROVISIONAL', '', false);
            if (isset($columns[$provisionalcolumn->id])) {
                unset($columns[$provisionalcolumn->id]);
            }
            $columns = array_values($columns);
            $columns[] = $provisionalcolumn;

            // Add descriptions.
            foreach ($columns as $column) {
                if ($column->gradetype == 'OTHER') {
                    $column->description = $column->other;
                } else {
                    $column->description = gradetype::get_description($column->gradetype);
                }
            }

        } else {
            $columns = [];
        }

        // There has to be a first column.
        $mapping = self::mapping_factory($courseid, $gradeitemid);
        if (!in_array('FIRST', array_column($columns, 'gradetype'))) {
            $firstcolumn = (object)[
                'id' => 0,
                'gradetype' => 'FIRST',
                'description' => gradetype::get_description('FIRST'),
                'other' => '',
                'points' => !$mapping->is_scale(),
            ];
            array_unshift($columns, $firstcolumn);
        }

        // Sort columns.
        $columns = \local_gugrades\gradetype::sort($columns);

        // Add editable flag.
        // If gradeitem has been converted then ONLY columns that are now scales can be edited.
        // Columns in the original points cannot.
        $converted = \local_gugrades\conversion::is_conversion_applied($courseid, $gradeitemid);
        foreach ($columns as $column) {
            if ($converted && $column->points) {
                $column->editable = false;
            } else {
                $column->editable = \local_gugrades\gradetype::can_gradetype_be_edited($column->gradetype);
            }
        }

        return $columns;
    }

    /**
     * Factory for mapping class
     * TODO: May need some improvement in detecting correct/supported grade (type)
     * The name of the class is in the scaletype table.
     * @param int $courseid
     * @param int $gradeitemid
     * @return object
     */
    public static function mapping_factory(int $courseid, int $gradeitemid) {
        global $DB;

        $gradeitem = self::get_gradeitem($gradeitemid);
        $gradetype = $gradeitem->gradetype;

        // Is it a category?
        if ($gradeitem->itemtype == 'category') {

            // Get the aggregated category
            $category = \local_gugrades\aggregation::get_enhanced_grade_category($courseid, $gradeitem->iteminstance);

            // Use the category 'atype' to determine correct class
            if ($category->atype == 'A') {
                $type = 'schedulea';
            } else if ($category->atype == 'B') {
                $type = 'scheduleb';
            } else {
                $type = 'points';
            }

            $classname = 'local_gugrades\\mapping\\' . $type;
            return new $classname($courseid, $gradeitemid);
        }

        // Has it been converted?
        $converted = \local_gugrades\conversion::is_conversion_applied($courseid, $gradeitemid);

        // Is it a scale of some sort?
        if ($converted) {

            $mapitem = $DB->get_record('local_gugrades_map_item', ['gradeitemid' => $gradeitemid], '*', MUST_EXIST);
            $map = $DB->get_record('local_gugrades_map', ['id' => $mapitem->mapid], '*', MUST_EXIST);

            $classname = 'local_gugrades\\mapping\\' . $map->scale;
            if (!class_exists($classname, true)) {
                throw new \moodle_exception('Unknown conversion class - "' . $classname . '"');
            }

            return new $classname($courseid, $gradeitemid, $converted);

        } else if ($gradetype == GRADE_TYPE_SCALE) {

            // See if scale is in our scaletype table.
            if (!$scaletype = $DB->get_record('local_gugrades_scaletype', ['scaleid' => $gradeitem->scaleid])) {
                throw new \moodle_exception('Scale not found in gugrades_scaletype table. ScaleID = ' . $gradeitem->scaleid . ' gradeitemid = ' . $gradeitemid);
            }

            // Get the name of the class and see if it exists.
            $classname = 'local_gugrades\\mapping\\' . $scaletype->type;
            if (!class_exists($classname, true)) {
                throw new \moodle_exception('Unknown conversion class - "' . $classname . '"');
            }

            return new $classname($courseid, $gradeitemid, $converted);
        } else {

            // It's points. BUT... *special case*
            // Grading out of 0 to 22 is a proxy for Schedule A.
            if (($gradeitem->grademin == 0) && ($gradeitem->grademax == 22)) {
                return new \local_gugrades\mapping\points22($courseid, $gradeitemid, false);
            }

            // We're assuming it's a points scale (already checked for weird, unsupported types).
            return new \local_gugrades\mapping\points($courseid, $gradeitemid);
        }
    }

    /**
     * Are all grades / scales supported in the current category tree?
     * e.g. if a scale, is it one we support?
     * @param int $courseid
     * @param int $gradecategoryid
     * @return boolean
     */
    public static function are_all_grades_supported(int $courseid, int $gradeitemid) {
        global $DB;

        // Get 'top level' for this category.
        $level1 = self::get_level_one_parent(self::get_gradecategoryid_from_gradeitemid($gradeitemid));

        // Get all the items.
        $level1category = $DB->get_record('grade_categories', ['id' => $level1], '*', MUST_EXIST);
        $items = self::get_gradeitems_recursive($level1category);

        // check all are supported
        foreach ($items as $item) {
            if (!self::is_grade_supported($item->id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get scale as value => name associative array
     * This is from our 'scalevalue' table.
     * If scaleid=0 then we'll return a default Schedule A or B scale
     * (this is for maxgrade=22 which doesn't have a scale)
     * @param int $scaleid
     * @param string $schedule
     * @return array
     *
     */
    public static function get_scale(int $scaleid, string $schedule = 'schedulea') {
        global $DB;

        // Scaleid=0 used for grademax=22.
        if (!$scaleid) {
            if ($schedule == 'scheduleb') {
                return \local_gugrades\mapping\scheduleb::get_map();
            } else {
                return \local_gugrades\mapping\schedulea::get_map();
            }
        }

        if ($items = $DB->get_records('local_gugrades_scalevalue', ['scaleid' => $scaleid])) {
            $output = [-1 => get_string('nograde', 'local_gugrades')];
            foreach ($items as $item) {
                $output[$item->value] = $item->item;
            }

            return $output;
        } else {
            throw new \moodle_exception('Invalid scaleid in grades::get_scale');
        }
    }

    /**
     * Get grades for display on Dashboard for a give gradecategoryid
     * Basically just returns realeased grades (TODO: is that correct?)
     * @param int $userid
     * @param int $gradecategoryid
     * @return array
     */
    public static function get_dashboard_grades(int $userid, int $gradecategoryid) {
        global $DB;

        // Get grades.
        $sql = "SELECT  * FROM {local_gugrades_grade} gg
            JOIN {grade_items} gi ON gi.id = gg.gradeitemid
            WHERE gi.categoryid = :gradecategoryid
            AND gg.userid = :userid
            AND gg.iscurrent = 1
            AND gg.gradetype = 'RELEASED'";
        $grades = $DB->get_records_sql($sql, ['gradecategoryid' => $gradecategoryid, 'userid' => $userid]);

        return $grades;
    }

    /**
     * Delete all data for gradeitemid
     * TODO: Don't forget to add anything new that we add in db.
     * @param int $courseid
     * @param int $gradeitemid
     */
    public static function delete_grade_item(int $courseid, int $gradeitemid) {
        global $DB;

        $DB->delete_records('local_gugrades_grade', ['gradeitemid' => $gradeitemid]);
        $DB->delete_records('local_gugrades_audit', ['gradeitemid' => $gradeitemid]);
        $DB->delete_records('local_gugrades_column', ['gradeitemid' => $gradeitemid]);
        $DB->delete_records('local_gugrades_hidden', ['gradeitemid' => $gradeitemid]);
        $DB->delete_records('local_gugrades_altered_weight', ['gradeitemid' => $gradeitemid]);
        $DB->delete_records('local_gugrades_map_item', ['gradeitemid' => $gradeitemid]);

        \local_gugrades\aggregation::invalidate_cache($courseid);
    }

    /**
     * Delete all data for gradeitemid
     * TODO: Don't forget to add anything new that we add in db.
     * @param int $courseid
     */
    public static function delete_course(int $courseid) {
        global $DB;

        $DB->delete_records('local_gugrades_agg_conversion', ['courseid' => $courseid]);
        $DB->delete_records('local_gugrades_config', ['courseid' => $courseid]);

        // Delete conversion maps
        $maps = $DB->get_records('local_gugrades_map', ['courseid' => $courseid]);
        foreach ($maps as $map) {
            $DB->delete_records('local_gugrades_map_item', ['mapid' => $map->id]);
            $DB->delete_records('local_gugrades_map_value', ['mapid' => $map->id]);
        }
        $DB->delete_records('local_gugrades_map', ['courseid' => $courseid]);
        $DB->delete_records('local_gugrades_resitrequired', ['courseid' => $courseid]);
    }

    /**
     * Show conversion (button)
     * If the grade eitem is a points grade then the conversion button can be shown
     * TODO: Figure what this means for level 2+ items
     * @param int $gradeitemid
     * @return boolean
     */
    public static function showconversion(int $gradeitemid) {
        global $DB;

        $gradeitem = self::get_gradeitem($gradeitemid);
        $gradetype = $gradeitem->gradetype;

        // Ropey check for exact 22.
        if ($gradeitem->grademax == 22) {
            return false;
        }

        return $gradetype == GRADE_TYPE_VALUE;
    }

    /**
     * Have grades been released?
     * @param int $courseid
     * @param int $gradeitemid
     * @return boolead
     */
    public static function is_grades_released(int $courseid, int $gradeitemid) {
        global $DB;

        return $DB->record_exists('local_gugrades_grade',
            ['courseid' => $courseid, 'gradeitemid' => $gradeitemid, 'gradetype' => 'RELEASED', 'iscurrent' => 1]);
    }

    /**
     * Get released grade for user
     * @param int $courseid
     * @param int $gradeitemid
     * @param int $userid
     * @return object | false
     */
    public static function get_released_grade(int $courseid, int $gradeitemid, int $userid) {
        global $DB;

        if ($grade = $DB->get_record('local_gugrades_grade', ['courseid' => $courseid, 'gradeitemid' => $gradeitemid, 'userid' => $userid, 'gradetype' => 'RELEASED', 'iscurrent' => 1])) {
            return $grade;
        } else {
            return false;
        }
    }

    /**
     * Get grade category level
     * Our level 1 is 'depth' in the table minus 1 (in core, depth 1 is the course)
     * @param int $gradecategoryid
     * @return int
     */
    public static function get_gradecategory_level(int $gradecategoryid) {
        global $DB;

        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);

        return $gradecategory->depth - 1;
    }

    /**
     * Get grade item level
     * (of the grade category it lives in)
     * Effectively the depth of the parent category
     * @param int $gradeitemid
     * @return int
     */
    public static function get_gradeitem_level(int $gradeitemid) {
        global $DB;

        $item = self::get_gradeitem($gradeitemid);
        if ($item->itemtype == 'category') {
            $category = $DB->get_record('grade_categories', ['id' => $item->iteminstance], '*', MUST_EXIST);

            return self::get_gradecategory_level($category->parent);
        } else {
            return self::get_gradecategory_level($item->categoryid);
        }
    }

    /**
     * Are there any grades imported/added for this grade item?
     * @param int $gradeitemid
     * @return bool
     */
    public static function any_grades(int $gradeitemid) {
        global $DB;

        return $DB->record_exists('local_gugrades_grade', ['gradeitemid' => $gradeitemid, 'iscurrent' => 1]);
    }

    /**
     * Handle event for grade item being updated
     * Basically, we'll re-aggregate if there's anything to aggregate
     * @param int $courseid
     * @param int $gradeitemid
     */
    public static function grade_item_updated(int $courseid, int $gradeitemid) {
        global $DB;

        // If there are no grades then there's little point
        if (!self::any_grades($gradeitemid)) {
            return;
        }

        // Need the categoryid.
        if ($gradeitem = $DB->get_record('grade_items', ['id' => $gradeitemid])) {
            if ($gradeitem->itemtype == 'category') {
                $categoryid = $gradeitem->iteminstance;
            } else {
                $categoryid = $gradeitem->categoryid;
            }
            $level1 = self::get_level_one_parent($categoryid);

            // Queue an adhoc-task
            if ($level1) {
                $task = \local_gugrades\task\recalculate::instance($courseid, $level1);
                \core\task\manager::queue_adhoc_task($task);
            }
        }
    }

    /**
     * Remove category override
     * @param int $gradeitemid
     * @param int $userid
     */
    public static function remove_catoverride(int $gradeitemid, int $userid) {
        global $DB;

        /*
        $grades = $DB->get_records('local_gugrades_grade',
        ['gradeitemid' => $gradeitemid, 'gradetype' => 'CATEGORY', 'userid' => $userid, 'iscurrent' => 1]);
    if (count($grades)>1) {
    var_dump($grades); die;}
    */

        if ($grade = $DB->get_record('local_gugrades_grade',
            ['gradeitemid' => $gradeitemid, 'gradetype' => 'CATEGORY', 'userid' => $userid, 'iscurrent' => 1])) {
            $grade->catoverride = false;
            $grade->iscurrent = false;
            $DB->update_record('local_gugrades_grade', $grade);
        }
    }

    /**
     * Get aggregated grade from gradeitemid
     * @param int $gradeitemid
     * @param int $userid
     * @return object | false
     */
    public static function get_aggregated_from_gradeitemid(int $gradeitemid, int $userid) {
        global $DB;

        // Is this definitely a category
        $item = self::get_gradeitem($gradeitemid);
        if ($item->itemtype != 'category') {
            return false;
        }

        // Get current corresponding gugrades_grade
        if ($grade = $DB->get_record('local_gugrades_grade', ['gradeitemid' => $gradeitemid, 'userid' => $userid, 'gradetype' => 'CATEGORY', 'iscurrent' => 1])) {
            return $grade;
        } else {
            return false;
        }
    }

    /**
     * Determine if 'exclude empty grades' is checked.
     * @param int $gradecategoryid
     * @return bool
     */
    public static function is_exclude_empty_grades(int $gradecategoryid) {
        global $DB;

        $gradecategory = $DB->get_record('grade_categories', ['id' => $gradecategoryid], '*', MUST_EXIST);

        return $gradecategory->aggregateonlygraded;
    }

    /**
     * Get weight of gradeitem. Taking into consideration possibility of
     * it being altered for a user.
     * Return the value and boolean true = altered
     * @param int $gradeitemid
     * @param int $userid
     * @return array [float, float, boolean]
     */
    public static function get_altered_weight(int $gradeitemid, int $userid) {
        global $DB;

        // Get original weight
        $gradeitem = self::get_gradeitem($gradeitemid);
        $originalweight = $gradeitem->aggregationcoef;
        $alteredweight = $originalweight;
        $isaltered = false;

        // Is there an altered weight?
        if ($altered = $DB->get_record('local_gugrades_altered_weight', ['gradeitemid' => $gradeitemid, 'userid' => $userid])) {
            $isaltered = true;
            $alteredweight = $altered->weight;
        }

        return [$originalweight, $alteredweight, $isaltered];
    }

    /**
     * Update / insert altered weight
     * @param int $courseid
     * @param int $categoryid
     * @param int $gradeitemid
     * @param int $userid
     * @param float $weight
     */
    public static function update_altered_weight(int $courseid, int $categoryid, int $gradeitemid, int $userid, float $weight) {
        global $DB;

        if ($altered = $DB->get_record('local_gugrades_altered_weight', ['gradeitemid' => $gradeitemid, 'userid' => $userid])) {
            $altered->weight = $weight;
            $altered->timealtered = time();
            $DB->update_record('local_gugrades_altered_weight', $altered);
        } else {
            $altered = new \stdClass;
            $altered->courseid = $courseid;
            $altered->categoryid = $categoryid;
            $altered->gradeitemid = $gradeitemid;
            $altered->userid = $userid;
            $altered->weight = $weight;
            $altered->timealtered = time();
            $DB->insert_record('local_gugrades_altered_weight', $altered);
        }

        self::invalidate_provisionalgrade_cache($gradeitemid, $userid);
    }

    /**
     * Revert altered weights
     * @param int $courseid
     * @param int $categoryid
     * @param int $userid
     */
    public static function revert_altered_weights(int $courseid, int $categoryid, int $userid) {
        global $DB;

        $DB->delete_records('local_gugrades_altered_weight', ['courseid' => $courseid, 'categoryid' => $categoryid]);
    }

    /**
     * Get category provisional and released grade
     * @param int $gradeitemid
     * @param int $userid
     * @return array
     */
    public static function get_category_grades(int $gradeitemid, int $userid) {
        global $DB;

        // Category.
        $sql = 'SELECT * FROM {local_gugrades_grade}
        WHERE id = (SELECT max(id) FROM {local_gugrades_grade}
            WHERE gradeitemid = :gradeitemid
            AND userid = :userid
            AND gradetype = "CATEGORY"
            AND iscurrent = 1)';
        $category = $DB->get_record_sql($sql, [
            'gradeitemid' => $gradeitemid,
            'userid' => $userid,
        ]);

        // Released
        $sql = 'SELECT * FROM {local_gugrades_grade}
        WHERE id = (SELECT max(id) FROM {local_gugrades_grade}
            WHERE gradeitemid = :gradeitemid
            AND userid = :userid
            AND gradetype = "RELEASED"
            AND iscurrent = 1)';
        $released = $DB->get_record_sql($sql, [
            'gradeitemid' => $gradeitemid,
            'userid' => $userid,
        ]);

        return [$category, $released];
    }

    /**
     * Cleanup unused columns
     * If a column has no grades, it will be removed
     * @param int $courseid
     */
    public static function cleanup_unused_columns_course(int $courseid) {
        global $DB;

        $columns = $DB->get_records('local_gugrades_column', ['courseid' => $courseid]);
        foreach ($columns as $column) {
            if (!$DB->record_exists('local_gugrades_grade', ['columnid' => $column->id])) {
                $DB->delete_records('local_gugrades_column', ['id' => $column->id]);
            }
        }
    }

    /**
     * Is gradeitemid a category
     * Confirm that a gradeitemid really is a category
     * @param int $gradeitemid
     * @return boolean
     */
    public static function is_gradeitemid_category(int $gradeitemid) {
        $item = self::get_gradeitem($gradeitemid);

        return $item->itemtype == 'category';
    }

    /**
     * Get cm from gradeitemid
     * @param int $gradeitemid
     * @return object
     */
    public static function get_cm_from_gradeitemid(int $gradeitemid) {
        $item = self::get_gradeitem($gradeitemid);

        // This (obviously) has to be a module.
        if ($item->itemtype != 'mod') {
            return false;
        }

        $cm = get_coursemodule_from_instance($item->itemmodule, $item->iteminstance, $item->courseid, false, MUST_EXIST);

        return $cm;
    }
}
