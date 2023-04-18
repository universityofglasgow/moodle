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
 * template Controller
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_template\controllers;

use core_privacy\local\metadata\collection;
use local_template\models;
use local_template\forms;
use core\notification;
use moodle_url;
use paging_bar;

defined('MOODLE_INTERNAL') || die();

class template {

    private static function context() {
        return \context_system::instance()->id;
    }

    public static function path() {
        global $CFG;
        if (is_template_admin()) {
            return $CFG->wwwroot . '/local/template/admin/templates.php';
        } else {
            return $CFG->wwwroot . '/local/template/index.php';
        }
    }

    private static function do_redirect() {
        redirect(self::path());
    }

    public static function set_view($view) {
        global $SESSION;
        switch($view) {
            case 'list':
                $SESSION->local_template_view = 'list';
                notification::info('View changed to ' . $view);
                break;
            case 'header':
                $SESSION->local_template_view = 'header';
                notification::info('View changed to ' . $view);
                break;
            case 'table':
                $SESSION->local_template_view = 'table';
                notification::info('View changed to ' . $view);
                break;
            default:
                $SESSION->local_template_view = 'table';
                notification::info('Could not set view to ' . $view);
                break;
        }

        $redirect = self::path();
        //if (!empty($SESSION->local_template_url)) {
        //$redirect = $SESSION->local_template_url;
        //}

        redirect($redirect);
    }

    public static function view_buttons() {
        global $SESSION, $PAGE, $OUTPUT;

        //$SESSION->local_template_url = $PAGE->url;
        //notification::info('sesssion local_template_url: ' . $SESSION->local_template_url);

        if (property_exists($SESSION, 'local_template_view')) {
            //notification::info('sesssion local_template_view: ' . $SESSION->local_template_view);
        }

        $view = 'table';
        if (empty($SESSION->local_template_view)) {
            $SESSION->local_template_view = 'table';
        } else {
            $view = $SESSION->local_template_view;
        }

        $tableviewlink = \html_writer::tag('i','', ['class' => 'icon fa fa-th fa-fw', 'title' => 'Table', 'role' => 'img', 'aria-label' => 'Table']);
        if ($view != 'table') {
            $tableviewlink = \html_writer::link(new \moodle_url(self::path(), ['action' => 'setview', 'view' => 'table']), $tableviewlink, ['title' => 'Table']);
        }
        $headerviewlink = \html_writer::tag('i','', ['class' => 'icon fa fa-sitemap fa-fw', 'title' => 'Header', 'role' => 'img', 'aria-label' => 'Header']);
        if ($view != 'header') {
            $headerviewlink = \html_writer::link(new \moodle_url(self::path(), ['action' => 'setview', 'view' => 'header']), $headerviewlink, ['title' => 'Header']);
        }
        $listviewlink = \html_writer::tag('i','', ['class' => 'icon fa fa-list fa-fw', 'title' => 'List', 'role' => 'img', 'aria-label' => 'List']);
        if ($view != 'list') {
            $listviewlink = \html_writer::link(new \moodle_url(self::path(), ['action' => 'setview', 'view' => 'list']), $listviewlink, ['title' => 'List']);
        }

        return $tableviewlink . $headerviewlink . $listviewlink;

    }

    public static function view($id) {
        global $OUTPUT, $PAGE;
        $template = new models\template($id);
        $record = $template->read();

        $customdata = [
            'id' => $id,
            'persistent' => $template,
            'action' => 'viewtemplate',
        ];

        $name = shorten_text(format_string($record->get('name')));

        $form = new forms\template($PAGE->url->out(false), $customdata, 'post', '', null, false);
        // Print the page.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('viewtemplate', 'local_template', $name));
        $form->display();
        echo \html_writer::start_tag('div', ['class' => 'container text-center align-center']);
        echo \html_writer::link(new \moodle_url(self::path(), ['action' => 'edittemplate', 'templateid' => $id]), get_string('edittemplate', 'local_template', $name),['class' => 'btn btn-primary']);
        echo $OUTPUT->spacer();
        echo \html_writer::link(new \moodle_url(self::path()), get_string('cancel'), ['class' => 'btn btn-secondary']);
        echo \html_writer::end_tag('div');
        echo $OUTPUT->footer();
        die;
    }

