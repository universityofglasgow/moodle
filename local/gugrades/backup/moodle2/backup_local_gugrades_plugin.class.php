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
 * Defines backup_local_gugrades class.
 *
 * @package     local_gugrades
 * @author      Howard Miller
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Backup plugin class.
 *
 * @package    local_gugrades
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_local_gugrades_plugin extends backup_local_plugin {

    /**
     * Returns the format information to attach to course element.
     */
    protected function define_course_plugin_structure() {

        // Are we including user data info in this backup.
        $userdata = $this->get_setting_value('users');

        $plugin = $this->get_plugin_element();
        $gugrades = new backup_nested_element($this->get_recommended_name());

        // Add local_gugrades_config.
        $config = new backup_nested_element('gugrades_config', null, ['name', 'value']);
        $config->set_source_table('local_gugrades_config', ['courseid' => backup::VAR_COURSEID]);
        $gugrades->add_child($config);

        // Add conversion map definitions.
        $maps = new backup_nested_element('gugrades_maps');
        $map = new backup_nested_element('gugrades_map', ['id'], ['name', 'scale', 'userid', 'maxgrade', 'timecreated', 'timemodified']);
        $map->annotate_ids('user', 'userid');
        $map->set_source_table('local_gugrades_map', ['courseid' => backup::VAR_COURSEID]);
        $maps->add_child($map);
        $gugrades->add_child($maps);


        // Add conversion map values
        // We need to nest this inside more maps to keep track of the ID. Can't do it above as we'll lose the ID when restoring
        // (think about it).
        $mapsvalues = new backup_nested_element('gugrades_map_values');
        $mapsvalue = new backup_nested_element('gugrades_map_value', ['id'], null);
        $mapsvalues->add_child($mapsvalue);
        $mapsvalue->set_source_table('local_gugrades_map', ['courseid' => backup::VAR_COURSEID]);
        $gmvalues = new backup_nested_element('gm_values');
        $gmvalue = new backup_nested_element('gm_value', null, ['mapid', 'percentage', 'scalevalue', 'band']);
        $gmvalues->add_child($gmvalue);
        $mapsvalue->add_child($gmvalues);
        $gmvalue->set_source_table('local_gugrades_map_value', ['mapid' => backup::VAR_PARENTID]);
        $gugrades->add_child($mapsvalues);

        // Add map items (converted items or categories).
        $mapitems = new backup_nested_element('gugrades_map_items');
        $mapitem = new backup_nested_element('gugrades_map_item', null, ['mapid', 'gradeitemid', 'gradecategoryid', 'userid', 'timemodified']);
        $mapitem->annotate_ids('user', 'userid');
        $mapitems->add_child($mapitem);
        $mapitem->set_source_table('local_gugrades_map_item', ['courseid' => backup::VAR_COURSEID]);
        $gugrades->add_child($mapitems);

        // If userdata is included (pretty much everything).
        if ($userdata) {

            // Backup columns
            $columns = new backup_nested_element('gugrades_columns');
            $column = new backup_nested_element('gugrades_column', ['id'], ['gradeitemid', 'gradetype', 'other', 'points']);
            $columns->add_child($column);
            $column->set_source_table('local_gugrades_column', ['courseid' => backup::VAR_COURSEID]);
            $gugrades->add_child($columns);

            // Backup grades
            $grades = new backup_nested_element('gugrades_grades');
            $grade = new backup_nested_element('gugrades_grade', null, 
                ['gradeitemid', 'userid', 'points', 'rawgrade', 'convertedgrade', 'admingrade', 'displaygrade', 'weightedgrade', 'gradetype', 'columnid', 'iscurrent',
                'iserror', 'auditby', 'audittimecreated', 'auditcomment', 'dropped', 'catoverride', 'normalisedweight']);
            $grade->annotate_ids('user', 'userid');
            $grade->annotate_ids('user', 'auditby');
            $grades->add_child($grade);
            $grade->set_source_table('local_gugrades_grade', ['courseid' => backup::VAR_COURSEID]);
            $gugrades->add_child($grades);

            // Backup altered weight.
            $weights = new backup_nested_element('gugrades_weights');
            $weight = new backup_nested_element('gugrades_weight', null, ['categoryid', 'gradeitemid', 'userid', 'weight', 'timealtered']);
            $weight->annotate_ids('user', 'userid');
            $weights->add_child($weight);
            $weight->set_source_table('local_gugrades_altered_weight', ['courseid' => backup::VAR_COURSEID]);
            $gugrades->add_child($weights);

            // Backup audit trail.
            $audits = new backup_nested_element('gugrades_audits');
            $audit = new backup_nested_element('gugrades_audit', null, ['userid', 'relateduserid', 'gradeitemid', 'timecreated', 'message']);
            $audit->annotate_ids('user', 'userid');
            $audit->annotate_ids('user', 'relateduserid');
            $audits->add_child($audit);
            $audit->set_source_table('local_gugrades_audit', ['courseid' => backup::VAR_COURSEID]);
            $gugrades->add_child($audits);

            // Backup hidden
            $hiddens = new backup_nested_element('gugrades_hiddens');
            $hidden = new backup_nested_element('gugrades_hidden', null, ['gradeitemid', 'userid']);
            $hidden->annotate_ids('user', 'userid');
            $hiddens->add_child($hidden);
            $hidden->set_source_table('local_gugrades_hidden', ['courseid' => backup::VAR_COURSEID]);
            $gugrades->add_child($hiddens);

            // Backup resit required
            $resits = new backup_nested_element('gugrades_resits');
            $resit = new backup_nested_element('gugrades_resit', null, ['userid']);
            $resit->annotate_ids('user', 'userid');
            $resits->add_child($resit);
            $resits->set_source_table('local_gugrades_resitrequired', ['courseid' => backup::VAR_COURSEID]);
            $gugrades->add_child($resits);
        }

        // Finally
        $plugin->add_child($gugrades);

        return $plugin;
    }

}
