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
 * Course list block settings
 *
 * @package    block_course_list
 * @copyright  2007 Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
global $DB;

$sql_ltitypes = "SELECT lt.id, lt.name, lt.course, c.fullname FROM {lti_types} lt, {course} c WHERE lt.course=c.id";
$arr_ltitypes = $DB->get_records_sql($sql_ltitypes);
/*
echo "<pre>";
print_r($arr_ltitypes);
echo "</pre>";
*/

$settings->add(new admin_setting_heading('includeltilabel',
    get_string('includeltilabel', 'block_newgu_spdetails'), ''));


    foreach ($arr_ltitypes as $key_ltitypes) {
    $settings->add(new admin_setting_configcheckbox('block_newgu_spdetails_include_' . $key_ltitypes->id, $key_ltitypes->name,
                       '', 0));
    }
}
