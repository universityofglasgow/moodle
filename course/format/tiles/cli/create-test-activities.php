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
 * CLI script for course activity creation for development only.
 * Never to be run on production sites.
 * @package    format_tiles
 * @copyright  2023 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('CLI_SCRIPT', true);
require_once(__DIR__ . "/../../../../config.php");
require_once("$CFG->dirroot/course/modlib.php");
require_once("$CFG->dirroot/lib/testing/generator/component_generator_base.php");
require_once("$CFG->dirroot/lib/testing/generator/module_generator.php");
require_once("$CFG->dirroot/lib/testing/generator/data_generator.php");

if (!$CFG->debugdeveloper || !$CFG->debugdisplay || !($CFG->phpunit_dataroot ?? null)) {
    mtrace("This development script should never be run on production sites");
    die();
}

$courseid = getopt('c:')['c'] ?? null;

if (!$courseid) {
    mtrace("Must provide course ID e.g. -c 123");
    die();
}

$course = $DB->get_record('course', ['id' => $courseid]);
if (!$course) {
    mtrace("Course $courseid not found");
    die();
}
mtrace("Course $course->fullname found");

$sectionnumber = 1;
$section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $sectionnumber]);

$modules = $DB->get_records('modules');

$generator = new \testing_data_generator();

foreach ($modules as $module) {
    try {
        echo "\n=================\nTrying $module->name\n";
        $libpath = "$CFG->dirroot/mod/$module->name/tests/generator/lib.php";
        if (file_exists($libpath)) {
            require_once($libpath);
        }

        $classname = "mod_$module->name" . "_generator";
        if (class_exists($classname)) {
            $data = new stdClass();
            $data->section          = $sectionnumber;
            $data->visible          = 1;
            $data->course           = $course->id;
            $data->module           = $module->id;
            $data->modulename       = $module->name;
            $data->groupmode        = $course->groupmode;
            $data->groupingid       = $course->defaultgroupingid;
            $data->id               = '';
            $data->instance         = '';
            $data->coursemodule     = '';
            $data->downloadcontent  = DOWNLOAD_COURSE_CONTENT_ENABLED;
            $data->intro = '';
            $data->introformat = 1;

            $class = new $classname($generator);
            $class->create_instance($data);
        } else {
            echo "\nClass not found $classname";
        }
    } catch (Exception $e) {
        echo "\nCould not create $module->name";
    }
}
