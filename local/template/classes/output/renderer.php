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

    public function render_import_search_form($courseid) {
        $url = new \moodle_url('/local/template/index.php', ['id' => $courseid]);
        $search = new \import_course_search(['url' => $url]);

        global $PAGE;
        $backuprenderer = $PAGE->get_renderer('core','backup');
        $courses = $backuprenderer->render($search);

        // TODO: correct context?
        $context = \context_course::instance($courseid);

        $data = (object)[
            'url' => '',
            'courseid' => $courseid,
            'target' => \backup::TARGET_CURRENT_ADDING,
            'courses' => $courses,
            'contextid' => $context->id,
        ];
        return parent::render_from_template('local_template/import-course-selector', $data);
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

    //\local_template\models\template $template,
    public function render_stepper($step, $data = []) {
        global $CFG;
        $template = '';

        switch ($step) {
            case \local_template\forms\template::STEPPER_HEADER:
                $template = 'local_template/stepper-header';
                break;
            case \local_template\forms\template::STEPPER_INTRODUCTION:
                $template = 'local_template/stepper-introduction';
                $data = $this->get_data_introduction();
                break;
            case \local_template\forms\template::STEPPER_TEMPLATE:
                $template = 'local_template/stepper-template';
                $data = $this->get_data_template($template);
                break;
            case \local_template\forms\template::STEPPER_COURSE_START:
                $data = (object)['step' => 3];
                $template = 'local_template/stepper-step-header';
                break;
            case \local_template\forms\template::STEPPER_COURSE_END:
                $data = (object)['buttons' => true];
                $template = 'local_template/stepper-step-footer';
                break;
            case \local_template\forms\template::STEPPER_DESCRIPTION_START:
                $data = (object)['step' => 4];
                $template = 'local_template/stepper-step-header';
                break;
            case \local_template\forms\template::STEPPER_DESCRIPTION_END:
                $data = (object)['buttons' => true];
                $template = 'local_template/stepper-step-footer';
                break;
            case \local_template\forms\template::STEPPER_ENROLMENT_START:
                $data = (object)['step' => 5];
                $template = 'local_template/stepper-step-header';
                break;
            case \local_template\forms\template::STEPPER_ENROLMENT_END:
                $data = (object)['buttons' => true];
                $template = 'local_template/stepper-step-footer';
                break;
            case \local_template\forms\template::STEPPER_IMPORT_START:
                $data = (object)['step' => 6];
                $template = 'local_template/stepper-step-header';
                break;
            case \local_template\forms\template::STEPPER_IMPORT_END:
                $data = (object)['buttons' => false];
                $template = 'local_template/stepper-step-footer';
                break;
            case \local_template\forms\template::STEPPER_FOOTER:
                $template = 'local_template/stepper-footer';
                break;
            case \local_template\forms\template::STEPPER_JAVASCRIPT:
                $template = 'local_template/javascript';
                break;
        }

        return parent::render_from_template($template, $data);
    }

    private function get_data_introduction() {
        global $CFG;

        $introduction = get_config('local_template', 'introduction');
        $categoryid = optional_param('category', 0, PARAM_INT); // Course category - can be changed in edit form.
        $returnto = optional_param('returnto', 0, PARAM_ALPHANUM); // Generic navigation return page switch.
        $returnurl = optional_param('returnurl', '', PARAM_LOCALURL); // A return URL. returnto must also be set to 'url'.

        $params = [];
        if (!empty($categoryid)) {
            $params['category'] = $categoryid;
        }
        if (!empty($returnto)) {
            $params['returnto'] = $returnto;
        }
        if (!empty($returnurl)) {
            $params['returnurl'] = $returnurl;
        }

        $addnewcourselink = (new \moodle_url($CFG->wwwroot . '/course/edit.php', $params))->out();

        return [
            'step' => 1,
            'introduction' => $introduction,
            'addnewcourselink' =>  $addnewcourselink
        ];
    }

    private function get_data_template()
    {

        global $DB, $USER, $CFG;
        $templatecategories = [];
        $templatecoursecount = 0;
        $settingscategories = get_config('local_template', 'categories');
        $settingscategoryids = array_filter(explode(',', $settingscategories));

        foreach ($settingscategoryids as $settingscategoryid) {
            if (empty($settingscategoryid)) {
                continue;
            }
            $category = \core_course_category::get($settingscategoryid);
            $courses = $category->get_courses(['summary']); //  'sort' => ['timecreated' => -1]
            $templatecourses = [];
            foreach ($courses as $course) {
                $templatecourse = (object)[
                    'courseid' => $course->id,
                    'fullname' => $course->get_formatted_fullname(),
                    'image' => \core_course\external\course_summary_exporter::get_course_image($course),
                    'summary' => $course->summary,
                    'url' => (new \moodle_url($CFG->wwwroot . '/course/view.php', ['id' => $course->id]))->out(),
                    'format' => $course->format,

                ];

                // TODO: Mute text
                // course_get_format($course)->uses_sections()

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
                'description' => format_text($category->description),
            ];
            $templatecategories[] = $templatecategory;
        }

        $message = '';
        if (empty($templatecoursecount)) {
            $message = get_string('notemplatesfound', 'local_template');
        }

        return (object) [
            'step' => 2,
            'categories' => $templatecategories,
            'message' => $message
        ];

/*

        foreach ($templatecategories as $templatecategory) {
            $courseshtml = '';
            foreach ($templatecategory->courses as $templatecourse) {


                //$preview = $OUTPUT->pix_icon('t/preview', 'preview');
                //$buttons = $OUTPUT->single_button( , $preview, 'get');
                $url = (new moodle_url($CFG->wwwroot . '/course/view.php', ['id' => $templatecourse->id]))->out();

                $buttons = '<a type="button" href="' . $url . '" class="btn btn-success">Preview &nbsp;<i class="fa fa-search-plus"></i></a>
                    <button type="button" class="btn btn-success add-template" data-id="' . $templatecourse->id . '">Use template &nbsp;<i class="fa fa-plus"></i></button><br>';

                $html = '<div class="card" data-toggle="modal" data-target="#course-modal-' . $templatecourse->id .'">
                    <div class="card-header">
                        <img class="card-img-top w-100" alt="' . $templatecourse->fullname .'" data-lazy="' . $templatecourse->image .'">
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">' . $templatecourse->fullname .'</h5>
                        <p class="card-text">' . $templatecourse->summary .'</p>
                    </div>
                    <div class="card-footer">
                        ' . $buttons . '
                        <small class="text-muted">
                            <i class="fa fa-list"></i> ' . $templatecourse->format .'
                        </small>
                    </div>
                </div>';
                $templatecourse->html = $html;
                $courseshtml .= $html;
            }
            $html = '<div class="slider-container">
                                    <h1>' . $templatecategory->name .'</h1>

                                    <!-- lastload progressive -->
                                    <section id="block_slick-slider-' . $templatecategory->id .'" class="responsive slider card-deck"
                                             data-adaptive-height="true"
                                             data-slick=\'{
            "arrows": true,
            "dots": true,
            "infinite": true,
            "slidesToShow": 4,
            "slidesToScroll": 4,
            "lazyLoad": "progressive",
            "responsive": [
                {"breakpoint": 1200, "settings": {"lazyLoad": "progressive", "slidesToShow": 3, "slidesToScroll": 3}},
                {"breakpoint": 1024, "settings": {"lazyLoad": "ondemand", "slidesToShow": 2, "slidesToScroll": 2}},
                {"breakpoint":  600, "settings": {"lazyLoad": "ondemand", "slidesToShow": 1, "slidesToScroll": 1}},
                {"breakpoint":  480, "settings": "unslick"}
                ]
        }\'>' . $courseshtml . '</section></div>';

            $templatecategory->html = $html;
            $categorieshtml .= $html;
        }

*/
    }

}
