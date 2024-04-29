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
 * Course related unit tests for format tiles
 *
 * @package    format_tiles
 * @copyright  2018 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use format_tiles\format_option;

global $CFG;
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Class format_tiles_testcase
 * @copyright  2018 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_tiles_testcase extends advanced_testcase {

    /**
     * The format options to use when setting up a course in tiles format.
     * @var array
     */
    private $tilescourseformatoptions = [
        'shortname' => 'GrowingCourse',
        'fullname' => 'Growing Course',
        'numsections' => 5,
        'format' => 'tiles',
        'defaulttileicon' => 'user',
        'basecolour' => '#700000',
        'courseusesubtiles' => 1,
        'courseshowtileprogress' => 0,
        'displayfilterbar' => 1,
        'usesubtilesseczero' => 0,
        'courseusebarforheadings' => 1,
    ];

    /**
     * Test updating the section format options e.g. changing the tile icon for a tile.
     * @throws moodle_exception
     */
    public function test_update_section_format_options() {
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course(
            $this->tilescourseformatoptions,
            ['createsections' => true]);

        $sectionscreated = get_fast_modinfo($course)->get_section_info_all();

        $toseticons = [
            1 => 'smile-o',
            2 => 'asterisk',
        ];
        foreach ($sectionscreated as $section) {
            $icon = $toseticons[$section->section] ?? null;
            if ($icon) {
                format_option::set(
                    $course->id, format_option::OPTION_SECTION_ICON, $section->section, $icon
                );
                $this->assertEquals(
                    $icon,
                    format_option::get($course->id, format_option::OPTION_SECTION_ICON, $section->section)
                );
            }
        }
    }

    /**
     * Test updating the course format options e.g. change the tile for a course.
     * @throws dml_exception
     */
    public function test_update_course_format_options() {
        global $DB;
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course(
            $this->tilescourseformatoptions,
            ['createsections' => true]);
        set_config('followthemecolour', 0, 'format_tiles');
        set_config('allowsubtilesview', 0, 'format_tiles');

        $pushedvalues = [
            'id' => $course->id,
            'defaulttileicon' => 'book',
            'courseusesubtiles' => '0',
            'courseshowtileprogress' => '1',
            'displayfilterbar' => '0',
            'usesubtilesseczero' => '0',
            'courseusebarforheadings' => '0',
        ];
        // TODO work out why basecolour setting fails here - maybe to do with followthemecolour admin config option?

        $format = course_get_format($course);
        $format->update_course_format_options($pushedvalues);

        $dbdata = $DB->get_records(
            'course_format_options',
            ['format' => 'tiles', 'courseid' => $course->id, 'sectionid' => 0]
        );
        $newvalues = [];
        foreach ($dbdata as $k => $v) {
            $newvalues[$v->name] = $v->value;
        }
        foreach ($pushedvalues as $name => $pushedvalue) {
            if ($name !== 'id') {
                // Id is course ID and will not be in new db values.
                $this->assertEquals($pushedvalue, $newvalues[$name], 'Item not updated as expected: ' . $name);
            }
        }

        // Now repeat the above with different values, and check again.
        $pushedvalues = [
            'id' => $course->id,
            'defaulttileicon' => 'television',
            'courseusesubtiles' => '1',
            'courseshowtileprogress' => '0',
            'displayfilterbar' => '1',
            'usesubtilesseczero' => '1',
            'courseusebarforheadings' => '1',
        ];
        // TODO work out why basecolour setting fails here - maybe to do with followthemecolour admin config option?

        $format = course_get_format($course);
        $format->update_course_format_options($pushedvalues);

        $dbdata = $DB->get_records(
            'course_format_options',
            ['format' => 'tiles', 'courseid' => $course->id, 'sectionid' => 0]
        );
        $newvalues = [];
        foreach ($dbdata as $k => $v) {
            $newvalues[$v->name] = $v->value;
        }
        foreach ($pushedvalues as $name => $pushedvalue) {
            if ($name !== 'id') {
                // Id is course ID and will not be in new db values.
                $this->assertEquals($pushedvalue, $newvalues[$name], 'No match on name ' . $name);
            }

        }
    }


    /**
     * Test web service updating section name
     * Function copied from format_topics with format changed to tiles.
     */
    public function test_update_inplace_editable() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/lib/external/externallib.php');
        require_once($CFG->dirroot . '/lib/external/classes/external_api.php');

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $course = $this->getDataGenerator()->create_course($this->tilescourseformatoptions, ['createsections' => true]);
        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 2]);

        // Call webservice without necessary permissions.
        try {
            core_external::update_inplace_editable('format_tiles', 'sectionname', $section->id, 'New section name');
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertEquals('Course or activity not accessible. (Not enrolled)',
                $e->getMessage());
        }

        // Change to teacher and make sure that section name can be updated using web service update_inplace_editable().
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $teacherrole->id);

        $res = core_external::update_inplace_editable('format_tiles', 'sectionname', $section->id, 'New section name');
        $res = \core_external\external_api::clean_returnvalue(core_external::update_inplace_editable_returns(), $res);
        $this->assertEquals('New section name', $res['value']);
        $this->assertEquals('New section name', $DB->get_field('course_sections', 'name', ['id' => $section->id]));
    }

    /**
     * Test callback updating section name
     * Function copied from format_topics with format changed to tiles.
     */
    public function test_inplace_editable() {
        global $DB, $PAGE;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course($this->tilescourseformatoptions, ['createsections' => true]);
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $teacherrole->id);
        $this->setUser($user);

        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 2]);

        // Call callback format_tiles_inplace_editable() directly.
        $tmpl = component_callback('format_tiles', 'inplace_editable', ['sectionname', $section->id, 'Rename me again']);
        $this->assertInstanceOf('core\output\inplace_editable', $tmpl);
        $res = $tmpl->export_for_template($PAGE->get_renderer('core'));
        $this->assertEquals('Rename me again', $res['value']);
        $this->assertEquals('Rename me again', $DB->get_field('course_sections', 'name', ['id' => $section->id]));

        // Try updating using callback from mismatching course format.
        try {
            $tmpl = component_callback('format_weeks', 'inplace_editable', ['sectionname', $section->id, 'New name']);
            $this->fail('Exception expected');
        } catch (moodle_exception $e) {
            $this->assertTrue(
                preg_match('/^Can not find data record in database/', $e->getMessage()) === 1
                || preg_match('/^Can\'t find data record in database/', $e->getMessage()) === 1
            );
        }
    }

    /**
     * Test video embed URL replacement
     */
    public function test_video_urls() {
        $this->resetAfterTest();
        $this->assertEquals(
            'https://www.youtube.com/embed/abcdefghijk',
            \format_tiles\output\course_output::check_modify_embedded_url('https://www.youtube.com/watch?v=abcdefghijk')
        );

        $this->assertEquals(
            'https://www.youtube.com/embed/abcdefghijk',
            \format_tiles\output\course_output::check_modify_embedded_url('https://youtu.be/abcdefghijk')
        );

        $this->assertEquals(
            'https://www.youtube.com/embed/abcdefghijk',
            \format_tiles\output\course_output::check_modify_embedded_url('https://www.youtube.com/shorts/abcdefghijk')
        );

        $this->assertEquals(
            'https://player.vimeo.com/video/347119375',
            \format_tiles\output\course_output::check_modify_embedded_url('https://vimeo.com/347119375')
        );

        $this->assertTrue(
            \format_tiles\output\course_output::is_video_url('https://www.youtube.com/embed/abcdefghijk?t=123')
        );

        $this->assertTrue(
            \format_tiles\output\course_output::is_video_url(
                'https://www.youtube.com/shorts/abcdefghijk?t=4&feature=share'
            )
        );

        $this->assertTrue(
            \format_tiles\output\course_output::is_video_url(
                'https://www.youtube.com/shorts/abcdefghijk?t=4&feature=share'
            )
        );

        // If the URL contains a param that we're unsure how to handle, we don't modify (i.e. return null).
        $this->assertEquals(
            null,
            \format_tiles\output\course_output::check_modify_embedded_url(
                'https://www.youtube.com/shorts/abcdefghijk?t=4&feature=share'
            )
        );

        $this->assertTrue(
            \format_tiles\output\course_output::is_video_url(
                'https://youtu.be/abcdefghijk?t=49'
            )
        );

        // If the URL contains a param that we're unsure how to handle, we don't modify (i.e. return null).
        $this->assertEquals(
            null,
            \format_tiles\output\course_output::check_modify_embedded_url(
                'https://youtu.be/abcdefghijk?t=49'
            )
        );
    }
}
