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
 * Class for Course Diagnostic
 *
 * Provides the functionality for running course diagnostics. This was
 * previously handled by procedural code embedded w/in the course page.
 *
 * @package    report_coursediagnositc
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;
class coursediagnostic {

    const CACHE_KEY = 'courseid:';

    /**
     * @var object - the cache store being used by the site.
     */
    private static object $cache;

    /**
     * @var array|array[] - a simple list of ranges we can refer to
     */
    protected static array $alertranges = [
        'info' => [
            'min' => 1,
            'max' => 34,
        ],
        'warning' => [
            'min' => 35,
            'max' => 69,
        ],
        'error' => [
            'min' => 70,
            'max' => 100,
        ]
    ];

    /**
     * @var array Contains the results of all selected tests, ready for caching
     */
    protected static array $diagnosticdata = [];

    /**
     * @var bool Needed for when the settings page is submitted.
     */
    protected static bool $purgeflag = false;

    /**
     * @return bool
     * @throws \dml_exception
     */
    public static function cfg_settings_check(): bool {

        global $SESSION;

        // To avoid a call to the db for the values each time this event is
        // triggered, make use of the session.
        if (!isset($SESSION->report_coursediagnosticconfig)) {
            $diagnosticconfig = get_config('report_coursediagnostic');
            $SESSION->report_coursediagnostic = false;
            $SESSION->report_coursediagnosticconfig = null;
            if (property_exists($diagnosticconfig, 'enablediagnostic') && $diagnosticconfig->enablediagnostic) {
                $SESSION->report_coursediagnostic = true;

                // Some things we don't need however...
                unset($diagnosticconfig->version);
                unset($diagnosticconfig->enablediagnostic);
                unset($diagnosticconfig->filesizelimit);
                unset($diagnosticconfig->startcourseindex);
                unset($diagnosticconfig->endcourseindex);
                unset($diagnosticconfig->timelimit);

                // Here we assign all the settings from the config object...
                $SESSION->report_coursediagnosticconfig = $diagnosticconfig;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array|false
     */
    public static function get_diagnosticsettings() {

        global $SESSION;

        if (isset($SESSION->report_coursediagnosticconfig)) {
            return get_object_vars($SESSION->report_coursediagnosticconfig);
        }

        return false;
    }

    /**
     * @return int
     */
    public static function get_settingscount() :int {

        $diagnosticsettings = self::get_diagnosticsettings();
        $counter = 0;
        if ($diagnosticsettings) {

            foreach ($diagnosticsettings as $k => $v) {
                if ($v) {
                    $counter++;
                }
            }
        }

        return $counter;
    }

    /**
     * @return mixed
     */
    public static function init_cache(): mixed {
        return self::$cache = \cache::make('report_coursediagnostic', 'coursediagnosticdata');
    }

    /**
     * @param $cachekey
     * @return mixed
     */
    public static function cache_data_exists($cachekey): mixed {
        $cachekey = self::CACHE_KEY . $cachekey;
        return self::$cache->get_many([$cachekey]);
    }

    /**
     * @param $diagnosticdata
     * @param $courseid
     * @return mixed
     */
    public static function prepare_cache($diagnosticdata, $courseid): mixed {

        // Now prepare the results for caching...
        $cachekey = self::CACHE_KEY . $courseid;
        $cachedata = [
            $cachekey => [
                $diagnosticdata[$courseid]
            ]
        ];

        // Should we clear self::$diagnosticdata[$courseid] now?

        // Cache this data set...
        return self::$cache->set_many($cachedata);
    }

    /**
     * Called from w/in the settings page, when a change is made.
     * @return void
     */
    public static function flag_cache_for_deletion() {
        self::$purgeflag = true;
    }

    public static function get_cache_deletion_flag() {
        return self::$purgeflag;
    }

    /**
     * Clears the entire coursediagnosticdata cache.
     * Keep in mind that with our cronjob running and populating the cache,
     * this function destroys what could potentially be a lot of data.
     *
     * Only ^ever^ carried out when System Admin->Courses->Course diagnostic
     * Settings page is updated.
     * @return void
     * @throws \moodle_exception
     */
    public static function purge_diagnostic_settings_cache() {

        // Safeguard....
        if (self::$purgeflag) {

            // Just to be doubly sure...
            require_sesskey();

            // This gets set when the course_viewed event is caught.
            // Have it regenerate after any changes have been made.
            global $SESSION;
            unset($SESSION->report_coursediagnostic);
            unset($SESSION->report_coursediagnosticconfig);

            self::$cache = \cache::make('report_coursediagnostic', 'coursediagnosticdata');
            self::$cache->purge();

            // Reset this now that the cache has been cleared.
            self::$purgeflag = false;
        }
    }

    /**
     * Function that creates an array of tests to be performed.
     * Taken from the options selected in System Administration.
     *
     * To make this extendable, only generic/default tests are included here.
     * Extendability is provided by allowing end users to supply a JSON file
     * containing the names of additional tests to be carried out.
     * The filepath parameter will allow the necessary class files to be stored
     * outside of the main Moodle directory, thereby not causing any VC issues
     * if that is how the source code is being managed, for example. End users
     * only need to ensure their class follows the same format as the generic
     * tests in order for things to run.
     *
     * This will also allow the names of these additional tests to appear in
     * System Admninistration -> Course -> course diagnostic settings.
     * @return array
     */
    public static function prepare_tests(): array {

        $diagnosticsetting = (object) self::get_diagnosticsettings();
        $testsuite = [];

        foreach ($diagnosticsetting as $setting => $value) {

            if ($setting == 'enddate' && !empty($value)) {
                $testsuite[] = 'enddate_notset';
            }

            if (!empty($value)) {
                $testsuite[] = $setting;
            }
        }

        // ...@todo - implement a mechanism for reading in any additional tests.
        // There will be a format that needs to be followed, tests should be
        // rejected otherwise.

        return $testsuite;
    }

    /**
     * @param $testsuite
     * @param $courseid
     * @return array
     */
    public static function run_tests($testsuite, $courseid): array {

        // Get all the pertinent course settings that we need...
        $course = get_course($courseid);
        // Pass this data onto our test suite...
        $factory = \report_coursediagnostic\diagnostic_factory::instance();

        $flag = false;
        $tmpdata = [];
        foreach ($testsuite as $testcase) {
            if ($flag) {
                // Reset and continue.
                $flag = false;
                continue;
            }

            $diagnostictest = $factory->create_diagnostic_test_from_config($testcase, $course);

            // Some tests are a two state test, e.g. if 'enabled', then test.
            // If the first test fails, there's no need to perform the next.
            $stringmatch = (bool) strstr($testcase, 'notset');
            if ($stringmatch && (!$diagnostictest->testresult || (is_array($diagnostictest->testresult) &&
                        array_key_exists('testresult', $diagnostictest->testresult) &&
                        !$diagnostictest->testresult['testresult']))) {
                // Skip the next test as it's not needed.
                $flag = true;
            }

            // Assign the test result.
            $tmpdata[$courseid][$diagnostictest->testname] = $diagnostictest->testresult;
        }

        // Before returning the results, we need to remove any of the 'notset'
        // tests that passed - this is skewing our results total and messing
        // up the colour coding for the notifications. Basically, we only need
        // to concern ourselves with the 'notset' ones if they failed. We don't
        // need to know, or care, that they passed.
        $tmp = [];
        foreach ($tmpdata[$courseid] as $testname => $testresult) {
            $stringmatch = (bool) strstr($testname, 'notset');
            if ($stringmatch && ($testresult || (!empty($testresult['testresult']) && !$testresult['testresult']))) {
                // We don't need this one anymore, just continue onto the next.
                continue;
            }
            $tmp[$testname] = $testresult;
        }

        // Assign the cleaned data...
        self::$diagnosticdata[$courseid] = $tmp;

        // Return just the data for this course, not everything else...
        return self::$diagnosticdata;
    }

    /**
     * @param $courseid
     * @return float
     */
    public static function fetch_test_results($courseid): float {

        // If any of our tests have failed - have our 'alert' banner (the link to the report) display.
        // Based on a % of the number of tests that have failed, display the appropriate severity banner/button.
        $totaltests = count(self::$diagnosticdata[$courseid]);
        $passed = [];
        foreach (self::$diagnosticdata[$courseid] as $result) {
            if (is_array($result)) {
                $passed[] = $result['testresult'];
            } else {
                $passed[] = $result;
            }
        }
        $totalpassed = array_sum($passed);
        $failed = ($totaltests - $totalpassed);

        return round($failed / $totaltests * 100);
    }

    /**
     * Examine the data that's been returned from the cache...
     * If any of our tests have failed previously have our 'alert' notification
     * (link to the report) displayed. Based on a % of the number of tests that
     * have failed, use the appropriate severity class for the alert
     *
     * @param $cachedata
     * @return float
     */
    public static function parse_results($cachedata): float {

        $tests = $cachedata[0];
        $totaltests = count($tests);
        $passed = [];
        foreach ($tests as $result) {
            if (is_array($result)) {
                $passed[] = $result['testresult'];
            } else {
                $passed[] = $result;
            }
        }
        $totalpassed = array_sum($passed);
        $failed = ($totaltests - $totalpassed);

        return round($failed / $totaltests * 100);
    }

    /**
     * @param $failedtests
     * @param $courseid
     * @return mixed
     */
    public static function diagnostic_notification($failedtests, $courseid): mixed {

        global $CFG;
        $class = '';
        foreach (self::$alertranges as $classname => $range) {
            if ($failedtests >= $range['min'] && $failedtests <= $range['max']) {
                $class = $classname;
                break;
            }
        }
        $messagetext = get_string('notification_text', 'report_coursediagnostic');
        $message = '<strong>' . $messagetext . '</strong> You can review what needs to be set <a class="alert-link" ';
        $message .= 'href="' . $CFG->wwwroot . '/report/coursediagnostic/index.php?courseid='.$courseid.'">on the report page</a>.';

        return \report_coursediagnostic\notification::$class($message);
    }
}
