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
 * Entry.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\provider;
defined('MOODLE_INTERNAL') || die();

/**
 * An entry is provided by a provider.
 *
 * This should be subclassed like providers to document the type of entries.
 *
 * Entries contain the object that is meant to be provided, as well as information
 * on the result from the provider. Proviers can decide to return invalid entries
 * in which case they can include errors.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface entry {

    /**
     * Get errors.
     *
     * @return array[] Keys are error codes, and strings are human-readable errors.
     */
    public function get_errors();

    /**
     * Get the underlying object designed.
     *
     * @throws moodle_exception When not valid.
     * @return mixed
     */
    public function get_object();

    /**
     * An arbitrary reference from the provider.
     *
     * For example, for a CSV provider, we may use the reference to indicate the line number.
     *
     * @return string|number
     */
    public function get_reference();

    /**
     * Whether the entry is valid.
     *
     * @return bool
     */
    public function is_valid();

}
