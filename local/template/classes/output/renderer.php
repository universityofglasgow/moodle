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

namespace local_template\output;

defined('MOODLE_INTERNAL') || die;

use local_template\collections\persistentcollection;
use plugin_renderer_base;
use local_template\models\template;
use local_template\models\backupcontroller;

class renderer extends plugin_renderer_base {

    /**
     * Defer to template.
     *
     * @param persistentcollection $persistentcollection
     *
     * @return string html for the collection
     */
    public function render_persistentcollection($persistentcollection) {
        $data = $persistentcollection->export_for_template($this);
        return parent::render_from_template('local_template/collection', $data);
    }

    /**
     * Defer to template.
     *
     * @param page $page
     *
     * @return string html for the page
     */
    public function render_page($persistentcollection) {
        $data = $persistentcollection->export_for_template($this);
        return parent::render_from_template('local_template/page', $data);
    }

    /**
     * Defer to template.
     *
     * @param template $template
     *
     * @return string html for the page
     */
    public function render_template($template) {
        $data = $template->export_for_template($this);
        return parent::render_from_template('local_template/template', $data);
    }

    /**
     * @param $step
     * @param $data
     * @return bool|string
     * @throws \moodle_exception
     */
    public function render_stepper($step, $data = []) {
        $template = '';

        switch ($step) {
            case \local_template\forms\template::STEPPER_HEADER:
                $template = 'local_template/stepper-header';
                break;
            case \local_template\forms\template::STEPPER_SELECTTEMPLATE:
                $template = 'local_template/stepper-template';
                $data = $this->get_data_template($template);
                break;
            case \local_template\forms\template::STEPPER_COURSE_START:
                $data = (object)['step' => 2];
                $template = 'local_template/stepper-step-header';
                break;
            case \local_template\forms\template::STEPPER_COURSE_END:
                $data = (object)['buttons' => true];
                $template = 'local_template/stepper-step-footer';
                break;
            case \local_template\forms\template::STEPPER_DESCRIPTION_START:
                $data = (object)['step' => 3];
                $template = 'local_template/stepper-step-header';
                break;
            case \local_template\forms\template::STEPPER_DESCRIPTION_END:
                $data = (object)['buttons' => true];
                $template = 'local_template/stepper-step-footer';
                break;
            case \local_template\forms\template::STEPPER_ENROLMENT_START:
                $data = (object)['step' => 4];
                $template = 'local_template/stepper-step-header';
                break;
            case \local_template\forms\template::STEPPER_ENROLMENT_END:
                $data = (object)['buttons' => true];
                $template = 'local_template/stepper-step-footer';
                break;
            case \local_template\forms\template::STEPPER_IMPORT_START:
                $data = (object)['step' => 5];
                $template = 'local_template/stepper-step-header';
                break;
            case \local_template\forms\template::STEPPER_IMPORT_END:
                $data = (object)['buttons' => true];
                $template = 'local_template/stepper-step-footer';
                break;
            case \local_template\forms\template::STEPPER_PROCESS_START:
                $data = (object)['step' => 6];
                $template = 'local_template/stepper-step-header';
                break;
            case \local_template\forms\template::STEPPER_PROCESS_END:
                $data = (object)['buttons' => false];
                $template = 'local_template/stepper-step-footer';
                break;
            case \local_template\forms\template::STEPPER_FOOTER:
                $template = 'local_template/stepper-footer';
                break;
        }

        return parent::render_from_template($template, $data);
    }

    public function render_high_compatability() {
        $template = 'local_template/high-compatability';
        $data = $this->get_data_template($template);
        return parent::render_from_template($template, $data);
    }

    private function get_data_template() {
        global $CFG;
        $introduction = get_config('local_template', 'introduction');

        $addnewcourselink = \local_template\models\template::get_addnewcourselink();

        $view = template::get_current_view();
        $slider = false;
        if ($view == template::VIEW_SLIDER) {
            $slider = true;
        }

        $templatecategories = [];
        $templatecoursecount = 0;
        $settingscategories = get_config('local_template', 'categories');
        $settingscategoryids = [];

        if (!empty($settingscategories)) {
            // Reduce set of template categories based on user capability in each category.
            $categories = explode(',', $settingscategories);
            if (!empty($categories)) {
                foreach ($categories as $categoryid) {
                    if (has_capability('local/template:usetemplate', \context_coursecat::instance($categoryid))) {
                        $settingscategoryids[] = $categoryid;
                    }
                }
            }
        }

        foreach ($settingscategoryids as $settingscategoryid) {
            if (empty($settingscategoryid)) {
                continue;
            }

            $category = \core_course_category::get($settingscategoryid, MUST_EXIST, true);
            $context = \context_coursecat::instance($settingscategoryid);
            if (!$category->visible && !has_capability('moodle/category:viewhiddencategories', $context)) {
                continue;
            }

            $courses = $category->get_courses(['summary']); //  'sort' => ['timecreated' => -1]
            $templatecourses = [];
            foreach ($courses as $course) {

                $hiddensections = 0;
                $visiblesections = 0;

                $courseformat = course_get_format($course);
                $usessections = $courseformat->uses_sections();

                if ($usessections) {
                    $sections = $courseformat->get_sections();
                    foreach ($sections as $section) {
                        if ($section->visible) {
                            $visiblesections ++;
                        } else {
                            $hiddensections ++;
                        }
                    }
                }

                $courseimage = \core_course\external\course_summary_exporter::get_course_image($course);
                if (empty($courseimage)) {
                    $courseimage = new \moodle_url('/local/template/pix/course.jpg');
                    global $OUTPUT;
                    $courseimage = $OUTPUT->get_generated_image_for_id($course->id);
                }

                $templatecourse = (object)[
                    'courseid' => $course->id,
                    'fullname' => $course->get_formatted_fullname(),
                    'image' => $courseimage,
                    'summary' => shorten_text(html_to_text($course->summary, 0), 100),
                    'url' => (new \moodle_url($CFG->wwwroot . '/course/view.php', ['id' => $course->id]))->out(),
                    'format' => $course->format,
                    'author' => $this->get_course_user($course->id),
                    'timemodified' => $course->timemodified,
                    'visiblesections' => $visiblesections,
                    'hiddensections' => $hiddensections,
                ];


                $templatecourses[] = $templatecourse;
                $templatecoursecount++;
            }

            $description = '';
            if (isset($category->description)) {
                $description = format_text($category->description);
            }
            $templatecategory = (object)[
                'categoryname' => $category->get_formatted_name(),
                'categoryid' => $category->id,
                'courses' => $templatecourses,
                'description' => $description,
                'visible' => $category->visible,
            ];
            $templatecategories[] = $templatecategory;
        }

        $message = '';
        if (empty($templatecoursecount)) {
            $message = get_string('notemplatesfound', 'local_template');
        }

        return (object) [
            'step' => 1,
            'introduction' => $introduction,
            'addnewcourselink' =>  $addnewcourselink,
            'categories' => $templatecategories,
            'message' => $message,
            'slider' => $slider
        ];
    }

    private function get_course_user($courseid) {
        global $DB;
        $where = "
            eventname IN ('\\\\core\\\\event\\\\course_created', '\\\\core\\\\event\\\\course_updated', '\\\\core\\\\event\\\\course_restored')
            AND courseid = ?
        ";

        $logitems = $DB->get_records_select('logstore_standard_log', $where, [$courseid], 'timecreated DESC', 'id, userid', 0, 1);
        if (empty($logitems)) return null;
        foreach ($logitems as $logitem) {
            return fullname(\core_user::get_user($logitem->userid));
        }

        return null;
    }

}