    public static function display($id, $form=null, $admin = false) {
        global $OUTPUT, $PAGE;

        // Are we 'creating' or 'editing'?
        $template = null;

        if (empty($id)) {
            $strheading = get_string('createnewtemplate', 'local_template');
        } else {
            $template = new models\template($id);
            $record = $template->read();
            $strheading = get_string('edittemplate', 'local_template', shorten_text(format_string($record->get('fullname'))));
        }

        // Initialise a form object if we haven't been provided with one.
        if ($form == null) {

            $customdata = [
                'id' => $id,
                'persistent' => $template,
                'action' => 'edittemplate',
                'admin' => $admin
            ];

            // Constructor calls set_data.
            $form = new forms\template($PAGE->url->out(false), $customdata);

        }

        if ($form->is_cancelled()) {
            self::do_redirect();
        }

        global $CFG;
        // Include CSS.
        $PAGE->requires->css(new moodle_url("https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css"));
        $PAGE->requires->css(new moodle_url($CFG->wwwroot . "/local/template/slick/slick.css"));
        $PAGE->requires->css(new moodle_url($CFG->wwwroot . "/local/template/slick/slick-theme.css"));
        $PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/template/styles.css'));
        $PAGE->requires->jquery();

        // <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
        //$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/template/slick/slick.js'));
        //$PAGE->requires->js(new moodle_url('https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js'));
        //$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/local/template/js/main.js'));

        // $PAGE->requires->js('/mod/namemodule/socket.io.js',true);


        // Print the page.
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strheading);

        global $PAGE;
        $renderer = $PAGE->get_renderer('local_template');

        $form->display();

        // echo $renderer->render_stepper(\local_template\forms\template::STEPPER_JAVASCRIPT, []);

        echo '<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js" type="text/javascript" charset="utf-8"></script>';
        echo '<script src="' . $CFG->wwwroot . '/local/template/js/main.js" type="text/javascript" charset="utf-8"></script>';
        echo '<script src="' . $CFG->wwwroot . '/local/template/slick/slick.js" type="text/javascript" charset="utf-8"></script>';
        echo $OUTPUT->footer();



        //

