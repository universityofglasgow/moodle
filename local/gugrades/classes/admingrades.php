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
            'GOODCAUSE_FO' => [
                'default' => [
                    'code' => 'MV',
                    'description' => get_string('adminmv', 'local_gugrades'),
                ],
                'levels' => [0, 1],
            ],
            'GOODCAUSE_NR' => [
                'default' => [
                    'code' => 'MV0',
                    'description' => get_string('adminmv0', 'local_gugrades'),
                ],
                'levels' => [0],
            ],
            'NOSUBMISSION' => [
                'default' => [
                    'code' => 'NS',
                    'description' => get_string('adminns', 'local_gugrades'),
                ],
                'levels' => [0],
            ],
            'NOSUBMISSION_0' => [
                'default' => [
                    'code' => 'NS0',
                    'description' => get_string('adminns0', 'local_gugrades'),
                ],
                'levels' => [2],
            ],
            'DEFERRED' => [
                'default' => [
                    'code' => '07',
                    'description' => get_string('admin07', 'local_gugrades'),
                ],
                'levels' => [0],
            ],
            'GOODCAUSECREDITWITHHELD' => [
                'default' => [
                    'code' => 'GCW',
                    'description' => get_string('admingcw', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            'CREDITWITHHELD' => [
                'default' => [
                    'code' => 'CW',
                    'description' => get_string('admincw', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            'UNSATISFACTORY' => [
                'name' => 'UNSATISFACTORY',
                'default' => [
                    'code' => 'UNS',
                    'description' => get_string('adminuns', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            'SATISFACTORY' => [
                'default' => [
                    'code' => 'UNS',
                    'description' => get_string('adminsat', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            'NOTPASSED' => [
                'default' => [
                    'code' => 'NP',
                    'description' => get_string('adminnp', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            'PASSED' => [
                'default' => [
                    'code' => 'P',
                    'description' => get_string('adminp', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            'NOTCOMPLETE' => [
                'default' => [
                    'code' => 'NC',
                    'description' => get_string('adminnc', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            'COMPLETE' => [
                'default' => [
                    'code' => 'CP',
                    'description' => get_string('admincp', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            'CREDITREFUSED' => [
                'default' => [
                    'code' => 'CR',
                    'description' => get_string('admincr', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            'CREDITAWARDED' => [
                'default' => [
                    'code' => 'CA',
                    'description' => get_string('adminca', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
            'AUDITONLY' => [
                'default' => [
                    'code' => 'AU',
                    'description' => get_string('adminau', 'local_gugrades'),
                ],
                'levels' => [1],
            ],
        ];
    }

    /**
     * Get map from old to new database entry codes 
     * used (once) in db/upgrade.php
     * @return array
     */
    public static function get_upgrade_map() {
        $defaults = self::defaults();
        $maps = [];
        foreach ($defaults as $name => $default) {
            $maps[$default['default']['code']] = $name;
        }

        return $maps;
    }

    /**
     * Get the data for settings page
     * @return array
     */
    public static function get_settings_data() {

        return self::defaults();
    }

    /**
     * Get the settings tag for admin grade
     * @param string $admingrade
     * @return string
     */
    public static function get_setting_tag(string $admingrade) {
        return 'admingrade_' . strtolower($admingrade);
    }

    /**
     * Check that admingrade (name) is valid
     * @param string $admingrade
     * @throws \moodle_exception
     */
    public static function validate_admingrade(string $admingrade) {
        $defaults = self::defaults();
        if (!array_key_exists($admingrade, $defaults)) {
            throw new \moodle_exception('Attempt to write invalid admin grade - "' . $admingrade . '"'); 
        }

        return $defaults[$admingrade];
    }

    /**
     * Get displaygrade and description from name
     * @param string $name
     * @return array
     */
    public static function get_displaygrade_from_name($admingrade) {
        $default = self::validate_admingrade($admingrade);

        // Admingrade details from settings
        $tag = self::get_setting_tag($admingrade);
        $setting = get_config('local_gugrades', $tag);
        if (!$setting) {
            throw new \moodle_exception('Setting not found for tag "' . $tag . '"');
        }
        $admin = json_decode($setting);

        return [$admin->code, $admin->description];
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
