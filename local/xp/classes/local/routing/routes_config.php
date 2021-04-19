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
 * Routes config.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\routing;
defined('MOODLE_INTERNAL') || die();

use \block_xp\local\routing\route_definition;
use \block_xp\local\routing\routes_config as routes_config_interface;

/**
 * Routes config.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class routes_config implements routes_config_interface {

    /** @var route_definition[] The routes. */
    protected $routes;

    /**
     * Constructor.
     *
     * @param block_xp\local\routing\routes_config $altconfig Alternative config.
     */
    public function __construct(routes_config_interface $altconfig) {
        $this->routes = [
            'group_ladder' => new route_definition(
                'group_ladder',
                '/group/ladder/:courseid',
                '~^/group/ladder/(\d+)$~',
                'group_ladder',
                [
                    1 => 'courseid'
                ]
            ),
            'import' => new route_definition(
                'import',
                '/import/:courseid',
                '~^/import/(\d+)$~',
                'import',
                [
                    1 => 'courseid'
                ]
            )
        ];
        $this->altconfig = $altconfig;
    }

    /**
     * Get a route.
     *
     * @param string $name The route name.
     * @return route_definition
     */
    public function get_route($name) {
        if (!array_key_exists($name, $this->routes)) {
            return $this->altconfig->get_route($name);
        }
        return $this->routes[$name];
    }

    /**
     * Return an array of routes.
     *
     * @return route_definition[]
     */
    public function get_routes() {
        return array_merge($this->altconfig->get_routes(), $this->routes);
    }

}
