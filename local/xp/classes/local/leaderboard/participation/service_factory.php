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

namespace local_xp\local\leaderboard\participation;

use block_xp\local\factory\context_world_factory;
use context;
use local_xp\local\config\default_course_world_config;

/**
 * Service factory.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_factory {

    /** @var context_world_factory */
    protected $worldfactory;

    /**
     * Constructor.
     *
     * @param context_world_factory $cwf
     */
    public function __construct(context_world_factory $cwf) {
        $this->worldfactory = $cwf;
    }

    /**
     * Get service for context.
     *
     * @param context $context
     */
    public function get_for_context(context $context) {
        $world = $this->worldfactory->get_world_from_context($context);
        $mode = (int) $world->get_config()->get('ladderparticipation');
        if ($mode === default_course_world_config::LEADERBOARD_PARTICIPATION_OPTIN) {
            return new optinout_service($world, false, true);
        } else if ($mode === default_course_world_config::LEADERBOARD_PARTICIPATION_OPTOUT) {
            return new optinout_service($world, true, false);
        }
        return new forced_service();
    }

}
