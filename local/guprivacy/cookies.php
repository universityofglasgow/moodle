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
 * TODO describe file cookies
 *
 * @package    local_guprivacy
 * @copyright  2025 Michael Clark <michael.d.clark@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require('../../config.php');

 require_login();
 
 $url = new moodle_url('/local/guprivacy/cookies.php', []);
 $PAGE->set_url($url);
 $PAGE->set_context(context_system::instance());
 
 $PAGE->set_heading($SITE->fullname);
 $PAGE->set_pagelayout('standard');
 $PAGE->set_title(get_string('pluginname','local_guprivacy'));
 $PAGE->set_heading(get_string('cstatement', 'local_guprivacy'));
 
 $sitecontent = get_config('local_guprivacy', 'ccontent');
 
 echo $OUTPUT->header();
 
 echo $sitecontent;
 echo '<button class="btn btn-primary" onclick="gaOptin()">Opt-In to third party cookies</button>&nbsp;<button class="btn btn-primary" onclick="gaOptout()">Opt-out of third party cookies.</button>';
 
 echo $OUTPUT->footer();
