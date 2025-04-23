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
     * Get filename
     * We need to get the code(s) for this course, discovered by enrol_gudatabase
     * There can be only 1. Failing that, we just use a default filename.
     * @param int $courseid
     * @return string
     */
    protected static function get_filename(int $courseid) {
        global $DB;

        $course = get_course($courseid);

        // Get the year from the course start date.
        $year = date('Y', $course->startdate);

        // Get any records for this course from gudatabase
        $codes = array_values($DB->get_records('enrol_gudatabase_codes', ['courseid' => $courseid]));

        // Create standard "MyCampus" format ONLY if there is a single code
        if (count($codes) == 1) {
            $code = $codes[0]->code;

            // Split the code into the alpha and numeric parts
            // e.g. BIOL1001 is BIOL and 1001
            preg_match('#([A-Z]+)(\d+)([A-Z]*)#', $code, $match);
            $subject = $match[1];
            $catnumber = $match[2];

            return $subject . '_' . $catnumber . '_' . $year;
        } else {

             // Make up some name
             return 'MyGrades_' . $course->shortname . '_' . $year;
        }
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

        // Get proposed filename
        $filename = self::get_filename($courseid);

        return [
            'plugins' => $plugins,
            'filename' => $filename,
        ];
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

    /**
     * Get aggregation export data
     * @param int $courseid
     * @param int $gradecategoryid
     * @param int $groupid
     * @param string $pluginname
     * @param array $form
     * @return array
     */
    public static function get_aggregation_export_data(int $courseid, int $gradecategoryid, int $groupid, string $pluginname, array $form) {

        $plugin = self::get_export_plugin($pluginname);

        $course = get_course($courseid);
        $filename = $course->shortname . '_' . date('Y-m-d_G:i:s');

        $data = $plugin->get_form_data($courseid, $gradecategoryid, $groupid, $form);

        return [
            'filename' => $filename,
            'csv' => $data,
        ];
    }

}
