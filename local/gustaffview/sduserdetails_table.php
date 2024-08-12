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
 * This class implements Moodle's Table API in order to provide assessment
 * data for a given student. Called via the standard web service approach.
 *
 * @package    local_gustaffview
 * @author     Shubhendra Diophode <shubhendra.doiphode@gmail.com>
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)).'../../config.php');
global $CFG;

require "$CFG->libdir/tablelib.php";

class sduserdetailscurrent_table extends table_sql
{

    /**
     * Constructor
     * @param int $unequeid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($unequeid)
    {
        parent::__construct($unequeid);
        // Define the list of columns to show.

        $columns = [
            'assessment',
            'assessmenttype',
            'weight',
            'itemmodule',
            'duedate',
            'includedingcat',
            'status',
            'grade',
            'feedback'
        ];

        $this->collapsible(false);
        $this->define_columns($columns);

        $tdr = optional_param('tdr', '', PARAM_INT);
        $ts = optional_param('ts', '', PARAM_ALPHA);
        $page = optional_param('page', 0, PARAM_INT);

        $tdrnew = 4;
        $tdirdd_icon = '';
        $tdirat_icon = '';
        $tdiract_icon = '';
        switch($ts) {
            case 'assessmenttype':
                $tdirat_icon = ' <i class="fa fa-caret-';
                switch ($tdr) {
                    case 3:
                        $tdirat_icon .= 'up';
                        break;
                    case 4:
                        $tdirat_icon .= 'down';
                        $tdrnew = 3;
                        break;

                }
                $tdirat_icon .= '" data-ts="assessmenttype" data-tdr="' . $tdrnew . '"></i>';
                break;

            case 'itemmodule':
                $tdiract_icon = ' <i class="fa fa-caret-';
                switch ($tdr) {
                    case 3:
                        $tdiract_icon .= 'up';
                        break;
                    case 4:
                        $tdiract_icon .= 'down';
                        $tdrnew = 3;
                        break;

                }
                $tdiract_icon .= '" data-ts="itemmodule" data-tdr="' . $tdrnew . '"></i>';
                break;

            case 'duedate':
                $tdirdd_icon = ' <i class="fa fa-caret-';
                switch($tdr) {
                    case 3:
                        $tdirdd_icon .= 'up';
                    break;

                    case 4:
                        $tdirdd_icon .= 'down';
                        $tdrnew = 3;
                    break;
                }
                $tdirdd_icon .= '" data-ts="duedate" data-tdr="' . $tdrnew . '"></i>';
                break;
        }

        $headers = [
            get_string('assessment'),
            '<a data-page="' . $page . '" data-ts="assessmenttype" data-tdr="' . $tdrnew . '" href="#">' . get_string('assessmenttype','block_newgu_spdetails') . $tdirat_icon . '</a>',
            get_string('weight', 'block_newgu_spdetails'),
            '<a data-page="' . $page . '" data-ts="itemmodule" data-tdr="' . $tdrnew . '" href="#">' . get_string('activity') . $tdiract_icon . '</a>',
            '<a data-page="' . $page . '" data-ts="duedate" data-tdr="' . $tdrnew . '" href="#">' . get_string('duedate','block_newgu_spdetails') . $tdirdd_icon . '</a>',
            get_string('source', 'block_newgu_spdetails'),
            get_string('status'),
            get_string('grade', 'local_gustaffview'),
            get_string('feedback')
        ];
        $this->define_headers($headers);

    }

    /**
     * @param $values
     * @return void
     */
    function col_assessment($values){
        global $CFG;
        $itemname = $values->itemname;

        $modulename = $values->itemmodule;
        $iteminstance = $values->iteminstance;
        $courseid = $values->courseid;

        $cmid = \block_newgu_spdetails\course::get_cmid($modulename, $courseid, $iteminstance);

        $link = $CFG->wwwroot . '/mod/' . $modulename . '/view.php?id=' . $cmid;

        if (!empty($link)) {
            return $itemname;
        }
    }

    /**
     * @param $values
     * @return mixed
     */
    function col_assessmenttype($values){

        global $DB;

        $courseid = $values->courseid;
        $categoryid = $values->categoryid;

        $arr_gradecategory = $DB->get_record('grade_categories',array('courseid'=>$courseid, 'id'=>$categoryid));
        if (!empty($arr_gradecategory)) {
            $gradecategoryname = $arr_gradecategory->fullname;
        }

        $aggregationcoef = $values->aggregationcoef;

        $assessmenttype = \block_newgu_spdetails\course::return_assessmenttype($gradecategoryname, $aggregationcoef);

        return $assessmenttype;

    }

    function col_weight($values){  
        $aggregationcoef = $values->aggregationcoef;
        $finalweight = \block_newgu_spdetails\course::return_weight($aggregationcoef);

        return $finalweight;
      }

    /**
     * @param $values
     * @return mixed
     */
    function col_itemmodule($values){
        return $values->itemmodule;
    }

