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
 * Collection counts indicator.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\logger;
defined('MOODLE_INTERNAL') || die();

use DateTime;

/**
 * Collection counts indicator.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface collection_counts_indicator {

    /**
     * Count collections since a date.
     *
     * @param int $id The ID.
     * @param DateTime $since The date.
     * @return int
     */
    public function count_collections_since($id, DateTime $since);

    /**
     * Get collected points since a date.
     *
     * @param int $id The ID.
     * @param DateTime $since The date.
     * @return int
     */
    public function get_collected_points_since($id, DateTime $since);

}
