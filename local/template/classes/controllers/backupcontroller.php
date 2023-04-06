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
 * backupcontroller controller
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_template\controllers;

use local_template\collections\backupcontrollercollection;
use local_template\models;
use local_template\forms;
use core\notification;
use moodle_url;

define(NO_OUTPUT_BUFFERING, true);

class backupcontroller {

    private static function context() {
        return \context_system::instance()->id;
    }

    public static function path() {
        global $CFG;
        if (is_template_admin()) {
            return $CFG->wwwroot . '/local/template/admin/backupcontrollers.php';
        } else {
            return $CFG->wwwroot . '/local/template/index.php';
        }
    }

    private static function do_redirect() {
        redirect(self::path());
    }

    public static function view($id) {
        global $OUTPUT, $PAGE;
        $backupcontroller = new models\backupcontroller($id);
        $record = $backupcontroller->read();

        $customdata = [
            'id' => $id,
            'persistent' => $backupcontroller,
            'action' => 'viewbackupcontroller',
        ];

        $name = shorten_text(format_string($record->get('name')));

        $form = new forms\backupcontroller($PAGE->url->out(false), $customdata, 'post', '', null, false);
        // Print the page.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('viewbackupcontroller', 'local_template', $name));
        $form->display();
        echo \html_writer::start_tag('div', ['class' => 'container text-center align-center']);
        echo \html_writer::link(new \moodle_url(self::path(), ['action' => 'editbackupcontroller', 'backupcontrollerid' => $id]), get_string('editbackupcontroller', 'local_template', $name),['class' => 'btn btn-primary']);
        echo $OUTPUT->spacer();
        echo \html_writer::link(new \moodle_url(self::path()), get_string('cancel'), ['class' => 'btn btn-secondary']);
        echo \html_writer::end_tag('div');
        echo $OUTPUT->footer();
        die;
    }

