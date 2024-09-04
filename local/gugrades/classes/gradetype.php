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
 * Language EN
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/grade/lib.php');

define('LOCAL_GUGRADES_FORMENU', true);
define('LOCAL_GUGRADES_NOTFORMENU', false);

/**
 * Handles custom gradetypes all in one place
 */
class gradetype {

    /**
     * Define the different types of grade
     * @param bool $menu - return truncated list for menu
     */
    private static function define(bool $menu = LOCAL_GUGRADES_NOTFORMENU) {

        // Complete list of types.
        $gradetypes = [
            'FIRST' => get_string('gradetypefirst', 'local_gugrades'),
            'SECOND' => get_string('gradetypesecond', 'local_gugrades'),
            'THIRD' => get_string('gradetypethird', 'local_gugrades'),
            'AGREED' => get_string('gradetypeagreed', 'local_gugrades'),
            'MODERATED' => get_string('gradetypemoderated', 'local_gugrades'),
            'LATE' => get_string('gradetypelate', 'local_gugrades'),
            'GOODCAUSE' => get_string('gradetypegoodcause', 'local_gugrades'),
            'CAPPED' => get_string('gradetypecapped', 'local_gugrades'),
            'CONDUCT' => get_string('gradetypeconduct', 'local_gugrades'),
            'OTHER' => get_string('gradetypeother', 'local_gugrades'),
            'CONVERTED' => get_string('converted', 'local_gugrades'),
            'PROVISIONAL' => get_string('provisional', 'local_gugrades'),
            'RELEASED' => get_string('released', 'local_gugrades'),
        ];

        // Types that should not be shown in a selection menu.
        $excludefrommenu = [
            'PROVISIONAL',
            'RELEASED',
            'CONVERTED',
        ];

        if ($menu) {
            foreach ($excludefrommenu as $exclude) {
                if (array_key_exists($exclude, $gradetypes)) {
                    unset($gradetypes[$exclude]);
                }
            }
        }

        return $gradetypes;
    }

    /**
     * Is the gradetype one that can be edited on the capture page?
     * @param string $gradetype
     * @return bool
     */
    public static function can_gradetype_be_edited(string $gradetype) {

        // List of eligible types.
        $edittypes = [
            'SECOND',
            'THIRD',
            'AGREED',
            'MODERATED',
            'LATE',
            'GOODCAUSE',
            'CAPPED',
            'CONDUCT',
            'OTHER',
        ];

        return in_array($gradetype, $edittypes);
    }

    /**
     * Get description
     * @param string $gradetype
     * @return string
     */
    public static function get_description(string $gradetype) {

        // Just handle CATEGORY on its own for simplicity.
        if ($gradetype == 'CATEGORY') {
            return get_string('gradetypecategory', 'local_gugrades');
        } else {
            $gradetypes = self::define();
            return $gradetypes[$gradetype] ?? '[[' . $gradetype . ']]';
        }
    }

    /**
     * Get gradetypes for menu
     * @param int $gradeitemid
     * @param bool $menu - return truncated list for menu
     * @return array
     */
    public static function get_menu(int $gradeitemid, bool $menu = LOCAL_GUGRADES_NOTFORMENU) {
        global $DB;

        $gradetypes = self::define($menu);

        // The menu doesn't include FIRST grades.
        unset($gradetypes['FIRST']);

        // Add 'other' gradetypes by name.
        $others = $DB->get_records('local_gugrades_column', ['gradeitemid' => $gradeitemid, 'gradetype' => 'OTHER']);
        foreach ($others as $other) {
            $gradetypes['OTHER_' . $other->id] = $other->other;
        }

        return $gradetypes;
    }

    /**
     * Sort columns array into correct order.
     * Order is as defined in the array in this class
     * Columns is an array of objects with the field 'gradetype'
     * NOTE: This means that anything not in the 'approved' array is filtered out.
     * ALSO NOTE: There can be multiple 'other' columns, which makes it interesting.
     * @param array $columns
     * @return array
     */
    public static function sort(array $columns) {
        $gradetypes = self::define();

        // Re-index columns by gradetype.
        // Other columns handled separately.
        $gtcolumns = [];
        $othercolumns = [];
        foreach ($columns as $column) {
            if ($column->gradetype == 'OTHER') {
                $othercolumns[] = $column;
            } else {
                $gtcolumns[$column->gradetype] = $column;
            }
        }

        // Sort into order of gradetypes.
        $sortedcolumns = [];
        foreach ($gradetypes as $gradetype => $description) {

            // There can be multiple 'other' columns.
            if ($gradetype == 'OTHER') {
                foreach ($othercolumns as $column) {
                    $sortedcolumns[] = $column;
                }
            } else if (array_key_exists($gradetype, $gtcolumns)) {
                $sortedcolumns[] = $gtcolumns[$gradetype];
            }
        }

        return $sortedcolumns;
    }

}
