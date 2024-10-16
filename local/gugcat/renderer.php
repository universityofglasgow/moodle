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
 * Renderer for gugcat
 *
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_gugcat_renderer extends plugin_renderer_base {

    /**
     * Render display of grade capture page
     * @param mixed $selectedmodule
     * @param array $activitiesarr
     * @param array $childactivitiesarr
     * @param array $rows
     * @param array $columns
     */
    public function display_grade_capture($selectedmodule, $activitiesarr, $childactivitiesarr, $rows, $columns) {
        global $SESSION;
        $SESSION->wantsurl = $this->page->url;
        $courseid = $this->page->course->id;
        $isblindmarking = local_gugcat::is_blind_marking($this->page->cm);
        $isconverted = ($selectedmodule) ? $selectedmodule->is_converted : false;
        $isimported = ($selectedmodule) ? $selectedmodule->isimported : false;
        $modid = (($selectedmodule) ? $selectedmodule->gradeitemid : null);
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $activityid = optional_param('activityid', null, PARAM_INT);
        $childactivityid = optional_param('childactivityid', null, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        if (!is_null($activityid)) {
            $modid = $activityid;
        } else if (isset($selectedmodule->activityid)) {
            $modid = $selectedmodule->activityid;
        }
        if (is_null($childactivityid) && count($childactivitiesarr) > 0 && isset($selectedmodule->gradeitemid)) {
            $childactivityid = $selectedmodule->gradeitemid;
        }
        $ammendgradeparams = "?id=$courseid&activityid=$modid&page=$page";

        // Add category id and childactivityid in the url parameters if not null.
        if (!is_null($categoryid)) {
            $ammendgradeparams .= '&categoryid=' . $categoryid;
        }
        if (!is_null($childactivityid)) {
            $ammendgradeparams .= '&childactivityid=' . $childactivityid;
        }

        // Url action form.
        $actionurl = "index.php$ammendgradeparams";
        // Add grade page url.
        $addgradeurl = new moodle_url('/local/gugcat/add/index.php') . $ammendgradeparams;
        // Upload page url.
        $uploadurl = new moodle_url('/local/gugcat/import/index.php') . $ammendgradeparams;
        // Convert page url.
        $converturl = new moodle_url('/local/gugcat/convert/index.php') . $ammendgradeparams;
        // Reindex activities, childactivities, reasons and grades array.
        $activities = array_values($activitiesarr);
        $childactivities = !empty($childactivitiesarr) ? array_values($childactivitiesarr) : null;
        $grades = array_values(array_unique(local_gugcat::$grades));
        $reasons = array_values(local_gugcat::get_reasons());
        // Grade capture columns and rows in html.
        $htmlcolumns = null;
        $htmlrows = null;
        foreach ($columns as $col) {
            $htmlcolumns .= html_writer::tag('th', $this->sort_header($col), array('class' => 'gradeitems'));
        }
        $htmlcolumns .= html_writer::tag('th', get_string('addallnewgrade', 'local_gugcat'),
         array('class' => 'togglemultigrd sortable'));
        $htmlcolumns .= html_writer::tag('th', get_string('reasonnewgrade', 'local_gugcat'),
         array('class' => 'togglemultigrd sortable'));
        $htmlcolumns .= $isconverted ? html_writer::tag('th', $this->sort_header(get_string('convertedgrade',
         'local_gugcat'))) : null;
        $htmlcolumns .= html_writer::tag('th', $this->sort_header(get_string('provisionalgrd', 'local_gugcat')));
        // Released grade column.
        $releasedarr = array_column($rows, 'releasedgrade');
        $displayreleasedgrade = (count(array_filter($releasedarr, function ($a) {
            return $a !== null && $a !== 'N/A';
        } )) > 0);
        $htmlcolumns .= $displayreleasedgrade ? html_writer::tag('th', get_string('releasedgrade', 'local_gugcat')) : null;
        $htmlcolumns .= html_writer::empty_tag('th');
        // Grade point field attributes.
        $gm = ($selectedmodule) ? intval($selectedmodule->gradeitem->grademax) : 0; // Grade max.
        $gt = ($selectedmodule) ? $selectedmodule->gradeitem->gradetype : null; // Grade type.
        $grdfieldattrs = array(
            'pattern' => '^([mM][vV]|[0-9]|[nN][sS])+$',
            'placeholder' => get_string('typegrade', 'local_gugcat'),
            'data-toggle' => 'tooltip',
            'data-placement' => 'right',
            'data-html' => 'true',
            'data-grademax' => $gm,
            'maxlength' => strlen($gm),
            'minlength' => '1',
            'title' => get_string('gradetooltip', 'local_gugcat'),
            'class' => 'input-gradept multi-select-grade form-control',
            'onkeypress' => "return event.keyCode != 13;",
            'type' => 'text'
        );

        // Grade capture rows.
        foreach ($rows as $row) {
            $inputgrdpt = html_writer::start_tag('div', array('class' => 'form-inline felement'));
            $inputgrdpt .= html_writer::empty_tag('input', array_merge(array('name' => 'newgrades['
            . $row->studentno . ']'), $grdfieldattrs));
            $inputgrdpt .= html_writer::tag('div', get_string('errorinputpoints', 'local_gugcat'),
             array('class' => 'form-control-feedback invalid-feedback'));
            $inputgrdpt .= html_writer::end_tag('div');
            // Field for multiple add grade.
            $gradefield = ($gt == GRADE_TYPE_VALUE)
            ? $inputgrdpt
            : $this->display_custom_select($grades, 'newgrades[' . $row->studentno . ']',
            get_string('selectgrade', 'local_gugcat'), 'multi-select-grade');
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->idnumber);
            if (!$isblindmarking) {
                $htmlrows .= html_writer::tag('td', $row->surname, array('class' => 'blind-marking'));
                $htmlrows .= html_writer::tag('td', $row->forename, array('class' => 'blind-marking'));
            }
            $htmlrows .= '<td>' . (($row->discrepancy)
                ? '<div class="grade-discrepancy">' . $row->firstgrade . '</div>'
                : $row->firstgrade) . '</td>';
            foreach ((array) $row->grades as $item) {
                $htmlrows .= '<td>' . (($item->discrepancy)
                    ? '<div class="grade-discrepancy">' . $item->grade . '</div>'
                    : $item->grade) . '</td>';
            }
            $htmlrows .= html_writer::tag('td', $gradefield, array('class' => 'togglemultigrd'));
            $htmlrows .= '<td class="togglemultigrd">' . $this->display_custom_select(
                $reasons,
                'reason',
                get_string('selectreason', 'local_gugcat'),
                'multi-select-reason'
            ) .
                html_writer::empty_tag('input', array(
                    'type' => 'text', 'name' => 'reason',
                    'class' => 'input-reason', 'onkeypress' => "return event.keyCode != 13;"
                ))
                . '</td>';
            $htmlrows .= $isconverted ? html_writer::tag('td', $row->convertedgrade) : null;
            $isgradehidden = (!isset($row->hidden)) ? null : (($row->hidden)
            ? '<br/>(' . get_string('hiddengrade', 'local_gugcat') . ')' : '');
            if (
                is_null($row->provisionalgrade) || $row->provisionalgrade == '' ||
                $row->provisionalgrade == get_string('nograde', 'local_gugcat') ||
                $row->provisionalgrade == get_string('missinggrade', 'local_gugcat')
            ) {
                $htmlrows .= '<td><b>' . $row->provisionalgrade . '</b>' . $isgradehidden . '</td>';
            } else {
                $htmlrows .= '<td><b>' . $row->provisionalgrade . '</b>'
                . $this->context_actions($row->studentno,
                 $isgradehidden, false, $ammendgradeparams, false) .  $isgradehidden . '</td>';
            }
            $htmlrows .= $displayreleasedgrade ? html_writer::tag('td', is_null($row->releasedgrade)
            ? get_string('nograde', 'local_gugcat') : $row->releasedgrade, array('class' => 'font-weight-bold')) : null;

            $htmlrows .= '<td>
                            <button type="button" class="btn btn-default addnewgrade" onclick="location.href=\''
                            . $addgradeurl . "&studentid=$row->studentno" . '\'">
                                ' . get_string('addnewgrade', 'local_gugcat') . '
                            </button>
                    </td>';
            $htmlrows .= html_writer::end_tag('tr');
        }
        $displaychild = !empty($childactivities) ? true : false;
        $tabheader = !empty($activities) ? (object)[
            'addallgrdstr' => get_string('addmultigrades', 'local_gugcat'),
            'saveallgrdstr' => get_string('saveallnewgrade', 'local_gugcat'),
            'uploadaddgrdstr' => $isimported ? get_string('uploadaddgrd', 'local_gugcat') : null,
            'adjustgrdstr' => get_string('adjustgrade', 'local_gugcat'),
            'adjustassconvstr' => ($isimported && $gt == GRADE_TYPE_VALUE)
            ? get_string('adjustassessgrdcvr', 'local_gugcat') : null,
            'saveallbtnstr' => get_string('savechanges', 'local_gugcat'),
            'cancelbtnstr' => get_string('cancel'),
            'grddiscrepancystr' => get_string('gradediscrepancy', 'local_gugcat'),
            'importgradesstr' => get_string('importgrades', 'local_gugcat'),
            'releaseprvgrdstr' => get_string('releaseprvgrades', 'local_gugcat'),
            'blkimportstr' => get_string('bulkimport', 'local_gugcat'),
            'displayactivities' => true,
            'displaychildactivities' => $displaychild,
            'activities' => $activities,
            'childactivities' => $childactivities,
            'uploadurl' => $uploadurl,
            'converturl' => $converturl
        ] : null;

        $importmodal = [
            'titlestr' => get_string('pleasewaitfewmin', 'local_gugcat'),
            'contentstr' => get_string('gradesarebeingimport', 'local_gugcat')
        ];
        // Start displaying the table.
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_import_loading', $importmodal);
        $html .= $this->render_from_template('local_gugcat/gcat_tab_header', $tabheader);
        $html .= html_writer::start_tag('form', array('id' => 'multigradesform', 'method' => 'post', 'action' => $actionurl));
        $html .= $this->display_table($htmlrows, $htmlcolumns);
        $html .= html_writer::empty_tag('button', array('id' => 'search-submit', 'name' => 'search', 'type' => 'submit'));
        $html .= html_writer::empty_tag('button', array('id' => 'release-submit', 'name' => 'release', 'type' => 'submit'));
        $html .= html_writer::empty_tag('button', array('id' => 'multiadd-submit', 'name' => 'multiadd', 'type' => 'submit'));
        $html .= html_writer::empty_tag('button', array('id' => 'importgrades-submit', 'name' => 'importgrades',
         'type' => 'submit'));
        $html .= html_writer::empty_tag('button', array('id' => 'bulk-submit', 'name' => 'bulkimport', 'type' => 'submit'));
        $html .= html_writer::empty_tag('input', array('name' => 'rowstudentno', 'type' => 'hidden', 'id' => 'studentno'));
        $html .= html_writer::empty_tag('input', array('name' => 'is_converted', 'type' => 'hidden', 'id' => 'isconverted',
         'value' => $isconverted));
        $html .= html_writer::end_tag('form');
        $html .= $this->footer();
        return $html;
    }

    /**
     * Renders display of add and edit grade form page
     *
     * @param mixed $course
     * @param mixed $student user info of student
     * @param array $gradeversions graded grade versions
     * @param mixed $module selected assessment or sub category
     * @param boolean $isaddform indication between add and edit
     *
     */
    public function display_add_edit_grade_form($course, $student, $gradeversions, $module, $isaddform) {
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_form_details', (object)[
            'title' => $isaddform ? get_string('addnewgrade', 'local_gugcat') : get_string('editgrade', 'local_gugcat'),
            'course' => $course,
            'section' => $module->name,
            'student' => $student,
            'blindmarking' => !local_gugcat::is_blind_marking($this->page->cm) ? true : null
        ]);
        $html .= html_writer::start_tag('div', array('class' => 'mform-container'));
        foreach ($gradeversions as $gradeversion) {
            $html .= html_writer::start_tag('div', array('class' => 'form-group row'));
            $html .= html_writer::start_tag('div', array('class' => 'col-md-3'));
            if ($gradeversion->itemname == get_string('moodlegrade', 'local_gugcat')) {
                $html .= html_writer::tag('label', $gradeversion->itemname
                . date(" j/n/Y", strtotime(userdate($gradeversion->timemodified))));
            } else {
                $html .= html_writer::tag('label', $gradeversion->itemname);
            }
            $html .= html_writer::end_tag('div');
            $html .= html_writer::div(local_gugcat::convert_grade($gradeversion->grades[$student->id]->grade,
             $module->gradeitem->gradetype), 'col-md-9 form-inline felement');
            $html .= html_writer::end_tag('div');
        }
        $html .= html_writer::end_tag('div');
        return $html;
    }

    /**
     * Render display of grade aggregation tool page
     * @param array $rows
     * @param array $columns
     */
    public function display_aggregation_tool($rows, $activities) {
        global $SESSION;
        $SESSION->wantsurl = $this->page->url;
        $courseid = $this->page->course->id;
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $isblindmarking = local_gugcat::is_blind_marking();

        // Url to grade form.
        $actionurl = "index.php?id=$courseid&page=$page";
        // Add category id in the url if not null.
        $historyeditcategory = null;
        $gradeformhistorycategory = null;
        $acgparams = "?id=$courseid&page=$page";
        if (!is_null($categoryid)) {
            $actionurl .= "&categoryid=$categoryid";;
            $historyeditcategory = "&categoryid=$categoryid";;
            $gradeformhistorycategory = "&categoryid=$categoryid";;
            $acgparams .= "&categoryid=$categoryid";
        }

        $htmlcolumns = null;
        $htmlrows = null;
        $colgroups = null; // Use for grouping the columns of child activities (to add border).
        $colspan = 0; // Number of columns a column group should span.
        $prevcatid = null;
        $missinggrade = in_array(get_string('missinggrade', 'local_gugcat'),
         array_unique(array_column(array_column($rows, 'aggregatedgrade'), 'display')))
         ? true : false;
        $iscomputed = $missinggrade && count(array_unique(array_column(array_column($rows,
         'aggregatedgrade'), 'display'))) == 1 ? false : true;
        $meritexists = in_array(null, array_column($rows, 'meritgrade')) ? false : true;
        $gpaexists = in_array(null, array_column($rows, 'gpagrade')) ? false : true;

        foreach ($activities as $act) {
            // Get the activity scale.
            $scalestr = $act->gradetypename;
            // Get the activity weight.
            $weightcoef1 = $act->gradeitem->aggregationcoef; // Aggregation coeficient used for weighted averages or extra credit.
            $weightcoef2 = $act->gradeitem->aggregationcoef2; // Aggregation coeficient used for weighted averages only.
            $weight = ((float)$weightcoef1 > 0) ? (float)$weightcoef1 : (float)$weightcoef2;
            // The collapse-expand icon in the table header.
            $toggleicon = html_writer::tag(
                'button',
                html_writer::empty_tag('i', array('class' => 'i-colexp fa fa-plus', 'style' => 'pointer-events:none')),
                array('data-categoryid' => $act->id, 'type' => 'button', 'class' => 'btn btn-colexp')
            );
            $convertgrdparams = "?id=$courseid&activityid=$act->gradeitemid&page=$page" . $historyeditcategory;
            $isimported = false;
            $isconverted = $act->is_converted;
            if ($act->modname == 'category') {
                if ($act->gradeitem->gradetype == GRADE_TYPE_VALUE || $isconverted) {
                    $isimported = local_gugcat::get_grade_item_id($courseid, $act->id,
                     get_string('subcategorygrade', 'local_gugcat'));
                }
            }
            $nameattr = array(
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
                'title' => $act->name
            );
            $n = $act->name;
            $namespan = html_writer::tag('span', strlen($n) > 20 ? substr($n, 0, 20) . '...' : $n, $nameattr);
            $header = "$namespan<br/>" . ($weight * 100) . "% <br/> $scalestr";
            $header = $this->sort_header($header) . ($act->modname == 'category'
            ? ($isimported ? $this->context_actions(null, null, false, $convertgrdparams, false, true)
            . $toggleicon : $toggleicon) : null);
            if ($act->modname == 'category') {
                if ($colspan > 0) {
                    $colgroups .= html_writer::empty_tag('colgroup', array('span' => $colspan,
                     'class' => "colgroup hidden catid-$prevcatid"));
                    $colspan = 0;
                }
                $colgroups .= html_writer::empty_tag('colgroup', array('span' => 1, 'class' => 'subcat-colgroup'));
            } else {
                if (local_gugcat::is_child_activity($act)) {
                    $colspan++;
                    $prevcatid = $act->gradeitem->categoryid;
                } else {
                    $colgroups .= html_writer::empty_tag('colgroup');
                }
            }
            /* If activity is a child of a sub category, hide by default;
             Data-category use for identifying which column is to be toggled in JS. */
            $class = local_gugcat::is_child_activity($act) ? array('class' => ' hidden',
             'data-category' => $act->gradeitem->categoryid) : null;
            $htmlcolumns .= html_writer::tag('th', $header, $class);
        }
        $htmlcolumns .= html_writer::tag('th', $this->sort_header(get_string('requiresresit', 'local_gugcat')));
        $htmlcolumns .= html_writer::tag('th', $this->sort_header(get_string('percentcomplete', 'local_gugcat')));
        $sortspan = $this->sort_header(get_string('aggregatedgrade', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', $sortspan . ($iscomputed && !($meritexists && $gpaexists)
            ? $this->context_actions(null, null, null, $acgparams, false, false, 0) : null));
        // Grade capture rows.

        $displaymerit = false;
        $displaygpa = false;
        foreach ($rows as $row) {
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->cnum);
            $htmlrows .= html_writer::tag('td', $row->idnumber);
            if (!$isblindmarking) {
                $htmlrows .= html_writer::tag('td', $row->surname, array('class' => 'blind-marking'));
                $htmlrows .= html_writer::tag('td', $row->forename, array('class' => 'blind-marking'));
            }
            foreach ((array) $row->grades as $grade) {
                $datacategory = $grade->category ? array('class' => 'hidden', 'data-category' => $grade->category) : null;
                $ammendgradeparams = "?id=$courseid&activityid=$grade->activityid&page=$page" . $historyeditcategory;
                $ammendgradeparams .= $grade->is_subcat ? "&cnum=$row->cnum" : null;
                $courseformhistoryparams = "?id=$courseid&cnum=$row->cnum&page=$page" . $gradeformhistorycategory;
                $gradecell = $grade->grade . ((strpos($grade->grade, 'No grade') !== false)
                || (strpos($grade->grade, 'Missing') !== false)
                ? null : ($grade->is_imported ? $this->context_actions($row->studentno, null, ($grade->is_subcat ? true : false),
                $ammendgradeparams, true) : null));
                $gradeui = ($row->highlightMV && $grade->grade == MEDICAL_EXEMPTION_AC && ($meritexists || $gpaexists))
                    ? html_writer::tag('div', $gradecell, array('class' => 'highlighted'))
                    : $gradecell;
                $htmlrows .= html_writer::tag('td', $gradeui, $datacategory);
            }
            // Require resit row.
            $requireresiturl = $actionurl . "&rowstudentno=$row->studentno&resit=1";
            $classname = (is_null($row->resit) ? "fa fa-circle" : ($row->resit == 'Y' ? "fa fa-check-circle"
            : "fa fa-times-circle"));
            $htmlrows .= html_writer::tag('td', html_writer::tag('a', null, array('class' => $classname,
             'href' => $requireresiturl)));
            $htmlrows .= html_writer::tag('td', $row->completed);
            $htmlrows .= ($row->aggregatedgrade->display != get_string('missinggrade', 'local_gugcat'))
            ? html_writer::tag('td', $row->aggregatedgrade->display
            . $this->context_actions($row->studentno, null, true, $courseformhistoryparams, true))
            : html_writer::tag('td', $row->aggregatedgrade->display);
            $row->meritgrade ? $htmlrows .= ($row->meritgrade->grade != get_string('missinggrade', 'local_gugcat')
            ? html_writer::tag('td', $row->meritgrade->grade
            . $this->context_actions($row->studentno, null, true, $courseformhistoryparams . '&alternativecg=1', true))
            : html_writer::tag('td', $row->meritgrade->grade)) : null;
            if ($row->meritgrade && !$displaymerit) {
                $displaymerit = true;
            }
            $row->gpagrade ? $htmlrows .= ($row->gpagrade->grade != get_string('missinggrade', 'local_gugcat')
            ? html_writer::tag('td', $row->gpagrade->grade
            . $this->context_actions($row->studentno, null, true, $courseformhistoryparams . '&alternativecg=2', true))
            : html_writer::tag('td', $row->gpagrade->grade)) : null;
            if ($row->gpagrade && !$displaygpa) {
                $displaygpa = true;
            }
            $htmlrows .= html_writer::end_tag('tr');
        }
        if ($displaymerit) {
            $htmlcolumns .= html_writer::tag('th', $this->sort_header(get_string('meritgrade', 'local_gugcat'))
            . $this->context_actions(null, null, false, "$acgparams&alternative=" . MERIT_GRADE, false, false, 1));
        }
        if ($displaygpa) {
            $htmlcolumns .= html_writer::tag('th', $this->sort_header(get_string('gpagrade', 'local_gugcat'))
                . $this->context_actions(null, null, false, "$acgparams&alternative=" . GPA_GRADE, false, false, 1));
        }
        $hiderelease = in_array(get_string('missinggrade', 'local_gugcat'),
         array_column(array_column($rows, 'aggregatedgrade'), 'display'));
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_tab_header', (object)[
            'downloadcsvstr' => get_string('downloadcsv', 'local_gugcat'),
            'releasefinalstr' => $hiderelease ? null : get_string('releasefinalassessment', 'local_gugcat'),
        ]);
        $html .= html_writer::start_tag('form', array('id' => 'requireresitform', 'method' => 'post', 'action' => $actionurl));
        $html .= $this->display_table($htmlrows, $htmlcolumns, false, true, [], $colgroups);
        $html .= html_writer::empty_tag('button', array('id' => 'search-submit', 'name' => 'search', 'type' => 'submit'));
        $html .= html_writer::empty_tag('input', array('id' => 'resitstudentno', 'name' => 'rowstudentno', 'type' => 'hidden'));
        $html .= html_writer::empty_tag('button', array('id' => 'downloadcsv-submit', 'name' => 'downloadcsv', 'type' => 'submit'));
        $html .= html_writer::empty_tag('button', array('id' => 'finalrelease-submit',
         'name' => 'finalrelease', 'type' => 'submit'));
        $html .= html_writer::end_tag('form');
        $html .= $this->footer();
        return $html;
    }

    /**
     * Render a reusable custom UI select element
     * @param array $options
     * @param string $name
     * @param string $default
     * @param string $class
     * @param string $id
     */
    public function display_custom_select($options, $name = null, $default = null, $class = null,
    $id = null, $attributes = array()) {
        $params = array_merge(array(
            'default' => $default,
            'options' => $options,
            'class' => $class,
            'id' => $id,
            'name' => $name
        ), $attributes);
        $html = $this->render_from_template('local_gugcat/gcat_custom_select', (object)$params);
        return $html;
    }

    /**
     * Render display of adjust weights and override grade form page
     *
     * @param mixed $student user info of the student
     */
    public function display_adjust_override_grade_form($student) {
        $setting = required_param('setting', PARAM_INT);
        $activityid = optional_param('activityid', null, PARAM_INT);
        $acg = optional_param('alternativecg', null, PARAM_INT);
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_form_details', (object)[
        'title' => get_string(($setting != 0 ? (!is_null($activityid) ? 'overridestudassgrade'
        : (is_null($acg) ? 'overridestudgrade' : ($acg == 1 ? 'overridestudmeritgrade'
        : 'overridestudgpagrade'))) : 'adjustcourseweight'), 'local_gugcat'),
        'student' => $student,
        'blindmarking' => !local_gugcat::is_blind_marking() ? true : null
        ]);
        return $html;
    }

    /**
     * Render display of grade history page
     *
     * @param mixed $student user info of the student
     * @param string $activity name of the activity
     * @param array $rows
     */
    public function display_grade_history($student, $activity, $rows) {
        $htmlcolumns = null;
        $htmlrows = null;
        $htmlcolumns .= html_writer::tag('th', get_string('datetime', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('gradeformgrade', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('revised', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('type', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('notes', 'local_gugcat'));
        foreach ($rows as $row) {
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->date);
            $htmlrows .= html_writer::tag('td', $row->grade);
            $htmlrows .= html_writer::tag('td', $row->modby);
            $htmlrows .= html_writer::tag('td', $row->type);
            $htmlrows .= html_writer::tag('td', $row->notes);
            $htmlrows .= html_writer::end_tag('tr');
        }
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_form_details', (object)[
            'title' => get_string('assessmentgradehistory', 'local_gugcat'),
            'student' => $student,
            'activity' => $activity,
            'blindmarking' => !local_gugcat::is_blind_marking($this->page->cm) ? true : null
        ]);
        $html .= $this->display_table($htmlrows, $htmlcolumns, true);
        return $html;
    }

    /**
     * Renders display of course grade history page
     *
     * @param mixed $student user info of the student
     * @param array $rows
     * @param array $activities
     */
    public function display_course_grade_history($student, $rows, $activities) {
        $htmlcolumns = null;
        $htmlrows = null;

        $htmlcolumns .= html_writer::tag('th', get_string('datetime', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('revised', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('coursegrade', 'local_gugcat'));
        foreach ($activities as $act) {
            $htmlcolumns .= html_writer::tag('th', $act->name . '<br> Weighting');
        }
        $htmlcolumns .= html_writer::tag('th', get_string('notes', 'local_gugcat'));
        foreach ($rows as $row) {
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->date);
            $htmlrows .= html_writer::tag('td', is_null($row->modby) ? get_string('nogradeweight', 'local_gugcat') : $row->modby);
            $htmlrows .= html_writer::tag('td', $row->grade);
            for ($i = 0; $i < count($activities); $i++) {
                $weight = isset($row->overridden) ? get_string('nogradeweight', 'local_gugcat')
                : (isset($row->weights[$i]) ? round((float)$row->weights[$i] * 100) . '%'
                : get_string('nogradeweight', 'local_gugcat'));
                $htmlrows .= html_writer::tag('td', $weight);
            }
            $htmlrows .= html_writer::tag('td', is_null($row->notes) && empty($row->notes)
            ? get_string('systemupdatecourse', 'local_gugcat') : $row->notes);

            $htmlrows .= html_writer::end_tag('tr');
        }
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_form_details', (object)[
            'title' => get_string('coursegradehistory', 'local_gugcat'),
            'student' => $student,
            'blindmarking' => !local_gugcat::is_blind_marking($this->page->cm) ? true : null
        ]);
        $html .= $this->display_table($htmlrows, $htmlcolumns, true);
        return $html;
    }

    /**
     * Render display of forms with contents from mform
     * @param string $title_ Title of the form
     */
    public function display_empty_form($formtitle = null) {
        $setting = optional_param('setting', 0, PARAM_INT);
        $title = is_null($formtitle)
            ? get_string(($setting != 0 ? 'setupimportoptions' : 'setupimportupload'), 'local_gugcat')
            : $formtitle;
        $html = $this->header();
        $html .= html_writer::start_tag('div', array('class' => 'form-container'));
        $html .= html_writer::tag('h5', $title, array('class' => 'title'));
        $html .= html_writer::end_tag('div');

        return $html;
    }

    /**
     * Render display of adjust weights and override grade form page
     *
     */
    public function display_import_preview($firstrow, $data, $isassign) {
        $title = get_string('setupimportoptions', 'local_gugcat');
        $importmodal = [
            'titlestr' => get_string('pleasewaitfewmin', 'local_gugcat'),
            'contentstr' => get_string('gradesarebeingupload', 'local_gugcat')
        ];
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_import_loading', $importmodal);
        $html .= html_writer::start_tag('div', array('class' => 'form-container'));
        $html .= html_writer::tag('h5', $title, array('class' => 'title'));
        $html .= html_writer::tag('label', get_string('datapreview', 'local_gugcat'));
        $htmlcolumns = null;
        $htmlcolumns .= html_writer::tag('th', get_string('studentno', 'local_gugcat'));
        if ($isassign) {
            $htmlcolumns .= html_writer::tag('th', get_string('participantno', 'local_gugcat'));
        } else {
            $htmlcolumns .= html_writer::tag('th', get_string('studentfirstname', 'turnitintooltwo'));
            $htmlcolumns .= html_writer::tag('th', get_string('studentlastname', 'turnitintooltwo'));
        }
        $htmlcolumns .= html_writer::tag('th', get_string('grade', 'local_gugcat'));

        $htmlrows = null;
        if (count($firstrow) != 0) {
            $htmlrows .= html_writer::start_tag('tr');
            foreach ($firstrow as $row) {
                $htmlrows .= html_writer::tag('td', $row);
            }
            $htmlrows .= html_writer::end_tag('tr');
        }

        foreach ($data as $row) {
            $fixedcolumns = $isassign ? 3 : 4;
            $htmlrows .= html_writer::start_tag('tr');
            foreach ($row as $rowdata) {
                $htmlrows .= html_writer::tag('td', $rowdata);
                $fixedcolumns--;
                if ($fixedcolumns == 0) {
                    break;
                }
            }
            $htmlrows .= html_writer::end_tag('tr');
        }

        $html .= $this->display_table($htmlrows, $htmlcolumns, true,
         false, array('style' => "margin-left:30px;padding-left:10px"));
        $html .= html_writer::empty_tag('br');
        $html .= html_writer::end_tag('div');

        return $html;
    }

    public function display_aggregated_assessment_history($activities, $rows, $student, $activity) {
        $htmlcolumns = null;
        $htmlrows = null;

        $courseid = optional_param('id', null, PARAM_INT);
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);

        $htmlcolumns .= html_writer::tag('th', get_string('datetime', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('aggregatedassgrd', 'local_gugcat'));
        foreach ($activities as $act) {
            $htmlcolumns .= html_writer::tag('th', $act->name);
        }
        $htmlcolumns .= html_writer::tag('th', get_string('revised', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('notes', 'local_gugcat'));
        foreach ($rows as $row) {
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->date);
            $htmlrows .= html_writer::tag('td', $row->grade);
            $i = 0;
            foreach ($activities as $act) {
                $activityid = $act->gradeitemid;
                $ammendgradeparams = "?id=$courseid&activityid=$activityid&page=$page&categoryid=$categoryid&history";
                $htmlrows .= html_writer::tag('td', isset($row->childgrades[$i]->grade) ?
                $row->childgrades[$i]->grade . $this->context_actions($student->id, null, false, $ammendgradeparams)
                : get_string('pendingimport', 'local_gugcat'));
                $i++;
            }
            $htmlrows .= html_writer::tag('td', $row->modby);
            $htmlrows .= html_writer::tag('td', $row->notes);
            $htmlrows .= html_writer::end_tag('tr');
        }
        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_form_details', (object)[
            'title' => get_string('assessmentgradehistory', 'local_gugcat'),
            'student' => $student,
            'activity' => $activity,
            'blindmarking' => !local_gugcat::is_blind_marking($this->page->cm) ? true : null
        ]);
        $html .= $this->display_table($htmlrows, $htmlcolumns, true);
        return $html;
    }

    /**
     * Renders display of alternative course grade history page
     *
     * @param array $rows
     * @param mixed $student user info of the student
     */
    public function display_alternative_cg_history($rows, $student) {

        $acg = optional_param('alternativecg', null, PARAM_INT);
        $htmlcolumns = null;
        $htmlrows = null;

        $htmlcolumns .= html_writer::tag('th', get_string('datetime', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('gradeformgrade', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('revised', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('type', 'local_gugcat'));
        $htmlcolumns .= html_writer::tag('th', get_string('notes', 'local_gugcat'));
        foreach ($rows as $row) {
            $htmlrows .= html_writer::start_tag('tr');
            $htmlrows .= html_writer::tag('td', $row->date);
            $htmlrows .= html_writer::tag('td', $row->grade);
            $htmlrows .= html_writer::tag('td', $row->modby);
            $htmlrows .= html_writer::tag('td', $row->type);
            $htmlrows .= html_writer::tag('td', $row->notes);
            $htmlrows .= html_writer::end_tag('tr');
        }

        $html = $this->header();
        $html .= $this->render_from_template('local_gugcat/gcat_form_details', (object) [
            'title' => get_string($acg == GPA_GRADE ? 'gpagradehistory' : 'meritgradehistory', 'local_gugcat'),
            'student' => $student,
            'blindmarking' => !local_gugcat::is_blind_marking($this->page->cm) ? true : null
        ]);
        $html .= $this->display_table($htmlrows, $htmlcolumns, true);
        return $html;
    }

    /**
     * Render a reusable custom UI table element
     * @param array $rows
     * @param array $columns
     * @param boolean $simple Shows/hides some columns when true
     * @param boolean $aggregation Shows/hides some columns when true
     * @param array $attributes Additional attributes to be added in table
     * @param mixed $colgroup Use for grouping the table columns
     */
    private function display_table($rows, $columns, $simple = false, $aggregation = false, $attributes = [], $colgroup = null) {
        $isblindmarking = local_gugcat::is_blind_marking($this->page->cm);
        $searchicon = html_writer::tag('i', null, array('class' => 'fa fa-search', 'role' => 'button', 'tabindex' => '0'));
        // Check if there's existing filters.
        $filters = optional_param_array('filters', array('idnumber' => '', 'firstname' => '', 'lastname' => ''), PARAM_NOTAGS);
        $filters = local_gugcat::get_filters_from_url($filters);
        // Add search bar element (sb) on idnumber, firstname, lastname.
        $sbattr = array('type' => 'text', 'placeholder' => get_string('search', 'local_gugcat'));
        $sbidnumber = html_writer::empty_tag('input', $sbattr + array(
            'name' => 'filters[idnumber]', 'value' => $filters['idnumber'],
            'class' => 'input-search ' . (!empty($filters['idnumber']) ? 'visible' : '')
        ));
        if (!$isblindmarking) {
            $sbfirstname = html_writer::empty_tag('input', $sbattr + array(
                'name' => 'filters[firstname]', 'value' => $filters['firstname'],
                'class' => 'input-search ' . (!empty($filters['firstname']) ? 'visible' : '')
            ));
            $sblastname = html_writer::empty_tag('input', $sbattr + array(
                'name' => 'filters[lastname]', 'value' => $filters['lastname'],
                'class' => 'input-search ' . (!empty($filters['lastname']) ? 'visible' : '')
            ));
        }
        $html = html_writer::start_tag('table', array_merge(array('id' => 'gcat-table', 'class' => 'table'), $attributes));
        if ($aggregation) {
            $html .= html_writer::empty_tag('colgroup', array('span' => $isblindmarking ? 2 : 4));
            $html .= $colgroup;
        }
        $html .= html_writer::start_tag('thead');
        $html .= html_writer::start_tag('tr');
        if (!$simple) {
            if ($aggregation) {
                $html .= html_writer::tag('th', $this->sort_header(get_string('candidateno', 'local_gugcat')));
            }
            $html .= html_writer::tag('th', $this->sort_header(get_string('studentno',
             'local_gugcat')) . $searchicon . $sbidnumber);
            if (!$isblindmarking) {
                $html .= html_writer::tag('th', $this->sort_header(get_string('surname',
                 'local_gugcat')) . $searchicon . $sblastname, array('class' => 'blind-marking'));
                $html .= html_writer::tag('th', $this->sort_header(get_string('forename',
                 'local_gugcat')) . $searchicon . $sbfirstname, array('class' => 'blind-marking'));
            }
        }
        $html .= $columns;
        $html .= html_writer::end_tag('tr');
        $html .= html_writer::end_tag('thead');
        $html .= html_writer::start_tag('tbody');
        $html .= $rows;
        $html .= html_writer::end_tag('tbody');
        $html .= html_writer::end_tag('table');

        return $html;
    }

    /**
     * Render a reusable context action element
     * @param int $studentno
     * @param boolean $ishidden Condition for url action
     * @param boolean $is_aggregrade Condition for url action
     * @param string $link Url link
     * @param boolean $is_overviewpage Condition for url action
     * @param int $is_acg Condition for url action
     */
    private function context_actions($studentno = null, $ishidden = null, $isaggregrade = false,
                                     $link = null, $isoverviewpage = false, $issubcat = false,
                                     $isacg = null) {
        $class = array('class' => 'dropdown-item');
        $html = html_writer::tag('i', null, array('class' => 'fa fa-ellipsis-h',
         'data-toggle' => 'dropdown', 'role' => 'button', 'tabindex' => '0'));
        $html .= html_writer::start_tag('div', array('class' => 'dropdown-menu'));
        $link .= is_null($studentno) ? null : "&studentid=$studentno";
        if ($isaggregrade) {
            $gradeformurl = new moodle_url('/local/gugcat/overview/gradeform/index.php') . $link;
            $coursehistoryurl = new moodle_url('/local/gugcat/overview/history/index.php') . $link;
            $adjustlink = $gradeformurl . '&setting=' . ADJUST_WEIGHT_FORM;
            $overridelink = $gradeformurl . '&setting=' . OVERRIDE_GRADE_FORM;
            // Check if url params has activity id, then aggregated grade is sub category.
            if (strpos($link, 'activityid')) {
                $historylink = new moodle_url('/local/gugcat/history/index.php') . $link;
                $html .= html_writer::tag('a', get_string('overrideggreassessgrade',
                 'local_gugcat'), array('href' => $overridelink) + $class);
                $html .= html_writer::tag('a', get_string('assessmentgradehistory',
                 'local_gugcat'), array('href' => $historylink) + $class);
            } else if (strpos($link, 'alternativecg')) {
                $acg = strpos($link, 'alternativecg=1') ? 1 : 2;
                $html .= html_writer::tag('a', get_string($acg == MERIT_GRADE
                ? 'overridestudmeritgrade' : 'overridestudgpagrade', 'local_gugcat'),
                 array('href' => $overridelink) + $class);
                $html .= html_writer::tag('a', get_string($acg == MERIT_GRADE
                ? 'viewmeritgradehistory' : 'viewgpagradehistory', 'local_gugcat'),
                 array('href' => $coursehistoryurl) + $class);
            } else {
                $html .= html_writer::tag('a', get_string('adjustcourseweight', 'local_gugcat'),
                 array('href' => $adjustlink) + $class);
                $html .= html_writer::tag('a', get_string('overrideggregrade', 'local_gugcat'),
                 array('href' => $overridelink) + $class);
                $html .= html_writer::tag('a', get_string('viewcoursehistory', 'local_gugcat'),
                 array('href' => $coursehistoryurl) + $class);
            }
        } else if ($issubcat) {
            $converturl = new moodle_url('/local/gugcat/convert/index.php') . $link;
            $html .= html_writer::tag('a', get_string('adjustassessgrdcvr', 'local_gugcat'),
             array('href' => $converturl) + $class);
        } else if (!is_null($isacg)) {
            $acgurl = new moodle_url('/local/gugcat/overview/alternative/index.php') . $link;
            $html .= html_writer::tag('a', get_string($isacg == 0
            ? 'createaltcoursegrade' : 'adjustaltcoursegrade', 'local_gugcat'),
            array('href' => $acgurl) + $class);
        } else {
            $historylink = new moodle_url('/local/gugcat/history/index.php') . $link;
            $editlink = new moodle_url('/local/gugcat/edit/index.php') . $link . '&overview=' . ($isoverviewpage ? 1 : 0);
            $hidelink = new moodle_url('/local/gugcat/index.php') . $link . "&showhidegrade=1";
            // Check if url params has history for aggregated assessment grade history context action.
            if (preg_match('/\b&history/i', $historylink)) {
                $historylink = preg_replace('/\b&history/i', '', $historylink);
                $html .= html_writer::tag('a', get_string('assessmentgradehistory', 'local_gugcat'),
                 array('href' => $historylink) + $class);
            } else {
                $hidestr = !empty($ishidden) ? get_string('showgrade', 'local_gugcat')
                : get_string('hidefromstudent', 'local_gugcat');
                $html .= html_writer::tag('a', get_string('amendgrades', 'local_gugcat'),
                 array('href' => $editlink) + $class);
                $html .= html_writer::tag('a', get_string('assessmentgradehistory', 'local_gugcat'),
                 array('href' => $historylink) + $class);
                $html .= html_writer::tag('a', $hidestr, array('href' => $hidelink,
                 'class' => 'dropdown-item hide-show-grade'));
            }
        }
        $html .= html_writer::end_tag('div');
        return $html;
    }

    /**
     * Render display of the GCAT cogwheel
     */
    private function gcat_settings() {
        $courseid = (int)$this->page->course->id;
        $coursecontext = context_course::instance($courseid);
        $class = array('class' => 'dropdown-item', 'href' => '#');
        if (has_capability('local/gugcat:displayassessments', $coursecontext)) {

            $checkboxvalue = local_gugcat::get_value_of_customfield_checkbox($courseid, $coursecontext->id);
            $switchstr = $checkboxvalue == 1 ? get_string('switchoffdisplay', 'local_gugcat')
            : get_string('switchondisplay', 'local_gugcat');
        }
        $html = html_writer::tag('i', null, array('id' => 'gcat-cog', 'class' => 'fa fa-cog',
         'data-toggle' => 'dropdown', 'role' => 'button', 'tabindex' => '0'));
        $html .= html_writer::start_tag('div', array('class' => 'dropdown-menu'));
        $html .= has_capability('local/gugcat:revealidentities', $coursecontext)
        ? html_writer::tag('a', get_string('hideidentities', 'local_gugcat'),
        array('id' => 'btn-identities') + $class) : null;
        $html .= has_capability('local/gugcat:displayassessments', $coursecontext)
        ? html_writer::tag('a',  $switchstr,
        array('id' => 'btn-switch-display') + $class) : null;
        $html .= html_writer::end_tag('div');
        return $html;
    }

    /**
     * Display table header in span element with sort properties
     */
    private function sort_header($text) {
        $sortable = array('class' => 'sortable', 'role' => 'button', 'tabindex' => '0');
        $span = html_writer::tag('span', $text, $sortable);
        return $span;
    }

    /**
     * Helper function in rendering header
     */
    private function header() {
        $courseid = $this->page->course->id;
        $coursecontext = context_course::instance($courseid);
        $categoryid = optional_param('categoryid', null, PARAM_INT);
        $activityid = optional_param('activityid', null, PARAM_INT);
        // Reindex grade category arrayco.
        $categories = local_gugcat::get_grade_categories($courseid);
        $assessmenturl = new moodle_url('/local/gugcat/index.php', array('id' => $courseid));
        $assessmenturl .= !is_null($activityid) ? '&activityid=' . $activityid : null;
        $coursegradeurl = new moodle_url('/local/gugcat/overview/index.php', array('id' => $courseid));
        // Add category id in the url if not null.
        if (!is_null($categoryid)) {
            $assessmenturl .= '&categoryid=' . $categoryid;
            $coursegradeurl .= '&categoryid=' . $categoryid;
        }
        $html = html_writer::start_tag('div', array('class' => 'gcat-container'));
        $html .= html_writer::tag('h3', get_string('title', 'local_gugcat'), array('class' => 'gcat-title'));
        $html .= html_writer::start_tag('div', array('class' => 'row gcat-header'));
        $html .= $this->display_custom_select(
            array_values($categories),
            'select-category',
            null,
            'select-category',
            'select-category',
            array('autofocus' => true)
        );
        $html .= (has_capability('local/gugcat:revealidentities', $coursecontext)
            || has_capability('local/gugcat:displayassessments', $coursecontext))
            ? $this->gcat_settings()
            : null;
        $html .= html_writer::end_tag('div');
        $html .= $this->render_from_template('local_gugcat/gcat_tabs', (object)[
            'assessmenttabstr' => get_string('assessmentgradecapture', 'local_gugcat'),
            'coursegradetabstr' => get_string('coursegradeaggregation', 'local_gugcat'),
            'assessmenturl' => $assessmenturl,
            'coursegradeurl' => $coursegradeurl,
        ]);
        $html .= html_writer::start_tag('div', array('class' => 'tabcontent'));
        return $html;
    }

    /**
     * Helper function in rendering footer
     */
    private function footer() {
        $html = html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');
        return $html;
    }
}
