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
 * Course world navigation factory.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\factory;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\course_world;
use block_xp\local\config\config;
use block_xp\local\factory\course_world_navigation_factory as course_world_navigation_factory_interface;
use block_xp\local\routing\url_resolver;
use local_xp\local\config\default_course_world_config;

/**
 * Course world navigation factory.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_world_navigation_factory implements course_world_navigation_factory_interface {

    /** @var config The admin config. */
    protected $adminconfig;
    /** @var course_world_navigation_factory_interface The parent factory. */
    protected $parentfactory;
    /** @var url_resolver The URL resolver. */
    protected $resolver;

    /**
     * Constructor.
     *
     * @param url_resolver $resolver The URL resolver.
     * @param course_world_navigation_factory_interface $parentfactory The parent factory.
     * @param config $adminconfig The admin config.
     */
    public function __construct(
            url_resolver $resolver,
            course_world_navigation_factory_interface $parentfactory,
            config $adminconfig) {
        $this->resolver = $resolver;
        $this->parentfactory = $parentfactory;
        $this->adminconfig = $adminconfig;
    }

    /**
     * Get the navigation.
     *
     * @param course_world $world The world.
     * @return array
     */
    public function get_course_navigation(course_world $world) {
        global $USER;

        $forwholesite = $this->adminconfig->get('context') == CONTEXT_SYSTEM;
        $parentlinks = $this->parentfactory->get_course_navigation($world);
        $courseid = $world->get_courseid();
        $urlresolver = $this->resolver;

        $links = [];
        $toadd = [];

        if ($world->get_config()->get('enablegroupladder') != default_course_world_config::GROUP_LADDER_NONE) {
            $toadd[] = [
                'after' => ['ladder', 'infos'],
                'link' => [
                    'id' => 'group_ladder',
                    'url' => $urlresolver->reverse('group_ladder', ['courseid' => $courseid]),
                    'text' => get_string('navgroupladder', 'local_xp')
                ]
            ];
        }

        $links = $parentlinks;
        while ($add = array_shift($toadd)) {
            $candidates = $links;

            $added = false;
            foreach ($add['after'] as $after) {
                $candidates = [];
                foreach ($links as $link) {
                    $candidates[] = $link;
                    if (!$added && $link['id'] == $after) {
                        $candidates[] = $add['link'];
                        $added = true;
                    }
                }
                if ($added) {
                    break;
                }
            }

            $links = $candidates;
            if (!$added) {
                array_unshift($links, $add['link']);
            }
        }

        return $links;
    }

}
