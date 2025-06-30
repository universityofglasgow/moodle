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
     * 'grandtotal' = available in 'grand total' selection
     * 'items' = available in the small selection for all items / cats
     * 'level2' = available in small selection ONLY for L2 and below
     * @return array
     */
    private static function defaults() {
        return [
            'GOODCAUSE_FO' => [
                'default' => [
                    'code' => 'EC',
                    'description' => get_string('adminmv', 'local_gugrades'),
                ],
                'grandtotal' => true,
                'items' => true,
            ],
            'GOODCAUSE_NR' => [
                'default' => [
                    'code' => 'EC0',
                    'description' => get_string('adminmv0', 'local_gugrades'),
                ],
                'items' => true,
            ],
            'NOSUBMISSION' => [
                'default' => [
                    'code' => 'NS',
                    'description' => get_string('adminns', 'local_gugrades'),
                ],
                'items' => true,
            ],
            'NOSUBMISSION_0' => [
                'default' => [
                    'code' => 'NS0',
                    'description' => get_string('adminns0', 'local_gugrades'),
                ],
                'items' => true,
                'level2' => true,
            ],
            'DEFERRED' => [
                'default' => [
                    'code' => 'DFR',
                    'description' => get_string('admin07', 'local_gugrades'),
                ],
                'grandtotal' => true,
                'items' => true,
            ],
            'GOODCAUSECREDITWITHHELD' => [
                'default' => [
                    'code' => 'ECW',
                    'description' => get_string('admingcw', 'local_gugrades'),
                ],
                'grandtotal' => true,
            ],
            'CREDITWITHHELD' => [
                'default' => [
                    'code' => 'CW',
                    'description' => get_string('admincw', 'local_gugrades'),
                ],
                'grandtotal' => true,
            ],
            'UNSATISFACTORY' => [
                'name' => 'UNSATISFACTORY',
                'default' => [
                    'code' => 'UNS',
                    'description' => get_string('adminuns', 'local_gugrades'),
                ],
                'grandtotal' => true,
            ],
            'SATISFACTORY' => [
                'default' => [
                    'code' => 'SAT',
                    'description' => get_string('adminsat', 'local_gugrades'),
                ],
                'grandtotal' => true,
            ],
            'NOTPASSED' => [
                'default' => [
                    'code' => 'NP',
                    'description' => get_string('adminnp', 'local_gugrades'),
                ],
                'grandtotal' => true,
            ],
            'PASSED' => [
                'default' => [
                    'code' => 'P',
                    'description' => get_string('adminp', 'local_gugrades'),
                ],
                'grandtotal' => true,
                'levels' => [1],
            ],
            'NOTCOMPLETE' => [
                'default' => [
                    'code' => 'NC',
                    'description' => get_string('adminnc', 'local_gugrades'),
                ],
                'grandtotal' => true,
            ],
            'COMPLETE' => [
                'default' => [
                    'code' => 'CP',
                    'description' => get_string('admincp', 'local_gugrades'),
                ],
                'grandtotal' => true,
            ],
            'CREDITREFUSED' => [
                'default' => [
                    'code' => 'CR',
                    'description' => get_string('admincr', 'local_gugrades'),
                ],
                'grandtotal' => true,
            ],
            'CREDITAWARDED' => [
                'default' => [
                    'code' => 'CA',
                    'description' => get_string('adminca', 'local_gugrades'),
                ],
                'grandtotal' => true,
            ],
            'AUDITONLY' => [
                'default' => [
                    'code' => 'AU',
                    'description' => get_string('adminau', 'local_gugrades'),
                ],
                'grandtotal' => true,
            ],
            'INTERRUPTIONOFSTUDIES' => [
                'default' => [
                    'code' => 'IS',
                    'description' => get_string('adminis', 'local_gugrades'),
                ],
                'grandtotal' => true,
                'items' => true,
            ]
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
     * Set empty settings to defaults
     */
    public static function setting_defaults() {
        $defaults = self::defaults();
        foreach ($defaults as $name => $default) {
            $tag = self::get_setting_tag($name);
            $setting = get_config('local_gugrades', $tag);
            if (empty($setting) || empty(json_decode($setting)->code)) {
                set_config($tag, json_encode($default['default']), 'local_gugrades');
            }
        }
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
     * Updates *all* the instances of admingrades in the grade table
     * when an admingrade setting is changed.
     * @param string $name
     */
    public static function update_displaynames(string $name) {
        global $DB;

        [$displaygrade, ] = self::get_displaygrade_from_name($name);
        $sql = 'UPDATE {local_gugrades_grade} SET displaygrade = :displaygrade WHERE admingrade = :name';
        $DB->execute($sql, [
            'displaygrade' => $displaygrade,
            'name' => $name,
        ]);
    }

    /**
     * Check the 'level' flags in the admingrades default array
     * @param array $default
     * @param string $key
     * @return bool
     */
    private static function flag_set($default, $key) {
        if (!array_key_exists($key, $default)) {
            return false;
        }

        return $default[$key];
    }

    /**
     * Get grades for supplied
     * Level =
     * @param int $level
     * @param bool $grandtotal
     * @return array
     */
    public static function get_admingrades_for_level(int $level, bool $grandtotal = false) {

        $defaults = self::defaults();

        $admingrades = [];
        foreach ($defaults as $name => $default) {

            // Work out if this is ok for this level / grandtotal
            $send = false;
            if ($grandtotal && self::flag_set($default, 'grandtotal')) {
                $send = true;
            }
            if (!$grandtotal && self::flag_set($default, 'items')) {
                $send = true;
            }
            if (!$grandtotal && ($level == 1) && self::flag_set($default, 'level2')) {
                $send = false;
            }

            if ($send) {
                [$displaygrade, $description] = self::get_displaygrade_from_name($name);
                $admingrades[$name] = "$displaygrade - $description";
            }
        }

        return $admingrades;
    }

    /**
     * Get admincodes for non level 1 total menu
     * @param int $gradeitemid
     * @return array
     */
    public static function get_menu(int $gradeitemid) {
        $level = \local_gugrades\grades::get_gradeitem_level($gradeitemid);
        $admingrades = self::get_admingrades_for_level($level, false);

        return $admingrades;
    }

    /**
     * Get admincodes for level 1 total menu
     * @return array
     */
    public static function get_menu_level_one() {
        $admingrades = self::get_admingrades_for_level(1, true);

        return $admingrades;
    }

}
