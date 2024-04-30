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

use block_xp\local\rulefilter\action_tester;
use block_xp\local\rulefilter\rulefilter;
use context;
use lang_string;

/**
 * Filter.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cmname implements rulefilter {

    /** Method equals. */
    const METHOD_EQUALS = 0;
    /** Method contains. */
    const METHOD_CONTAINS = 1;

    public function get_action_tester(context $effectivecontext, object $config): action_tester {
        return new cmname_tester($config->int1 ?? static::METHOD_EQUALS, $config->char1 ?? '');
    }

    public function get_compatible_context_levels(): array {
        return [CONTEXT_SYSTEM, CONTEXT_COURSE];
    }

    public function get_display_name(): lang_string {
        return new lang_string('rulefiltercmname', 'block_xp');
    }

    public function get_label_for_config(object $config, ?context $effectivecontext = null): string {
        if (($config->int1 ?? 0) === static::METHOD_EQUALS) {
            return get_string('nameequalsto', 'block_xp', $config->char1);
        }
        return get_string('namecontains', 'block_xp', $config->char1);
    }

    public function get_short_description(): lang_string {
        return new lang_string('rulefiltercmnamedesc', 'block_xp');
    }

    public function is_compatible_with_admin(): bool {
        return true;
    }

    public function is_multiple_allowed(): bool {
        return true;
    }

}
