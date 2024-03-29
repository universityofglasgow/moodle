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
use local_template\utils;

define(NO_OUTPUT_BUFFERING, true);

class backupcontroller {

    private static function context() {
        return \context_system::instance()->id;
    }

    public static function path() {
        global $CFG;
        if (utils::is_admin_page()) {
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

        $name = shorten_text(format_string($record->get_name()));

        $form = new forms\backupcontroller($PAGE->url->out(false), $customdata, 'post', '', null, false);
        // Print the page.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('viewbackupcontroller', 'local_template', $name));
        $form->display();
        echo \html_writer::start_tag('div', ['class' => 'container text-center align-center']);
        echo \html_writer::link(new \moodle_url(self::path(), ['action' => 'editbackupcontroller', 'backupcontrollerid' => $id]),
            get_string('editbackupcontroller', 'local_template', $name),['class' => 'btn btn-primary']);
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
            $strheading = get_string('editbackupcontroller', 'local_template', shorten_text(format_string($record->get_name())));
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

                $backupcontrollertext = $data->operation . ' of ' . $data->type . '(' . $data->itemid . ') - ' . $data->status;

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

    public static function runbackupcontroller($id) {

        global $OUTPUT;
        if (!empty($id)) {

            $backupcontroller = new models\backupcontroller($id);
            $result = $backupcontroller->process();
            if (!$result) {
                // $backupcontroller->notifications->output(true);
            } else {
                /*
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
                */
            }
        }
        self::do_redirect();
    }

    public static function delete($id) {
        $backupcontroller = new models\backupcontroller($id);
        $name = get_string('missingbackupcontrollername', 'local_template');
        $name = $backupcontroller->get_name();

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
        if (utils::is_admin()) {
            $buttons .= $OUTPUT->single_button(new \moodle_url(self::path()), get_string('admin', 'local_template'));
        }

        // Print the header.
        echo $OUTPUT->header();
        if (utils::is_admin()) {
            echo utils::admin();
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
        if (object_property_exists($SESSION, 'local_template_templateview')) {
            $view = $SESSION->local_template_templateview;
        }
        $backupcontrollers = models\backupcontroller::collection($parentid, $view);
        return $backupcontrollers->render() . $backupcontrollers->render_paging_bar(self::path());
    }

    public static function progress(models\backupcontroller $backupcontroller) {

        $numrecords = $backupcontroller->get_template()->get('numrecords');
        $recordsprocessed = $backupcontroller->get('recordsprocessed');

        $content = '';
        $content .= \html_writer::start_tag('div', array('class' => 'container'));
        $content .= \html_writer::start_tag('div', array('class' => 'progress'));
        //$content .= self::progresslevel($backupcontroller->get('recordsinfo'), $numrecords, 'info');
        //$content .= self::progresslevel($backupcontroller->get('recordssuccess'), $numrecords, 'success');
        //$content .= self::progresslevel($backupcontroller->get('recordswarning'), $numrecords, 'warning');
        //$content .= self::progresslevel($backupcontroller->get('recordserror'), $numrecords, 'danger');
        $content .= \html_writer::end_tag('div');
        $content .= \html_writer::tag('span', $recordsprocessed . ' of ' . $numrecords);
        $content .= \html_writer::end_tag('div');

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
            'role' => 'progressbar',
            'style' => 'width:' . $percentage .'%',
        ]);
    }

}
