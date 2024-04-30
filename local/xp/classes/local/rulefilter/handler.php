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
 * Handler.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rulefilter;

use block_xp\local\rulefilter\rulefilter;

/**
 * Handler.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class handler extends \block_xp\local\rulefilter\default_handler {

    /**
     * Get the filter's name.
     *
     * @param rulefilter $filter The filter instance.
     */
    public function get_filter_name(rulefilter $filter): string {
        $classname = get_class($filter);
        if (strpos($classname, 'local_xp\\') === 0) {
            return str_replace('local_xp\\local\\rulefilter\\', '', $classname);
        }
        return parent::get_filter_name($filter);
    }

    /**
     * Load a filter.
     *
     * @param string $name The name.
     * @return rulefilter|null
     */
    protected function load_filter($name) {
        $class = "local_xp\\local\\rulefilter\\$name";
        $instance = null;
        if (class_exists($class)) {
            $instance = new $class();
            if (!$instance instanceof rulefilter) {
                $instance = null;
            }
        }
        return $instance ?? parent::load_filter($name);
    }

    /**
     * Make the filters list with priority.
     *
     * @return array
     */
    protected function make_filters_list_with_priority(): array {
        $filters = parent::make_filters_list_with_priority();
        return array_merge($filters, [
            // Course modules.
            'cm' => 9000,
            'cmname' => 2000,

            // Sections.
            'section' => 1000,

            // Course.
            'thiscourse' => 100,

            // Any.
            'anycm' => 9,
            'anysection' => 6,
            'anycourse' => 3,
        ]);
    }

}
