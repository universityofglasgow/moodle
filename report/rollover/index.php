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
 * @package    report_rollover
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/rollover/classes/locallib.php');

require_login();

// Renderer.
$context = context_system::instance();
$PAGE->set_context($context);
//$output = $PAGE->get_renderer('report_rollover');

// Start the page.
admin_externalpage_setup('reportrollover', '', null, '', array('pagelayout' => 'report'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pluginname', 'report_rollover'));

// Get config from rollover plugin
$config = get_config('local_rollover');

// get counts for the various statuses
$sql = "SELECT state, count(*) AS countval FROM {local_rollover} GROUP BY state WHERE session = :session";
$counts = $DB->get_records_sql($sql, ['session' => $config->session]);
$values = [
    ROLLOVER_COURSE_WAITING => 0,
    ROLLOVER_COURSE_BACKUP => 0,
    ROLLOVER_COURSE_RESTORE => 0,
];
foreach ($counts as $count) {
    $values[$count->state] = $count->countval;
}

$chart = new \core\chart_pie();
$chart->set_doughnut(true);
$series = new \core\chart_series('Counts', [
    $values[ROLLOVER_COURSE_WAITING],
    $values[ROLLOVER_COURSE_BACKUP],
    $values[ROLLOVER_COURSE_RESTORE],
]);
$chart->add_series($series);
$chart->set_labels([
    'ROLLOVER_COURSE_WAITING',
    'ROLLOVER_COURSE_BACKUP',
    'ROLLOVER_COURSE_RESTORE',
]);

echo $OUTPUT->render($chart);


echo $OUTPUT->footer();

