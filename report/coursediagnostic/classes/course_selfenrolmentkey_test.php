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
 * For the Self enrolment method, has a key been set or not.
 *
 * This tests whether, if the self-enrolment method is being used, a key has
 * been set or not. Returns true if either self enrolment isn't being used,
 * or, if it is being used and a key has been set. Returns false otherwise.
 *
 * @package    report_coursediagnositc
 * @copyright  2023 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;
class course_selfenrolmentkey_test implements \report_coursediagnostic\course_diagnostic_interface {

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

        global $CFG;
        require_once("$CFG->dirroot/enrol/locallib.php");

        // We're only interested in the enabled Self enrolment methods.
        $enrolmentplugins = enrol_get_instances($this->course->id, true);
        $plugin = enrol_get_plugin('self');
        $selfenrolmentresult = true;
        $selfenrolmentmethodexists = false;
        $counter = 0;
        $enrolmentlinks = [];

        foreach ($enrolmentplugins as $enrolmentinstance) {
            if ($enrolmentinstance->enrol != 'self') {
                continue;
            }

            $selfenrolmentmethodexists = true;
            if ($enrolmentinstance->status == 0) {
                if (empty($enrolmentinstance->password)) {
                    $counter++;
                    $url = new \moodle_url('/enrol/editinstance.php', [
                        'id' => $enrolmentinstance->id,
                        'courseid' => $enrolmentinstance->courseid,
                        'type' => $enrolmentinstance->enrol
                    ]);

                    $displayname = $plugin->get_instance_name($enrolmentinstance);
                    $link = \html_writer::link($url, $displayname);
                    $enrolmentlinks[] = $link;
                    $selfenrolmentresult = false;
                }
            }
        }

        if ($selfenrolmentmethodexists == true) {
            if ($selfenrolmentresult == true) {
                $outcometext = get_string('selfenrolmentkey_success_text', 'report_coursediagnostic');
            } else {
                $options = [
                    'enrolmentlinks' => implode('<br />', $enrolmentlinks),
                    'word1' => (($counter > 1) ? get_string('plural_4', 'report_coursediagnostic') :
                        get_string('singular_4', 'report_coursediagnostic')),
                    'word2' => (($counter > 1) ? get_string('plural_5', 'report_coursediagnostic') :
                        get_string('singular_5', 'report_coursediagnostic'))
                ];
                $outcometext = get_string('selfenrolmentkey_missing_key_text', 'report_coursediagnostic', $options);
            }
        } else {
            $enrolmentpluginsurl = new \moodle_url('/enrol/instances.php', ['id' => $this->course->id]);
            $enrolmentpluginslink = \html_writer::link($enrolmentpluginsurl,
                get_string('enrolmentplugins_link_text', 'report_coursediagnostic'));
            $options = [
                'enrolmentmethodslink' => $enrolmentpluginslink
            ];
            $outcometext = get_string('selfenrolmentkey_not_enabled_text', 'report_coursediagnostic', $options);
        }

        $this->testresult = [
            'testresult' => $selfenrolmentresult,
            'outcometext' => $outcometext
        ];

        return $this->testresult;
    }
}
