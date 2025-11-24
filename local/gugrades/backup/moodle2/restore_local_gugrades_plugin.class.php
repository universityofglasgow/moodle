<?php
// This file is part of Moodle
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
 * Defines restore_local_gugrades class.
 *
 * @package     local_gugrades
 * @author      Howard Miller
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore plugin class.
 *
 * @package    local_gugrades
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_gugrades_plugin extends restore_local_plugin {

    /**
     * Return the paths to be handled by the plugin (course level)
     */
    protected function define_course_plugin_structure() {

        $paths = [];
        $coursepath = $this->get_pathfor('/');

        $paths[] = new restore_path_element('gugrades_config', $coursepath . '/gugrades_config');
        $paths[] = new restore_path_element('gugrades_map', $coursepath . '/gugrades_maps/gugrades_map');
        $paths[] = new restore_path_element('gugrades_resitcat', $coursepath . '/gugrades_resitcats/gugrades_resitcat');
        $paths[] = new restore_path_element('gugrades_map_value', $coursepath . '/gugrades_map_values/gugrades_map_value/gm_values/gm_value');
        $paths[] = new restore_path_element('gugrades_map_item', $coursepath . '/gugrades_map_items/gugrades_map_item');
        $paths[] = new restore_path_element('gugrades_column', $coursepath . '/gugrades_columns/gugrades_column');
        $paths[] = new restore_path_element('gugrades_grade', $coursepath . '/gugrades_grades/gugrades_grade');
        $paths[] = new restore_path_element('gugrades_weight', $coursepath . '/gugrades_weights/gugrades_weight');
        $paths[] = new restore_path_element('gugrades_audit', $coursepath . '/gugrades_audits/gugrades_audit');
        $paths[] = new restore_path_element('gugrades_hidden', $coursepath . '/gugrades_hiddens/gugrades_hidden');
        $paths[] = new restore_path_element('gugrades_resit', $coursepath . '/gugrades_resits/gugrades_resit');

        return $paths;
    }

    /**
     * Process config data
     */
    public function process_gugrades_config($data) {
        global $DB;

        $data = (object) $data;
        $data->courseid = $this->task->get_courseid();
        $data->gradeitemid = 0;

        $DB->insert_record('local_gugrades_config', $data);
    }

    /**
     * Process conversion map
     */
    public function process_gugrades_map($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->courseid = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newid = $DB->insert_record('local_gugrades_map', $data);
        $this->set_mapping('gugrades_map', $oldid, $newid);
    }

    /**
     * Process resit category
     */
    public function process_gugrades_resitcat($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->courseid = $this->task->get_courseid();
        $data->categoryid = 0 - $data->categoryid;
        $data->gradeitemid = 0 - $data->gradeitemid;

        $DB->insert_record('local_gugrades_resit', $data);
    }

    /**
     * Process conversion map items
     */
    public function process_gugrades_map_value($data) {
        global $DB;

        $data = (object) $data;
        $data->mapid = $this->get_mappingid('gugrades_map', $data->mapid);

        $DB->insert_record('local_gugrades_map_value', $data);
    }

    /**
     * Process map items
     */
    public function process_gugrades_map_item($data) {
        global $DB;

        $data = (object) $data;
        $data->courseid = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->mapid = $this->get_mappingid('gugrades_map', $data->mapid);

        $DB->insert_record('local_gugrades_map_item', $data);
    }

    /** 
     * Process columns
     */
    public function process_gugrades_column($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->courseid = $this->task->get_courseid();

        $newid = $DB->insert_record('local_gugrades_column', $data);
        $this->set_mapping('gugrades_column', $oldid, $newid);
    }

    /**
     * Process grades
     */
    public function process_gugrades_grade($data) {
        global $DB;

        $data = (object) $data;
        $data->courseid = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->columnid = $this->get_mappingid('gugrades_column', $data->columnid);
        $data->auditby = $this->get_mappingid('user', $data->auditby);

        $DB->insert_record('local_gugrades_grade', $data);
    }

    /**
     * Process altered weights
     */
    public function process_gugrades_weight($data) {
        global $DB;

        $data = (object) $data;
        $data->courseid = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        // Negative to prevent unique key errors. 
        $data->categoryid = 0 - $data->categoryid;
        $data->gradeitemid = 0 - $data->gradeitemid;

        $DB->insert_record('local_gugrades_altered_weight', $data);
    }

    /**
     * Process audit trail
     */
    public function process_gugrades_audit($data) {
        global $DB;

        $data = (object) $data;
        $data->courseid = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->relateduserid = $this->get_mappingid('user', $data->relateduserid);

        $DB->insert_record('local_gugrades_audit', $data);
    }

    /**
     * Process hidden items
     */
    public function process_gugrades_hidden($data) {
        global $DB;

        $data = (object) $data;
        $data->courseid = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->gradeitemid = 0 - $data->gradeitemid;

        $DB->insert_record('local_gugrades_hidden', $data);
    }

    /**
     * Process resits
     */
    public function process_gugrades_resit($data) {
        global $DB;

        $data = (object) $data;
        $data->courseid = $this->task->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('local_gugrades_hidden', $data);
    }

    /**
     * Update ids that were unknown when course.xml processed
     */
    protected function after_restore_course() {
        global $DB;

        // Map items.
        $items = $DB->get_recordset('local_gugrades_map_item', ['courseid' => $this->task->get_courseid()]);
        foreach ($items as $item) {
            $item->gradeitemid = $this->get_mappingid('grade_item', $item->gradeitemid);
            $item->gradecategoryid = $this->get_mappingid('grade_category', $item->gradecategoryid);
            $DB->update_record('local_gugrades_map_item', $item);
        }

        // Resit catageories.
        $resitcats = $DB->get_recordset('local_gugrades_resit', ['courseid' => $this->task->get_courseid()]);
        foreach ($resitcats as $resitcat) {
            $resitcat->gradeitemid = $this->get_mappingid('grade_item', $resitcat->gradeitemid);
            $resitcat->gradecategoryid = $this->get_mappingid('grade_category', 0 - $resitcat->gradecategoryid);
            $DB->update_record('local_gugrades_resit', $resitcat);
        }

        // Missing grade items in columns.
        $columns = $DB->get_recordset('local_gugrades_column', ['courseid' => $this->task->get_courseid()]);
        foreach ($columns as $column) {
            $column->gradeitemid = $this->get_mappingid('grade_item', $column->gradeitemid);
            $DB->update_record('local_gugrades_column', $column);
        }

        // Missing grade items in grades.
        $grades = $DB->get_recordset('local_gugrades_grade', ['courseid' => $this->task->get_courseid()]);
        foreach ($grades as $grade) {
            $grade->gradeitemid = $this->get_mappingid('grade_item', $grade->gradeitemid);
            $DB->update_record('local_gugrades_grade', $grade);
        }

        // Altered weights.
        $weights = $DB->get_recordset('local_gugrades_altered_weight', ['courseid' => $this->task->get_courseid()]);
        foreach ($weights as $weight) {
            $weight->categoryid = $this->get_mappingid('grade_category', 0 - $weight->categoryid);
            $weight->gradeitemid = $this->get_mappingid('grade_item', 0 - $weight->gradeitemid);
            $DB->update_record('local_gugrades_altered_weight', $weight);
        }

        // Audit trail.
        $audits = $DB->get_recordset('local_gugrades_audit', ['courseid' => $this->task->get_courseid()]);
        foreach ($audits as $audit) {
            $audit->gradeitemid = $this->get_mappingid('grade_item', $audit->gradeitemid);
            $DB->update_record('local_gugrades_audit', $audit);
        }

        // Hidden items
        $hiddens = $DB->get_recordset('local_gugrades_hidden', ['courseid' => $this->task->get_courseid()]);
        foreach ($hiddens as $hidden) {
            $hidden->gradeitemid = $this->get_mappingid('grade_item', 0 - $hidden->gradeitemid);
            $DB->update_record('local_gugrades_hidden', $hidden);
        }
    }

}