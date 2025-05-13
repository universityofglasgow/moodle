<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

namespace local_xp\local\factory;

use block_xp\local\division\division;
use block_xp\local\division\empty_division;
use block_xp\local\leaderboard\leaderboard;
use block_xp\local\userfilter\user_filter;
use block_xp\local\xp\state_anonymiser;
use local_xp\local\config\default_course_world_config;
use local_xp\local\division\cohort_division;
use local_xp\local\userfilter\leaderboard_participants;
use local_xp\local\userfilter\stack;
use local_xp\local\xp\firstname_initial_lastname_anonymiser;

/**
 * Factory.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class world_leaderboard_factory extends \block_xp\local\factory\world_leaderboard_factory {

    /**
     * Get the leaderboard
     *
     * @param int $targetuserid The target user ID.
     * @param user_filter|null $userfilter The user filter.
     * @return leaderboard
     */
    protected function assemble_leaderboard(int $targetuserid, ?user_filter $userfilter = null): leaderboard {

        // If the leaderboard participation is not forced, we stack in the leaderboard participants filter.
        $ladderpart = (int) $this->config->get('ladderparticipation');
        if ($ladderpart !== default_course_world_config::LEADERBOARD_PARTICIPATION_FORCED) {
            $userfilter = new stack(array_filter([
                $userfilter,
                new leaderboard_participants($this->world->get_context()->id, $ladderpart),
            ]));
        }

        return parent::assemble_leaderboard($targetuserid, $userfilter);
    }

    /**
     * Get the anonymiser.
     *
     * @param int $targetuserid The target user ID.
     * @return state_anonymiser|null
     */
    protected function get_anonymiser(int $targetuserid): ?state_anonymiser {
        if ($this->config->get('identitymode') == default_course_world_config::IDENTITY_FIRSTNAME_INITIAL_LASTNAME) {
            return new firstname_initial_lastname_anonymiser([$targetuserid]);
        }
        return parent::get_anonymiser($targetuserid);
    }

    /**
     * Get the default division.
     *
     * @param int $targetuserid The target user ID.
     * @return division
     */
    protected function get_default_division(int $targetuserid): division {
        $ladderiso = (int) $this->config->get('ladderiso');
        if ($ladderiso === default_course_world_config::LEADERBOARD_ISO_COHORTS) {
            $candidates = $this->db->get_records('cohort_members', ['userid' => $targetuserid],
                'cohortid ASC', 'id, cohortid, userid', 0, 1);
            if (empty($candidates)) {
                return new empty_division();
            }
            return new cohort_division(reset($candidates)->cohortid);
        }
        return parent::get_default_division($targetuserid);
    }

}