    /**
     * @param $values
     * @return string
     */
    function col_duedate($values){

        global $DB;

        $userid = $values->userid;
        $modulename = $values->itemmodule;
        $iteminstance = $values->iteminstance;
        $courseid = $values->courseid;


        $duedate = 0;
        $extspan = "";

        // READ individual TABLE OF ACTIVITY (MODULE)
        if ($modulename!="") {
            $arr_duedate = $DB->get_record($modulename,array('course'=>$courseid, 'id'=>$iteminstance));

            if (!empty($arr_duedate)) {
                if ($modulename=="assign") {
                    $duedate = $arr_duedate->duedate;

                    $arr_userflags = $DB->get_record('assign_user_flags', array('userid'=>$userid, 'assignment'=>$iteminstance));

                    if ($arr_userflags) {
                        $extensionduedate = $arr_userflags->extensionduedate;
                        if ($extensionduedate>0) {
                            $extspan = '<a href="javascript:void(0)" title="' . get_string('extended', 'block_newgu_spdetails') . '" class="extended">*</a>';
                        }
                    }

                }
                if ($modulename=="forum") {
                    $duedate = $arr_duedate->duedate;
                }
                if ($modulename=="quiz") {
                    $duedate = $arr_duedate->timeclose;
                }
                if ($modulename=="workshop") {
                    $duedate = $arr_duedate->submissionend;
                }
            }
        }

        if ($duedate!=0) {
            return date("d/m/Y", $duedate) . $extspan;
        } else {
            return get_string('noduedate', 'block_newgu_spdetails');
        }
    }

    /**
     * @param $values
     * @return string
     */
    function col_includedingcat($values){
        $courseid = $values->courseid;
        $mygradesenabled = block_newgu_spdetails\course::is_type_mygrades($courseid);

        if ($mygradesenabled) {
            return get_string('mygradesenabled', 'local_gustaffview');
        }
        if (!$mygradesenabled) {
            return get_string('regulargradebook', 'local_gustaffview');
        }
    }

    /**
     * @param $values
     * @return string
     */
    function col_status($values){

        $userid = $values->userid;
        $modulename = $values->itemmodule;
        $iteminstance = $values->iteminstance;
        $courseid = $values->courseid;
        $itemid = $values->id;

        $gradestatus = \block_newgu_spdetails\grade::return_gradestatus($modulename, $iteminstance, $courseid, $itemid, $userid);

        $status = $gradestatus["status"];
        $finalgrade = $gradestatus["finalgrade"];
        $statustodisplay = "";

        if($status == 'tosubmit'){
            $statustodisplay = '<span class="status-item status-submit">'.get_string('readytosubmit', 'local_gustaffview').'</span> ';
        }
        if($status == 'notsubmitted'){
            $statustodisplay = '<span class="status-item">'.get_string('notsubmitted', 'block_newgu_spdetails').'</span> ';
        }
        if($status == 'submitted' || $status == 'graded'){
            $statustodisplay = '<span class="status-item status-submitted">'. ucwords(trim(get_string('submitted', 'block_newgu_spdetails'))) . '</span> ';
            if ($finalgrade !== null) {
                $statustodisplay = '<span class="status-item status-item status-graded">'.get_string('graded', 'block_newgu_spdetails').'</span>';
            }
        }
        if($status == "notopen"){
            $statustodisplay = '<span class="status-item">' . get_string('submissionnotopen', 'block_newgu_spdetails') . '</span> ';
        }
        if($status == "TO_BE_ASKED"){
            $statustodisplay = '<span class="status-item status-graded">' . get_string('individualcomponents', 'block_newgu_spdetails') . '</span> ';
        }
        if($status == "overdue"){
            $statustodisplay = '<span class="status-item status-overdue">' . get_string('overdue', 'block_newgu_spdetails') . '</span> ';
        }

        return $statustodisplay;

    }

    /**
     * @param $values
     * @return mixed
     */
    function col_grade($values){

        $userid = $values->userid;
        $modulename = $values->itemmodule;
        $iteminstance = $values->iteminstance;
        $courseid = $values->courseid;
        $itemid = $values->id;
        $gradetype = $values->gradetype;
        $arr_gradetodisplay = \block_newgu_spdetails\grade::get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $userid, $values->grademax, $gradetype);
        $gradetodisplay = $arr_gradetodisplay["gradetodisplay"];

        return $gradetodisplay;
    }

    /**
     * @param $values
     * @return mixed
     */
    function col_feedback($values){

        $userid = $values->userid;
        $modulename = $values->itemmodule;
        $iteminstance = $values->iteminstance;
        $courseid = $values->courseid;
        $itemid = $values->id;
        $gradetype = $values->gradetype;

        $feedback = \block_newgu_spdetails\grade::get_gradefeedback($modulename, $iteminstance, $courseid, $itemid, $userid, $values->grademax, $gradetype);
        $gradetodisplay = $feedback["gradetodisplay"];

        return $gradetodisplay;
    }
}
