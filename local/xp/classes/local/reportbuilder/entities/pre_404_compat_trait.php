<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

/**
 * Pre 4.4 compat.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\reportbuilder\entities;

use core_reportbuilder\local\helpers\database;

defined('MOODLE_INTERNAL') || die();

if ($CFG->branch >= 404) {
    /**
     * Pre 4.4 compat.
     *
     * @package    local_xp
     * @copyright  2025 Frédéric Massart
     * @author     Frédéric Massart <fred@branchup.tech>
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    trait pre_404_compat_trait {
    }
} else {
    /**
     * Pre 4.4 compat.
     *
     * @package    local_xp
     * @copyright  2025 Frédéric Massart
     * @author     Frédéric Massart <fred@branchup.tech>
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    trait pre_404_compat_trait { // @codingStandardsIgnoreLine

        /** @var array|null */
        private $defaultaliases;

        /**
         * Get default aliases.
         *
         * @return array
         */
        protected function get_default_table_aliases(): array {
            if (!isset($this->defaultaliases)) {
                $this->defaultaliases = array_reduce($this->get_default_tables(), function($carry, $table) {
                    $carry[$table] = database::generate_alias();
                    return $carry;
                }, []);
            }
            return $this->defaultaliases;
        }

    }
}