        die;
    }

    public static function process($id, $admin = false) {
        global $PAGE, $USER;

        $template = null;
        $customdata = [
            'persistent' => $template,
            'id' => $id,
            'action' => 'edittemplate',
            'admin' => $admin,
        ];
        if (!empty($id)) {
            $template = new models\template($id);
            $customdata['persistent'] = $template;

        }

        $form = new forms\template($PAGE->url->out(false), $customdata);
        $data = $form->get_data();

        if ($data) {

            unset($data->action);
            $process = false;
            $redirect = false;
            if (isset($data->createandredirect)) {
                $process = true;
                $redirect = true;
                unset($data->createandredirect);
            }
            if (isset($data->createcourse)) {
                $process = true;
                unset($data->createcourse);
            }
            unset($data->savetemplate);


            try {
                $data->usercreated = $USER->id;

                $templatetext = $data->fullname;

                $template = null;
                if (empty($data->id)) {
                    $template = new models\template(0, $data);
                    if ($template->create()) {
                        notification::success("Course wizard: $templatetext successfully created");
                    } else {
                        notification::error("Could not create course wizard: $templatetext");
                    }

                } else {
                    $template = new models\template();
                    $template->from_record($data);
                    if ($template->update()) {
                        notification::success("Course wizard: $templatetext updated");
                    } else {
                        notification::error("Could not update course wizard: $templatetext");
                    }
                }

                if (is_template_admin() || $id == 0) {
                    /*
                    $data = file_postupdate_standard_filemanager($data, 'importfile', models\template::get_importfileoptions(), models\template::get_context(),
                        models\template::TABLE, models\template::FILEAREA_IMPORT, $template->get('id'));
                    */
                }

                //if (!$data->importfile) {
                //    notification::error("Could not save draft file for: $templatetext");
                //} else {
                    /*
                    $fs = get_file_storage();
                    $files = $fs->get_area_files(models\template::get_context()->id, models\template::TABLE, models\template::FILEAREA_IMPORT, $template->get('id'));
                    $filecount = 0;
                    $fileid = 0;
                    foreach ($files as $file) {
                        if ($file->get_filename() != '.') {
                            $filecount++;
                            $fileid = $file->get_id();
                        }
                    }
                    if ($filecount == 0) {
                        notification::error("No files found for template!");
                    }
                    if ($filecount > 1) {
                        notification::error("Multiple files found for template!");
                    }
                    if (!empty($fileid)) {
                        $template->set('importfileid', $fileid);
                        $template->save();
                    }
                    $template->preprocess();
                    */
               // }
                if ($process) {
                    if ($template->process()) {
                        notification::success("Course wizard: $templatetext successfully processed");
                    } else {
                        notification::error("Could not process course wizard: $templatetext");
                    }
                }
                if ($redirect) {
                    $template->redirect_coursepage();
                }

            } catch (\Exception $e) {
                notification::error($e->getMessage());
            }

            self::do_redirect();
        }
        self::display($id, $form);
    }

    public static function runtemplate($id) {

        global $OUTPUT;
        if (!empty($id)) {

            $template = new models\template($id);
            $result = $template->process();
            if (!$result) {
                // TODO for notifications
                // $template->notifications->output(true);
            } else {
                if ($template->get('recordsinfo') > 0) {
                    notification::info($template->get('recordsinfo') . ' rows generated information');
                }
                if ($template->get('recordssuccess') > 0) {
                    notification::success($template->get('recordssuccess') . ' rows generated success');
                }
                if ($template->get('recordswarning') > 0) {
                    notification::warning($template->get('recordswarning') . ' rows generated warnings');
                }
                if ($template->get('recordserror') > 0) {
                    notification::error($template->get('recordserror') . ' rows generated errors');
                }
            }
        }
        self::do_redirect();
        //$template->redirect_coursepage();
        //redirect(self::path());

    }

    public static function delete($id) {
        $template = new models\template($id);
        $name = get_string('missingtemplatename','local_template');
        if (models\template::has_property('name')) {
            $name = $template->get('name');
        }

        if (!empty($id)) {
            if (confirm_sesskey()) {
                if ($template->cascadedelete()) {
                    notification::success('template: ' . $name . ' deleted');
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
        global $SESSION, $OUTPUT;

        $view = 'table';
        if (object_property_exists($SESSION, 'local_template_view')) {
            $view = $SESSION->local_template_view;
        }
        $template = models\template::collection(0, $view);
        $pagingbar = $template->render_paging_bar(self::path());

        $buttons = self::view_buttons();

        // Create a new template link.
        $buttons .= template_icon_link('add', self::path(), ['action' => 'createtemplate', 'id' => '0']);
        // $buttons .= $OUTPUT->single_button(new \moodle_url(self::path(), array('action' => 'createtemplate')), get_string('createtemplate', 'local_template'));
        if (is_template_admin()) {
            $buttons .= \html_writer::link(new \moodle_url(self::path()), '<i class="fa fa-user-secret" title="Admin" role="img" aria-label="Admin"></i>', ['title' => 'Admin']);
        }

        // Print the header.
        echo $OUTPUT->header();
        if (is_template_admin()) {
            echo template_admin();
        }
        echo $OUTPUT->heading(get_string('template', 'local_template'));
        echo $confirm;
        // echo '<pre>' . var_export($SESSION->local_template_paging, true) . '</pre>';
        echo $buttons;
        echo get_string('templateintro', 'local_template');
        echo '<hr />';
        echo $pagingbar;
        echo $template->render();
        echo '<hr />';
        echo $buttons;
        echo $pagingbar;
        echo $OUTPUT->footer();
        die;

    }

    public static function rendertemplates($parentid = 0) {
        global $SESSION;
        $view = 'table';
        if (object_property_exists($SESSION, 'local_template_view')) {
            $view = $SESSION->local_template_view;
        }
        $templates = models\template::collection($parentid, $view);
        return $templates->render() . $templates->render_paging_bar(self::path());
    }

    private static function rendertemplate($parentid = 0) {
        global $SESSION;
        $view = 'table';
        if (object_property_exists($SESSION, 'local_template_view')) {
            $view = $SESSION->local_template_view;
        }
        $template = models\template::collection($parentid, $view);
        return $template->render() . $template->render_paging_bar(self::path());



        global $SESSION, $OUTPUT, $PAGE, $USER;
        $view = 'table';
        if (object_property_exists($SESSION, 'local_template_view')) {
            $view = $SESSION->local_template_view;
        }
        $headings = ['date', 'name', 'importfile'];
        $headingalignment = ['left', 'left', 'left'];
        $norecordslangstring = 'notemplatedefined';
        $addrecordlangstring = 'addtemplate';
        $addnewiconlink = template_icon_link('add', self::path(), ['action' => 'createtemplate', 'id' => '0']);
        $containsactions = true;

        if ($view == 'table') {
            $headings[] = 'backupcontrollers';
            $headingalignment[] = 'left';
        }

        $filters = [];
        if (is_template_admin()) {
            // Add usercreated column
            $headings[] = 'user';
            $headingalignment[] = 'left';
        } else {
            // Only show records for current user, and not hidden records.
            $filters['usercreated'] = $USER->id;
        }

        $records = [];
        $templatecollection = models\template::get_records($filters, 'timemodified', 'DESC');

        $output = '';
        foreach ($templatecollection as $template) {
            $record = [];

            $templatedate = userdate($template->get('timemodified'), get_string('strftimedatetimeshort', 'core_langconfig'));
            if (empty($templatedate)) {
                $templatedate = get_string('missingtemplatedate','local_template');
            }
            $record[] = format_string($templatedate);

            $templatename = $template->get('name');
            if (empty($templatename)) {
                $templatename = get_string('missingtemplatename','local_template');
            }
            $templatename = format_string($templatename);
            $templatename .= $OUTPUT->spacer() . template_icon_link('edit',self::path(), ['action' => 'edittemplate', 'templateid' => $template->get('id')]);

            $record[] = $templatename;

            $importfile = '';
            if (!empty($template->get('courses'))) {
                $importfile .= $template->get('courses') . '<br>';
            }
            if (!empty($template->get('assessments'))) {
                $importfile .= $template->get('assessments') . '<br>';
            }

            $file = $template->get_file();
            if (empty($file)) {
                $importfile .= get_string('missingfilename','local_template');
            } else {
                $importfile .= template_icon_link('download', $template->get_file_url());
            }
            $record[] = $importfile;

            if ($view == 'table') {
                $record[] = backupcontroller::renderbackupcontrollers($template->get('id'), false);
            }

            if (is_template_admin()) {
                // Show user
                $record[] = format_string(fullname($template->get_createuser()));
            }

            $actions = '';
            // add, edit, hide, show, moveup, movedown, delete
            $actions .= template_icon_link('edit',self::path(), ['action' => 'edittemplate', 'templateid' => $template->get('id')]);

            if (is_template_admin()) {
                if ($template->get('hidden')) {
                    $actions .= template_icon_link('show',self::path(), ['action' => 'showtemplate', 'templateid' => $template->get('id')]);
                } else {
                    $actions .= template_icon_link('hide',self::path(), ['action' => 'hidetemplate', 'templateid' => $template->get('id')]);
                }
                $actions .= template_icon_link('delete',self::path(), ['action' => 'deletetemplate', 'templateid' => $template->get('id'), 'sesskey' => sesskey()]);
            } else {
                $actions .= template_icon_link('delete',self::path(), ['action' => 'hidetemplate', 'templateid' => $template->get('id')]);
            }

            $record[] = $actions;
            $records[] = $record;
            $renderer = $PAGE->get_renderer('local_template');
            $output .= $renderer->render($template);
        }
        /*
        if ($view == 'table') {
            $output = create_action_table($records, $headings, $headingalignment, $norecordslangstring, $addrecordlangstring, $addnewiconlink, $containsactions);
        } else {
            if ($view == 'list') {
                $output = create_action_list($records, $headings, $headingalignment, $norecordslangstring, $addrecordlangstring, $addnewiconlink, $containsactions);
            } else {
                $output = create_action_collapse($records, $headings, $headingalignment, $norecordslangstring, $addrecordlangstring, $addnewiconlink, $containsactions);
            }
        }
        */
        // $output .= backupcontroller::renderbackupcontrollers$template->get('id'));

        return $output;
    }
}