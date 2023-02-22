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
 * Activity completion settings when completion is off in the course.
 *
 * If Activity Completion is off in the course, have any activity completion
 * settings been set in any activities linked to the course.
 *
 * @package    report_coursediagnositc
 * @copyright  2023 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;
class course_activitycompletion_test implements \report_coursediagnostic\course_diagnostic_interface {

    /** @var string The name of the test - needed w/in the report */
    public string $testname;

    /** @var object The course object */
    public object $course;

    /** @var array $testresult whether the test has passed or failed. */
    public array $testresult;

    /**
     * @param $name
     * @param $course
     */
    public function __construct($name, $course) {
        $this->testname = $name;
        $this->course = $course;
    }

    /**
     * @return array
     */
    public function runtest(): array {
        $coursecompletion = $this->course->enablecompletion;
        $activitycompletion = true;
        $counter = 0;
        $activitylinks = [];
        $settingslink = '';

        // If completion is currently not set in the course...
        if ($coursecompletion == 0) {
            // Get all activities associated with the course...
            $moduleinfo = get_fast_modinfo($this->course->id);
            $modules = $moduleinfo->get_used_module_names();
            $settingsurl = new \moodle_url('/course/edit.php', ['id' => $this->course->id]);
            $settingslink = \html_writer::link($settingsurl, get_string('settings_link_text', 'report_coursediagnostic'));
            foreach ($modules as $module) {
                $cminfo = $moduleinfo->get_instances_of($module->get_component());
                foreach ($cminfo as $moduledata) {
                    if ($moduledata->completion > 0) {
                        $counter++;
                        if ($moduledata->get_url() != null) {
                            $url = new \moodle_url('/course/modedit.php',
                                ['update' => $moduledata->url->param('id'), 'return' => 1]
                            );
                        } else {
                            // So, mod type 'label' doesn't contain an easy way
                            // to get the url to the edit page...
                            $url = new \moodle_url('/course/mod.php',
                                [
                                    'sesskey' => sesskey(),
                                    'sr' => $moduledata->sectionnum,
                                    'update' => $moduledata->id
                                ]
                            );
                        }
                        $link = \html_writer::link($url, $moduledata->get_name());
                        $activitylinks[] = $link;
                        // The 'Completion tracking' dropdown in the activity
                        // settings is something other than 'Show activity...'.
                        $activitycompletion = false;
                    }
                }
            }
        }

        $this->testresult = [
            'testresult' => $activitycompletion,
            'activitylinks' => $activitylinks,
            'settingslink' => $settingslink,
            'word1' => (($counter > 1) ? get_string('plural_3', 'report_coursediagnostic') :
                get_string('singular_3', 'report_coursediagnostic')),
            'word2' => (($counter > 1) ? get_string('plural_2', 'report_coursediagnostic') :
                get_string('singular_2', 'report_coursediagnostic'))
        ];

        return $this->testresult;
    }
}
