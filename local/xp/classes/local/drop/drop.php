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
 * Drop interface.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\drop;
defined('MOODLE_INTERNAL') || die();

/**
 * Drop interface.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface drop {

    /**
     * Get the ID.
     *
     * @return int
     */
    public function get_id();

    /**
     * Get the course ID.
     *
     * @return int
     */
    public function get_courseid();

    /**
     * Get the points for the drop.
     *
     * @return int
     */
    public function get_points();

    /**
     * Get the secret for the drop.
     *
     * @return string
     */
    public function get_secret();

    /**
     * Get the name of the drop.
     *
     * @return string
     */
    public function get_name();

    /**
     * Whether the drop is enabled.
     *
     * @return bool
     */
    public function is_enabled();

}