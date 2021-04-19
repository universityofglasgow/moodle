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
 * Badge URL resolver stack.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\xp;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\xp\badge_url_resolver;

/**
 * Badge URL resolver stack.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class badge_url_resolver_stack implements badge_url_resolver {

    /** @var array The resolvers. */
    protected $resolvers;

    /**
     * Constructor.
     *
     * @param array $resolvers Array of resolvers.
     */
    public function __construct(array $resolvers) {
        $this->resolvers = $resolvers;
    }

    /**
     * Get badge URL for level.
     *
     * @param int $level The level, as an integer.
     * @return moodle_url|null
     */
    public function get_url_for_level($level) {
        foreach ($this->resolvers as $resolver) {
            $url = $resolver->get_url_for_level($level);
            if ($url !== null) {
                return $url;
            }
        }
        return null;
    }

}
