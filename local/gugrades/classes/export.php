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
 * Deal with export stuff
 *
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades;

/**
 * Static class implementation to deal with audit trail
 */
class export {


    /**
     * Instantiate export plugin
     * @param string $pluginname
     * @return object
     */
    public static function get_export_plugin(string $pluginname) {

        // Cannot use base class.
        if ($pluginname == 'base') {
            throw new \moodle_exception('Cannot load export base class');
        }

        $classname = 'local_gugrades\\export\\' . $pluginname;
        if (!class_exists($classname, true)) {
            throw new \moodle_exception('Export plugin does not exist - "' . $pluginname . '"');
        }

        return new $classname;
    }

    /**
     * Get list of aggregation export plugins
     * @param int $courseid
     * @param int $gradecategoryid
     * @return array
     */
    public static function get_aggregation_export_plugins(int $courseid, int $gradecategoryid) {
        global $CFG;

        // Get all the php files in export directory
        $dirpath = $CFG->dirroot . '/local/gugrades/classes/export/';
        $paths = glob($dirpath . '*.php');

        // Process class names and description from within class.
        $plugins = [];
        foreach ($paths as $path) {
            $parts = explode('/', $path);
            if ($file = end($parts)) {
                $nameparts = explode('.', $file);
                if ($name = reset($nameparts)) {

                    // 'base' is not for use
                    if ($name == 'base') {
                        continue;
                    }

                    // Instantiate class
                    $classname = 'local_gugrades\\export\\' . $name;
                    $export = new $classname;
                    $description = $export->get_name();

                    $plugins[] = [
                        'name' => $name,
                        'description' => $description,
                    ];
                }
            }
        }

        return $plugins;
    }

    /**
     * Get aggregation export form
     * @param int $courseid
     * @param int $gradecategoryid
     * @param string $pluginname
     * @return array
     */
    public static function get_aggregation_export_form(int $courseid, int $gradecategoryid, string $pluginname) {

        $plugin = self::get_export_plugin($pluginname);

        // Does the plugin define a form at all?
        $hasform = $plugin->defines_optional_fields();

        $form = [];
        if ($hasform) {
            $form = $plugin->get_form_fields($courseid, $gradecategoryid);
        }

        return [
            'hasform' => $hasform,
            'form' => $form,
        ];
    }

}
