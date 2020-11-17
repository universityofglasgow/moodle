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
 * @package     qtype_kprime
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @author      Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author      Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @copyright   2014 eDaktik GmbH {@link http://www.edaktik.at}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the backup for kprime questions.
 */
class backup_qtype_kprime_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to the question element.
     */
    protected function define_question_plugin_structure() {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'kprime');
        // Create one standard named plugin element (the visible container).
        $name = $this->get_recommended_name();
        $pluginwrapper = new backup_nested_element($name);
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Now create the qtype own structures.
        $kprime = new backup_nested_element('kprime', array('id'
        ), array('scoringmethod', 'shuffleanswers', 'numberofrows', 'numberofcolumns'));
        $rows = new backup_nested_element('rows');
        $row = new backup_nested_element('row', array('id'
        ), array('number', 'optiontext', 'optiontextformat', 'optionfeedback', 'optionfeedbackformat'));
        $columns = new backup_nested_element('columns');
        $column = new backup_nested_element('column', array('id'), array('number', 'responsetext', 'responsetextformat'));
        $weights = new backup_nested_element('weights');
        $weight = new backup_nested_element('weight', array('id'), array('rownumber', 'columnnumber', 'weight'));
        // Now the qtype tree.
        $pluginwrapper->add_child($kprime);
        $pluginwrapper->add_child($rows);
        $pluginwrapper->add_child($columns);
        $pluginwrapper->add_child($weights);
        $rows->add_child($row);
        $columns->add_child($column);
        $weights->add_child($weight);
        // Set sources to populate the data.
        $kprime->set_source_table('qtype_kprime_options', array('questionid' => backup::VAR_PARENTID));
        $row->set_source_table('qtype_kprime_rows', array('questionid' => backup::VAR_PARENTID), 'number ASC');
        $column->set_source_table('qtype_kprime_columns', array('questionid' => backup::VAR_PARENTID), 'number ASC');
        $weight->set_source_table('qtype_kprime_weights', array('questionid' => backup::VAR_PARENTID));
        // We don't need to annotate ids nor files.
        return $plugin;
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype.
     *
     * Used by {@link get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas() {
        return array('optiontext' => 'qtype_kprime_rows', 'feedbacktext' => 'qtype_kprime_rows'
        );
    }
}
