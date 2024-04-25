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
 * Are the uploaded video files making this course excessively large?
 *
 * This tests whether the given course has video files that could be
 * impacting Moodle's performance.
 *
 * @package    report_coursediagnositc
 * @copyright  2023 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;
class course_coursevideo_test implements \report_coursediagnostic\course_diagnostic_interface {

    /** @var string The name of the test - needed w/in the report. */
    public string $testname;

    /** @var object The course object. */
    public object $course;

    /** @var array The test passed or failed, plus other additional options. */
    public array $testresult;

    /** @var int The sum total of all files stored for this course. */
    private static int $totalfiles = 0;

    /** @var int The filesize of all files combined. */
    private static int $totalfilesize = 0;

    /** @var int 'filesize' in table mdl_files is stored as bytes.  */
    const FILESIZE_100MB = 104857600;

    /** @var int  */
    const FILESIZE_500MB = 524288000;

    /** @var int  */
    const FILESIZE_1GB = 1073741824;

    /** @var int  */
    const FILESIZE_10GB = 10737418240;

    /** @var int */
    const FILESIZE_100GB = 107374182400;

    /**
     * This array maps to the option values in the Settings page.
     * @var array
     */
    protected static array $filesizeoptions = [
        1 => self::FILESIZE_100MB,
        2 => self::FILESIZE_500MB,
        3 => self::FILESIZE_1GB,
        4 => self::FILESIZE_10GB,
        5 => self::FILESIZE_100GB
    ];

    /**
     * @var array|string[] A list of video related mime types.
     */
    protected static array $mimetypes = [
        '"video/mp4"',
        '"video/mpeg"',
        '"video/ogg"',
        '"video/quicktime"',
        '"video/webm"',
        '"video/x-flv"',
        '"video/x-ms-asf"',
        '"video/x-ms-wm"',
        '"video/x-ms-wmv"'
    ];

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

        global $DB, $CFG;
        require_once("$CFG->dirroot/report/coursediagnostic/lib.php");

        $kalturaurl = new \moodle_url('https://www.gla.ac.uk/myglasgow/learningandteaching/video/kaltura/');
        $kalturalink = \html_writer::link($kalturaurl, get_string('coursevideo_kaltura_text', 'report_coursediagnostic'), ['target' => 'blank']);
        $echo360url = new \moodle_url('https://www.gla.ac.uk/myglasgow/learningandteaching/learningspacesupport/lecturerecording/#addingecho360recordingstomoodle');
        $echo360link = \html_writer::link($echo360url, get_string('coursevideo_echo360_text', 'report_coursediagnostic'), ['target' => 'blank']);
        $lisuurl = new \moodle_url('https://www.gla.ac.uk/myglasgow/learningandteaching/lisu/');
        $lisulink = \html_writer::link($lisuurl, get_string('coursevideo_lisu_text', 'report_coursediagnostic'), ['target' => 'blank']);
        $filesizeoption = get_config('report_coursediagnostic', 'filesizelimit');
        $filesizelimit = self::$filesizeoptions[$filesizeoption];
        $context = \context_course::instance($this->course->id);
        $result = $DB->get_records_sql('SELECT COUNT(*) AS ttl, SUM(filesize) AS filesize FROM {files} mf
                                        JOIN {context} mc ON mc.id = mf.contextid WHERE mc.path LIKE "'.$context->path.'/%"
                                        AND mf.filename <> "." AND mimetype IN ('.implode(', ', self::$mimetypes).')');

        $filesizewithinlimit = true;
        if (count($result) > 0) {
            foreach ($result as $row) {
                if ($row->filesize > 0) {
                    if ($row->filesize >= $filesizelimit) {
                        $filesizewithinlimit = false;
                        self::$totalfiles = $row->ttl;
                        self::$totalfilesize = $row->filesize;
                        break;
                    }
                }
            }
        }

        $this->testresult = [
            'testresult' => $filesizewithinlimit,
            'totalfiles' => self::$totalfiles,
            'totalfilesize' => formatsize(self::$totalfilesize),
            'filesizelimit' => formatsize($filesizelimit),
            'kalturalink' => $kalturalink,
            'echo360link' => $echo360link,
            'lisulink' => $lisulink
        ];

        return $this->testresult;
    }
}
