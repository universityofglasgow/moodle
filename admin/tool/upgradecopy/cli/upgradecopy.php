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
 * Create copy commands for migraiting optional plugins
 *
 * @package    tool_upgradecpy
 * @copyright  Howard Miller 2003
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/adminlib.php');

$help =
    "Generate copy commands to copy optional plugins to new Moodle.

Options:
--from=STRING         Copy from prefix.
--to=STRING           Copy to prefix.
--command=STRING      Copy command (default 'cp -R')
-h, --help            Print out this help.

Example:
\$ sudo -u www-data /usr/bin/php admin/tool/upgradecopy/cli/upgradecopy.php --from=//home//fred//moodle42 --to=//home//fred/moodle43
";

list($options, $unrecognized) = cli_get_params(
    [
        'from'  => null,
        'to' => null,
        'command' => 'cp -R',
        'help' => false,
    ],
    [
        'h' => 'help',
    ]
);

if ($options['help'] || $options['from'] === null || $options['to'] === null) {
    echo $help;
    exit(0);
}

$paths = \tool_upgradecopy\process::get_paths();
$fromprefix = $options['from'];
$toprefix = $options['to'];
$command = $options['command'];
echo "\n";
foreach ($paths as $path) {
    echo "$command $fromprefix$path->from $toprefix$path->to\n";
}

cli_heading(get_string('success'));
exit(0);
