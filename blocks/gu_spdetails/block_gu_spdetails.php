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
 * Contains the class for the UofG Assessments Details block.
 *
 * @package    block_gu_spdetails
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_gu_spdetails extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_gu_spdetails');
    }

    public function hide_header() {
        return TRUE;
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     * @todo Create a renderer for assessments block.
     *       See myoverview plugin for reference.
     */
    public function get_content() {
        global $OUTPUT, $DB, $USER;
        if ($this->content !== null) {
          return $this->content;
        }

        $languageString = 'block_gu_spdetails';

        $data = $DB->get_records_sql('SELECT DISTINCT
                                        cm.id,
                                        c.shortname,
                                        c.fullname,
                                        a.course,
                                        i.itemname,
                                        m.name,
                                        i.aggregationcoef2 * 100 `weight`,
                                        a.duedate,
                                        a.allowsubmissionsfromdate,
                                        asb.status,
                                        a.gradingduedate,
                                        g.finalgrade,
                                        g.feedback
                                    FROM
                                        `mdl_course_modules` cm
                                            INNER JOIN
                                        `mdl_course` c ON c.id = cm.course
                                            INNER JOIN
                                        `mdl_modules` m ON m.id = cm.module
                                            INNER JOIN
                                        `mdl_grade_items` i ON i.courseid = cm.course
                                            INNER JOIN
                                        `mdl_assign` a ON a.id = cm.instance
                                            AND a.name = i.itemname
                                            INNER JOIN
                                        `mdl_grade_grades` g ON g.itemid = i.id
                                            INNER JOIN
                                        `mdl_user` u ON u.id = g.userid
                                            INNER JOIN
                                        `mdl_assign_submission` asb ON asb.userid = g.userid
                                            AND asb.assignment = a.id
                                    WHERE
                                        m.name IN ("assign")
                                            AND i.itemmodule IS NOT NULL
                                            AND u.id = ?', [$USER->id]);

        $dataArray = array();
        foreach ($data as $row){
            //links
            $row->courselink   = new moodle_url('/course/view.php', array('id' => $row->course));
            $row->activitylink = new moodle_url('/' . $row->itemtype . '/' . $row->itemmodule . '/view.php', array('id' => $row->id + 1));
            
            //truncate floats to int
            $row->formatted->weight = strval(intval($row->weight));

            if (time() <= $row->gradingduedate && empty($row->finalgrade)){
                $row->formatted->finalgrade = get_string('due', $languageString) . userdate($row->gradingduedate,  get_string('strfdates', $languageString));
            } else if (!empty($row->finalgrade)){
                $row->formatted->finalgrade = strval($row->finalgrade);
            } else if (time() > $row->gradingduedate && empty($row->finalgrade)) {
                $row->formatted->finalgrade = get_string('nograde', $languageString);
            }

            //status
            $statuslink = null;
            if ($row->status == 'submitted') {
                    $status = get_string('submitted', $languageString);
                }
            else {
                if ($row->allowsubmissionsfromdate < time()) {
                    $status = get_string('notopen', $languageString);
                } else {
                    if ($row->duedate <= time()) {
                        $status = get_string('submitassessment', $languageString);
                        $status_url = new moodle_url('/mod/assign/view.php', array('id' => $row->id));
                    } else {
                        $status = get_string('overdue', $languageString);
                    }
                }
            }
            $row->formatted->status = $status;
            $row->formatted->statuslink = $status_url;
            //Unix date to actual date
            $row->formatted->duedate = userdate($row->duedate,  get_string('strfdates', $languageString));
            
            array_push($dataArray, $row);
        }

        $templatecontext = (array)[
            //Labels
            'title'             => get_string('gu_spdetails', $languageString),
            'currentenrolled'   => get_string('currentenrolled', $languageString),
            'pastcourse'        => get_string('pastcourse', $languageString),
            'sortby'            => get_string('sortby', $languageString),

            //header name
            'courselbl'         => get_string('course', $languageString),
            'assessmentlbl'     => get_string('assessment', $languageString),
            'weightlbl'         => get_string('weight', $languageString),
            'duedatelbl'        => get_string('duedate', $languageString),
            'statuslbl'         => get_string('status', $languageString),
            'gradelbl'          => get_string('grade', $languageString),
            'feedbacklbl'       => get_string('feedback', $languageString),
            
            //Records retrieved from DBS
            'data' => $dataArray,

            //Sort by Options
            'sortbyoptions' => array(
                (object) [
                    'value' => 1,
                    'text'  => get_string('course', $languageString)
                ],
                (object) [
                    'value' => 2,
                    'text'  => get_string('duedate', $languageString)
                ]
            )
        ];

        $this->content         = new stdClass();
        $this->content->text   = $OUTPUT->render_from_template('block_gu_spdetails/spdetails', $templatecontext);
     
        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }
}
