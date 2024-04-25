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
 * Test backup and retore.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once(__DIR__ . '/base_testcase.php');

use local_xp\local\reason\manual_reason;

/**
 * Test backup and retore.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_backup_testcase extends local_xp_base_testcase {

    public function test_restore_in_new_course() {
        global $DB;

        $data = $this->setup_courses();
        $c1 = $data['c1'];

        $this->setAdminUser();
        $backupid = $this->backup($c1);

        $newid = restore_dbops::create_new_course('xptest', 'xptest', $c1->category);
        $this->restore($backupid, $newid, backup::TARGET_NEW_COURSE);
        $newctxid = context_course::instance($newid)->id;

        // Config is restored.
        $this->assertTrue($DB->record_exists('local_xp_config', ['courseid' => $newid, 'maxpointspertime' => 8]));
        // Logs are never restored.
        $this->assertEquals(0, $DB->count_records('local_xp_log', ['contextid' => $newctxid]));
        // The drop secret already exists, not restored.
        $this->assertEquals(0, $DB->count_records('local_xp_drops', ['courseid' => $newid]));
    }

    public function test_restore_merge_in_other() {
        global $DB;

        $data = $this->setup_courses();
        $c1 = $data['c1'];
        $c2 = $data['c2'];
        $c2ctxid = context_course::instance($c2->id)->id;

        $this->setAdminUser();
        $backupid = $this->backup($c1);
        $this->restore($backupid, $c2->id, backup::TARGET_EXISTING_ADDING);

        // Config is not overwritten.
        $this->assertTrue($DB->record_exists('local_xp_config', ['courseid' => $c2->id, 'maxpointspertime' => 18]));
        // Logs are never restored.
        $this->assertEquals(1, $DB->count_records('local_xp_log', ['contextid' => $c2ctxid]));
        // The drop secret already exists, not restored.
        $this->assertEquals(0, $DB->count_records('local_xp_drops', ['courseid' => $c2->id]));
    }

    public function test_restore_delete_and_merge_in_other() {
        global $DB;

        $data = $this->setup_courses();
        $c1 = $data['c1'];
        $c2 = $data['c2'];
        $c1ctxid = context_course::instance($c1->id)->id;
        $c2ctxid = context_course::instance($c2->id)->id;

        $this->setAdminUser();
        $backupid = $this->backup($c1);
        $this->restore($backupid, $c2->id, backup::TARGET_EXISTING_DELETING);

        // Config is overridden.
        $this->assertTrue($DB->record_exists('local_xp_config', ['courseid' => $c2->id, 'maxpointspertime' => 8]));
        // Logs are not restored.
        $this->assertEquals(0, $DB->count_records('local_xp_log', ['contextid' => $c2ctxid]));
        // The drop secret already exists, not restored.
        $this->assertEquals(0, $DB->count_records('local_xp_drops', ['courseid' => $c2->id]));

        // Validate nothing changed in other courses (c1).
        $this->assertTrue($DB->record_exists('local_xp_config', ['courseid' => $c1->id, 'maxpointspertime' => 8]));
        $this->assertEquals(4, $DB->count_records('local_xp_log', ['contextid' => $c1ctxid]));
        $this->assertEquals(1, $DB->count_records('local_xp_drops', ['courseid' => $c1->id]));
    }

    public function test_restore_merge_in_same_without_change() {
        global $DB;

        $data = $this->setup_courses();
        $c1 = $data['c1'];
        $c1ctxid = context_course::instance($c1->id)->id;

        $this->setAdminUser();
        $backupid = $this->backup($c1);
        $this->restore($backupid, $c1->id, backup::TARGET_EXISTING_ADDING);

        // Config is not changed.
        $this->assertTrue($DB->record_exists('local_xp_config', ['courseid' => $c1->id, 'maxpointspertime' => 8]));
        // Logs are never restored.
        $this->assertEquals(4, $DB->count_records('local_xp_log', ['contextid' => $c1ctxid]));
        // Drop secrets conflict, no restore.
        $this->assertEquals(1, $DB->count_records('local_xp_drops', ['courseid' => $c1->id]));
    }

    public function test_restore_merge_in_same_with_changes() {
        global $DB;

        $data = $this->setup_courses();
        $c1 = $data['c1'];
        $c1ctxid = context_course::instance($c1->id)->id;

        $this->setAdminUser();
        $backupid = $this->backup($c1);

        // Applying minor changes.
        $this->assertTrue($DB->record_exists('local_xp_drops', ['secret' => 'abcdef']));
        $DB->set_field('local_xp_drops', 'secret', 'abcdef2', ['secret' => 'abcdef']);
        $this->assertFalse($DB->record_exists('local_xp_drops', ['secret' => 'abcdef']));

        $this->restore($backupid, $c1->id, backup::TARGET_EXISTING_ADDING);

        // Config is not changed.
        $this->assertTrue($DB->record_exists('local_xp_config', ['courseid' => $c1->id, 'maxpointspertime' => 8]));
        // Logs are never restored.
        $this->assertEquals(4, $DB->count_records('local_xp_log', ['contextid' => $c1ctxid]));
        // The drop is restored.
        $this->assertEquals(2, $DB->count_records('local_xp_drops', ['courseid' => $c1->id]));
        $this->assertTrue($DB->record_exists('local_xp_drops', ['secret' => 'abcdef']));
        $this->assertTrue($DB->record_exists('local_xp_drops', ['secret' => 'abcdef2']));
    }

    public function test_restore_delete_and_merge_in_same() {
        global $DB;

        $data = $this->setup_courses();
        $c1 = $data['c1'];
        $w1 = $data['w1'];
        $c1ctxid = context_course::instance($c1->id)->id;

        $this->setAdminUser();
        $backupid = $this->backup($c1);

        // Applying minor changes.
        $w1->get_config()->set('maxpointspertime', 999);
        $DB->set_field('local_xp_drops', 'secret', 'abcdef2', ['secret' => 'abcdef']);
        $this->assertFalse($DB->record_exists('local_xp_drops', ['secret' => 'abcdef']));
        $this->assertFalse($DB->record_exists('local_xp_config', ['courseid' => $c1->id, 'maxpointspertime' => 8]));
        $this->restore($backupid, $c1->id, backup::TARGET_EXISTING_DELETING);

        // Config is changed.
        $this->assertTrue($DB->record_exists('local_xp_config', ['courseid' => $c1->id, 'maxpointspertime' => 8]));
        $this->assertFalse($DB->record_exists('local_xp_config', ['courseid' => $c1->id, 'maxpointspertime' => 999]));
        // Logs are deleted and not restored.
        $this->assertEquals(0, $DB->count_records('local_xp_log', ['contextid' => $c1ctxid]));
        // Drop secrets don't conflict, restore.
        $this->assertEquals(1, $DB->count_records('local_xp_drops', ['courseid' => $c1->id]));
        $this->assertTrue($DB->record_exists('local_xp_drops', ['secret' => 'abcdef']));
        $this->assertFalse($DB->record_exists('local_xp_drops', ['secret' => 'abcdef2']));
    }

    public function test_restore_delete_and_merge_in_same_without_overwrite_conf() {
        global $DB;

        $data = $this->setup_courses();
        $c1 = $data['c1'];
        $w1 = $data['w1'];
        $c1ctxid = context_course::instance($c1->id)->id;

        $this->setAdminUser();
        $backupid = $this->backup($c1);

        // Applying minor changes.
        $w1->get_config()->set('maxpointspertime', 999);
        $DB->set_field('local_xp_drops', 'secret', 'abcdef2', ['secret' => 'abcdef']);
        $this->assertFalse($DB->record_exists('local_xp_drops', ['secret' => 'abcdef']));
        $this->assertFalse($DB->record_exists('local_xp_config', ['courseid' => $c1->id, 'maxpointspertime' => 8]));

        // Overwrite conf is required for any merge.
        $this->restore($backupid, $c1->id, backup::TARGET_EXISTING_DELETING, ['overwrite_conf' => false]);

        // Config is not changed.
        $this->assertFalse($DB->record_exists('local_xp_config', ['courseid' => $c1->id, 'maxpointspertime' => 8]));
        $this->assertTrue($DB->record_exists('local_xp_config', ['courseid' => $c1->id, 'maxpointspertime' => 999]));
        // Logs are not deleted and not restored.
        $this->assertEquals(4, $DB->count_records('local_xp_log', ['contextid' => $c1ctxid]));
        // Drop secrets don't conflict, restore.
        $this->assertEquals(1, $DB->count_records('local_xp_drops', ['courseid' => $c1->id]));
        $this->assertFalse($DB->record_exists('local_xp_drops', ['secret' => 'abcdef']));
        $this->assertTrue($DB->record_exists('local_xp_drops', ['secret' => 'abcdef2']));
    }

    /**
     * Backs a course up to temp directory.
     *
     * Inspired from tool_log.
     *
     * @param \stdClass $course Course object to backup.
     * @return string ID of backup.
     */
    protected function backup($course) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

        // Turn off file logging, otherwise it can't delete the file (Windows).
        $CFG->backup_file_logger_level = backup::LOG_NONE;

        // Do backup with default settings. MODE_IMPORT means it will just create the directory and not zip it.
        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id,
            backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_IMPORT,
            $USER->id);

        $settings = [
            'users' => true,
            'groups' => true,
            'blocks' => true,
            'role_assignments' => false,
            'permissions' => false,
            'files' => false,
            'filters' => false,
            'comments' => false,
            'badges' => false,
            'calendarevents' => false,
            'userscompletion' => false,
            'logs' => false,
            'grade_histories' => false,
            'questionbank' => false,
            'competencies' => false,
            'customfield' => false,
            'contentbankcontent' => false,
            'legacyfiles' => false,
        ];

        $plan = $bc->get_plan();
        foreach ($settings as $name => $value) {
            if (!$plan->setting_exists($name)) {
                continue;
            }
            $setting = $plan->get_setting($name);
            if ($setting->get_status() != base_setting::NOT_LOCKED) {
                $setting->set_status(base_setting::NOT_LOCKED);
            }
            $setting->set_value($value);
        }

        $backupid = $bc->get_backupid();

        $bc->execute_plan();
        $bc->destroy();
        return $backupid;
    }

    /**
     * Restore a course.
     *
     * @param string $backupid Backup ID.
     * @param string $courseid Destination course ID.
     * @param int $target The backup::TARGET_* constant.
     * @param array $settings Backup settings.
     */
    protected function restore($backupid, $courseid, $target, $settings = []) {
        global $USER;

        $rc = new restore_controller($backupid, $courseid, backup::INTERACTIVE_NO, backup::MODE_GENERAL, $USER->id, $target);

        $settings += [
            'overwrite_conf' => true,
            'users' => true,
            'groups' => true,
            'blocks' => true,
            'role_assignments' => false,
            'permissions' => false,
            'files' => false,
            'filters' => false,
            'comments' => false,
            'badges' => false,
            'calendarevents' => false,
            'userscompletion' => false,
            'logs' => false,
            'grade_histories' => false,
            'questionbank' => false,
            'competencies' => false,
            'customfield' => false,
            'contentbankcontent' => false,
            'legacyfiles' => false,
        ];

        $plan = $rc->get_plan();
        foreach ($settings as $name => $value) {
            if (!$plan->setting_exists($name)) {
                continue;
            }
            $setting = $plan->get_setting($name);
            if ($setting->get_status() != base_setting::NOT_LOCKED) {
                $setting->set_status(base_setting::NOT_LOCKED);
            }
            $setting->set_value($value);
        }

        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();
    }

    /**
     * Setup the courses.
     *
     * @return array
     */
    protected function setup_courses() {
        global $DB, $USER;

        $dg = $this->getDataGenerator();

        $c1 = $dg->create_course();
        $c2 = $dg->create_course();
        $c1ctx = context_course::instance($c1->id);
        $c2ctx = context_course::instance($c2->id);

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();
        $u3 = $dg->create_user();

        $w1 = $this->get_world($c1->id);
        $w2 = $this->get_world($c2->id);

        // Modify config.
        $w1->get_config()->set('maxpointspertime', 8);
        $w2->get_config()->set('maxpointspertime', 18);

        // Create logs.
        $w1->get_store()->increase_with_reason($u1->id, '5', new manual_reason($USER->id));
        $w1->get_store()->increase_with_reason($u1->id, '5', new manual_reason($USER->id));
        $w1->get_store()->increase_with_reason($u2->id, '11', new manual_reason($USER->id));
        $w1->get_store()->increase_with_reason($u3->id, '22', new manual_reason($USER->id));
        $w2->get_store()->increase_with_reason($u1->id, '33', new manual_reason($USER->id));

        // Create a drop.
        $DB->insert_record('local_xp_drops', ['courseid' => $c1->id, 'name' => 'Foo', 'points' => 420, 'enabled' => 1,
            'secret' => 'abcdef']);

        // Validate and document setup.
        $this->assertTrue($DB->record_exists('local_xp_config', ['courseid' => $c1->id, 'maxpointspertime' => 8]));
        $this->assertTrue($DB->record_exists('local_xp_config', ['courseid' => $c2->id, 'maxpointspertime' => 18]));
        $this->assertEquals(4, $DB->count_records('local_xp_log', ['contextid' => $c1ctx->id]));
        $this->assertEquals(1, $DB->count_records('local_xp_log', ['contextid' => $c2ctx->id]));
        $this->assertEquals(1, $DB->count_records('local_xp_drops', ['courseid' => $c1->id]));
        $this->assertEquals(0, $DB->count_records('local_xp_drops', ['courseid' => $c2->id]));

        // Add block to courses.
        $page = new moodle_page();
        $page->set_context($c1ctx);
        $page->set_pagetype('page-type');
        $page->set_url(new moodle_url('/'));
        $blockmanager = new block_manager($page);
        $blockmanager->add_regions(['a'], false);
        $blockmanager->set_default_region('a');
        $blockmanager->add_block('xp', 'a', 0, false);

        $page = new moodle_page();
        $page->set_context($c2ctx);
        $page->set_pagetype('page-type');
        $page->set_url(new moodle_url('/'));
        $blockmanager = new block_manager($page);
        $blockmanager->add_regions(['a'], false);
        $blockmanager->set_default_region('a');
        $blockmanager->add_block('xp', 'a', 0, false);

        return ['c1' => $c1, 'c2' => $c2, 'u1' => $u1, 'u2' => $u2, 'u3' => $u3, 'w1' => $w1];
    }

}
