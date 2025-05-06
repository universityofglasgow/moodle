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

// Constant definitions of all the admin grades.
// Note that value is what is used to identify grade in settings, database and so on.
// So don't change them if you add one or take one away.
define('AG_GOODCAUSE_FO', 1);
define('AG_GOODCAUSE_NR', 2);
define('AG_NOSUBMISSION', 3);
define('AG_NOSUBMISSION_0', 4);
define('AG_DEFERRED', 5);
define('AG_GOODCAUSECREDITWITHHELD', 6);
define('AG_CREDITWITHHELD', 7);
define('AG_UNSATISFACTORY', 8);
define('AG_SATISFACTORY', 9);
define('AG_NOTPASSED', 10);
define('AG_PASSED', 11);
define('AG_NOTCOMPLETE', 12);
define('AG_COMPLETE', 13);
define('AG_CREDITREFUSED', 14);
define('AG_CREDITAWARDED', 15);
define('AG_AUDITONLY', 16);

require_once($CFG->dirroot . '/grade/lib.php');

/**
 * Handles admin grades in one place
 */
class admingrades {

    /**
     * Default definitions of admin grades and where they may be used.
     * levels means....
     * 0 = gradeitems (all levels)
     * 1 = level 1 (totals)
     * 2 = l2+ only
     * [] = inactive admingrade
     * @return array
     */
    private static function defaults() {
        return [
            AG_GOODCAUSE_FO => [
                'name' => 'GOODCAUSE_FO',
                'default' => [
                    'code' => 'MV',
                    'description' => get_string('adminmv', 'local_gugrades'),
                ],
                'levels' => [0, 1],
            ],
            AG_GOODCAUSE_NR => [
                'name' => 'GOODCAUSE_NR',
                'default' => [
                    'code' => 'MV0',
                    'description' => get_string('adminmv0', 'local_gugrades'),
                ],
                'levels' => [0],
            ],
            AG_NOSUBMISSION => [
                'name' => 'NOSUBMISSION',
                'default' => [
                    'code' => 'NS',
                    'description' => get_string('adminns', 'local_gugrades'),
                ],
                'levels' => [0],
            ],
            AG_NOSUBMISSION_0 => [
                'name' => 'NOSUBMISSION_0',
                'default' => [
                    'code' => 'NS',
                    'description' => get_string('adminns0', 'local_gugrades'),
                ],
                'levels' => [2],
            ],
            AG_DEFERRED => [
                'name' => 'DEFERRED',
                'default' => [
                    'code' => '07',
                    'description' => get_string('admin07', 'local_gugrades'),
                ],
                'levels' => [0],
            ],
            AG_GOODCAUSECREDITWITHHELD => [
                'name' => 'GOODCAUSECREDITWITHHELD',
                'default' => [
                    'code' => 'GCW',
                    'description' => get_string('admingcw', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            AG_CREDITWITHHELD => [
                'name' => 'CREDITWITHHELD',
                'default' => [
                    'code' => 'CW',
                    'description' => get_string('admincw', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            AG_UNSATISFACTORY => [
                'name' => 'UNSATISFACTORY',
                'default' => [
                    'code' => 'UNS',
                    'description' => get_string('adminuns', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            AG_SATISFACTORY => [
                'name' => 'SATISFACTORY',
                'default' => [
                    'code' => 'UNS',
                    'description' => get_string('adminsat', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            AG_NOTPASSED => [
                'name' => 'NOTPASSED',
                'default' => [
                    'code' => 'NP',
                    'description' => get_string('adminnp', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            AG_PASSED => [
                'name' => 'PASSED',
                'default' => [
                    'code' => 'P',
                    'description' => get_string('adminp', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            AG_NOTCOMPLETE => [
                'name' => 'NOTCOMPLETE',
                'default' => [
                    'code' => 'NC',
                    'description' => get_string('adminnc', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            AG_COMPLETE => [
                'name' => 'COMPLETE',
                'default' => [
                    'code' => 'CP',
                    'description' => get_string('admincp', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            AG_CREDITREFUSED => [
                'name' => 'CREDITREFUSED',
                'default' => [
                    'code' => 'CR',
                    'description' => get_string('admincr', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            AG_CREDITAWARDED => [
                'name' => 'CREDITAWARDED',
                'default' => [
                    'code' => 'CA',
                    'description' => get_string('adminca', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            AG_AUDITONLY => [
                'name' => 'AUDITONLY',
                'default' => [
                    'code' => 'AU',
                    'description' => get_string('adminau', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
        ];
    }

    /**
     * Get the data for settings page
     * @return array
     */
    public static function get_settings_data() {

        return self::defaults();
    }

    /**
     * Define the different types of grade
     * for level 1 cat total grades
     * @param int $level
     */
    private static function define(int $level) {
        $admingrades = [
            'MV' => get_string('adminmv', 'local_gugrades'),
            'MV0' => get_string('adminmv0', 'local_gugrades'),
            'NS' => get_string('adminns', 'local_gugrades'),
            'NS0' => get_string('adminns0', 'local_gugrades'),
            '07' => get_string('admin07', 'local_gugrades'),
        ];

        foreach ($admingrades as $code => $admingrade) {
            $admingrades[$code] = "$code - $admingrade";
        }

        // NS0 is not available at Level 1
        if ($level == 1) {
            unset($admingrades['NS0']);
        }

        return $admingrades;
    }

    /**
     * Define level 1 total grades
     */
    private static function define_level_one() {
        $admingrades = [
            'GCW' => get_string('admingcw', 'local_gugrades'),
            '07' => get_string('admin07', 'local_gugrades'),
            'MV' => get_string('adminmv', 'local_gugrades'),
            'CW' => get_string('admincw', 'local_gugrades'),
            'UNS' => get_string('adminuns', 'local_gugrades'),
            'SAT' => get_string('adminsat', 'local_gugrades'),
            'NP' => get_string('adminnp', 'local_gugrades'),
            'P' => get_string('adminp', 'local_gugrades'),
            'NC' => get_string('adminnc', 'local_gugrades'),
            'CP' => get_string('admincp', 'local_gugrades'),
            'CR' => get_string('admincr', 'local_gugrades'),
            'CA' => get_string('adminca', 'local_gugrades'),
            'AU' => get_string('adminau', 'local_gugrades'),
        ];

        foreach ($admingrades as $code => $admingrade) {
            $admingrades[$code] = "$code - $admingrade";
        }

        return $admingrades;
    }

    /**
     * Get description
     * @param string $admincode
     * @return string
     */
    public static function get_description(string $admincode) {
        $admincodes = self::define();
        return $admincodes[$admincode] ?? '[[' . $admincode . ']]';
    }

    /**
     * Get admincodes for non level 1 total menu
     * @param int $gradeitemid
     * @return array
     */
    public static function get_menu(int $gradeitemid) {
        $level = \local_gugrades\grades::get_gradeitem_level($gradeitemid);
        $gradetypes = self::define($level);

        return $gradetypes;
    }

    /**
     * Get admincodes for level 1 total menu
     * @return array
     */
    public static function get_menu_level_one() {
        $gradetypes = self::define_level_one();

        return $gradetypes;
    }

}
