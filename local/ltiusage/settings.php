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
 * TODO describe file
 *
 * @package    local_ltiusage
 * @copyright  2025 Michael Clark <michael.d.clark@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_ltiusage',
        get_string('pluginname', 'local_ltiusage'),
        new moodle_url('/local/ltiusage/index.php'),
        'local/ltiusage:manage'
    ));
}

// Add to navigation menu for users with view permission.
$nav = $PAGE->navigation;
$ltiusagenode = $nav->add(
    get_string('pluginname', 'local_ltiusage'),
    new moodle_url('/local/ltiusage/index.php'),
    navigation_node::TYPE_CUSTOM,
    null,
    'local_ltiusage',
    new pix_icon('t/viewdetails', get_string('pluginname', 'local_ltiusage'))
);
$ltiusagenode->showinflatnavigation = true;
