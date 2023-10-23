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
 * Script to handle exporting/importing of plugin data
 *
 * This script deals with exporting the list of optional plugins in JSON format
 * and also provides the process for importing plugins from a JSON list into
 * a new Moodle install.
 *
 * @package tool_optionalplugins
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/dataformatlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/optionalplugins/lib.php');

require_admin();

$action = required_param('action', PARAM_ALPHA);
$systemcontext = context_system::instance();

switch ($action) {
    case 'exportoptionalplugins':

        export_optional_plugins();

        break;

    case 'validatesourcepluginlist':

        $validation = validate_source_plugin_list($SESSION->filecontents, $CFG->updateminmaturity, $CFG->version);

        if ($validation == true) {
            redirect(new moodle_url("/admin/tool/optionalplugins/pluginpreview.php"));
            exit(0);
        } else {
            redirect(new moodle_url("/admin/tool/optionalplugins/index.php", array('errormsg' => 'File has no contents')));
            exit(0);
        }

    case 'installoptionalplugins':

        require_capability('tool/optionalplugins:importplugins', $systemcontext);
        require_sesskey();
        require_once($CFG->libdir.'/upgradelib.php');

        $context = context_system::instance();

        $PAGE->set_url(new moodle_url("/admin/tool/optionalplugins/controller.php"));
        $PAGE->set_context($context);

        $installationchoices = array();
        if (isset($SESSION->installationchoice)) {
            $installationchoices = $SESSION->installationchoice;
        }

        $installation = install_optional_plugins($SESSION->canbeinstalled, $installationchoices, $PAGE->url);

        if ($installation == true) {
            redirect(new moodle_url('/admin/index.php', array('cache' => 0, 'confirmplugincheck' => 0, 'confirmupgrade' => 1,
                'confirmrelease' => 1)));
            exit(0);
        }

        break;
}
