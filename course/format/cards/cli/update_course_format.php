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
 * CLI script to update the format for multiple courses to format_cards
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');

global $CFG, $DB;

require_once("$CFG->libdir/clilib.php");
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/course/format/cards/lib.php");

list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'course' => '',
        'course-format' => '',
    ], [
        'h' => 'help',
    ]
);

if ($options["help"]) {
    echo <<<help
Converts courses (either a set or all courses) to use format_cards
If the source course uses format_grid, grid images are automatically copied across

Options:
 -h, --help             Show this help text
     --course           Comma separated list of course ID's to convert
     --course-format    Only convert courses in this format

Example:
sudo -u www-data /usr/bin/php update_course_format.php --course=1,4,32
sudo -u www-data /usr/bin/php update_course_format.php --course-format=grid
help;

    exit(0);

}

$courses = [];
$select = [
    'format != :cards_format',
    'id != :site_id',
];
$params = [
    'cards_format' => 'cards',
    'site_id' => SITEID,
];

if ($options['course']) {
    $courseids = explode(',', $options['course']);

    list($sql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
    $select[] = $sql;
    $params = array_merge($params, $inparams);
}

if ($options['course-format']) {
    $select[] = 'format = :course_format';
    $params['course_format'] = $options['course-format'];
}

$courses = $DB->get_records_select('course', implode(' AND ', $select), $params);

$progress = new progress_bar();
$progress->create();
$i = 0;
$count = count($courses);

foreach ($courses as $course) {
    $course->format = 'cards';
    $course->section0 = FORMAT_CARDS_USEDEFAULT;
    $course->cardorientation = FORMAT_CARDS_USEDEFAULT;
    $course->importgridimages = true;

    try {
        update_course($course);
        $progress->update(++$i, $count, "Updated format for $course->shortname");
    } catch (moodle_exception $e) {
        mtrace($e->getMessage());
        mtrace($e->getTraceAsString());
        $progress->update(++$i, $count, "Failed to update format for $course->shortname");
    }
}
