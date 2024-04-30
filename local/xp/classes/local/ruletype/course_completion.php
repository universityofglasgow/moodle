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
use local_xp\local\reason\course_completed_reason;

/**
 * Type.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_completion implements ruletype {

    public function get_compatible_filters(): array {
        return ['thiscourse', 'anycourse'];
    }

    public function get_display_name(): lang_string {
        return new lang_string('ruletypecoursecompletion', 'block_xp');
    }

    public function get_repeat_window(): ?string {
        return static::WINDOW_ONCE;
    }

    public function get_short_description(): lang_string {
        return new lang_string('ruletypecoursecompletiondesc', 'block_xp');
    }

    public function is_action_compatible(action $action): bool {
        return $action->get_type() === 'course_completed';
    }

    public function is_action_satisfying_requirements(action $action): bool {
        return true;
    }

    public function make_reason(action $action): reason {
        return new course_completed_reason($action->get_object_id());
    }

}
