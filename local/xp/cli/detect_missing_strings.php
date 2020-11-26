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
 * Detect missing language strings.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/clilib.php');


$usage = "Displays the missing language string keys.

Usage:
    # php detect_missing_strings.php --lang=fr

Options:
    -h --help                   Print this help.
    --lang=<lang>               Name of the language string to compare English with.

Examples:

    # php detect_missing_strings.php --lang=fr
        Prints a list of the missing language string in the selected language.
";

list($options, $unrecognised) = cli_get_params([
    'help' => false,
    'lang' => null,
], [
    'h' => 'help'
]);

if ($unrecognised) {
    $unrecognised = implode(PHP_EOL.'  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if ($options['help']) {
    cli_writeln($usage);
    exit(2);
}

$lang = !empty($options['lang']) ? clean_param($options['lang'], PARAM_SAFEDIR) : null;
if (!local_xp_cli_has_language($lang)) {
    cli_writeln($usage);
    exit(3);
}

$enstrings = local_xp_cli_load_strings('en');
$otherstrings = local_xp_cli_load_strings($lang);

$diff = array_diff_key($enstrings, $otherstrings);
if (empty($diff)) {
    mtrace('All good, all strings have a translations.');
    exit(0);
}

mtrace(sprintf('The following %d key(s) are missing:', count($diff)));
mtrace('');
foreach (array_keys($diff) as $key) {
    mtrace(sprintf("\$string['$key'] = '%s';", str_replace("'", '\\', $enstrings[$key])));
}
