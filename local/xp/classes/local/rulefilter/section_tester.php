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
 * Tester.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rulefilter;

use block_xp\local\action\action;
use block_xp\local\rulefilter\action_tester;

/**
 * Tester.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_tester implements action_tester {

    /** @var int The section number. */
    protected $sectionnum = 0;

    /**
     * Constructor.
     *
     * @param int $sectionnum The section.
     */
    public function __construct($sectionnum) {
        $this->sectionnum = (int) $sectionnum;
    }

    public function is_action_passing_constraints(action $action): bool {
        $context = $action->get_context();
        if (!$context instanceof \context_module) {
            return false;
        }

        $coursectx = $context->get_course_context(false);
        if (!$coursectx) {
            return false;
        }

        $modinfo = get_fast_modinfo($coursectx->instanceid);
        $cmid = $context->instanceid;
        try {
            $cm = $modinfo->get_cm($cmid);
        } catch (\moodle_exception $e) {
            return false;
        }

        return ((int) $cm->sectionnum) === $this->sectionnum;
    }

}
