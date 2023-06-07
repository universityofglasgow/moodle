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
 * External utils.
 *
 * @package    block_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_xp\local\utils;

/**
 * External utils.
 *
 * @package    block_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external_utils {

    /**
     * External format strings.
     *
     * Compatibility with PHP Units from Moodle 4.2 where external libs should no longer be
     * directly included. This convenience methods ensures that we are using the preferred
     * class or function.
     *
     * @param string $str The string to be filtered.
     * @param context|int $contextorid The context.
     * @param boolean $striplinks Whether to strip links.
     * @param array $options Options.
     * @return string
     */
    public static function format_string($str, $context, $striplinks = true, $options = []) {
        global $CFG;
        if ($CFG->branch >= 402) {
            return \core_external\util::format_string($str, $context, $striplinks, $options);
        }
        static::load_libs();

        // Older implementations of external_format_string expected an ID.
        $contextid = $context instanceof \context ? $context->id : $context;
        return external_format_string($str, $contextid, $striplinks, $options);
    }

    /**
     * Load external libs.
     */
    protected static function load_libs() {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');
    }

}
