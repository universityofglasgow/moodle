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

        $data = $DB->get_records_sql('SELECT 
                                        ROW_NUMBER() OVER(ORDER BY mdl_grade_items.itemname) AS RowNumber,
                                        mdl_course.fullname,
                                        mdl_grade_items.itemname,
                                        mdl_grade_items.aggregationcoef2 * 100 as `weight`,
                                        "duedate",
                                        "status",
                                        mdl_grade_grades.finalgrade,
                                        mdl_grade_grades.feedback
                                    FROM
                                        mdl_grade_items,
                                        mdl_course,
                                        mdl_course_categories,
                                        mdl_grade_grades
                                    WHERE
                                        mdl_grade_items.courseid = mdl_course.id AND
                                        mdl_grade_items.categoryid = mdl_course_categories.id AND
                                        mdl_grade_grades.itemid = mdl_grade_items.id AND
                                        mdl_grade_grades.userid = ?', [$USER->id]);

        $dataArray = array();
        foreach ($data as $row){
            array_push($dataArray, $row);
        }

        $templatecontext = (array)[
            'data' => $dataArray
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
