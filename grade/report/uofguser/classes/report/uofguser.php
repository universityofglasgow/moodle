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

namespace gradereport_uofguser\report;

use cache;
use context_course;
use course_modinfo;
use grade_grade;
use grade_helper;
use grade_item;
use grade_report;
use grade_tree;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/report/singleview/lib.php');

/**
 * Class uofguser
 *
 * @package    gradereport_uofguser
 * @copyright  2025 Ferenc 'Frank' Fengyel <ferenc.lengyel@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class uofguser extends \gradereport_user\report\user {
    /**
     * show source of the grade
     * @var void
     */
    public $showsource;

    /**
     * show status of the assessment
     * @var void
     */
    public $showstatus;

    /**
     * can view status column
     * @var bool
     */
    public $canviewstatus;

    /**
     * can view source column
     * @var bool
     */
    public $canviewsource;

    /**
     * collection of MyGrades scales
     * @var array
     */
    public $mygradesscales = [];

    /**
     * collection of Moodle grade scales
     * @var array
     */
    public $moodlescales = [];


    /**
     * Constructor. Sets local copies of user preferences and initialises grade_tree.
     * @param int $courseid
     * @param null|object $gpr grade plugin return tracking object
     * @param object $context
     * @param int $userid The id of the user
     * @param bool|null $viewasuser Set this to true when the current user is a mentor/parent of the targetted user.
     * @return void
     */
    public function __construct(int $courseid, ?object $gpr, object $context, int $userid, ?bool $viewasuser = null) {
        global $DB, $CFG;
        parent::__construct($courseid, $gpr, $context, $userid, $viewasuser);

        $this->showrank = grade_get_setting(
            $this->courseid,
            'report_uofguser_showrank',
            $CFG->grade_report_uofguser_showrank
        );
        $this->showsource = grade_get_setting(
            $this->courseid,
            'report_uofguser_showsource',
            $CFG->grade_report_uofguser_showsource
        );
        $this->showstatus = grade_get_setting(
            $this->courseid,
            'report_uofguser_showstatus',
            $CFG->grade_report_uofguser_showstatus
        );
        $this->showpercentage = grade_get_setting(
            $this->courseid,
            'report_uofguser_showpercentage',
            $CFG->grade_report_uofguser_showpercentage
        );
        $this->showhiddenitems = grade_get_setting(
            $this->courseid,
            'report_uofguser_showhiddenitems',
            $CFG->grade_report_uofguser_showhiddenitems
        );
        $this->showtotalsifcontainhidden = [$this->courseid => grade_get_setting(
            $this->courseid,
            'report_uofguser_showtotalsifcontainhidden',
            $CFG->grade_report_uofguser_showtotalsifcontainhidden
        )];
        $this->showgrade = grade_get_setting(
            $this->courseid,
            'report_uofguser_showgrade',
            !empty($CFG->grade_report_uofguser_showgrade)
        );
        $this->showrange = grade_get_setting(
            $this->courseid,
            'report_uofguser_showrange',
            !empty($CFG->grade_report_uofguser_showrange)
        );
        $this->showfeedback = grade_get_setting(
            $this->courseid,
            'report_uofguser_showfeedback',
            !empty($CFG->grade_report_uofguser_showfeedback)
        );
        $this->showweight = grade_get_setting(
            $this->courseid,
            'report_uofguser_showweight',
            !empty($CFG->grade_report_uofguser_showweight)
        );
        $this->showcontributiontocoursetotal = grade_get_setting(
            $this->courseid,
            'report_uofguser_showcontributiontocoursetotal',
            !empty($CFG->grade_report_uofguser_showcontributiontocoursetotal)
        );
        $this->showlettergrade = grade_get_setting(
            $this->courseid,
            'report_uofguser_showlettergrade',
            !empty($CFG->grade_report_uofguser_showlettergrade)
        );
        $this->showaverage = grade_get_setting(
            $this->courseid,
            'report_uofguser_showaverage',
            !empty($CFG->grade_report_uofguser_showaverage)
        );

        $this->viewasuser = $viewasuser;

        // The default grade decimals is 2.
        $defaultdecimals = 2;
        if (property_exists($CFG, 'grade_decimalpoints')) {
            $defaultdecimals = $CFG->grade_decimalpoints;
        }
        $this->decimals = grade_get_setting(
            $this->courseid,
            'decimalpoints',
            $defaultdecimals
        );

        // The default range decimals is 0.
        $defaultrangedecimals = 0;
        if (property_exists($CFG, 'grade_report_uofguser_rangedecimals')) {
            $defaultrangedecimals = $CFG->grade_report_uofguser_rangedecimals;
        }
        $this->rangedecimals = grade_get_setting(
            $this->courseid,
            'report_uofguser_rangedecimals',
            $defaultrangedecimals
        );

        $this->switch = grade_get_setting(
            $this->courseid,
            'aggregationposition',
            $CFG->grade_aggregationposition
        );

        // Prepare MyGrades scales collection.
        $scalesa[] = array_values(\local_gugrades\mapping\schedulea::get_map());
        $scalesb[] = array_values(\local_gugrades\mapping\scheduleb::get_map());
        $this->mygradesscales = array_merge(array_values($scalesa[0]), array_values($scalesb[0]));

        // Prepare MyGrades related Moodle grade scales collection.
        $this->moodlescales = gradereport_uofguser_moodlescale_generate_map();

        // Grab the grade_tree for this course.
        $this->gtree = new grade_tree($this->courseid, false, $this->switch, null, !$CFG->enableoutcomes);

        // Get the user (for full name).
        $this->user = $DB->get_record('user', ['id' => $userid]);

        // What user are we viewing this as?
        $coursecontext = context_course::instance($this->courseid);
        if ($viewasuser) {
            $this->modinfo = new course_modinfo($this->course, $this->user->id);
            $this->canviewhidden = has_capability('moodle/grade:viewhidden', $coursecontext, $this->user->id);
            // Can view status and source columns?
            $this->canviewstatus = has_capability('gradereport/uofguser:status', $coursecontext, $this->user->id);
            $this->canviewsource = has_capability('gradereport/uofguser:source', $coursecontext, $this->user->id);
        } else {
            $this->modinfo = $this->gtree->modinfo;
            $this->canviewhidden = has_capability('moodle/grade:viewhidden', $coursecontext);
            // Can view status and source columns?
            $this->canviewstatus = has_capability('gradereport/uofguser:status', $coursecontext);
            $this->canviewsource = has_capability('gradereport/uofguser:source', $coursecontext);
        }

        // Determine the number of rows and indentation.
        $this->maxdepth = 1;
        $this->inject_rowspans($this->gtree->top_element);
        $this->maxdepth++; // Need to account for the lead column that spans all children.
        for ($i = 1; $i <= $this->maxdepth; $i++) {
            $this->evenodd[$i] = 0;
        }

        $this->tabledata = [];

        // The base url for sorting by first/last name.
        $this->baseurl = new \moodle_url('/grade/report', ['id' => $courseid, 'userid' => $userid]);
        $this->pbarurl = $this->baseurl;

        // There no groups on this report - rank is from all course users.
        $this->setup_table();
    }

    /**
     * Prepares the headers and attributes of the flexitable.
     */
    public function setup_table() {
        /*
         * Table has 1-10 columns
         *| All columns except for itemname/description are optional
         */

        // Setting up table headers.

        $this->tablecolumns = ['itemname'];
        $this->tableheaders = [get_string('gradeitem', 'grades')];

        if ($this->showweight) {
            $this->tablecolumns[] = 'weight';
            $this->tableheaders[] = get_string('weightuc', 'grades');
        }

        if ($this->showgrade) {
            $this->tablecolumns[] = 'grade';
            $this->tableheaders[] = get_string('gradenoun');
        }

        if ($this->showrange) {
            $this->tablecolumns[] = 'range';
            $this->tableheaders[] = get_string('range', 'grades');
        }

        if ($this->showpercentage) {
            $this->tablecolumns[] = 'percentage';
            $this->tableheaders[] = get_string('percentage', 'grades');
        }

        if ($this->showlettergrade) {
            $this->tablecolumns[] = 'lettergrade';
            $this->tableheaders[] = get_string('lettergrade', 'grades');
        }

        if ($this->showrank) {
            $this->tablecolumns[] = 'rank';
            $this->tableheaders[] = get_string('rank', 'grades');
        }

        if ($this->showsource && $this->canviewsource) {
            $this->tablecolumns[] = 'source';
            $this->tableheaders[] = get_string('source', 'gradereport_uofguser');
        }

        if ($this->showstatus && $this->canviewstatus) {
            $this->tablecolumns[] = 'status';
            $this->tableheaders[] = get_string('status');
        }

        if ($this->showaverage) {
            $this->tablecolumns[] = 'average';
            $this->tableheaders[] = get_string('average', 'grades');
        }

        if ($this->showfeedback) {
            $this->tablecolumns[] = 'feedback';
            $this->tableheaders[] = get_string('feedback', 'grades');
        }

        if ($this->showcontributiontocoursetotal) {
            $this->tablecolumns[] = 'contributiontocoursetotal';
            $this->tableheaders[] = get_string('contributiontocoursetotal', 'grades');
        }
    }

    /**
     * Provide an entry point to build the table.
     *
     * @return bool
     */
    public function fill_table(): bool {
        // Is this a MyGrades report? Only check it once and pass it on to the recursive function.
        $coursemygradesenabled = \local_gugrades\api::is_mygrades_customfield_enabled($this->courseid);
        if (!$coursemygradesenabled) {
            // If MyGrades is not enabled for this course, we do not need to check further.
            $mygradesactive = false;
        } else {
            // Check if MyGrades is actively used in this course.
            $mygradesactive = \local_gugrades\api::is_mygrades_enabled_for_course($this->courseid);
        }

        $this->fill_table_recursive($this->gtree->top_element, $mygradesactive);
        return true;
    }

    /**
     * Fill the table with data.
     *
     * @param array $element - The table data for the current row.
     * @param bool $mygradesactive - Is MyGrades active in this course?
     */
    private function fill_table_recursive(array &$element, bool $mygradesactive = false) {
        global $DB, $CFG, $OUTPUT;

        $type = $element['type'];
        $depth = $element['depth'];
        $gradeobject = $element['object'];
        $eid = $gradeobject->id;
        $element['userid'] = $userid = $this->user->id;
        $fullname = grade_helper::get_element_header($element, true, false, true, false, true);
        $fullnamenolink = grade_helper::get_element_header($element, false, false, false, false, true);
        if ($depth == 1 && $type == 'category' && has_capability('gradereport/uofguser:mygradesstatus', $this->context)) {
            // If this is a top-level category element, we need to add MyGrades acive status.
            $fullname .= ' <span class="badge';
            $fullname .= $mygradesactive ?
                            ' bg-warning">' . get_string('mygradesactive', 'gradereport_uofguser') :
                            ' bg-secondary">' . get_string('mygradesinactive', 'gradereport_uofguser');
            $fullname .= '</span>';
        }
        $data = [];
        $gradeitemdata = [];
        $hidden = '';
        $excluded = '';
        $itemlevel = ($type == 'categoryitem' || $type == 'category' || $type == 'courseitem') ? $depth : ($depth + 1);
        $class = 'level' . $itemlevel;
        $classfeedback = '';
        $rowspandata = [];

        // If this is a hidden grade category, hide it completely from the user.
        if ($type == 'category' && $gradeobject->is_hidden() && !$this->canviewhidden && (
                $this->showhiddenitems == GRADE_REPORT_UOFGUSER_HIDE_HIDDEN ||
                ($this->showhiddenitems == GRADE_REPORT_UOFGUSER_HIDE_UNTIL && !$gradeobject->is_hiddenuntil()))) {
            return false;
        }

        // Process those items that have scores associated.
        if ($type == 'item' || $type == 'categoryitem' || $type == 'courseitem') {
            $headerrow = "row_{$eid}_{$this->user->id}";
            $headercat = "cat_{$gradeobject->categoryid}_{$this->user->id}";

            if (! $gradegrade = grade_grade::fetch(['itemid' => $gradeobject->id, 'userid' => $this->user->id])) {
                $gradegrade = new grade_grade();
                $gradegrade->userid = $this->user->id;
                $gradegrade->itemid = $gradeobject->id;
            }

            $gradegrade->load_grade_item();

            // Check if there is a released grade for this user in MyGrades.
            $mygradesreleasedgrade = \local_gugrades\grades::get_released_grade(
                $this->courseid,
                $gradegrade->grade_item->id,
                $userid
            );

            // Check (internal) MyGrades hidden flag.
            $mygradeshidden = $DB->record_exists(
                'local_gugrades_hidden',
                [
                    'gradeitemid' => $gradegrade->grade_item->id,
                    'userid' => $userid,
                ]
            );

            // Check if grade item is converted to a scale in MyGrades.
            $mygradesconverted = \local_gugrades\conversion::is_conversion_applied(
                $this->courseid,
                $gradegrade->grade_item->id
            );

            // If the grade is released in MyGrades, we flip the flag and let the user see it regardless of their role.
            $mygradesreleasedflag = false;
            if ($mygradesreleasedgrade && $mygradesactive) {
                $mygradesreleasedflag = true;
                $this->canviewhidden = true;
            }

            // Hidden Items.
            if ($gradegrade->grade_item->is_hidden() && $this->canviewhidden) {
                $hidden = ' dimmed_text';
            }

            $hide = false;
            $activityrestriction = false;
            // If this is a hidden grade item, hide it completely from the user.
            if ($gradegrade->is_hidden() && !$this->canviewhidden && (
                    $this->showhiddenitems == GRADE_REPORT_USER_HIDE_HIDDEN ||
                    ($this->showhiddenitems == GRADE_REPORT_USER_HIDE_UNTIL && !$gradegrade->is_hiddenuntil()))) {
                $hide = true;
            } else if (!empty($gradeobject->itemmodule) && !empty($gradeobject->iteminstance)) {
                // The grade object can be marked visible but still be hidden if
                // the student cannot see the activity due to conditional access
                // and it's set to be hidden entirely.
                $instances = $this->modinfo->get_instances_of($gradeobject->itemmodule);
                if (!empty($instances[$gradeobject->iteminstance])) {
                    $cm = $instances[$gradeobject->iteminstance];
                    $gradeitemdata['cmid'] = $cm->id;
                    if (!$cm->uservisible) {
                        // If there is 'availableinfo' text then it is only greyed
                        // out and not entirely hidden.
                        if (!$cm->availableinfo) {
                            $hide = true;
                        } else {
                            $activityrestriction = true;
                        }
                    }
                }
            }
            // If the grade is released and visible in MyGrades, we do not hide it.
            if ($mygradesreleasedflag) {
                $instances = $this->modinfo->get_instances_of($gradeobject->itemmodule);
                if (!empty($instances[$gradeobject->iteminstance])) {
                    $cm = $instances[$gradeobject->iteminstance];
                    $hide = false;
                }
            }
            // What should we do with uncategorised items?
            // If MyGrades is inactive, we mimic normal Moodle hidden item rules.
            $uncategorised = false;
            if ($mygradesactive && $type == 'item' && $depth == 1) {
                if (isset($this->viewasuser) && !$this->viewasuser) {
                    // If the user is a teacher, we show uncategorised items but dimmed.
                    // We add a special icon for uncategorised items later on.
                    $hide = false;
                    $hidden = ' dimmed_text';
                    $uncategorised = true;
                } else {
                    // For students, we hide uncategorised items in the report.
                    $hide = true;
                }
            }

            // Actual Grade - We need to calculate this whether the row is hidden or not.
            $gradeval = $gradegrade->finalgrade;
            $hint = $gradegrade->get_aggregation_hint();

            if ($mygradesreleasedflag) {
                if (!$mygradeshidden) {
                    // Points are the same as they are in grade_grades, but scales are re-indexed to [0..22] instead of [1..23].
                    $gradeval = $mygradesreleasedgrade->convertedgrade + 1 - $mygradesreleasedgrade->points;
                } else {
                    // If the grade is hidden in MyGrades, we do not show it.
                    $gradeval = null;
                }
            }

            if (!$this->canviewhidden) {
                // Virtual Grade (may be calculated excluding hidden items etc).
                $adjustedgrade = $this->blank_hidden_total_and_adjust_bounds($this->courseid,
                    $gradegrade->grade_item,
                    $gradeval);

                $gradeval = $adjustedgrade['grade'];

                // We temporarily adjust the view of this grade item - because the min and
                // max are affected by the hidden values in the aggregation.
                $gradegrade->grade_item->grademax = $adjustedgrade['grademax'];
                $gradegrade->grade_item->grademin = $adjustedgrade['grademin'];
                $hint['status'] = $adjustedgrade['aggregationstatus'];
                $hint['weight'] = $adjustedgrade['aggregationweight'];
            } else {
                // The max and min for an aggregation may be different to the grade_item.
                if (!is_null($gradeval)) {
                    $gradegrade->grade_item->grademax = $gradegrade->get_grade_max();
                    $gradegrade->grade_item->grademin = $gradegrade->get_grade_min();
                }
            }

            if (($mygradesconverted || $type == "categoryitem" || $type == "item") && $mygradesreleasedflag) {
                // If the grade item is converted to a scale in MyGrades, we have to change the gradetype.
                // Also convert all the aggregated (category) and released grades back to scale.
                // We have to make sure that the released grade is a scale, so hopefully the converted one.
                if (in_array($mygradesreleasedgrade->displaygrade, $this->mygradesscales)) {
                    $gradegrade->grade_item->gradetype = GRADE_TYPE_SCALE;
                    $display = $mygradesreleasedgrade->displaygrade;
                    $number = null;
                    foreach ($this->moodlescales as $num => $entry) {
                        if (!empty($entry['items']) && in_array($display, $entry['items'], true)) {
                            $number = $num;
                            break;
                        }
                    }
                    $gradegrade->grade_item->scaleid = $number;
                }
            }

            if (!$hide) {
                $canviewall = has_capability('moodle/grade:viewall', $this->context);
                // Other class information.
                $class .= $hidden . $excluded;
                // Alter style based on whether aggregation is first or last.
                if ($this->switch) {
                    $class .= ($type == 'categoryitem' || $type == 'courseitem') ? " d$depth baggt b2b" : " item b1b";
                } else {
                    $class .= ($type == 'categoryitem' || $type == 'courseitem') ? " d$depth baggb" : " item b1b";
                }

                $itemicon = \html_writer::div(grade_helper::get_element_icon($element), 'me-1');
                $elementtype = grade_helper::get_element_type_string($element);
                $itemtype = \html_writer::span($elementtype, 'd-block text-uppercase small ' . $hidden,
                    ['title' => $elementtype]);

                if ($type == 'categoryitem' || $type == 'courseitem') {
                    $headercat = "cat_{$gradeobject->iteminstance}_{$this->user->id}";
                }

                // Generate the content for a cell that represents a grade item.
                // If the grade item is hidden, but the grade released and visible in MyGrades we show the full name without a link.
                if ($mygradesreleasedflag && $gradegrade->grade_item->is_hidden()) {
                    $itemtitle = \html_writer::div($fullnamenolink, 'rowtitle');
                    $itemicon = \html_writer::div($OUTPUT->pix_icon(
                        'i/show',
                        get_string('hidden_icon_alt_text', 'block_newgu_spdetails'),
                        null,
                        ['class' => 'inline me-1']
                    ));
                } else {
                    $itemtitle = \html_writer::div($fullname, 'rowtitle');
                }
                // If the activity is restricted and the theacher views it as the student, we remove the link.
                if ($activityrestriction && isset($this->viewasuser) && $this->viewasuser) {
                    $itemtitle = \html_writer::div($fullnamenolink, 'rowtitle');
                }
                // If the activity is selected for reassessment, we add a pill but only for teachers.
                $resititem = \local_gugrades\grades::is_resit_gradeitem($gradegrade->grade_item->id);
                if (isset($this->viewasuser) && !$this->viewasuser && $resititem && $mygradesactive) {
                    $itemtitle .= \html_writer::tag(
                        'span',
                        get_string('resitselected', 'gradereport_uofguser'),
                        ['class' => 'badge badge-info badge-pill']
                    );
                }
                $content = \html_writer::div($itemtype . $itemtitle);
                // If activity is restricted, we add the lock icon to the content.
                if ($activityrestriction) {
                    $content .= \html_writer::div($OUTPUT->pix_icon(
                        'i/lock',
                        get_string('locked', 'grades'),
                        null,
                        ['class' => 'inline']
                    ));
                }
                // If uncatetegorised, we add a special uncategorised icon.
                if ($uncategorised) {
                    $content .= \html_writer::div($OUTPUT->pix_icon(
                        'i/flagged',
                        get_string('uncategorised_help', 'gradereport_uofguser'),
                        null,
                        ['class' => 'inline text-danger']
                    ));
                }

                // Name.
                $data['itemname']['content'] = \html_writer::div($itemicon . $content, "{$type} d-flex align-items-center");
                $data['itemname']['class'] = $class;
                $data['itemname']['colspan'] = ($this->maxdepth - $depth);
                $data['itemname']['id'] = $headerrow;

                // Basic grade item information.
                $gradeitemdata['id'] = $gradeobject->id;
                $gradeitemdata['itemname'] = $gradeobject->itemname;
                $gradeitemdata['itemtype'] = $gradeobject->itemtype;
                $gradeitemdata['itemmodule'] = $gradeobject->itemmodule;
                $gradeitemdata['iteminstance'] = $gradeobject->iteminstance;
                $gradeitemdata['itemnumber'] = $gradeobject->itemnumber;
                $gradeitemdata['idnumber'] = $gradeobject->idnumber;
                $gradeitemdata['categoryid'] = $gradeobject->categoryid;
                $gradeitemdata['outcomeid'] = $gradeobject->outcomeid;
                $gradeitemdata['scaleid'] = $gradeobject->outcomeid;
                $gradeitemdata['locked'] = $canviewall ? $gradegrade->grade_item->is_locked() : null;

                if ($this->showfeedback) {
                    // Copy $class before appending itemcenter as feedback should not be centered.
                    $classfeedback = $class;
                }
                $class .= " itemcenter ";
                if ($this->showweight) {
                    $data['weight']['class'] = $class;
                    $data['weight']['content'] = '-';
                    $data['weight']['headers'] = "$headercat $headerrow weight$userid";
                    // Is there an altered weight for this item and user in MyGrades?
                    [$originalweight, $alteredweight, $isaltered] = \local_gugrades\grades::get_altered_weight(
                        $gradegrade->grade_item->id,
                        $userid
                    );
                    if ($mygradesreleasedflag && $isaltered) {
                        // We show the altered weight in the report.
                        $hint['weight'] = $alteredweight;
                    }
                    // Has a weight assigned, might be extra credit.

                    // This obliterates the weight because it provides a more informative description.
                    if (is_numeric($hint['weight'])) {
                        $data['weight']['content'] = format_float($hint['weight'] * 100.0, 2) . ' %';
                        $gradeitemdata['weightraw'] = $hint['weight'];
                        $gradeitemdata['weightformatted'] = $data['weight']['content'];
                    }
                    if ($hint['status'] != 'used' && $hint['status'] != 'unknown') {
                        $data['weight']['content'] .= '<br>' . get_string('aggregationhint' . $hint['status'], 'grades');
                        $gradeitemdata['status'] = $hint['status'];
                    }
                }

                if ($this->showgrade) {
                    $gradestatus = '';
                    // We only show status icons for a teacher if he views report as himself.
                    if (isset($this->viewasuser) && !$this->viewasuser) {
                        $context = [
                            'hidden' => $gradegrade->is_hidden(),
                            'locked' => $gradegrade->is_locked(),
                            'overridden' => $gradegrade->is_overridden(),
                            'excluded' => $gradegrade->is_excluded(),
                        ];

                        if (in_array(true, $context)) {
                            $context['classes'] = 'gradestatus';
                            $gradestatus = $OUTPUT->render_from_template('core_grades/status_icons', $context);
                        }
                    }

                    $gradeitemdata['graderaw'] = null;
                    $gradeitemdata['gradehiddenbydate'] = false;
                    $gradeitemdata['gradeneedsupdate'] = $gradegrade->grade_item->needsupdate;
                    $gradeitemdata['gradeishidden'] = $gradegrade->is_hidden();
                    $gradeitemdata['gradedatesubmitted'] = $gradegrade->get_datesubmitted();
                    $gradeitemdata['gradedategraded'] = $gradegrade->get_dategraded();
                    $gradeitemdata['gradeislocked'] = $canviewall ? $gradegrade->is_locked() : null;
                    $gradeitemdata['gradeisoverridden'] = $canviewall ? $gradegrade->is_overridden() : null;

                    if ($gradegrade->grade_item->needsupdate) {
                        $data['grade']['class'] = $class.' gradingerror';
                        $data['grade']['content'] = get_string('error');
                    } else if (
                        !empty($CFG->grade_hiddenasdate)
                        && $gradegrade->get_datesubmitted()
                        && !$this->canviewhidden
                        && $gradegrade->is_hidden()
                        && !$gradegrade->grade_item->is_category_item()
                        && !$gradegrade->grade_item->is_course_item()
                    ) {
                        // The problem here is that we do not have the time when grade value was modified
                        // 'timemodified' is general modification date for grade_grades records.
                        $class .= ' datesubmitted';
                        $data['grade']['class'] = $class;
                        $data['grade']['content'] = get_string(
                            'submittedon',
                            'grades',
                            userdate(
                                $gradegrade->get_datesubmitted(),
                                get_string('strftimedatetimeshort')
                            ) . $gradestatus
                        );
                        $gradeitemdata['gradehiddenbydate'] = true;
                    } else if ($gradegrade->is_hidden()) {
                        $data['grade']['class'] = $class;
                        $data['grade']['content'] = '-';

                        if ($this->canviewhidden) {
                            $data['grade']['class'] .= ' dimmed_text';
                            $gradeitemdata['graderaw'] = $gradeval;
                            $data['grade']['content'] = grade_format_gradevalue($gradeval,
                                $gradegrade->grade_item,
                                true) . $gradestatus;
                        }
                    } else {
                        $gradestatusclass = '';
                        $gradepassicon = '';
                        $ispassinggrade = $gradegrade->is_passed($gradegrade->grade_item);
                        $ispassinggrade = $mygradesreleasedflag ? null : $ispassinggrade;
                        if (!is_null($gradeval) && !is_null($ispassinggrade)) {
                            $gradestatusclass = $ispassinggrade ? 'gradepass' : 'gradefail';
                            if ($ispassinggrade) {
                                $gradepassicon = $OUTPUT->pix_icon(
                                    'i/valid',
                                    get_string('pass', 'grades'),
                                    null,
                                    ['class' => 'inline']
                                );
                            } else {
                                $gradepassicon = $OUTPUT->pix_icon(
                                    'i/invalid',
                                    get_string('fail', 'grades'),
                                    null,
                                    ['class' => 'inline']
                                );
                            }
                        }

                        // Deal with aggregated grades if MyGrades active.
                        if ($type !== 'item' && $mygradesactive) {
                            if (!$mygradesreleasedgrade) {
                                // If the grade is not released in MyGrades, we do not show it.
                                $gradeval = null;
                                $gradepassicon = '';
                                // Is it a non-released aggregated category normal grade?
                                $mygradesaggregatedgrade = \local_gugrades\grades::get_aggregated_from_gradeitemid(
                                    $gradegrade->grade_item->id,
                                    $userid
                                );
                                if ($mygradesaggregatedgrade) {
                                    if (isset($this->viewasuser) && !$this->viewasuser) {
                                        // If the user is a teacher, we show the aggregated level 1 category grade in Mygrades.
                                        $gradeval = $mygradesaggregatedgrade->convertedgrade + 1 - $mygradesaggregatedgrade->points;
                                        $class .= ' dimmed_text';
                                        $gradepassicon = $OUTPUT->pix_icon(
                                            'i/grading',
                                            get_string('unreleased', 'gradereport_uofguser'),
                                            null,
                                            ['class' => 'inline']
                                        );
                                    }
                                }
                            }
                        }

                        $data['grade']['class'] = "{$class} {$gradestatusclass}";
                        $data['grade']['content'] = $gradepassicon . grade_format_gradevalue($gradeval,
                                $gradegrade->grade_item, true) . $gradestatus;
                        $gradeitemdata['graderaw'] = $gradeval;
                    }
                    // Dealing with admin grades in MyGrades.
                    if ($mygradesreleasedflag && $mygradesreleasedgrade->admingrade !== '') {
                        $data['grade']['content'] = \local_gugrades\admingrades::get_displaygrade_from_name(
                            $mygradesreleasedgrade->admingrade
                        )[1];
                    }
                    // Dealing with unreleased aggregated admin or missing grades if MyGrades active.
                    if ($type !== 'item' && $mygradesactive) {
                        if (!$mygradesreleasedgrade) {
                            // Is it a non-released aggregated category admin grade?
                            $mygradesaggregatedgrade = \local_gugrades\grades::get_aggregated_from_gradeitemid(
                                $gradegrade->grade_item->id,
                                $userid
                            );
                            if ($mygradesaggregatedgrade) {
                                if (isset($this->viewasuser) && !$this->viewasuser) {
                                    // If the user is a teacher, we show the admin grade.
                                    $class .= ' dimmed_text';
                                    $gradepassicon = $OUTPUT->pix_icon(
                                        'i/grading',
                                        get_string('unreleased', 'gradereport_uofguser'),
                                        null,
                                        ['class' => 'inline']
                                    );
                                    // Admin grade?
                                    if ($mygradesaggregatedgrade->admingrade !== '') {
                                        $data['grade']['content'] = $gradepassicon .
                                        \local_gugrades\admingrades::get_displaygrade_from_name(
                                            $mygradesaggregatedgrade->admingrade
                                        )[1];
                                    }
                                    // Missing grade?
                                    if ($mygradesaggregatedgrade->convertedgrade == null) {
                                        $data['grade']['content'] = $mygradesaggregatedgrade->displaygrade;
                                    }
                                }
                            }
                        }
                    }

                    $data['grade']['headers'] = "$headercat $headerrow grade$userid";
                    $gradeitemdata['gradeformatted'] = $data['grade']['content'];
                    // If the current grade item need to show a grade action menu, generate the appropriate output.
                    if ($gradeactionmenu = $this->gtree->get_grade_action_menu($gradegrade)) {
                        $gradecontainer = html_writer::div($data['grade']['content']);
                        $grademenucontainer = html_writer::div($gradeactionmenu, 'ps-1 d-flex align-items-center');
                        $data['grade']['content'] = html_writer::div($gradecontainer . $grademenucontainer,
                            'd-flex align-items-center');
                    }
                }

                // Range.
                if ($this->showrange) {
                    $data['range']['class'] = $class;
                    $data['range']['content'] = $gradegrade->grade_item->get_formatted_range(
                        GRADE_DISPLAY_TYPE_REAL,
                        $this->rangedecimals
                    );
                    $data['range']['headers'] = "$headercat $headerrow range$userid";

                    $gradeitemdata['rangeformatted'] = $data['range']['content'];
                    $gradeitemdata['grademin'] = $gradegrade->grade_item->grademin;
                    $gradeitemdata['grademax'] = $gradegrade->grade_item->grademax;
                }

                // Percentage.
                if ($this->showpercentage) {
                    if ($gradegrade->grade_item->needsupdate) {
                        $data['percentage']['class'] = $class.' gradingerror';
                        $data['percentage']['content'] = get_string('error');
                    } else if ($gradegrade->is_hidden()) {
                        $data['percentage']['class'] = $class;
                        $data['percentage']['content'] = '-';
                        if ($this->canviewhidden) {
                            $data['percentage']['class'] .= ' dimmed_text';
                            $data['percentage']['content'] = grade_format_gradevalue(
                                $gradeval,
                                $gradegrade->grade_item,
                                true,
                                GRADE_DISPLAY_TYPE_PERCENTAGE
                            );
                        }
                    } else {
                        $data['percentage']['class'] = $class;
                        $data['percentage']['content'] = grade_format_gradevalue(
                            $gradeval,
                            $gradegrade->grade_item,
                            true,
                            GRADE_DISPLAY_TYPE_PERCENTAGE
                        );
                    }
                    $data['percentage']['headers'] = "$headercat $headerrow percentage$userid";
                    $gradeitemdata['percentageformatted'] = $data['percentage']['content'];
                }

                // Lettergrade.
                if ($this->showlettergrade) {
                    if ($gradegrade->grade_item->needsupdate) {
                        $data['lettergrade']['class'] = $class.' gradingerror';
                        $data['lettergrade']['content'] = get_string('error');
                    } else if ($gradegrade->is_hidden()) {
                        $data['lettergrade']['class'] = $class;
                        if (!$this->canviewhidden) {
                            $data['lettergrade']['class'] .= ' dimmed_text';
                            $data['lettergrade']['content'] = '-';
                        } else {
                            $data['lettergrade']['content'] = grade_format_gradevalue(
                                $gradeval,
                                $gradegrade->grade_item,
                                true,
                                GRADE_DISPLAY_TYPE_LETTER
                            );
                        }
                    } else {
                        $data['lettergrade']['class'] = $class;
                        $data['lettergrade']['content'] = grade_format_gradevalue(
                            $gradeval,
                            $gradegrade->grade_item,
                            true,
                            GRADE_DISPLAY_TYPE_LETTER
                        );
                    }
                    $data['lettergrade']['headers'] = "$headercat $headerrow lettergrade$userid";
                    $gradeitemdata['lettergradeformatted'] = $data['lettergrade']['content'];
                }

                // Rank.
                if ($this->showrank) {
                    $gradeitemdata['rank'] = 0;
                    if ($gradegrade->grade_item->needsupdate) {
                        $data['rank']['class'] = $class.' gradingerror';
                        $data['rank']['content'] = get_string('error');
                    } else if ($gradegrade->is_hidden()) {
                        $data['rank']['class'] = $class;
                        $data['rank']['content'] = '-';
                        if ($this->canviewhidden) {
                            $data['rank']['class'] .= ' dimmed_text';
                        }
                    } else if (is_null($gradeval)) {
                        // No grade, o rank.
                        $data['rank']['class'] = $class;
                        $data['rank']['content'] = '-';

                    } else {
                        // Find the number of users with a higher grade.
                        $sql = "SELECT COUNT(DISTINCT(userid))
                                  FROM {grade_grades}
                                 WHERE finalgrade > ?
                                       AND itemid = ?
                                       AND hidden = 0";
                        $rank = $DB->count_records_sql($sql, [$gradegrade->finalgrade, $gradegrade->grade_item->id]) + 1;

                        $data['rank']['class'] = $class;
                        $numusers = $this->get_numusers(false);
                        $data['rank']['content'] = "$rank/$numusers"; // Total course users.

                        $gradeitemdata['rank'] = $rank;
                        $gradeitemdata['numusers'] = $numusers;
                    }
                    $data['rank']['headers'] = "$headercat $headerrow rank$userid";
                }

                // Source.
                if ($this->showsource && $this->canviewsource) {
                    $data['source']['content'] = '-';
                    $data['source']['class'] = $class;
                    $data['source']['headers'] = "$headercat $headerrow source$userid";
                    if ($gradeitemdata['itemtype'] == 'mod' || $gradeitemdata['itemtype'] == 'manual') {
                        $data['source']['content'] =
                            ($mygradesreleasedflag) ?
                                get_string('source_mygrades', 'gradereport_uofguser') :
                                get_string('source_gradebook', 'gradereport_uofguser');
                    } else {
                        if ($gradeitemdata['itemtype'] == 'category' || $gradeitemdata['itemtype'] == 'course') {
                            $data['source']['content'] =
                                ($mygradesactive) ?
                                    get_string('source_mygrades', 'gradereport_uofguser') :
                                    get_string('source_gradebook', 'gradereport_uofguser');
                        }
                    }
                }

                // Status.
                if ($this->showstatus && $this->canviewstatus) {
                    $data['status']['content'] = '-';
                    $data['status']['class'] = $class;
                    $data['status']['headers'] = "$headercat $headerrow status$userid";

                    if ($gradeitemdata['itemtype'] == 'mod') {
                        $data['status']['content'] = get_string('status_text_submissionunavailable', 'gradereport_uofguser');

                        // If the activity is already graded anywhere, we show the Graded status.
                        if ($gradeval) {
                            $status = get_string('status_text_graded', 'gradereport_uofguser');
                        } else {
                            // Use the Student MyGrades API to get the supported activity.
                            $studentmygradesactivity = \block_newgu_spdetails\activity::activity_factory(
                                $gradegrade->itemid,
                                $cm->course
                            );
                            // Get the status of the activity for the current user.
                            $studentmygradesstatus = $studentmygradesactivity->get_status(
                                $this->user->id
                            );

                            $status = $studentmygradesstatus->status_text;
                        }

                        $statusicons = gradereport_uofguser_status_icons($OUTPUT);

                        // Loop through the map and check if $status matches the localized string.
                        foreach ($statusicons as $statuskey => $icon) {
                            $localizedstring = get_string($statuskey, 'gradereport_uofguser');
                            if ($status === $localizedstring) {
                                $data['status']['content'] = $icon . ' ' . $localizedstring;
                                break;
                            }
                        }
                        // If the grade is released in MyGrades, we either show the TBC status (hidden in MyGrades)
                        // or the graded status (not hidden in MyGrades).
                        // The hidden activities are greyed out and do not have links in the 1st column.
                        if ($mygradesreleasedflag) {
                            if ($mygradeshidden) {
                                $data['status']['content'] = $statusicons['status_text_tobeconfirmed'] . ' ' .
                                    get_string('status_text_tobeconfirmed', 'gradereport_uofguser');
                            } else {
                                $data['status']['content'] = $statusicons['status_text_graded'] . ' ' .
                                    get_string('status_text_graded', 'gradereport_uofguser');
                            }
                        }
                    }
                }

                // Average.
                if ($this->showaverage) {
                    $data['average']['class'] = $class;
                    $cache = \cache::make_from_params(\cache_store::MODE_REQUEST, 'gradereport_user', 'averages');
                    $avg = $cache->get(get_class($this));

                    $data['average']['content'] = $avg[$eid]->text;;
                    $gradeitemdata['averageformatted'] = $avg[$eid]->text;;
                    $data['average']['headers'] = "$headercat $headerrow average$userid";
                }

                // Feedback.
                if ($this->showfeedback) {
                    $gradeitemdata['feedback'] = '';
                    $gradeitemdata['feedbackformat'] = $gradegrade->feedbackformat;

                    if ($gradegrade->feedback) {
                        $gradegrade->feedback = file_rewrite_pluginfile_urls(
                            $gradegrade->feedback,
                            'pluginfile.php',
                            $gradegrade->get_context()->id,
                            GRADE_FILE_COMPONENT,
                            GRADE_FEEDBACK_FILEAREA,
                            $gradegrade->id
                        );
                    }

                    $data['feedback']['class'] = $classfeedback.' feedbacktext';
                    if (empty($gradegrade->feedback) || (!$this->canviewhidden && $gradegrade->is_hidden())) {
                        $data['feedback']['content'] = '&nbsp;';
                    } else {
                        $data['feedback']['content'] = format_text($gradegrade->feedback, $gradegrade->feedbackformat,
                            ['context' => $gradegrade->get_context()]);
                        $gradeitemdata['feedback'] = $gradegrade->feedback;
                    }
                    $data['feedback']['headers'] = "$headercat $headerrow feedback$userid";
                }
                // Contribution to the course total column.
                if ($this->showcontributiontocoursetotal) {
                    $data['contributiontocoursetotal']['class'] = $class;
                    $data['contributiontocoursetotal']['content'] = '-';
                    $data['contributiontocoursetotal']['headers'] = "$headercat $headerrow contributiontocoursetotal$userid";

                }
                $this->gradeitemsdata[] = $gradeitemdata;
            }

            $parent = $gradeobject->load_parent_category();
            if ($gradeobject->is_category_item()) {
                $parent = $parent->load_parent_category();
            }

            // We collect the aggregation hints whether they are hidden or not.
            if ($this->showcontributiontocoursetotal) {
                $hint['grademax'] = $gradegrade->grade_item->grademax;
                $hint['grademin'] = $gradegrade->grade_item->grademin;
                $hint['grade'] = $gradeval;
                $hint['parent'] = $parent->load_grade_item()->id;
                $this->aggregationhints[$gradegrade->itemid] = $hint;
            }
            // Get the IDs of all parent categories of this grading item.
            $data['parentcategories'] = array_filter(explode('/', $gradeobject->parent_category->path));
        }

        // Category.
        if ($type == 'category') {
            // Determine directionality so that icons can be modified to suit language.
            $arrow = right_to_left() ? 'left' : 'right';
            // Alter style based on whether aggregation is first or last.
            if ($this->switch) {
                $data['itemname']['class'] = $class . ' ' . "d$depth b1b b1t category";
            } else {
                $data['itemname']['class'] = $class . ' ' . "d$depth b2t category";
            }
            $data['itemname']['colspan'] = ($this->maxdepth - $depth + count($this->tablecolumns));
            $data['itemname']['content'] = $OUTPUT->render_from_template('gradereport_user/user_report_category_content',
                ['categoryid' => $gradeobject->id, 'categoryname' => $fullname, 'arrow' => $arrow]);
            $data['itemname']['id'] = "cat_{$gradeobject->id}_{$this->user->id}";
            // Get the IDs of all parent categories of this grade category.
            $data['parentcategories'] = array_diff(array_filter(explode('/', $gradeobject->path)), [$gradeobject->id]);

            $rowspandata['leader']['class'] = $class . " d$depth b1t b2b b1l";
            $rowspandata['leader']['rowspan'] = $element['rowspan'];
            $rowspandata['parentcategories'] = array_filter(explode('/', $gradeobject->path));
            $rowspandata['spacer'] = true;
        }

        // Add this row to the overall system.
        foreach ($data as $key => $celldata) {
            if (isset($celldata['class'])) {
                $data[$key]['class'] .= ' column-' . $key;
            }
        }

        $this->tabledata[] = $data;

        if (!empty($rowspandata)) {
            $this->tabledata[] = $rowspandata;
        }

        // Recursively iterate through all child elements.
        if (isset($element['children'])) {
            foreach ($element['children'] as $key => $child) {
                $this->fill_table_recursive($element['children'][$key], $mygradesactive);
            }
        }

        // Check we are showing this column, and we are looking at the root of the table.
        // This should be the very last thing this fill_table_recursive function does.
        if ($this->showcontributiontocoursetotal && ($type == 'category' && $depth == 1)) {
            // We should have collected all the hints by now - walk the tree again and build the contributions column.
            $this->fill_contributions_column($element);
        }
    }


    /**
     * Trigger the grade_report_viewed event
     *
     * @since Moodle 2.9
     */
    public function viewed() {
        $event = \gradereport_uofguser\event\grade_report_viewed::create(
            [
                'context' => $this->context,
                'courseid' => $this->courseid,
                'relateduserid' => $this->user->id,
                'other' => ['viewasuser' => $this->viewasuser ?? null],
            ]
        );
        $event->trigger();
    }
}
