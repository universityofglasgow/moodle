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
 * Manage template
 * @package local_template
 * @copyright  2023 David Aylmer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

// Moodle codechecker incorrectly asserts require must use parenthesis.
// @codingStandardsIgnoreLine
require '../../../config.php';

use local_template\utils;

global $CFG, $PAGE, $OUTPUT;

// Moodle codechecker incorrectly asserts require_once must use parenthesis.
// @codingStandardsIgnoreLine
require_once $CFG->libdir . '/adminlib.php';

// Moodle codechecker incorrectly asserts require_once must use parenthesis.
// @codingStandardsIgnoreLine
require_once $CFG->dirroot . '/local/template/lib.php';

utils::enforce_security(true);

$PAGE->navbar->add(get_string('template', 'local_template'), new moodle_url('/local/template/index.php'));
$PAGE->navbar->add(get_string('templateadmin', 'local_template'), new moodle_url('/local/template/admin/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/template/admin/index.php'));
$PAGE->set_title(get_string('templateadmin', 'local_template'));

echo $OUTPUT->header();
echo utils::admin();
echo $OUTPUT->footer();
