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
 * Export strings..
 *
 * @package    core
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/csvlib.class.php');


$usage = "Import the language strings from a CSV.

Usage:
    # php import_string.php --file=/tmp/strings.csv

Options:
    -f --file                   The CSV file to import from.
    -h --help                   Print this help.

Examples:

    # php import_strings.php --file=/tmp/all_strings.csv
        Import the language strings from the CSV file.
";

list($options, $unrecognised) = cli_get_params([
    'file' => null,
    'help' => false,
], [
    'f' => 'file',
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

$file = !empty($options['file']) ? $options['file'] : null;
if (!is_file($file) || !is_readable($file)) {
    cli_error('Import file not found, or not readable.');
    exit(3);
}

$importid = csv_import_reader::get_new_iid('local_xp_str_import');
$importer = new csv_import_reader($importid, 'local_xp_str_import');
$importer->load_csv_content(file_get_contents($file), 'utf-8', 'comma');
$columns = $importer->get_columns();

$otherlangs = array_map(function($header) {
    // The first part of the header should be the raw language string.
    return explode(' ', $header)[0];
}, array_slice($columns, 2, null, true));
$otherstrings = array_reduce($otherlangs, function($carry, $lang) {
    $carry[$lang] = [];
    return $carry;
});

$importer->init();
while ($row = $importer->next()) {
    $identifier = $row[0];
    foreach ($row as $i => $str) {
        if ($i < 2 || empty($str)) {
            continue;
        }
        $lang = $otherlangs[$i];
        $otherstrings[$lang][$identifier] = $str;
    }
}

foreach ($otherlangs as $lang) {
    list($langdir, $file) = local_xp_cli_get_language_paths($lang);
    if (empty($otherstrings[$lang])) {
        continue;
    }

    if (!is_dir($langdir)) {
        mkdir($langdir);
    }

    mtrace('Creating ' . $lang . ' file...');
    $fp = fopen($file, 'w');
    if (!$fp) {
        mtrace('Could not open the file for writing:');
        mtrace('  ' . $file);
        continue;
    }
    fputs($fp, trim(local_xp_cli_get_lang_header()));
    fputs($fp, "\n");
    fputs($fp, "\n");
    foreach ($otherstrings[$lang] as $key => $value) {
        fputs($fp, '$string[\'' . $key . '\'] = ' . var_export($value, true) . ";\n");
    }

    $langstrs = local_xp_cli_load_strings($lang);
    if ($langstrs !== $otherstrings[$lang]) {
        mtrace('Whoops, the exported strings do not match the new file content.');
    }
}

function local_xp_cli_get_lang_header() {
    return <<<HEADER
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
 * Language file.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
HEADER;
}
