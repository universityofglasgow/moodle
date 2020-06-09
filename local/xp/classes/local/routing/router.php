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
 * Router.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\routing;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use block_xp\local\routing\routed_request;

/**
 * Router.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class router extends \block_xp\local\routing\router {

    /**
     * Override to include local controllers.
     *
     * @param routed_request $request The request.
     * @return block_xp\local\controller\controller
     */
    protected function get_controller_from_request(routed_request $request) {
        $route = $request->get_route();
        $name = $route->get_definition()->get_controller_name();

        $candidates = [
            "local_xp\\local\\controller\\{$name}_controller",
            "block_xp\\local\\controller\\{$name}_controller",
        ];

        $class = null;
        foreach ($candidates as $candidate) {
            if (!class_exists($candidate)) {
                continue;
            }
            $class = $candidate;
            break;
        }

        if (!$class) {
            throw new coding_exception('Controller for route not found.');
        }

        return new $class();
    }

}
