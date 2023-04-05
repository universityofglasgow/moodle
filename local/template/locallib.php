<?php


function add_new_course_hook() {
    // Based on plugin setting, hook into page load to test if requested page is course-edit without id (add new course)
    global $PAGE, $SESSION;

    if (is_object($PAGE)) {
        if ($PAGE->pagetype == 'course-edit') {

            // Add to session variable. Check from session variable
            $addnewcoursehook = get_config('local_template', 'addnewcoursehook');

            if ($addnewcoursehook) {
                $id = optional_param('id', 0, PARAM_INT); // Course id.
                if (!$id) {

                    $categoryid = optional_param('category', 0, PARAM_INT);
                    $returnto = optional_param('returnto', 0, PARAM_ALPHANUM);
                    $returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

                    $courseurl = new moodle_url('/local/template', [
                        'id' => $id,
                        'categoryid' => $categoryid,
                        'returnto' => $returnto,
                        'returnurl' => $returnurl,
                    ]);

                    // If
                    redirect($courseurl);
                }
            }
        }
    }
}



/**
 * Creates a course copy.
 * Sets up relevant controllers and adhoc task.
 *
 * @param \stdClass $copydata Course copy data from process_formdata
 * @return array $copyids The backup and restore controller ids
 */


function create_copy(\stdClass $copydata): array {
    global $USER;
    $progress = new \core\progress\display();
    $progress->set_display_names();

    $bc = new backup_controller(backup::TYPE_1COURSE, $copydata->courseid, backup::FORMAT_MOODLE,
        backup::INTERACTIVE_NO, backup::MODE_GENERAL, $USER->id, \backup::RELEASESESSION_NO);
    $backupid = $bc->get_backupid();
    $bc->execute_plan();
    $bc->destroy();


    // Create the initial restore contoller.
    list($fullname, $shortname) = \restore_dbops::calculate_course_names(
        0, get_string('copyingcourse', 'backup'), get_string('copyingcourseshortname', 'backup'));
    $newcourseid = \restore_dbops::create_new_course($fullname, $shortname, $copydata->category);
    $rc = new \restore_controller($backupid, $newcourseid, \backup::INTERACTIVE_NO,
        \backup::MODE_GENERAL, $USER->id, \backup::TARGET_NEW_COURSE, $progress,
        \backup::RELEASESESSION_NO, $copydata);
    $copyids['restoreid'] = $rc->get_restoreid();

    $bc->set_status(\backup::STATUS_AWAITING);
    $bc->get_status();
    $rc->save_controller();

    // Create the ad-hoc task to perform the course copy.
    $asynctask = new \core\task\asynchronous_copy_task();
    $asynctask->set_blocking(false);
    $asynctask->set_custom_data($copyids);
    \core\task\manager::queue_adhoc_task($asynctask);

    // Clean up the controller.
    $bc->destroy();

    return $copyids;
}
