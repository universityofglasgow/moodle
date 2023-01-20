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
 * Grade filter renderable.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\output;
defined('MOODLE_INTERNAL') || die();


/**
 * Grade filter renderable class.
 *
 * The only purpose of this class is for rendering. We need a different
 * renderable for different kinds of filters, but as block_xp only has one
 * kind of filter, the easiest is simply for us to .
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_filter implements \renderable {

    /** @var \block_xp_filter The filter. */
    public $filter;

    /**
     * Constructor.
     *
     * @param \block_xp_filter $filter The embedded filter.
     */
    public function __construct(\block_xp_filter $filter) {
        $this->filter = $filter;
    }

}
