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
 * Main factory.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\factory;
defined('MOODLE_INTERNAL') || die();

use context_course;
use context_system;
use moodle_database;
use block_xp\local\factory\badge_url_resolver_course_world_factory;
use local_xp\local\strategy\collection_target_resolver_from_event;

/**
 * Main factory.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_world_factory implements \block_xp\local\factory\course_world_factory {

    /** @var coure_config_factory The config factory. */
    protected $configfactory;
    /** @var bool For the whole site? */
    protected $forwholesite = false;
    /** @var course_world[] World cache. */
    protected $worlds = [];
    /** @var collection_target_resolver_from_event The target resolver. */
    protected $usercolletiontargetresolver;
    /** @var badge_url_resolver_course_world_factory The admin resolver. */
    protected $urlresolverfactory;

    /**
     * Constructor.
     *
     * @param int $contextmode The context mode.
     * @param moodle_database $db The DB.
     * @param course_config_factory $configfactory The factory.
     */
    public function __construct($contextmode, moodle_database $db, course_config_factory $configfactory,
            collection_target_resolver_from_event $usercolletiontargetresolver,
            badge_url_resolver_course_world_factory $urlresolverfactory) {
        $this->db = $db;
        $this->configfactory = $configfactory;
        $this->usercolletiontargetresolver = $usercolletiontargetresolver;
        $this->urlresolverfactory = $urlresolverfactory;
        if ($contextmode == CONTEXT_SYSTEM) {
            $this->forwholesite = true;
        }
    }

    /**
     * Get the world.
     *
     * @param int $courseid Course ID.
     * @return block_xp\local\course_world
     */
    public function get_world($courseid) {

        // When the block was set up for the whole site we attach it to the site course.
        // We do this here to ensure that all instances of the block will show the same information,
        // regardless of the block in which it was added.
        if ($this->forwholesite) {
            $courseid = SITEID;
        }

        $courseid = intval($courseid);
        if (!isset($this->worlds[$courseid])) {
            $config = $this->configfactory->get_config($courseid);
            $this->worlds[$courseid] = new \local_xp\local\course_world(
                $config,
                $this->db,
                $courseid,
                $this->usercolletiontargetresolver,
                $this->urlresolverfactory
            );
        }
        return $this->worlds[$courseid];
    }

}
