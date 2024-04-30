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
 * Default course world leaderboard factory.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\factory;

use moodle_database;
use block_xp\local\course_world;
use block_xp\local\config\config;
use block_xp\local\factory\default_course_world_leaderboard_factory;
use block_xp\local\leaderboard\ranker;
use local_xp\local\config\default_course_world_config;
use local_xp\local\iomad\course_user_leaderboard as iomad_course_user_leaderboard;
use local_xp\local\iomad\facade as iomadfacade;
use local_xp\local\leaderboard\course_user_leaderboard;
use local_xp\local\xp\firstname_initial_lastname_anonymiser;
use local_xp\local\xp\user_global_state;

/**
 * Default course world leaderboard factory.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_world_leaderboard_factory extends default_course_world_leaderboard_factory {

    /** @var iomadfacade IOMAD. */
    protected $iomadfacade;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param iomadfacade $iomadfacade IOMAD.
     */
    public function __construct(moodle_database $db, iomadfacade $iomadfacade) {
        parent::__construct($db);
        $this->iomadfacade = $iomadfacade;
    }

    /**
     * Get the anonymiser.
     *
     * @param course_world $world The course world.
     * @param config $config The config.
     * @return \block_xp\local\xp\state_anonymiser
     */
    protected function get_anonymiser_with_config(course_world $world, config $config) {
        global $USER;

        if ($config->get('identitymode') == default_course_world_config::IDENTITY_FIRSTNAME_INITIAL_LASTNAME) {
            return new firstname_initial_lastname_anonymiser([$USER->id]);
        }

        return parent::get_anonymiser_with_config($world, $config);
    }

    /**
     * Get the leaderboard instance.
     *
     * @param course_world $world The course world.
     * @param int $groupid The group ID.
     * @param array $columns The columns.
     * @param config $config The config.
     * @param ranker|null $ranker The ranker.
     * @return \block_xp\local\leaderboard\leaderboard
     */
    protected function get_leaderboard_instance_with_config(course_world $world, $groupid, array $columns,
            config $config, ranker $ranker = null) {

        $userstatefactory = null;
        $levelsinfo = $world->get_levels_info();
        if ($config->get('progressbarmode') == default_course_world_config::PROGRESS_BAR_MODE_OVERALL) {
            $userstatefactory = function($user, $points) use ($levelsinfo) {
                return new user_global_state($user, $points, $levelsinfo);
            };
        }

        if ($this->iomadfacade->exists()) {
            return new iomad_course_user_leaderboard(
                $this->db,
                $levelsinfo,
                $world->get_courseid(),
                $columns,
                $ranker,
                $groupid,
                $userstatefactory,
                $this->iomadfacade->get_viewing_companyid()
            );
        }
        return new course_user_leaderboard(
            $this->db,
            $levelsinfo,
            $world->get_courseid(),
            $columns,
            $ranker,
            $groupid,
            $userstatefactory
        );
    }

}
