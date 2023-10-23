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
 * Badge URL resolver course world factory.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\factory;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\course_world;
use block_xp\local\xp\badge_url_resolver;
use local_xp\local\theme\theme_repository;
use local_xp\local\xp\badge_url_resolver_stack;
use local_xp\local\xp\theme_badge_url_resolver;

/**
 * Badge URL resolver course world factory.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class badge_url_resolver_course_world_factory
        extends \block_xp\local\factory\default_badge_url_resolver_course_world_factory {

    /** @var theme_repository The repository. */
    protected $repo;

    /**
     * Constructor.
     *
     * @param badge_url_resolver $adminresolver The admin resolver.
     * @param theme_repository $repo The repo.
     */
    public function __construct(badge_url_resolver $adminresolver, theme_repository $repo) {
        parent::__construct($adminresolver);
        $this->repo = $repo;
    }

    /**
     * Get the URL resolver.
     *
     * @param course_world $world The world.
     * @return block_xp\local\xp\badge_url_resolver
     */
    public function get_url_resolver(course_world $world) {
        $defaultresolver = parent::get_url_resolver($world);
        $config = $world->get_config();
        $themecode = $config->get('badgetheme');

        if (empty($themecode)) {
            return $defaultresolver;
        }

        return new badge_url_resolver_stack([
            $defaultresolver,
            new theme_badge_url_resolver($this->repo, $themecode),
        ]);
    }

}
