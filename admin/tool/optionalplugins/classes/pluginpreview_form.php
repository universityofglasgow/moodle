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
 * This class represents the additional plugins that can be installed
 *
 * Having been passed in the data for which plugins can be installed,
 * and which cannot, display this to the user, along with the option
 * of installing a recommended version, or sticking with the current
 * version.
 *
 * @package    tool_optionalplugins
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");

/**
 * This class is responsible for the previewing of plugins to be installed
 */
class pluginpreview_form extends moodleform
{
    /**
     * This function defines the elements on the form.
     *
     * @return void
     * @throws coding_exception
     */
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore!
        $data = $this->_customdata;

        if (isset($data['canbeinstalled']) && count($data['canbeinstalled']) > 0) {
            $canbeinstalled = $data['canbeinstalled'];

            $installtableheader = '<div class="alert alert-success generalbox boxwidthnormal boxaligncenter">'
                . get_string('pluginstoinstall', 'tool_optionalplugins') . '</div>';

            $mform->addElement('html', $installtableheader);
            $mform->addElement('html', '<table id="plugins-control-panel" class="generaltable">');
            $mform->addElement('html', '<thead>'
                . '<th class="header">' . get_string('displayname', 'core_plugin') . '</th>'
                . '<th class="header">' . get_string('renderer_columnname', 'tool_optionalplugins') . '</th>'
                . '<th class="header">' . get_string('version', 'core_plugin') . '</th>'
                . '<th class="header">' . get_string('notes_string', 'tool_optionalplugins') . '</th></thead>');

            foreach ($canbeinstalled as $idx => $plugin) {
                $mform->addElement('html', '<tr>'
                    . '<td class="pluginname">' . $plugin['displayname'] . '</td>'
                    . '<td class="pluginname">' . $plugin['pluginname'] . '</td>'
                    . '<td class="version">');

                if (isset($plugin['release']) && (string)$plugin['release'] !== '') {
                    $mform->addElement('html', '<div class="release">' . $plugin['release'] . '</div>');
                }

                $mform->addElement('html', '<div class="versionnumber">' . $plugin['version'] . '</div>');

                $mform->addElement('html', '</td>');

                $mform->addElement('html', '<td class="notes">');

                if (!empty($plugin['notes'])) {

                    $mform->addElement('html', '<div class="source badge badge-info">' . get_string('additional_string', 'tool_optionalplugins') . '</div>');

                    if (!empty($plugin['requiredby'])) {
                        $mform->addElement('html', '<div class="requiredby">' . get_string('requiredby',
                                'core_plugin', implode(', ', $plugin['requiredby'])) . '</div>');
                    }

                    if (!empty($plugin['maturitylevel'])) {
                        $mform->addElement('html', '<div class="pluginupdateinfo maturity' . $plugin['maturitylevel'] . '">'
                            . '<div class="version">');
                    }

                    if (isset($plugin['notice']) && $plugin['notice'] != '') {
                        $mform->addElement('html', $plugin['notice']);
                    } else {
                        if (!empty($plugin['remotepluginversion'])) {
                            $mform->addElement('html', get_string('updateavailable',
                                'core_plugin', $plugin['remotepluginversion']));
                        }
                    }

                    if (!empty($plugin['remotepluginrelease'])) {
                        $mform->addElement('html', '</div><div class="infos">'
                            . get_string('updateavailable_release', 'core_plugin', $plugin['remotepluginrelease']));
                    }

                    if (isset($plugin['maturitylevel'])) {
                        $mform->addElement('html', ' | <span class="info">'
                            . get_string('maturity' . $plugin['maturitylevel'], 'core_admin') . '</span>');
                    }

                    if (isset($plugin['conditiontext']) && $plugin['conditiontext'] != '') {
                        $mform->addElement('html', '<br />' . get_string('pluginstoinstall_extra',
                                'tool_optionalplugins', $plugin['conditiontext']));
                    }

                    $mform->addElement('html', '</div>');

                    $default = 0;
                    $displaycheckbox = 0;
                    $text = '';
                    if (!empty($plugin['checkbox_y'])) {
                        $text = get_string('installationchoice_y', 'tool_optionalplugins');
                        $displaycheckbox = 1;
                    }

                    if (!empty($plugin['checkbox_n'])) {
                        $text = get_string('installationchoice_n', 'tool_optionalplugins');
                        $default = 1;
                        $displaycheckbox = 1;
                    }

                    if ($displaycheckbox) {
                        $mform->addElement('advcheckbox', 'installationchoice[' . $plugin['pluginname'] . ']',
                            '', $text, '', array(0, 1));
                        $mform->setDefault('installationchoice[' . $plugin['pluginname'] . ']', $default);
                    }

                    $mform->addElement('html', '</div>');
                }

                $mform->addElement('html', '</td></tr>');
            }

            $mform->addElement('html', '</table>');

            $this->add_action_buttons(true, get_string('install_btn_string', 'tool_optionalplugins'));
        }

