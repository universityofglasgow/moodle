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
 * Drop collection strategy.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\strategy;
defined('MOODLE_INTERNAL') || die();

use local_xp\local\drop\drop;

/**
 * Drop collection strategy.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface drop_collection_strategy extends collection_strategy {

    /**
     * Handle an event.
     *
     * @param drop $drop The drop that the user has found.
     * @param int $userid The user that has found the drop.
     * @return bool True if acquired.
     */
    public function collect_drop_for_user(drop $drop, $userid);

    /**
     * Can the user collect the event?
     *
     * @param drop $drop The drop that the user has found.
     * @param int $userid The user that we need to check.
     * @return bool
     */
    public function can_collect(drop $drop, $userid);
}
