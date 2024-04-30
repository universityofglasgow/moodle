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
 * Default course world grouped leaderboard factory.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\factory;

use lang_string;
use moodle_database;
use renderer_base;
use block_xp\local\course_world;
use block_xp\local\config\course_world_config;
use block_xp\local\leaderboard\anonymisable_leaderboard;
use block_xp\local\leaderboard\empty_leaderboard;
use block_xp\local\leaderboard\leaderboard;
use block_xp\local\xp\full_anonymiser;
use coding_exception;
use local_xp\local\config\default_course_world_config;
use local_xp\local\iomad\company_leaderboard;
use local_xp\local\iomad\department_leaderboard;
use local_xp\local\iomad\facade as iomadfacade;
use local_xp\local\leaderboard\cohort_leaderboard;
use local_xp\local\leaderboard\course_group_leaderboard;
use local_xp\local\team\team_membership_resolver;

/**
 * Default course world grouped leaderboard factory.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class default_course_world_grouped_leaderboard_factory implements
        course_world_grouped_leaderboard_factory,
        course_world_team_membership_resolver_factory {

    /** @var moodle_database The DB. */
    protected $db;
    /** @var iomadfacade IOMAD. */
    protected $iomadfacade;
    /** @var renderer_base The renderer. */
    protected $renderer;
    /** @var grouped_leaderboard_helper The helper. */
    protected $helper;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param renderer_base $renderer The renderer.
     * @param iomadfacade $iomadfacade IOMAD.
     * @param grouped_leaderboard_helper $helper The helper.
     */
    public function __construct(moodle_database $db, renderer_base $renderer, iomadfacade $iomadfacade, $helper) {
        $this->db = $db;
        $this->renderer = $renderer;
        $this->iomadfacade = $iomadfacade;
        $this->helper = $helper;
    }

    /**
     * Get the leaderboard.
     *
     * @param course_world $world The world.
     * @return leaderboard
     */
    public function get_course_grouped_leaderboard(course_world $world) {
        global $USER;

        // Find out what columns to use.
        $columns = $this->get_columns($world);

        // Get the leaderboard.
        $leaderboard = $this->get_leaderboard_instance($world, $columns);

        // Wrap?
        $leaderboard = $this->wrap_leaderboard($world, $leaderboard);

        return $leaderboard;
    }

    /**
     * Get the membership resolver.
     *
     * @param course_world $world The world.
     * @return team_membership_resolver
     */
    public function get_course_team_membership_resolver(course_world $world) {
        $leaderboard = $this->get_leaderboard_instance($world, $this->get_columns($world));
        if (!$leaderboard instanceof team_membership_resolver) {
            throw new coding_exception('Team leaderboard is expected to implement team_membership_resolver');
        }
        return $leaderboard;
    }

    /**
     * Get the columns.
     *
     * @param course_world $world The world.
     * @return array
     */
    protected function get_columns(course_world $world) {
        $columns = [
            'rank' => new lang_string('rank', 'block_xp'),
            'name' => new lang_string('groupname', 'local_xp'),
        ];

        $config = $world->get_config();
        $additionalcols = explode(',', $config->get('groupladdercols'));
        if (in_array('xp', $additionalcols)) {
            $columns['xp'] = new lang_string('grouppoints', 'local_xp');
        }
        if (in_array('progress', $additionalcols)) {
            $columns['progress'] = new lang_string('progress', 'block_xp');
        }

        return $columns;
    }

    /**
     * Get the leaderboard instance.
     *
     * @param course_world $world The world.
     * @param array $columns The columns.
     * @return leaderboard
     */
    protected function get_leaderboard_instance(course_world $world, array $columns) {
        $source = $world->get_config()->get('enablegroupladder');
        $courseid = $world->get_courseid();
        $levelsinfo = $world->get_levels_info();
        $orderby = $world->get_config()->get('grouporderby');
        $leaderboard = null;

        if ($source == default_course_world_config::GROUP_LADDER_COURSE_GROUPS) {
            $leaderboard = new course_group_leaderboard($this->db, $courseid, $columns, $levelsinfo, $orderby);

        } else if ($source == default_course_world_config::GROUP_LADDER_COHORTS) {
            $leaderboard = new cohort_leaderboard($this->db, $courseid, [], $columns, $levelsinfo, $orderby);

        } else if ($source == default_course_world_config::GROUP_LADDER_IOMAD_COMPANIES) {
            $leaderboard = new company_leaderboard($this->db, $courseid, $this->iomadfacade, [], $columns, $levelsinfo, $orderby);

        } else if ($source == default_course_world_config::GROUP_LADDER_IOMAD_DEPARTMENTS) {
            $leaderboard = new department_leaderboard($this->db, $courseid, $this->iomadfacade,
                $this->iomadfacade->get_viewing_companyid(), [], $columns, $levelsinfo, $orderby);

        } else {
            debugging('Unknown source for the group leaderboard: ' . $source, DEBUG_DEVELOPER);
            $leaderboard = new empty_leaderboard($columns);
        }

        return $leaderboard;

    }

    /**
     * Wrap the leaderboard if needed.
     *
     * @param course_world $world The world.
     * @param leaderboard $leaderboard The leaderboard.
     * @return leaderboard
     */
    protected function wrap_leaderboard(course_world $world, leaderboard $leaderboard) {
        global $USER;
        $config = $world->get_config();

        // Is the leaderboard anonymous?
        if ($config->get('groupidentitymode') == course_world_config::IDENTITY_OFF) {
            $anonymiser = new full_anonymiser(
                guest_user(),
                $this->helper->get_user_group_ids($USER, $world),
                $this->helper->get_anonymous_group_name($world)
            );
            $leaderboard = new anonymisable_leaderboard($leaderboard, $anonymiser);
        }

        return $leaderboard;
    }

}
