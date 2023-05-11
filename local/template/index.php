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
 * Manage template.
 * @package local_template
 * @copyright 2023 David Aylmer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\notification;
use local_template\controllers\template;
use local_template\controllers\backupcontroller;
use local_template\models;
use local_template\utils;

define('NO_OUTPUT_BUFFERING', true);

// Moodle codechecker incorrectly asserts require must use parenthesis.
// @codingStandardsIgnoreLine
require '../../config.php';

global $CFG, $PAGE;

// Moodle codechecker incorrectly asserts require_once must use parenthesis.
// @codingStandardsIgnoreLine
require_once $CFG->libdir . '/adminlib.php';

// Moodle codechecker incorrectly asserts require_once must use parenthesis.
// @codingStandardsIgnoreLine
require_once $CFG->dirroot . '/local/template/lib.php';

utils::enforce_security(true);

// $PAGE->navbar->add(get_string('template', 'local_template'), new moodle_url('/local/template/index.php'));
// $PAGE->navbar->add(get_string('templateadmin', 'local_template'), new moodle_url('/local/template/admin/index.php'));
// $PAGE->navbar->add(get_string('template', 'local_template'), new moodle_url('/local/template/admin/templates.php'));

$action = optional_param('action', '', PARAM_ALPHANUMEXT );
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/template/admin/templates.php', ['action' => $action]));
$PAGE->set_title(get_string('pluginname', 'local_template'));

switch ($action) {
    case 'setview':
        $view = optional_param('view', 'table', PARAM_TEXT);
        template::set_view($view);
        break;
    case 'createtemplate':
        //notification::info('Should be zero: ' . optional_param('id', 0, PARAM_INT));
        $templateid = optional_param('templateid', 0, PARAM_INT);
        template::display($templateid);
        break;

    case 'edittemplate':
        $templateid = optional_param('templateid', 0, PARAM_INT);
        if (empty($templateid)) {
            $templateid = optional_param('id', 0, PARAM_INT);
        }
        template::process($templateid);
        break;

    case 'showtemplate':
        $templateid = required_param('templateid', PARAM_INT);
        template::show($templateid);
        break;

    case 'hidetemplate':
        $templateid = required_param('templateid', PARAM_INT);
        template::hide($templateid);
        break;

    case 'deletetemplate':
        $templateid = required_param('templateid', PARAM_INT);
        $confirm = optional_param('confirm', '0', PARAM_INT);

        if ($confirm && confirm_sesskey()) {
            template::delete($templateid);
        } else {
            global $OUTPUT;

            $template = new models\template($templateid);

            $output = 'Confirm delete of template?<br>';
            $backupcontrollershtml = '';

            $output .= '<dl>';
            $output .= '<dt>Name</dt><dd>' . $template->get('fullname') . '</dd>';
            $output .= '<dt>backupcontrollers</dt><dd>' . $template->get_backupcontrollersnames() . '</dd>';
            $output .= '</dl>';

            $continue = new moodle_url('/local/template/index.php', [
                'action' => 'deletetemplate',
                'templateid' => $templateid,
                'confirm' => 1,
                'sesskey' => sesskey()
            ]);
            $returnurl = new moodle_url('/local/template/index.php');
            $confirm = $OUTPUT->confirm($output, $continue, $returnurl);
            template::renderpage($confirm);
        }
        break;

    case 'runtemplate':
        $templateid = optional_param('templateid', 0, PARAM_INT);
        if (empty($templateid)) {
            $templateid = optional_param('id', 0, PARAM_INT);
        }
        template::runtemplate($templateid);
        break;

    case 'createbackupcontroller':
        //notification::info('Should be zero: ' . optional_param('id', 0, PARAM_INT));
        $backupcontrollerid = optional_param('backupcontrollerid', 0, PARAM_INT);
        $templateid = optional_param('templateid', 0, PARAM_INT);
        backupcontroller::display($backupcontrollerid, $templateid);
        break;

    case 'editbackupcontroller':
        $backupcontrollerid = optional_param('backupcontrollerid', 0, PARAM_INT);
        if (empty($backupcontrollerid)) {
            $backupcontrollerid = optional_param('id', 0, PARAM_INT);
        }
        backupcontroller::process($backupcontrollerid);
        break;

    case 'runbackupcontroller':
        $backupcontrollerid = optional_param('backupcontrollerid', 0, PARAM_INT);
        backupcontroller::runbackupcontroller($backupcontrollerid);
        break;

    case 'showbackupcontroller':
        $backupcontrollerid = optional_param('backupcontrollerid', 0, PARAM_INT);
        backupcontroller::show($backupcontrollerid);
        break;

    case 'hidebackupcontroller':
        $backupcontrollerid = optional_param('backupcontrollerid', 0, PARAM_INT);
        backupcontroller::hide($backupcontrollerid);
        break;

    case 'deletebackupcontroller':
        $backupcontrollerid = required_param('backupcontrollerid', PARAM_INT);
        backupcontroller::delete($backupcontrollerid);
        break;

    case 'rejectlocaltemplate':
        global $SESSION;
        $SESSION->reject_local_template = true;

        $categoryid = optional_param('category', 0, PARAM_INT);
        $returnto = optional_param('returnto', 0, PARAM_ALPHANUM);
        $returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

        $params = [];
        if (!empty($categoryid)) {
            $params['category'] = $categoryid;
        }
        if (!empty($returnto)) {
            $params['returnto'] = $returnto;
        }
        if (!empty($returnurl)) {
            $params['returnurl'] = $returnurl;
        }

        $courseurl = (new \moodle_url($CFG->wwwroot . '/course/edit.php', $params))->out();

        redirect($courseurl);

        break;

    default:
        //template::renderpage();
        $templateid = optional_param('templateid', 0, PARAM_INT);
        template::display($templateid);
}
