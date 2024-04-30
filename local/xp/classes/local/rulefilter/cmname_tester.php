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
 * Filter.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rulefilter;

use block_xp\local\action\action;
use block_xp\local\rulefilter\action_tester;
use core_text;

/**
 * Filter.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cmname_tester implements action_tester {

    /** Method equals. */
    const METHOD_EQUALS = cmname::METHOD_EQUALS;
    /** Method contains. */
    const METHOD_COTNAINS = cmname::METHOD_CONTAINS;

    /** @var int The comparison method. */
    protected $method = 0;
    /** @var string The string. */
    protected $str;

    /**
     * Constructor.
     *
     * @param int $method The comparison method.
     * @param string $str The string.
     */
    public function __construct($method, $str) {
        $this->method = (int) $method;
        $this->str = core_text::strtolower(trim((string) $str));
    }

    public function is_action_passing_constraints(action $action): bool {
        if ($this->str === '') {
            return false;
        }

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

        $name = core_text::strtolower(trim($cm->name));
        if ($name === '') {
            return false;
        }

        if ($this->method === static::METHOD_EQUALS) {
            return $name === $this->str;
        }
        return core_text::strpos($name, $this->str) !== false;
    }

}
