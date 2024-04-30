<?php
/**
 * This file is part of Level Up XP+.
 *
 * Level Up XP+ is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Level Up XP+ is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
 *
 * https://levelup.plus
 */

/**
 * Type.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\ruletype;

use block_xp\local\action\action;
use block_xp\local\reason\reason;
use block_xp\local\ruletype\ruletype;
use lang_string;
use local_xp\local\reason\section_completion_reason;

/**
 * Type.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_completion implements ruletype {

    public function get_compatible_filters(): array {
        return ['section', 'anysection'];
    }

    public function get_display_name(): lang_string {
        return new lang_string('ruletypesectioncompletion', 'block_xp');
    }

    public function get_repeat_window(): ?string {
        return static::WINDOW_ONCE;
    }

    public function get_short_description(): lang_string {
        return new lang_string('ruletypesectioncompletiondesc', 'block_xp');
    }

    public function is_action_compatible(action $action): bool {
        return $action->get_type() === 'cm_completed';
    }

    public function is_action_satisfying_requirements(action $action): bool {
        return $this->is_section_completed_by_action($action);
    }

    public function make_reason(action $action): reason {
        return new section_completion_reason($this->get_course_id($action), $this->get_section_num($action));
    }

    /**
     * Ensure completion lib is included.
     */
    protected function ensure_completion_lib_is_included() {
        global $CFG;
        require_once($CFG->libdir . '/completionlib.php');
    }

    /**
     * Get the course ID.
     *
     * @param action $action The action.
     * @return int $courseid The course ID.
     */
    protected function get_course_id(action $action) {
        $context = $action->get_context();
        if (!$context instanceof \context_module) {
            return 0;
        }
        return (int) $context->get_course_context()->instanceid;
    }

    /**
     * Get the section num.
     *
     * @param action $action The action.
     * @return int The section number.
     */
    protected function get_section_num(action $action) {
        $courseid = $this->get_course_id($action);
        $modinfo = get_fast_modinfo($courseid, $action->get_user_id());
        $cm = $modinfo->get_cm($action->get_context()->instanceid);
        return $cm->sectionnum;
    }

    /**
     * Whether the section of action is completed.
     *
     * @param action $action The action.
     * @return bool
     */
    protected function is_section_completed_by_action(action $action) {
        $courseid = $this->get_course_id($action);
        $modinfo = get_fast_modinfo($courseid, $action->get_user_id());
        $cm = $modinfo->get_cm($action->get_context()->instanceid);
        if (!$this->is_section_completed($modinfo, $cm->sectionnum)) {
            return false;
        }
        return true;
    }

    /**
     * Whether the section of cm is completed.
     *
     * This is an identical copy of the code in collection_strategy.
     *
     * @param modinfo $modinfo The course modinfo for the given user.
     * @param int $sectionnum The section number.
     * @return bool
     */
    protected function is_section_completed($modinfo, $sectionnum) {
        global $CFG;

        $this->ensure_completion_lib_is_included();

        $sections = $modinfo->get_sections();
        if (empty($sections[$sectionnum])) {
            return false;
        }

        $completioninfo = new \completion_info($modinfo->get_course());
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

}
