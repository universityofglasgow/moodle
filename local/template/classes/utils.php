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
 * Util functions for local template
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_template;

use moodle_url;
use coding_exception;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Util functions for local template
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils  {

    static function is_admin() {
        // return false;
        return has_capability('local/template:managetemplate', \context_system::instance());
    }

    static function is_admin_page() {
        global $PAGE;
        if ($PAGE->title == get_string('pluginname', 'local_template')) {
            return false;
        }
        if ($PAGE->title == get_string('templateadmin', 'local_template')) {
            return true;
        }
    }

    static function admin() {
        global $OUTPUT;
        $data = [
            'links' => [
                ['link' => new \moodle_url('/local/template/admin/templates.php'), 'string' => get_string('templates', 'local_template')],
                ['link' => new \moodle_url('/local/template/admin/backupcontrollers.php'), 'string' => get_string('backupcontrollers', 'local_template')],
                ['link' => new \moodle_url('/admin/settings.php', ['section' => 'local_template']), 'string' => get_string('settings', 'local_template')],
                ['link' => new \moodle_url('/local/template/index.php'), 'string' => get_string('dashboard', 'local_template')],
            ]
        ];
        return $OUTPUT->render_from_template('local_template/admin', $data);
    }

    static function enforce_security($requiremanagement = false) {
        require_login(null, false);
        if (isguestuser()) {
            redirect('/login/index.php');
        }

        // Require Management
        if ($requiremanagement) {
            require_capability('local/template:managetemplate', \context_system::instance());
        }

        $canusetemplates = self::user_can_use_templates();
        $managetemplate = has_capability('local/template:managetemplate', \context_system::instance());

        // If we cannot use template.
        if (!$canusetemplates && !$managetemplate) {
            global $OUTPUT;
            echo $OUTPUT->header();
            echo get_string('noaccess', 'local_template');

            //set session variable and redirect.
            echo $OUTPUT->footer();
            die();
        }
    }

    /**
     * @global object
     * @uses CONTEXT_COURSECAT
     * @return boolean Whether the user can use templates in any category in the system.
     */
    static function user_can_use_templates() {
        global $DB;
        $categories = $DB->get_recordset('course_categories');
        foreach ($categories as $category) {
            if (has_capability('local/template:usetemplate', \context_coursecat::instance($category->id))) {
                $categories->close();
                return true;
            }
        }
        $categories->close();
        return false;
    }

    /**
     *
     * @param string $pagingtype The paging type. One of ['templatepage', 'backupcontrollerpage']
     * @param int|null $templateparent If retrieving paging with a template parent, specify here.
     * @param int|null $backupcontrollerparent If retrieving paging with a backupcontroller parent, speficiy here.
     * @return int The requested paging
     * @throws coding_exception
     */
    static function get_paging(string $pagingtype, int $templateparent = null, int $backupcontrollerparent = null) {
        global $SESSION;

        $page = 0;

        if (!object_property_exists($SESSION, 'local_template_paging')) {
            $SESSION->local_template_paging = [];
        }

        $retrieve = true;
        if (!isset($_POST[$pagingtype]) && !isset($_GET[$pagingtype])) {
            $retrieve = false;
        }
        $optionalparam = optional_param($pagingtype, 0, PARAM_INT);

        // Must check $POST and $GET here because optional_param always returns default if post/get not present.

        // Param isn't being explicitly set for this page load, check session variable.

        if ($pagingtype == 'templatepage') {
            if ($retrieve && array_key_exists($pagingtype, $SESSION->local_template_paging)) {
                // Field present. Retrieve.
                $page = $SESSION->local_template_paging[$pagingtype];
            } else {
                // Field not present. Set.
                $page = $optionalparam;
                $SESSION->local_template_paging[$pagingtype] = $page;
            }
        }
        if ($pagingtype == 'backupcontrollerspage') {
            if (isset($templateparent)) {
                if ($retrieve && array_key_exists($templateparent, $SESSION->local_template_paging) &&
                    array_key_exists($pagingtype, $SESSION->local_template_paging[$templateparent])) {
                    // Field present. Retrieve.
                    $page = $SESSION->local_template_paging[$templateparent][$pagingtype];
                } else {
                    // Field not present. Set.
                    $page = $optionalparam;
                    $SESSION->local_template_paging[$templateparent][$pagingtype] = $page;
                }
            } else {
                // No parent. General paging.
                if ($retrieve && array_key_exists($pagingtype, $SESSION->local_template_paging)) {
                    // Field present. Retrieve.
                    $page = $SESSION->local_template_paging[$pagingtype];
                } else {
                    // Field not present. Set.
                    $page = $optionalparam;
                    $SESSION->local_template_paging[$pagingtype] = $page;
                }
            }
        }
        if ($pagingtype == 'backupcontrollergradespage' || $pagingtype == 'backupcontrollerrowspage') {
            if (isset($templateparent) && isset($backupcontrollersparent)) {
                if ($retrieve && array_key_exists($templateparent, $SESSION->local_template_paging) &&
                    array_key_exists($backupcontrollersparent, $SESSION->local_template_paging[$templateparent]) &&
                    array_key_exists($pagingtype, $SESSION->local_template_paging[$templateparent][$backupcontrollersparent])) {

                    // Field present. Retrieve.
                    $page = $SESSION->local_template_paging[$templateparent][$backupcontrollersparent][$pagingtype];
                } else {
                    // Field not present. Set.
                    $page = $optionalparam;
                    $SESSION->local_template_paging[$templateparent][$backupcontrollersparent][$pagingtype] = $page;
                }
            } else {
                // No parent. General paging.
                if ($retrieve && array_key_exists($pagingtype, $SESSION->local_template_paging)) {
                    // Field present. Retrieve.
                    $page = $SESSION->local_template_paging[$pagingtype];
                } else {
                    // Field not present. Set.
                    $page = $optionalparam;
                    $SESSION->local_template_paging[$pagingtype] = $page;
                }
            }
        }
        return $page;
    }

    /**
     * @param string $type One of: [add, edit, hide, show, moveup, movedown, delete, go]
     * @param string|moodle_url $url page local to '/local/template/'
     * @param array $urlparams params for moodle_url
     * @return string The generated HTML
     * @throws coding_exception
     * @throws moodle_exception
     */
    static function icon_link(string $type, $url, array $urlparams = null) {
        global $OUTPUT;
        if (is_string($url)) {
            $url = new moodle_url($url, $urlparams);
        }

        $label = get_string($type);
        $iconcode = 't/' . $type;
        $pixicon = $OUTPUT->pix_icon($iconcode, $label);

        $class = '';
        if ($type == 'delete') {
            $class .= 'text-danger';
        }
        if ($type == 'go') {
            $class .= 'text-success';
        }

        return \html_writer::link($url, $pixicon, ['title' => $label, 'class' => $class]);
    }

    /**
     * @param array $records records for display
     * @param array $headings heading language string codes
     * @param array $headingalignments e.g. left left left left right
     * @param string $norecordslangstring language string code when no records exist
     * @param string $addnewiconlink template icon link or ''
     * @param bool $containsactions whether records contain actions
     * @return string generated table or error notification if norecordslangstring is present
     * @throws coding_exception
     */
    static function local_template_create_action_table(array $records, array $headings, array $headingalignments, string $norecordslangstring = '', string $addrecordlangstring = '', string $addnewiconlink = '', bool $containsactions = false) {
        global $OUTPUT;

        $table = new \html_table();

        $headerrow = [];
        foreach ($headings as $heading) {
            $headerrow[] = get_string($heading, 'local_template');
        }

        if (!empty($addnewiconlink) || !empty($containsactions)) {
            $actionheading = get_string('edit');

            if (!empty($addnewiconlink)) {
                $actionheading .= $OUTPUT->spacer() . $addnewiconlink;
            }

            // Add the actions column to the headings.
            if (!empty($headings)) {
                $headings[] = 'edit';
                $headerrow[] = $actionheading;
            }
        }

        $table->head = $headerrow;
        $table->align = $headingalignments;
        $table->attributes['class'] = 'generaltable';
        $table->data = $records;

        $add = '';
        if (!empty($addnewiconlink)) {
            $add = $OUTPUT->spacer() . get_string($addrecordlangstring, 'local_template') . $addnewiconlink;
        }

        if (count($table->data)) {
            return \html_writer::table($table) . $add;
        } else {
            if (!empty($norecordslangstring)) {
                return $OUTPUT->notification(get_string($norecordslangstring, 'local_template') . $add);
            }
        }

        return '';
    }

    /**
     * @param array $records records for display
     * @param array $headings heading language string codes
     * @param array $headingalignments e.g. left left left left right
     * @param string $norecordslangstring language string code when no records exist
     * @param string $addnewiconlink template icon link or ''
     * @param bool $containsactions whether records contain actions
     * @return string generated table or error notification if norecordslangstring is present
     * @throws coding_exception
     */
    static function local_template_create_action_collapse(array $records, array $headings, array $headingalignments, string $norecordslangstring = '', string $addrecordlangstring = '', string $addnewiconlink = '', bool $containsactions = false) {
        global $OUTPUT;

        if (!empty($addnewiconlink) || !empty($containsactions)) {
            $actionheading = get_string('edit');

            if (!empty($addnewiconlink)) {
                $actionheading .= $OUTPUT->spacer() . $addnewiconlink;
            }

            // Add the actions column to the headings.
            if (!empty($headings)) {
                $headings[] = 'edit';
                $headingalignments[] = 'left';
            }
        }

        $accordion = 'accordion-job-1';
        $output = \html_writer::start_tag('div', ['id' => $accordion]);
        foreach ($records as $row => $record) {
            $headingid = $accordion . '-heading-' . $row;
            $collapseid = $accordion . '-collapse-' . $row;
            $output .= '
            <div class="card">
                <div class="card-header" id="' . $headingid . '">
                  <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#' . $collapseid . '" aria-expanded="true" aria-controls="' . $collapseid . '">
                        ' . $collapseid . '
                    </button>
                  </h5>
                </div>
                <div id="' . $collapseid . '" class="collapse show" aria-labelledby="' . $headingid . '" data-parent="#' . $accordion . '">
                <div class="card-body">
      ';

            foreach ($record as $index => $cell) {
                $heading = get_string($headings[$index], 'local_template');

                $output .= \html_writer::tag('dt', $heading, ['class' => 'text-' . $headingalignments[$index]]);
                $output .= \html_writer::tag('dd', $cell, ['class' => 'text-' . $headingalignments[$index]]);
            }
            $output .= '
                </div>
            </div>
        </div>';
        }
        $output .= \html_writer::end_tag('div');

        $add = '';
        if (!empty($addnewiconlink)) {
            $add = $OUTPUT->spacer() . get_string($addrecordlangstring, 'local_template') . $addnewiconlink;
        }

        if (count($records)) {
            return $output . $add;
        } else {
            if (!empty($norecordslangstring)) {
                return $OUTPUT->notification(get_string($norecordslangstring, 'local_template') . $add);
            }
        }

        return '';
    }

    /**
     * @param array $records records for display
     * @param array $headings heading language string codes
     * @param array $headingalignments e.g. left left left left right
     * @param string $norecordslangstring language string code when no records exist
     * @param string $addnewiconlink template icon link or ''
     * @param bool $containsactions whether records contain actions
     * @return string generated table or error notification if norecordslangstring is present
     * @throws coding_exception
     */
    static function local_template_create_action_list(array $records, array $headings, array $headingalignments, string $norecordslangstring = '', string $addrecordlangstring = '', string $addnewiconlink = '', bool $containsactions = false) {
        global $OUTPUT;

        // https://getbootstrap.com/docs/4.0/components/collapse/

        if (!empty($addnewiconlink) || !empty($containsactions)) {
            $actionheading = get_string('edit');

            if (!empty($addnewiconlink)) {
                $actionheading .= $OUTPUT->spacer() . $addnewiconlink;
            }

            // Add the actions column to the headings.
            if (!empty($headings)) {
                $headings[] = 'edit';
                $headingalignments[] = 'left';
            }
        }

        $output = \html_writer::start_tag('dl');
        foreach ($records as $row => $record) {
            foreach ($record as $index => $cell) {
                $heading = get_string($headings[$index], 'local_template');
                $output .= \html_writer::tag('dt', $heading, ['class' => 'text-' . $headingalignments[$index]]);
                $output .= \html_writer::tag('dd', $cell, ['class' => 'text-' . $headingalignments[$index]]);
            }
        }
        $output .= \html_writer::end_tag('dl');

        $add = '';
        if (!empty($addnewiconlink)) {
            $add = $OUTPUT->spacer() . get_string($addrecordlangstring, 'local_template') . $addnewiconlink;
        }

        if (count($records)) {
            return $output . $add;
        } else {
            if (!empty($norecordslangstring)) {
                return $OUTPUT->notification(get_string($norecordslangstring, 'local_template') . $add);
            }
        }

        return '';
    }

}

