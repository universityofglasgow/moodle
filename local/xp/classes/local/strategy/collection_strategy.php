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

use DateTime;
use block_xp\local\factory\course_world_factory;
use block_xp\local\strategy\event_collection_strategy;
use local_xp\local\logger\activity_completion_historical_state_recorder;

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
        if ($event->component === 'block_xp' || $event->component === 'local_xp') {
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

}