        if (isset($data['alreadyinstalled']) && count($data['alreadyinstalled']) > 0) {
            $alreadyinstalled = $data['alreadyinstalled'];

            $alreadyinstalltableheader = '<div class="alert alert-warning generalbox boxwidthnormal boxaligncenter">'
                . get_string('pluginsalreadyinstalled', 'tool_optionalplugins') . '</div>';

            $mform->addElement('html', $alreadyinstalltableheader);
            $mform->addElement('html', '<table id="plugins-control-panel" class="generaltable">');
            $mform->addElement('html', '<thead>'
                . '<th class="header">' . get_string('displayname', 'core_plugin') . '</th>'
                . '<th class="header">' . get_string('renderer_columnname', 'tool_optionalplugins') . '</th>'
                . '<th class="header">' . get_string('version', 'core_plugin') . '</th>'
                . '</thead>');

            foreach ($alreadyinstalled as $idx => $plugin) {
                $mform->addElement('html', '<tr>'
                    . '<td class="pluginname">' . $plugin['displayname'] . '</td>'
                    . '<td class="pluginname">' . $plugin['pluginname'] . '</td>'
                    . '<td class="version">');

                if ((string)$plugin['release'] !== '') {
                    $mform->addElement('html', '<div class="release">' . $plugin['release'] . '</div>');
                }

                $mform->addElement('html', '<div class="versionnumber">' . $plugin['version'] . '</div>');

                $mform->addElement('html', '</td>');

                $mform->addElement('html', '</tr>');
            }

            $mform->addElement('html', '</table>');

        }

        if (isset($data['cannotbeinstalled']) && count($data['cannotbeinstalled']) > 0) {
            $cannotbeinstalled = $data['cannotbeinstalled'];

            $cannotbeinstalledtableheader = '<div class="alert alert-danger generalbox boxwidthnormal boxaligncenter">'
                . get_string('pluginstoskip', 'tool_optionalplugins') . '</div>';

            $mform->addElement('html', $cannotbeinstalledtableheader);
            $mform->addElement('html', '<table id="plugins-control-panel" class="generaltable">');
            $mform->addElement('html', '<thead>'
                . '<th class="header">' . get_string('displayname', 'core_plugin') . '</th>'
                . '<th class="header">' . get_string('renderer_columnname', 'tool_optionalplugins') . '</th>'
                . '<th class="header">' . get_string('version', 'core_plugin') . '</th>'
                . '<th class="header">' . get_string('notes_string', 'tool_optionalplugins') . '</th></thead>');

            foreach ($cannotbeinstalled as $idx => $plugin) {
                $mform->addElement('html', '<tr>'
                    . '<td class="pluginname">' . $plugin['displayname'] . '</td>'
                    . '<td class="pluginname">' . $plugin['pluginname'] . '</td>'
                    . '<td class="version">');

                if (isset($plugin['release']) && (string)$plugin['release'] !== '') {
                    $mform->addElement('html', '<div class="release">' . $plugin['release'] . '</div>');
                }

                $mform->addElement('html', '<div class="versionnumber">' . $plugin['version'] . '</div>');

                $mform->addElement('html', '</td>');

                $mform->addElement('html', '<td class="notes">');

                if (!empty($plugin['notes'])) {
                    $mform->addElement('html', '<div class="source badge badge-info">' . get_string('additional_string', 'tool_optionalplugins') . '</div>');
                    $mform->addElement('html', '<div class="notes">' . $plugin['notes'] . '</div>');
                }

                $mform->addElement('html', '</td></tr>');
            }

            $mform->addElement('html', '</table>');

        }

        $this->set_data($data);
    }
}
