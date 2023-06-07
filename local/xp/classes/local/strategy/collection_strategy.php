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
 * Collection strategy.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\strategy;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\factory\course_world_factory;
use block_xp\local\strategy\event_collection_strategy;
use completion_info;
use context_course;
use local_xp\event\section_completed;
use moodle_exception;

/**
 * The collection strategy.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class collection_strategy implements event_collection_strategy {

    /** @var array Contexts allowed. */
    protected $allowedcontexts = [];
    /** @var course_world_factory The course factory. */
    protected $worldfactory;
    /** @var collection_target_resolver_from_event Target resolver. */
    protected $targetresolver;

    /**
     * Constructor.
     *
     * @param course_world_factory $worldfactory The world.
     * @param int $contextmode The context mode.
     */
    public function __construct(course_world_factory $worldfactory, $contextmode,
            collection_target_resolver_from_event $targetresolver) {

        $allowedcontexts = [CONTEXT_COURSE, CONTEXT_MODULE];
        if (!empty($contextmode) && $contextmode == CONTEXT_SYSTEM) {
            $allowedcontexts[] = CONTEXT_SYSTEM;
        }
        $this->allowedcontexts = $allowedcontexts;
        $this->worldfactory = $worldfactory;
        $this->targetresolver = $targetresolver;
    }

    /**
     * Collect an event.
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public function collect_event(\core\event\base $event) {
        $this->internal_collect_event($event);

        // This should probably be done elsewhere, but it's a bit tedious to override the
        // observers from block_xp in order to create a new one for the sole purpose of
        // converting these, so we can place them here for now. That should be done until
        // we decide to do some testing on the event collection alone.
        $this->convert_and_broadcast_event($event);
    }

    /**
     * Collect an event.
     *
     * @param \core\event\base $event The event.
     */
    protected function internal_collect_event(\core\event\base $event) {
        if (($event->component === 'block_xp' || $event->component === 'local_xp') && empty($event->isxpcompatible)) {
            // Skip own events.
            return;
        }

        $userid = $this->targetresolver->get_target_from_event($event);
        if (!$userid || isguestuser($userid) || is_siteadmin($userid)) {
            // Skip non-logged in users and guests.
            return;
        } else if ($event->anonymous) {
            // Skip all the events marked as anonymous.
            return;
        } else if (!in_array($event->contextlevel, $this->allowedcontexts)) {
            // Ignore events that are not in the right context.
            return;
        } else if (!$event->get_context()) {
            // For some reason the context does not exist...
            return;
        }

        try {
            // It has been reported that this can throw an exception when the context got missing
            // but is still cached within the event object. Or something like that...
            $canearn = has_capability('block/xp:earnxp', $event->get_context(), $userid);
        } catch (moodle_exception $e) {
            return;
        }

        // Skip the events if the user does not have the capability to earn XP.
        if (!$canearn) {
            return;
        }

        // There are some events we never want to capture.
        if ($this->should_skip_event($event)) {
            return;
        }

        $world = $this->worldfactory->get_world($event->courseid);
        $strategy = $world->get_collection_strategy();
        if ($strategy instanceof event_collection_strategy) {
            $strategy->collect_event($event);
        }
    }

    /**
     * Should we skip this event altogether?
     *
     * @param \core\event\base $event The event.
     * @return bool
     */
    protected function should_skip_event(\core\event\base $event) {
        if ($event instanceof \core\event\course_module_completion_updated) {
            // We skip incomplete, or failed.
            $data = $event->get_record_snapshot('course_modules_completion', $event->objectid);
            $state = $data->completionstate;
            if ($state !== COMPLETION_COMPLETE && $state !== COMPLETION_COMPLETE_PASS) {
                return true;
            }
        }
        return false;
    }

    /**
     * Convert and broadcast event.
     *
     * In some circumstances, we want to take the event and convert it to another
     * event which will be broadcasted.
     *
     * @param \core\event\base $event
     */
    protected function convert_and_broadcast_event(\core\event\base $event) {
        if ($event->anonymous) {
            return;
        } else if (!$event->get_context()) {
            // For some reason the context does not exist...
            return;
        }

        // Currently only support completion events.
        if (!$event instanceof \core\event\course_module_completion_updated) {
            return;
        }

        // Ensure completion lib is included, we need for constants and objects.
        $this->ensure_completion_lib_is_included();

        // Check that the event is about a successful completion.
        $data = $event->get_record_snapshot('course_modules_completion', $event->objectid);
        $state = $data->completionstate;
        if ($state !== COMPLETION_COMPLETE && $state !== COMPLETION_COMPLETE_PASS) {
            return;
        }

        // Check whether the whole section was completed.
        $modinfo = get_fast_modinfo($event->courseid, $event->relateduserid);
        $cm = $modinfo->get_cm($event->get_context()->instanceid);
        if (!$this->is_section_completed($modinfo, $cm->sectionnum)) {
            return;
        }

        // Trigger the event.
        section_completed::create([
            'context' => context_course::instance($event->courseid),
            'relateduserid' => $event->relateduserid,
            'other' => [
                'sectionnum' => $cm->sectionnum
            ]
        ])->trigger();
    }

    /**
     * Whether the section of cm is completed.
     *
     * @param modinfo $modinfo The course modinfo for the given user.
     * @param int $sectionnum The section number.
     * @return bool
     */
    protected function is_section_completed($modinfo, $sectionnum) {
        global $CFG;

        $sections = $modinfo->get_sections();
        if (empty($sections[$sectionnum])) {
            return false;
        }

        $completioninfo = new completion_info($modinfo->get_course());
        $modinfoorunused = $CFG->branch < 400 ? $modinfo : null;

        $loadwholecourse = true;
        $cmswithcompletioninsection = 0;
        $cmscompletedinsection = 0;
        $cmids = $sections[$sectionnum];
        foreach ($cmids as $cmid) {
            $cm = $modinfo->get_cm($cmid);

            // Always ignore activities that have been deleted.
            if (!empty($cm->deletioninprogress)) {
                continue;
            }

            // Check whether completion is enabled.
            $isenabled = $completioninfo->is_enabled($cm) != COMPLETION_TRACKING_NONE;
            if (!$isenabled) {
                continue;
            }
            $cmswithcompletioninsection++;

            // Check whether activity is complete.
            $data = $completioninfo->get_data($cm, $loadwholecourse, $modinfo->get_user_id(), $modinfoorunused);
            $loadwholecourse = false;
            $iscompleted = $data->completionstate == COMPLETION_COMPLETE || $data->completionstate == COMPLETION_COMPLETE_PASS;
            if (!$iscompleted) {
                continue;
            }
            $cmscompletedinsection++;
        }

        return $cmswithcompletioninsection > 0 && $cmswithcompletioninsection <= $cmscompletedinsection;
    }

    /**
     * Ensure completion lib is included.
     */
    protected function ensure_completion_lib_is_included() {
        global $CFG;
        require_once($CFG->libdir . '/completionlib.php');
    }

}
