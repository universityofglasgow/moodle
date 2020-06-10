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
 * Grouped leaderboard helper.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\leaderboard;
defined('MOODLE_INTERNAL') || die();

use moodle_database;
use block_xp\local\course_world;
use local_xp\local\config\default_course_world_config;
use local_xp\local\iomad\facade as iomadfacade;

/**
 * Grouped leaderboard helper.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grouped_leaderboard_helper {

    /** @var moodle_database The database. */
    protected $db;
    /** @var iomadfacade IOMAD. */
    protected $iomad;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param iomadfacade $iomad IOMAD.
     */
    public function __construct(moodle_database $db, iomadfacade $iomad) {
        $this->db = $db;
        $this->iomad = $iomad;
    }

    /**
     * Get anonymous group name.
     *
     * @param course_world $world The world.
     * @return string
     */
    public function get_anonymous_group_name(course_world $world) {
        $config = $world->get_config();
        $source = $config->get('enablegroupladder');
        $name = get_string('anonymousgroup', 'local_xp');

        if ($source == default_course_world_config::GROUP_LADDER_IOMAD_COMPANIES) {
            $name = get_string('anonymousiomadcompany', 'local_xp');

        } else if ($source == default_course_world_config::GROUP_LADDER_IOMAD_DEPARTMENTS) {
            $name = get_string('anonymousiomaddepartment', 'local_xp');
        }

        return $name;
    }

    /**
     * Get a user's group IDs.
     *
     * Where the group source depends on the world settings.
     *
     * @param object $user The user.
     * @param world $world The world.
     * @return array
     */
    public function get_user_group_ids($user, course_world $world) {
        global $CFG;

        $config = $world->get_config();
        $source = $config->get('enablegroupladder');

        if ($source == default_course_world_config::GROUP_LADDER_COURSE_GROUPS) {
            require_once($CFG->libdir . '/grouplib.php');
            return groups_get_user_groups($world->get_courseid(), $user->id)[0];

        } else if ($source == default_course_world_config::GROUP_LADDER_COHORTS) {
            require_once($CFG->dirroot . '/cohort/lib.php');
            return array_keys(cohort_get_user_cohorts($user->id));

        } else if ($source == default_course_world_config::GROUP_LADDER_IOMAD_COMPANIES) {
            return $this->iomad->exists() ? $this->iomad->get_user_company_ids($user) : [];

        } else if ($source == default_course_world_config::GROUP_LADDER_IOMAD_DEPARTMENTS) {
            return $this->iomad->exists() ? $this->iomad->get_user_department_ids($user) : [];
        }

        return [];
    }
}
