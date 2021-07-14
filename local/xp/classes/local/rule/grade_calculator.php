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
 * Grade calculator.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

/**
 * Grade calculator.
 *
 * Extracts the points off a grade. Note that this will produce strange behaviours
 * when the item is a scale item, but for performance reasons we do not care
 * about this just yet. Also, we round grades to the nearest integer, and do
 * not accept negative grades.
 *
 * This calculator will always repond with a final result, or with points,
 * when it has established that the subject was related to receiving a grade.
 * It does not want to allow other calculators to evaluate this subject.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_calculator implements result_calculator {

    /**
     * Constructor.
     *
     * @param array $filters An array of event filters.
     */
    public function __construct(array $filters) {
        // We filter out the rulesets that are empty as they would always
        // match and we do not want this to happen for grades.
        $this->filters = array_filter($filters, function($filter) {
            $rule = $filter->get_rule();
            if ($rule instanceof \block_xp_ruleset) {
                return !static::is_ruleset_empty($rule);
            }
            return $rule;
        });
    }

    /**
     * Get the points for this subject.
     *
     * @param subject $subject The subject.
     * @return int Or null.
     */
    public function get_points(subject $subject) {
        $result = $this->get_result($subject);
        if ($result->is_final()) {
            return (int) $result->get_points(); // Always 0, or the actual points.
        }
        return $result->get_points();
    }

    /**
     * Get the result.
     *
     * @param subject $subject The subject.
     * @return result
     */
    public function get_result(subject $subject) {

        if (!$subject instanceof event_subject) {
            return new static_result(null);
        }

        $event = $subject->get_event();
        if (!$event instanceof \core\event\user_graded) {
            return new static_result(null);
        }

        // When we have established that this was a grade event, we will respond
        // with a final result of null. That is because the grade calculator takes
        // precedece over other calculators, and does not allow for other
        // calculators to establish points for user_graded actions.
        $finalnullresult = new static_result(null, true);

        if ($event->is_restored()) {
            // We can't call user_graded::get_grade on restore.
            return $finalnullresult;
        } else if (empty($event->other['itemid'])) {
            // We don't have a grade item ID? Good bye!
            return $finalnullresult;
        }

        // Now check that this event actually matches one of our filters. We only use
        // the filters to determine whether there is a match, not to determine the points.
        if (!$this->matches_filters($event)) {
            return $finalnullresult;
        }

        $gradeobject = $event->get_grade();
        if (!$gradeobject) {
            $gradeobject = \grade_grade::fetch(['id' => $event->objectid]);
        }

        // Grade pre-checks.
        if (!$gradeobject) {
            // Never trust the gradebook!
            return $finalnullresult;
        }

        // Whether to ignore the grade items when they are hidden. We do not support this yet,
        // it is up to the person that manages Level up! to ensure that hidden grades are not leaked.
        $ignoreifhidden = false;
        if ($ignoreifhidden) {
            if ($gradeobject->hidden) {
                // This check does not force the grade_item to be loaded, so let's give it a shot.
                // We do this because otherwise we would disclose the grade to the student.
                return $finalnullresult;
            } else if ($gradeobject->is_hidden()) {
                // This is the final check we should be doing before checking the grade_item, because
                // the grade item is loaded internally in grade_grade::is_hidden().
                return $finalnullresult;
            }
        }

        // We should be good now, return the grade as final result.
        $points = $gradeobject->finalgrade !== null ? max(0, (int) $gradeobject->finalgrade) : null;
        return new static_result($points, true);
    }

    /**
     * Whether the event matches our filters.
     *
     * @param \core\event\user_graded $e The event.
     * @return bool
     */
    protected function matches_filters(\core\event\user_graded $e) {
        foreach ($this->filters as $filter) {
            if ($filter->match($e)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Recursively if a ruleset is empty.
     *
     * We consider it empty when it only contains empty rulesets.
     * Any other kind of rule in a ruleset would consider it as non-empty.
     *
     * @param \block_xp_ruleset $ruleset The ruleset.
     * @return bool
     */
    protected static function is_ruleset_empty(\block_xp_ruleset $ruleset) {
        $rules = $ruleset->get_rules();
        if (empty($rules)) {
            return true;
        };

        foreach ($ruleset->get_rules() as $rule) {
            if (!$rule instanceof \block_xp_ruleset) {
                return false;
            }
            if (!static::is_ruleset_empty($rule)) {
                return false;
            }
        }

        return true;
    }

}
