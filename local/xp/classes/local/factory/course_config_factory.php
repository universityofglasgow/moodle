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
 * Course config factory.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\factory;
defined('MOODLE_INTERNAL') || die();

use moodle_database;
use block_xp\local\config\config;
use block_xp\local\config\config_stack;
use block_xp\local\config\immutable_config;
use block_xp\local\config\filtered_config;
use block_xp\local\config\table_row_config;

use local_xp\local\config\default_course_world_config;
use local_xp\local\config\default_admin_config;

/**
 * Course config factory.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_config_factory { // No interface for now, maybe later...

    /** @var config The admin config. */
    protected $adminconfig;
    /** @var config The config overrides. */
    protected $configoverrides;
    /** @var moodle_database The DB. */
    protected $db;
    /** @var config Local course defaults. */
    protected $localcoursedefaults;
    /** @var config Remote admin defaults. */
    protected $remoteadminconfig;

    /** @var array Config cache. */
    private $configcache = [];

    /**
     * Constructor.
     *
     * @param config $adminconfig The admin config.
     * @param moodle_database $db The DB.
     */
    public function __construct(config $adminconfig, moodle_database $db, config $adminconfiglocked) {
        $this->adminconfig = $adminconfig;
        $this->db = $db;

        // These are the defaults from the local plugin.
        // As we get the whole admin config, which contains the remote plugin's config too
        // we need to apply a filter on what we know are the remote defaults.
        $localdefaultadminconfig = new default_admin_config();
        $this->localcoursedefaults = new config_stack([
            new immutable_config(new filtered_config(
                $this->adminconfig,
                array_keys($localdefaultadminconfig->get_all())
            )),
            new default_course_world_config()
        ]);

        // There are the defaults for the admin config (from block_xp). Again
        // we also need to filter on the keys which are only known to block_xp.
        $remotedefaultadminconfig = new \block_xp\local\config\default_admin_config();
        $this->remoteadminconfig = new filtered_config(
            $this->adminconfig,
            array_keys($remotedefaultadminconfig->get_all())
        );

        // The overrides for a course config are based on the admin settings, for those admin settings that have
        // had their locked status set to true. The whole config is immutable to prevent writes on the admin settings.
        $this->configoverrides = new immutable_config(
            new filtered_config($this->adminconfig, array_keys(array_filter($adminconfiglocked->get_all())))
        );
    }

    /**
     * Get the config.
     *
     * @param int $courseid Course ID.
     * @return block_xp\local\config\config
     */
    public function get_config($courseid) {
        // This deserves a bit of an explanation.
        // We have configuration in two different locations, in the block, and in the local plugin.
        // We also have default settings, either from the course defaults, or from the admin when they are set.
        // So what we do in full is:
        // Create a stack with two main config objects, the local plugin, and the block.
        // We know that the stack will defer to the next object until it finds the config we need.
        // However, both our main config objects require to use some defaults. The local one
        // requires all the defaults, so we give it a reference to what we consider to be the defaults
        // from the local plugin's perspective. That means that we only look at the admin settings that we
        // would have set. And because our local plugin by default merges the admin config of both, we
        // had to filter the set. Secondly, we manually instantiate the block's config structure, which
        // requires the admin config as well, but as it should only know about its own admin, we filter
        // out what is not meant to be part of it.
        if (!isset($this->configcache[$courseid])) {
            $this->configcache[$courseid] = new config_stack([
                $this->configoverrides,
                new table_row_config($this->db, 'local_xp_config', $this->localcoursedefaults, ['courseid' => $courseid]),
                new \block_xp\local\config\course_world_config($this->remoteadminconfig, $this->db, $courseid)
            ]);
        }
        return $this->configcache[$courseid];
    }

}
