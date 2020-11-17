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
 * Course currency factory.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\factory;
defined('MOODLE_INTERNAL') || die();

use \local_xp\local\config\default_course_world_config;
use \local_xp\local\currency\currency;

/**
 * Course currency factory.
 *
 * Note quite proud of this implementation...
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_currency_factory {

    /** @var course_world_factory The factory. */
    protected $worldfactory;
    /** @var array Currency cache. */
    protected $currencies = [];
    /** @var currency The admin resolver. */
    protected $defaultcurrency;

    /**
     * Constructor.
     *
     * @param course_world_factory $worldfactory World factory.
     */
    public function __construct(course_world_factory $worldfactory, currency $defaultcurrency) {
        $this->worldfactory = $worldfactory;
        $this->defaultcurrency = $defaultcurrency;
    }

    /**
     * Get the config.
     *
     * @param int $courseid Course ID.
     * @return currency
     */
    public function get_currency($courseid) {
        $courseid = (int) $courseid;
        if (!isset($this->currencies[$courseid])) {
            $world = $this->worldfactory->get_world($courseid);
            $courseid = $world->get_courseid();
            $config = $world->get_config();

            if ($config->get('currencystate') == default_course_world_config::CURRENCY_USE_DEFAULT) {
                $currency = $this->defaultcurrency;
            } else {
                $resolver = new \local_xp\local\currency\course_sign_url_resolver($world->get_context());
                $currency = new \local_xp\local\currency\default_currency($resolver);
            }

            $this->currencies[$courseid] = $currency;
        }
        return $this->currencies[$courseid];
    }

}
