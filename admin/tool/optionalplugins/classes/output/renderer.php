<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Renderer for use with the Optional Plugins report.
 *
 * @package    tool_optionalplugins
 * @copyright  2022 Greg Pedder
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This class is responsible for rendering the report
 */
class tool_optionalplugins_renderer extends plugin_renderer_base
{
    /**
     * This function deals with retrieving and outputting the report data.
     *
     * @param array $logdata
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function render_installation_log($logdata) {

        global $DB;
        $o = '';
        $o .= $this->output->container_start('logdatatable');
        $columns = array(
            get_string('displayname', 'core_plugin'),
            get_string('renderer_columnname', 'tool_optionalplugins'),
            get_string('version', 'core_plugin'),
        );
        $colclasses = array(
            'pluginname', 'repositoryname', 'version'
        );
        $headspan = array(1, 1, 1);

        foreach ($logdata as $data) {

            $reportdate = userdate($data->timecreated);
            $user = $DB->get_record('user', array('id' => $data->userid));
            $useremail = html_writer::tag('a', $user->email, array('href' => 'mailto:' . $user->email));
            $createdby = fullname($user);
            $installed = json_decode($data->installed);
            $alreadyinstalled = json_decode($data->alreadyinstalled);
            $notinstalled = json_decode($data->notinstalled);

            $o .= html_writer::div('<h2>' . get_string('installationdetails', 'tool_optionalplugins') . '</h2><ul><li>'
                . get_string('user_string', 'tool_optionalplugins') . ': ' . $createdby . ' <' . $useremail
                . '></li><li>' . get_string('date_string', 'tool_optionalplugins') . ': ' . $reportdate . '</li><li>'
                . get_string('pluginsinstalled', 'tool_optionalplugins') . ': '
                . count(get_object_vars($installed))
                . '</li><li>' . get_string('pluginsalreadyinstalled', 'tool_optionalplugins') . ': ' . count($alreadyinstalled)
                . '</li><li>' . get_string('pluginsnotinstalled', 'tool_optionalplugins') . ': '
                . count($notinstalled) . '</li></ul>', 'box generalbox');

            // For plugins that were installed...
            if (!empty($installed)) {

                $o .= $this->output->box_start('boxaligncenter logdatatable');
                $o .= html_writer::div(get_string('pluginsinstalled', 'tool_optionalplugins'),
                    'alert alert-success generalbox boxwidthnormal boxaligncenter');

                array_push($columns, get_string('notes', 'core_plugin'));
                array_push($headspan, 1);
                array_push($colclasses, 'notes');
                $t = new html_table();
                $t->id = 'installedoptionlplugins';
                $t->head = $columns;
                $t->headspan = $headspan;
                $t->colclasses = $colclasses;
                foreach ($installed as $installedplugin) {

                    $row = new html_table_row();
                    $displayname = html_writer::tag('div', $installedplugin->displayname, array('class' => 'componentname'));
                    $displayname = new html_table_cell($displayname);

                    $pluginname = html_writer::tag('div', $installedplugin->pluginname, array('class' => 'componentname'));
                    $pluginname = new html_table_cell($pluginname);

                    $installedversion = ((isset($installedplugin->versioninstalled))
                        ? $installedplugin->versioninstalled : $installedplugin->versiontobeinstalled);
                    $version = html_writer::div($installedversion, 'versionnumber');

                    $installedrelease = (($installedplugin->remoteinstalled) ? ((isset($installedplugin->remotepluginrelease))
                        ? (string)$installedplugin->remotepluginrelease : '') : (isset($installedplugin->release)
                        ? (string)$installedplugin->release : ''));

                    if ($installedrelease !== '') {
                        $version = html_writer::div($installedrelease, 'release') . $version;
                    }

                    $version = new html_table_cell($version);

                    $notes = '';
                    if (isset($installedplugin->notes)) {
                        $notes = html_writer::div($installedplugin->notes, 'notes');
                        $notes = new html_table_cell($notes);
                    }

                    $row->cells = array(
                        $displayname, $pluginname, $version, $notes
                    );
                    $t->data[] = $row;
                }

                $o .= html_writer::table($t);
                $o .= $this->output->box_end();
            }

            // For plugins already installed...
            if (!empty($alreadyinstalled)) {

                $o .= $this->output->box_start('boxaligncenter logdatatable');
                $o .= html_writer::div(get_string('pluginsalreadyinstalled', 'tool_optionalplugins'),
                    'alert alert-warning generalbox boxwidthnormal boxaligncenter');
                $u = new html_table();
                $u->id = 'alreadyinstalledoptionalplugins';
                $u->head = $columns;
                $u->headspan = $headspan;
                $u->colclasses = $colclasses;
                foreach ($alreadyinstalled as $alreadyinstalledplugin) {

                    $row = new html_table_row();
                    $displayname = html_writer::tag('div', $alreadyinstalledplugin->displayname, array('class' => 'componentname'));
                    $displayname = new html_table_cell($displayname);

                    $pluginname = html_writer::tag('div', $alreadyinstalledplugin->pluginname, array('class' => 'componentname'));
                    $pluginname = new html_table_cell($pluginname);

                    $version = html_writer::div($alreadyinstalledplugin->version, 'versionnumber');
                    $version = new html_table_cell($version);

                    $row->cells = array(
                        $displayname, $pluginname, $version, $notes
                    );
                    $u->data[] = $row;
                }

                $o .= html_writer::table($u);
                $o .= $this->output->box_end();
            }

            // For plugins not installed...
            if (!empty($notinstalled)) {

                $o .= $this->output->box_start('boxaligncenter logdatatable');
                $o .= html_writer::div(get_string('pluginstoskip', 'tool_optionalplugins'),
                    'alert alert-danger generalbox boxwidthnormal boxaligncenter');
                $v = new html_table();
                $v->id = 'optionalpluginsnotinstalled';
                $v->head = $columns;
                $v->headspan = $headspan;
                $v->colclasses = $colclasses;
                foreach ($notinstalled as $notinstalledplugin) {

                    $row = new html_table_row();
                    $displayname = html_writer::tag('div', $notinstalledplugin->displayname, array('class' => 'componentname'));
                    $displayname = new html_table_cell($displayname);

                    $pluginname = html_writer::tag('div', $notinstalledplugin->pluginname, array('class' => 'componentname'));
                    $pluginname = new html_table_cell($pluginname);

                    $version = html_writer::div($notinstalledplugin->version, 'versionnumber');
                    $version = new html_table_cell($version);

                    $notes = '';
                    if (isset($notinstalledplugin->notes)) {
                        $notes = html_writer::div(get_string('additional_string', 'tool_optionalplugins'), 'source badge badge-info');
                        $notes .= html_writer::div($notinstalledplugin->notes, 'notes');
                        $notes = new html_table_cell($notes);
                    }

                    $row->cells = array(
                        $displayname, $pluginname, $version, $notes
                    );
                    $v->data[] = $row;
                }

                $o .= html_writer::table($v);
                $o .= $this->output->box_end();
            }
        }

        $o .= $this->output->container_end();

        return $o;
    }
}
