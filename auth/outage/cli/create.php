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
 * CLI for creating outages.
 *
 * @package    auth_outage
 * @author     Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright  2016 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_outage\local\cli\cli_exception;
use auth_outage\local\cli\create;
use auth_outage\local\outagelib;

define('CLI_SCRIPT', true);
require_once(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

try {
    $cli = new create();
    $config = outagelib::get_config();
    $cli->set_defaults([
        'help' => false,
        'warn' => (int)($config->default_warning_duration),
        'start' => null,
        'duration' => (int)($config->default_duration),
        'title' => $config->default_title,
        'description' => $config->default_description,
    ]);
    $cli->execute();
} catch (cli_exception $e) {
    cli_error($e->getMessage());
}
