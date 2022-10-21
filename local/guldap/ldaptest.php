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
 * Admin settings and defaults.
 *
 * @package local_guldap
 * @copyright  2022 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

// Security
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Page setup
$url = new moodle_url('/local/guldap/ldaptest.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_guldap'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_guldap'));

echo "<pre><ul>";

ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);

// Connect to LDAP
$ldap = new \local_guldap\ldap();
if ($resource = $ldap->connect()) {
    echo '<li>' . get_string('connected', 'local_guldap') . '</li>';
} else {
    echo "</li></pre>";
    echo $OUTPUT->footer();
    die;
}

// Search
$results = $ldap->search($resource, 'sn=smith');
echo '<li>' . get_string('numberofresults', 'local_guldap', count($results)) . '</li>';

// Close connection
$ldap->close();
echo '<li>' . get_string('closed', 'local_guldap') . '</li>';

echo "</ul></pre>";

echo $OUTPUT->footer();

