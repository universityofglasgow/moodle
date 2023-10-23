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
 * @package    local_guldap
 * @copyright  2022 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/user/profile/definelib.php');

function xmldb_local_guldap_install() {
    global $CFG, $DB;

    // Category for UofG field(s)
    if (!$category = $DB->get_record('user_info_category', ['name' => 'UofG'])) {
        $category = new \stdClass;
        $category->name = 'UofG';
        profile_save_category($category);
    }

    // Check/create profile field for homeemailaddress
    if (!$field = $DB->get_record('user_info_field', ['shortname' => 'homeemailaddress', 'categoryid' => $category->id])) {
        $field = new \stdClass;
        $field->shortname = 'homeemailaddress';
        $field->name = 'HomeEmailAddress';
        $field->datatype = 'text';
        $field->description['text'] = 'Home email address';
        $field->description['format'] = 1;
        $field->categoryid = $category->id;
        $field->sortorder = 1;
        $field->required = 0;
        $field->locked = 1;
        $field->visible = 3;
        $field->forceunique = 0;
        $field->signup = 0;
        $field->defaultdata = 0;
        $field->defaultdataformat = 0;
        profile_save_field($field, []);
    }
}