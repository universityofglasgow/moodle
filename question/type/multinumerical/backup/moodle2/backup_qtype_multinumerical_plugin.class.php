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
 * Version information
 *
 * @package    qtype
 * @subpackage multinumerical
 * @copyright  2013 Université de Lausanne
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class backup_qtype_multinumerical_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to question element
     */
    protected function define_question_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill
        $plugin = $this->get_plugin_element(null, '../../qtype', 'multinumerical');

        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // connect the visible container ASAP
        $plugin->add_child($pluginwrapper);

        // Now create the qtype own structures
        $multinumerical = new backup_nested_element('multinumerical', array('id'), array('parameters', 'conditions', 'feedbackperconditions', 'binarygrade', 'displaycalc', 'usecolorforfeedback'));

        // Now the own qtype tree
        $pluginwrapper->add_child($multinumerical);

        // set source to populate the data
        $multinumerical->set_source_table('question_multinumerical',
                array('question' => backup::VAR_PARENTID));

        // don't need to annotate ids nor files

        return $plugin;
    }
}
