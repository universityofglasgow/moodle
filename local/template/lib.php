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
 * Template lib
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// require_once(__DIR__ . '/locallib.php');
// add_new_course_hook();


function is_template_admin() {
    // return false;
    return has_capability('local/template:managetemplate', \context_system::instance());
}

function template_admin() {
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

function enforce_template_security($requiremanagement = false) {
    require_login(null, false);
    if (isguestuser()) {
        redirect('/login/index.php');
    }

    // Require Management
    if ($requiremanagement) {
        require_capability('local/template:managetemplate', context_system::instance());
    }

    $usetemplate = has_capability('local/template:usetemplate', \context_system::instance());
    $managetemplate = has_capability('local/template:managetemplate', \context_system::instance());

    // If we cannot use template.
    if (!$usetemplate && !$managetemplate) {
        global $OUTPUT;
        echo $OUTPUT->header();
        echo get_string('noaccess', 'local_template');

        //set session variable and redirect.
        echo $OUTPUT->footer();
        die();
    }
}



/**
 *
 * @param string $pagingtype The paging type. One of ['templatepage', 'backupcontrollerpage']
 * @param int|null $templateparent If retrieving paging with a template parent, specify here.
 * @param int|null $backupcontrollerparent If retrieving paging with a backupcontroller parent, speficiy here.
 * @return int The requested paging
 * @throws coding_exception
 */
function template_get_paging(string $pagingtype, int $templateparent = null, int $backupcontrollerparent = null) {
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
function template_icon_link(string $type, $url, array $urlparams = null) {
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

    return html_writer::link($url, $pixicon, ['title' => $label, 'class' => $class]);
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
function local_template_create_action_table(array $records, array $headings, array $headingalignments, string $norecordslangstring = '', string $addrecordlangstring = '', string $addnewiconlink = '', bool $containsactions = false) {
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
function local_template_create_action_collapse(array $records, array $headings, array $headingalignments, string $norecordslangstring = '', string $addrecordlangstring = '', string $addnewiconlink = '', bool $containsactions = false) {
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
function local_template_create_action_list(array $records, array $headings, array $headingalignments, string $norecordslangstring = '', string $addrecordlangstring = '', string $addnewiconlink = '', bool $containsactions = false) {
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



/**
 * Serve the files from the local_template file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function local_template_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    // Should not be in a course context.
    if ($course != null) {
        return false;
    }

    // Should not be in a mod context.
    if ($cm != null) {
        return false;
    }

    // Should be in a system context.
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    // Should be import or export file area only
    if (!($filearea == 'import' || $filearea == 'export')) {
        return false;
    }

    // Enforce login and capability checks.
    enforce_template_security();

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Managetemplate users have access to all files.
    if (!is_template_admin()) {
        if ($filearea == 'import') {
            $template = new \local_template\models\template($itemid);
            global $USER;

            // Enforce template userid is correct file userid.
            if ($USER->id != $template->get('usercreated')) {
                return false;
            }
        }
        if ($filearea == 'export') {
            $template = new \local_template\models\backupcontroller($itemid);
            global $USER;
            // Enforce template userid is correct file userid.
            if ($USER->id != $template->get('usercreated')) {
                return false;
            }
        }

    }

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_template', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}




function test_dave() {

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
     * Edit course settings
     *
     * @package    core_course
     * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */

    require_once('../config.php');
    require_once('lib.php');
    require_once('edit_form.php');

    $id = optional_param('id', 0, PARAM_INT); // Course id.
    $categoryid = optional_param('category', 0, PARAM_INT); // Course category - can be changed in edit form.
    $returnto = optional_param('returnto', 0, PARAM_ALPHANUM); // Generic navigation return page switch.
    $returnurl = optional_param('returnurl', '', PARAM_LOCALURL); // A return URL. returnto must also be set to 'url'.

    if ($returnto === 'url' && confirm_sesskey() && $returnurl) {
        // If returnto is 'url' then $returnurl may be used as the destination to return to after saving or cancelling.
        // Sesskey must be specified, and would be set by the form anyway.
        $returnurl = new moodle_url($returnurl);
    } else {
        if (!empty($id)) {
            $returnurl = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $id));
        } else {
            $returnurl = new moodle_url($CFG->wwwroot . '/course/');
        }
        if ($returnto !== 0) {
            switch ($returnto) {
                case 'category':
                    $returnurl = new moodle_url($CFG->wwwroot . '/course/index.php', array('categoryid' => $categoryid));
                    break;
                case 'catmanage':
                    $returnurl = new moodle_url($CFG->wwwroot . '/course/management.php', array('categoryid' => $categoryid));
                    break;
                case 'topcatmanage':
                    $returnurl = new moodle_url($CFG->wwwroot . '/course/management.php');
                    break;
                case 'topcat':
                    $returnurl = new moodle_url($CFG->wwwroot . '/course/');
                    break;
                case 'pending':
                    $returnurl = new moodle_url($CFG->wwwroot . '/course/pending.php');
                    break;
            }
        }
    }

    $PAGE->set_pagelayout('admin');
    if ($id) {
        $pageparams = array('id' => $id);
    } else {
        $pageparams = array('category' => $categoryid);
    }
    if ($returnto !== 0) {
        $pageparams['returnto'] = $returnto;
        if ($returnto === 'url' && $returnurl) {
            $pageparams['returnurl'] = $returnurl;
        }
    }
    $PAGE->set_url('/course/edit.php', $pageparams);

// Basic access control checks.
    if ($id) {
        // Editing course.
        if ($id == SITEID) {
            // Don't allow editing of  'site course' using this from.
            throw new \moodle_exception('cannoteditsiteform');
        }

        // Login to the course and retrieve also all fields defined by course format.
        $course = get_course($id);
        require_login($course);
        $course = course_get_format($course)->get_course();

        $category = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);
        $coursecontext = context_course::instance($course->id);
        require_capability('moodle/course:update', $coursecontext);

    } else if ($categoryid) {
        // Creating new course in this category.
        $course = null;
        require_login();
        $category = $DB->get_record('course_categories', array('id' => $categoryid), '*', MUST_EXIST);
        $catcontext = context_coursecat::instance($category->id);
        require_capability('moodle/course:create', $catcontext);
        $PAGE->set_context($catcontext);

    } else {
        // Creating new course in default category.
        $course = null;
        require_login();
        $category = core_course_category::get_default();
        $catcontext = context_coursecat::instance($category->id);
        require_capability('moodle/course:create', $catcontext);
        $PAGE->set_context($catcontext);
    }

// We are adding a new course and have a category context.
    if (isset($catcontext)) {
        $PAGE->set_secondary_active_tab('categorymain');
    }

// Prepare course and the editor.
    $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
    $overviewfilesoptions = course_overviewfiles_options($course);
    if (!empty($course)) {
        // Add context for editor.
        $editoroptions['context'] = $coursecontext;
        $editoroptions['subdirs'] = file_area_contains_subdirs($coursecontext, 'course', 'summary', 0);
        $course = file_prepare_standard_editor($course, 'summary', $editoroptions, $coursecontext, 'course', 'summary', 0);
        if ($overviewfilesoptions) {
            file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, $coursecontext, 'course', 'overviewfiles', 0);
        }

        // Inject current aliases.
        $aliases = $DB->get_records('role_names', array('contextid' => $coursecontext->id));
        foreach ($aliases as $alias) {
            $course->{'role_' . $alias->roleid} = $alias->name;
        }

        // Populate course tags.
        $course->tags = core_tag_tag::get_item_tags_array('core', 'course', $course->id);

    } else {
        // Editor should respect category context if course context is not set.
        $editoroptions['context'] = $catcontext;
        $editoroptions['subdirs'] = 0;
        $course = file_prepare_standard_editor($course, 'summary', $editoroptions, null, 'course', 'summary', null);
        if ($overviewfilesoptions) {
            file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, null, 'course', 'overviewfiles', 0);
        }
    }

// First create the form.
    $args = array(
        'course' => $course,
        'category' => $category,
        'editoroptions' => $editoroptions,
        'returnto' => $returnto,
        'returnurl' => $returnurl
    );
    $editform = new course_edit_form(null, $args);
    if ($editform->is_cancelled()) {
        // The form has been cancelled, take them back to what ever the return to is.
        redirect($returnurl);
    } else if ($data = $editform->get_data()) {
        // Process data if submitted.
        if (empty($course->id)) {
            // In creating the course.
            $course = create_course($data, $editoroptions);

            // Get the context of the newly created course.
            $context = context_course::instance($course->id, MUST_EXIST);

            // Admins have all capabilities, so is_viewing is returning true for admins.
            // We are checking 'enroladminnewcourse' setting to decide to enrol them or not.
            if (is_siteadmin($USER->id)) {
                $enroluser = $CFG->enroladminnewcourse;
            } else {
                $enroluser = !is_viewing($context, null, 'moodle/role:assign');
            }

            if (!empty($CFG->creatornewroleid) and $enroluser and !is_enrolled($context, null, 'moodle/role:assign')) {
                // Deal with course creators - enrol them internally with default role.
                // Note: This does not respect capabilities, the creator will be assigned the default role.
                // This is an expected behaviour. See MDL-66683 for further details.
                enrol_try_internal_enrol($course->id, $USER->id, $CFG->creatornewroleid);
            }

            // The URL to take them to if they chose save and display.
            $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
        } else {
            // Save any changes to the files used in the editor.
            update_course($data, $editoroptions);
            // Set the URL to take them too if they choose save and display.
            $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
        }

        if (isset($data->saveanddisplay)) {
            // Redirect user to newly created/updated course.
            redirect($courseurl);
        } else {
            // Save and return. Take them back to wherever.
            redirect($returnurl);
        }
    }

// Print the form.

    $site = get_site();

    $streditcoursesettings = get_string("editcoursesettings");
    $straddnewcourse = get_string("addnewcourse");
    $stradministration = get_string("administration");
    $strcategories = get_string("categories");

    if (!empty($course->id)) {
        // Navigation note: The user is editing a course, the course will exist within the navigation and settings.
        // The navigation will automatically find the Edit settings page under course navigation.
        $pagedesc = $streditcoursesettings;
        $title = $streditcoursesettings;
        $fullname = $course->fullname;
    } else {
        // The user is adding a course, this page isn't presented in the site navigation/admin.
        // Adding a new course is part of course category management territory.
        // We'd prefer to use the management interface URL without args.
        $managementurl = new moodle_url('/course/management.php');
        // These are the caps required in order to see the management interface.
        $managementcaps = array('moodle/category:manage', 'moodle/course:create');
        if ($categoryid && !has_any_capability($managementcaps, context_system::instance())) {
            // If the user doesn't have either manage caps then they can only manage within the given category.
            $managementurl->param('categoryid', $categoryid);
        }
        // Because the course category interfaces are buried in the admin tree and that is loaded by ajax
        // we need to manually tell the navigation we need it loaded. The second arg does this.
        navigation_node::override_active_url(new moodle_url('/course/index.php', ['categoryid' => $category->id]), true);
        $PAGE->set_primary_active_tab('home');
        $PAGE->navbar->add(get_string('coursemgmt', 'admin'), $managementurl);

        $pagedesc = $straddnewcourse;
        $title = "$site->shortname: $straddnewcourse";
        $fullname = format_string($category->name);
        $PAGE->navbar->add($pagedesc);
    }

    $PAGE->set_title($title);
    $PAGE->add_body_class('limitedwidth');
    $PAGE->set_heading($fullname);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($pagedesc);

    $editform->display();

    echo $OUTPUT->footer();

}