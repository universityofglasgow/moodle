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

namespace theme_hillhead40\output;

use context_coursecat;
use core_course_category;
use course_request;
use moodle_page;
use moodle_url;

/**
 *
 * Overrides core course class to inject additional options in dropdown
 * This class should be monitored for changes.
 *
 * Class responsible for generating the action bar (tertiary nav) elements in an individual category page
 *
 * @package    core
 * @copyright  2021 Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_hillhead40_category_action_bar extends \core_course\output\category_action_bar {


    /**
     *
     * Overrides core course class to inject additional options in dropdown
     * This function should be monitored for changes.
     *
     * Gets the additional options to be displayed within a 'More' dropdown in the tertiary navigation.
     * The predefined order defined by UX is:
     *  - Add a course
     *  - Add a sub cat
     *  - Manage course
     *  - Request a course
     *  - Course pending approval
     *
     * @return array
     */
    protected function get_additional_category_options(): array {
        if (!theme_hillhead40_exists_template_plugin()) {
            // Unreachable code. Defensive.
            return parent::get_additional_category_options();
        }

        global $CFG, $DB;
        if ($this->category->is_uservisible()) {
            $context = get_category_or_system_context($this->category->id);
            if (has_capability('moodle/course:create', $context)) {
                $params = [
                    'category' => $this->category->id ?: $CFG->defaultrequestcategory,
                    'returnto' => $this->category->id ? 'category' : 'topcat'
                ];

                $options[0] = [
                    'url' => new moodle_url('/course/edit.php', $params),
                    'string' => get_string('addnewcourse')
                ];
                $options[1] = [
                    'url' => new moodle_url('/local/template/index.php', $params),
                    'string' => get_string('addnewcourseviatemplate', 'local_template')
                ];
            }

            if (!empty($CFG->enablecourserequests)) {
                // Display an option to request a new course.
                if (course_request::can_request($context)) {
                    $params = [];
                    if ($context instanceof context_coursecat) {
                        $params['category'] = $context->instanceid;
                    }

                    $options[4] = [
                        'url' => new moodle_url('/course/request.php', $params),
                        'string' => get_string('requestcourse')
                    ];
                }

                // Display the manage pending requests option.
                if (has_capability('moodle/site:approvecourse', $context)) {
                    $disabled = !$DB->record_exists('course_request', array());
                    if (!$disabled) {
                        $options[5] = [
                            'url' => new moodle_url('/course/pending.php'),
                            'string' => get_string('coursespending')
                        ];
                    }
                }
            }
        }

        if ($this->category->can_create_course() || $this->category->has_manage_capability()) {
            // Add 'Manage' button if user has permissions to edit this category.
            $options[3] = [
                'url' => new moodle_url('/course/management.php', ['categoryid' => $this->category->id]),
                'string' => get_string('managecourses')
            ];

            if ($this->category->has_manage_capability()) {
                $addsubcaturl = new moodle_url('/course/editcategory.php', array('parent' => $this->category->id));
                $options[2] = [
                    'url' => $addsubcaturl,
                    'string' => get_string('addsubcategory')
                ];
            }
        }

        // We have stored the options in a predefined order. Sort it based on index and return.
        if (isset($options)) {

            // TODO: Original contains a bug (using sort does not: "Sort it based on index".
            ksort($options);
            return ['options' => $options];
        }

        return [];
    }
}
