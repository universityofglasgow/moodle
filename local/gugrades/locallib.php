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
 * Admin settings for local_gugrades
 *
 * @package    local_gugrades
 * @copyright  2023
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Update scale settings in DB
 * This is called whenever the plugin settings are updated
 * It syncs the settings to the database.
 * @param string $name
 */
function scale_setting_updated($name) {
    global $DB;

    $config = get_config('local_gugrades');

    $scales = $DB->get_records('scale', ['courseid' => 0]);
    foreach ($scales as $scale) {
        $name = "scalevalue_" . $scale->id;
        $value = get_config('local_gugrades', $name);
        $typename = "scaletype_" . $scale->id;
        $type = get_config('local_gugrades', $typename);
        if (!$value || !$type) {
            $DB->delete_records('local_gugrades_scalevalue', ['scaleid' => $scale->id]);
            $DB->delete_records('local_gugrades_scaletype', ['scaleid' => $scale->id]);
            continue;
        }
        $lines = explode(PHP_EOL, $value);
        foreach ($lines as $rawline) {
            $line = trim($rawline);
            if (!$line) {
                continue;
            }
            list($item, $value) = explode(',', $line);
            $item = trim($item);
            $value = trim($value);
            $sql = 'SELECT * FROM {local_gugrades_scalevalue}
                WHERE scaleid=:scaleid
                AND ' . $DB->sql_compare_text('item') . '=:item';
            if ($record = $DB->get_record_sql($sql, ['item' => $item, 'scaleid' => $scale->id])) {
                $record->value = $value;
                $DB->update_record('local_gugrades_scalevalue', $record);
            } else {
                $record = new stdClass;
                $record->scaleid = $scale->id;
                $record->item = $item;
                $record->value = $value;
                $DB->insert_record('local_gugrades_scalevalue', $record);
            }
        }

        // Add type record.
        if ($scaletype = $DB->get_record('local_gugrades_scaletype', ['scaleid' => $scale->id])) {
            $scaletype->type = $type;
            $DB->update_record('local_gugrades_scaletype', $scaletype);
        } else {
            $scaletype = new stdClass();
            $scaletype->scaleid = $scale->id;
            $scaletype->type = $type;
            $DB->insert_record('local_gugrades_scaletype', ['scaleid' => $scale->id]);
        }
    }
}

/**
 * Check for MyGrades custom course category and field
 */
function custom_course_field() {
    global $DB;

    // Check if the category exists
    if (!$category = $DB->get_record('customfield_category', ['name' => 'Student MyGrades'])) {
        $category = new stdClass;
        $category->name = 'Student MyGrades';
        $category->descriptionformat = 0;
        $category->sortorder = 0;
        $category->component = 'core_course';
        $category->area = 'course';
        $category->itemid = 0;
        $category->contextid = 1;
        $category->timecreated = time();
        $category->timemodified = time();
        $categoryid = $DB->insert_record('customfield_category', $category);
    } else {
        $categoryid = $category->id;
    }

    // Check if the customfield exists in some other category
    $allfields = $DB->get_records('customfield_field', ['shortname' => 'studentmygrades']);
    foreach ($allfields as $allfield) {
        if ($allfield->categoryid != $categoryid) {
            $DB->delete_records('customfield_field', ['id' => $allfield->id]);
        }
    }

    // Check if the customfield exists
    if (!$field = $DB->get_record('customfield_field', ['shortname' => 'studentmygrades', 'categoryid' => $categoryid])) {
        $field = new stdClass;
        $field->shortname = 'studentmygrades';
        $field->name = 'Enable Student MyGrades';
        $field->type = 'checkbox';
        $field->description = 'Insert description here.';
        $field->descriptionformat = 1;
        $field->sortorder = 0;
        $field->categoryid = $categoryid;
        $field->configdata = json_encode((object) [
            'required' => '0',
            'uniquevalues' => '0',
            'checkbydefault' => '0',
            'locked' => '0',
            'visibility' => '2',
        ]);
        $field->timecreated = time();
        $field->timemodified = time();
        $DB->insert_record('customfield_field', $field);
    }
}
