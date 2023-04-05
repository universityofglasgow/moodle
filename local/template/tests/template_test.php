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
 * Unit tests local_template
 *
 * @package   local_template
 * @category  phpunit
 * @copyright 2023 David Aylmer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

use core\output\notification;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/*

    //shell.bat web
    //php admin/tool/phpunit/cli/init.php

    from moodle-docker/bin
    moodle-docker-compose exec webserver php admin/tool/phpunit/cli/init.php
    moodle-docker-compose exec webserver vendor/bin/phpunit /var/www/html/local/template/tests/template_test.php

    vendor/bin/phpunit --debug local/template/tests/templates_test.php



    vendor/bin/phpunit --debug --filter test_1 local/template/tests/template_test.php
    vendor/bin/phpunit --verbose --filter test_1 local/template/tests/template_test.php


 */

/**
 * Tests local_template
 * @group local_template
 */
class template_test extends advanced_testcase {

    private static function debug($message) {
        if(in_array('--debug', $_SERVER['argv'], true)) {
            echo $message;
        }
    }

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void {
        // Setup fixtures.
    }

    public static function tearDownAfterClass(): void {
        // Teardown fixtures.
    }

    public function test_1() {

        $generator = $this->getDataGenerator();
        // Generate category 1
        $templatecategory = $generator->create_category();
        $templatecourse = $generator->create_course(['fullname' => 'template', 'numsections' => 3, 'category' => $templatecategory->id]);
        $manager = $generator->create_and_enrol($templatecourse, 'student', '', 'gudatabase');

        // Generate category 2
        $importcategory = $generator->create_category();
        $importcourse = $generator->create_course(['fullname' => 'import', 'numsections' => 5, 'category' => $importcategory->id]);


        // Generate course 1

        $template = new local_template\models\template(0, (object)[
            'templatecourseid' => $templatecourse->id,
            'importcourseid' => $importcourse->id,

            'category' => $importcategory->id,
            'fullname' => 'Test Course',
            'shortname' => 'Test course',
            'summary' => '<b>Test course</b>',
            'summaryformat' => FORMAT_HTML,
            'startdate' => 0,
            'enddate' => 0,
            'visible' => 0,

        ]);

        $template->save();

        $this->assertEquals(true, true);

        //$backupcontroller = new local_template\models\backupcontroller(0, (object)[
        //    'templateid' => $template->get('id'),
        //]);

        //$notifications = $controller->process();

        // $this->assertNotification($notifications, 'Successfully processed 0 records', notification::NOTIFY_SUCCESS);
        // $this->assertNotificationCount($notifications, 1);
    }

}