    public static function display($id, $templateid=0, $form=null) {
        global $OUTPUT, $PAGE;

        // Are we 'creating' or 'editing'?
        $backupcontroller = null;

        if (empty($id)) {
            $strheading = get_string('createnewbackupcontroller', 'local_template');
        } else {
            $backupcontroller = new models\backupcontroller($id);
            $record = $backupcontroller->read();
            $strheading = get_string('editbackupcontroller', 'local_template', shorten_text(format_string($record->get('name'))));
        }

        // Initialise a form object if we haven't been provided with one.
        if ($form == null) {

            $customdata = [
                'id' => $id,
                'persistent' => $backupcontroller,
                'action' => 'editbackupcontroller',
                'templateid' => $templateid
            ];

            // Constructor calls set_data.
            $form = new forms\backupcontroller($PAGE->url->out(false), $customdata);

        }

        if ($form->is_cancelled()) {
            self::do_redirect();
        }

        // Print the page.
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);
        $form->display();
        echo $OUTPUT->footer();
        die;
    }

    public static function process($id, $templateid=0) {
        global $PAGE, $USER;

        $backupcontroller = null;
        $customdata = [
            'id' => $id,
            'persistent' => $backupcontroller,
            'action' => 'editbackupcontroller',
        ];

        if (!empty($id)) {
            $backupcontroller = new models\backupcontroller($id);
            $customdata['persistent'] = $backupcontroller;
        }

        $form = new forms\backupcontroller($PAGE->url->out(false), $customdata);
        $data = $form->get_data();

        if ($data) {
            try {
                $data->usercreated = $USER->id;

                $backupcontrollertext = $data->name;

                $backupcontroller = null;
                if (empty($data->id)) {
                    $backupcontroller = new models\backupcontroller(0, $data);
                    if ($backupcontroller->create()) {
                        notification::success("backupcontroller: $backupcontrollertext successfully created");
                    } else {
                        notification::error("Could not create backupcontroller: $backupcontrollertext");
                    }

                } else {
                    $backupcontroller = new models\backupcontroller();
                    $backupcontroller->from_record($data);
                    if ($backupcontroller->update()) {
                        notification::success("backupcontroller: $backupcontrollertext updated");
                    } else {
                        notification::error("Could not update backupcontroller: $backupcontrollertext");
                    }
                }

            } catch (\Exception $e) {
                notification::error($e->getMessage());
            }

            self::do_redirect();
        }
        self::display($id, $templateid, $form);
    }

    public static function show($id) {
        if (!empty($id)) {
            $backupcontroller = new models\backupcontroller($id);
            $backupcontroller->set('hidden', models\backupcontroller::HIDDEN_FALSE);
            if ($backupcontroller->update()) {
                notification::success('backupcontroller ' . $backupcontroller->get('name') . ' shown');
            } else {
                notification::error('Could not show backupcontroller ' . $backupcontroller->get('name'));
            }
        }
        self::do_redirect();
    }

    public static function runbackupcontroller($id) {

        global $OUTPUT;
        if (!empty($id)) {

            $backupcontroller = new models\backupcontroller($id);
            $result = $backupcontroller->process();
            if (!$result) {
                $backupcontroller->notifications->output(true);
            } else {
                if ($backupcontroller->get('recordsinfo') > 0) {
                    notification::info($backupcontroller->get('recordsinfo') . ' rows generated information');
                }
                if ($backupcontroller->get('recordssuccess') > 0) {
                    notification::success($backupcontroller->get('recordssuccess') . ' rows generated success');
                }
                if ($backupcontroller->get('recordswarning') > 0) {
                    notification::warning($backupcontroller->get('recordswarning') . ' rows generated warnings');
                }
                if ($backupcontroller->get('recordserror') > 0) {
                    notification::error($backupcontroller->get('recordserror') . ' rows generated errors');
                }
            }
        }
        self::do_redirect();
    }

    public static function hide($id) {
        if (!empty($id)) {
            $backupcontroller = new models\backupcontroller($id);
            $backupcontroller->set('hidden', models\backupcontroller::HIDDEN_TRUE);
            if ($backupcontroller->update()) {
                notification::success('backupcontroller ' . $backupcontroller->get('name') . ' is now hidden. Now only template admin can see this record.');
            } else {
                notification::error('Could not hide backupcontroller ' . $backupcontroller->get('name'));
            }
        }
        self::do_redirect();
    }

    public static function delete($id) {
        $backupcontroller = new models\backupcontroller($id);
        $name = get_string('missingbackupcontrollername', 'local_template');
        if (models\backupcontroller::has_property('name')) {
            $name = $backupcontroller->get('name');
        }

        if (!empty($id)) {
            if (confirm_sesskey()) {
                if ($backupcontroller->cascadedelete()) {
                    notification::success('backupcontroller: ' . $name . ' deleted');
                } else {
                    notification::success('Could not delete: ' . $name . '.');
                }
            } else {
                notification::success('Could not delete: ' . $name . '.');
            }
        } else {
            notification::success('Could not delete: ' . $name . '.');
        }

        self::do_redirect();
    }

    public static function renderpage($confirm='') {
        global $OUTPUT, $SESSION;

        // Create a new backupcontroller link.
        $buttons = $OUTPUT->single_button(
            new \moodle_url(self::path(), ['action' => 'createbackupcontroller']),
            get_string('createbackupcontroller', 'local_template')
        );
        if (is_template_admin()) {
            $buttons .= $OUTPUT->single_button(new \moodle_url(self::path()), get_string('admin', 'local_template'));
        }

        // Print the header.
        echo $OUTPUT->header();
        if (is_template_admin()) {
            echo template_admin();
        }
        echo $OUTPUT->heading(get_string('backupcontroller', 'local_template'));

        echo $confirm;

        echo $buttons;
        echo '<hr />';

        echo self::renderbackupcontrollers();
        echo '<hr />';
        echo $buttons;
        echo $OUTPUT->footer();
        die;

    }

    public static function renderbackupcontrollers($parentid = 0) {
        global $SESSION;
        $view = 'table';
        if (object_property_exists($SESSION, 'local_template_view')) {
            $view = $SESSION->local_template_view;
        }
        $backupcontrollers = models\backupcontroller::collection($parentid, $view);
        return $backupcontrollers->render() . $backupcontrollers->render_paging_bar(self::path());

        global $PAGE, $SESSION;
        $view = 'table';
        if (object_property_exists($SESSION, 'local_template_view')) {
            $view = $SESSION->local_template_view;
        }

        $backupcontrollers = new backupcontrollercollection('backupcontroller Rows', self::path(), $view);
        return $backupcontrollers->render();

        global $SESSION, $OUTPUT, $USER;
        $view = 'table';
        if (object_property_exists($SESSION, 'local_template_view')) {
            $view = $SESSION->local_template_view;
        }

        $headings = ['date', 'backupcontrollername', 'status', 'exportfile', 'grades'];
        $headingalignment = ['left', 'left', 'left', 'left', 'left'];
        $norecordslangstring = 'nobackupcontrollersdefined';
        $addrecordlangstring = 'addbackupcontroller';
        $addnewiconlink = template_icon_link('add',self::path(), ['id' => '0', 'action' => 'createbackupcontroller', 'templateid' => $templateid]);
        $containsactions = true;

        $filters = [];
        if (!empty($templateid)) {
            $filters['templateid'] = $templateid;
        }

        if (is_template_admin()) {
            // Add usercreated column
            $headings[] = 'user';
            $headingalignment[] = 'left';
        } else {
            // Only show records for current user, and not hidden records.
            $filters['usercreated'] = $USER->id;
            $filters['hidden'] = models\template::HIDDEN_FALSE;
        }

        $records = [];
        $backupcontrollers = models\backupcontroller::get_records($filters, 'timemodified', 'DESC');

        foreach ($backupcontrollers as $backupcontroller) {

            $record = [];

            $backupcontrollerdate = userdate($backupcontroller->get('timemodified'), get_string('strftimedatetimeshort', 'core_langconfig'));
            if (empty($backupcontrollerdate)) {
                $backupcontrollerdate = get_string('missingbackupcontrollerdate','local_template');
            }
            $record[] = format_string($backupcontrollerdate);

            $backupcontrollername = $backupcontroller->get('name');
            if (empty($backupcontrollername)) {
                $backupcontrollername = get_string('missingbackupcontrollername','local_template');
            }
            $backupcontrollername = format_string($backupcontrollername);
            $backupcontrollername .= $OUTPUT->spacer() . template_icon_link('edit',self::path(), ['action' => 'editbackupcontroller', 'backupcontrollerid' => $backupcontroller->get('id')]);
            $record[] = $backupcontrollername;

            $status = models\backupcontroller::get_status_string($backupcontroller->get('status'));
            $record[] = self::progress($backupcontroller) . $OUTPUT->spacer() . format_string($status);

            $file = $backupcontroller->get_file();
            if (empty($file)) {
                $exportfile = get_string('missingfilename','local_template');
            } else {
                $exportfile = template_icon_link('download', $backupcontroller->get_file_url());
            }
            $record[] = $exportfile;

            $grades = backupcontrollergrade::renderbackupcontrollergrades($backupcontroller->get('id'), false);
            $record[] = $grades;

            if (is_template_admin()) {
                // Show user
                $record[] = format_string(fullname($backupcontroller->get_createuser()));
            }

            $actions = '';

            $gradeslink = $backupcontroller->get_grades_link();
            if (!empty($gradeslink)) {
                $actions .= template_icon_link('grades', $gradeslink);
            }

            // add, edit, hide, show, moveup, movedown, delete
            $actions .= template_icon_link('edit', self::path(), ['action' => 'editbackupcontroller', 'templateid' => $templateid, 'backupcontrollerid' => $backupcontroller->get('id')]);

            if (is_template_admin()) {
                if ($backupcontroller->get('hidden')) {
                    $actions .= template_icon_link('show', self::path(), ['action' => 'showbackupcontroller', 'templateid' => $templateid, 'backupcontrollerid' => $backupcontroller->get('id')]);
                } else {
                    $actions .= template_icon_link('hide', self::path(), ['action' => 'hidebackupcontroller', 'templateid' => $templateid, 'backupcontrollerid' => $backupcontroller->get('id')]);
                }
                $actions .= template_icon_link('delete', self::path(), ['action' => 'deletebackupcontroller', 'templateid' => $templateid, 'backupcontrollerid' => $backupcontroller->get('id'), 'sesskey' => sesskey()]);
            } else {
                $actions .= template_icon_link('delete', self::path(), ['action' => 'hidebackupcontroller', 'templateid' => $templateid, 'backupcontrollerid' => $backupcontroller->get('id')]);
            }

            $actions .= template_icon_link('go', self::path(), ['action' => 'runbackupcontroller', 'templateid' => $templateid, 'backupcontrollerid' => $backupcontroller->get('id')]);

            $record[] = $actions;

            // Append this backupcontroller to records for output
            $records[] = $record;
        }

        if ($view == 'table' || $view == 'header' && $hasparent) {
            $output = local_template_create_action_table($records, $headings, $headingalignment, $norecordslangstring, $addrecordlangstring, $addnewiconlink, $containsactions);
        } else {
            $output = local_template_create_action_list($records, $headings, $headingalignment, $norecordslangstring, $addrecordlangstring, $addnewiconlink, $containsactions);
        }
        return $output;
    }

    // TODO: Use static bootstrap progress bar for status field
    public static function progress(models\backupcontroller $backupcontroller) {

        $numrecords = $backupcontroller->get_template()->get('numrecords');
        $recordsprocessed = $backupcontroller->get('recordsprocessed');

        $content = '';
        $content .= \html_writer::start_tag('div', array('class' => 'container'));
        $content .= \html_writer::start_tag('div', array('class' => 'progress'));
        $content .= self::progresslevel($backupcontroller->get('recordsinfo'), $numrecords, 'info');
        $content .= self::progresslevel($backupcontroller->get('recordssuccess'), $numrecords, 'success');
        $content .= self::progresslevel($backupcontroller->get('recordswarning'), $numrecords, 'warning');
        $content .= self::progresslevel($backupcontroller->get('recordserror'), $numrecords, 'danger');
        $content .= \html_writer::end_tag('div');

        /*
        $content .= \html_writer::start_tag('dl');
        $content .= \html_writer::tag('dt', 'Info') . \html_writer::tag('dd', $backupcontroller->get('recordsinfo'));
        $content .= \html_writer::tag('dt', 'Success') . \html_writer::tag('dd', $backupcontroller->get('recordssuccess'));
        $content .= \html_writer::tag('dt', 'Warning') . \html_writer::tag('dd', $backupcontroller->get('recordswarning'));
        $content .= \html_writer::tag('dt', 'Error') . \html_writer::tag('dd', $backupcontroller->get('recordserror'));
        $content .= \html_writer::end_tag('dl');
        $content .= \html_writer::start_tag('div', array('class' => 'd-flex justify-content-between'));
        */

        //$content .= \html_writer::tag('span', 'Info: ' . $backupcontroller->get('recordsinfo'));
        //$content .= \html_writer::tag('span', 'Success: ' . $backupcontroller->get('recordssuccess'));
        //$content .= \html_writer::tag('span', 'Warning: ' . $backupcontroller->get('recordswarning'));
        //$content .= \html_writer::tag('span', 'Error: ' . $backupcontroller->get('recordserror'));
        $content .= \html_writer::tag('span', $recordsprocessed . ' of ' . $numrecords);
        //$content .= \html_writer::end_tag('div');
        $content .= \html_writer::end_tag('div');

        //.progress .progress-bar

        return $content;
    }

    private static function progresslevel($number, $total, $class) {

        // Avoid division by zero.
        if ($total == 0) {
            $percentage = 0;
        } else {
            $percentage = round($number / $total * 100);
        }

        return \html_writer::tag('div', '<span>' . $number . '</span>', [
            'class' => 'progress-bar bg-' . $class . ' position-relative',
            //'class' => 'progress-bar progress-bar-' . $class,
            'role' => 'progressbar',
            'style' => 'width:' . $percentage .'%',
            //'aria-valuenow' => $percentage,
            //'aria-valuemin' => 0,
            //'aria-valuemax' => 100
        ]);
    }

}
