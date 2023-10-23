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
    /** @var array The navigation cache. */
    protected $navcache = [];

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
        if (!isset($this->navcache[$world->get_courseid()])) {
            $this->navcache[$world->get_courseid()] = $this->make_course_navigation($world);
        }
        return $this->navcache[$world->get_courseid()];
    }

    /**
     * Make course navigation.
     *
     * @param course_world $world The world.
     * @return array
     */
    protected function make_course_navigation(course_world $world) {
        $parentlinks = $this->parentfactory->get_course_navigation($world);
        $courseid = $world->get_courseid();
        $urlresolver = $this->resolver;

        $links = [];
        $toadd = [];

        if ($world->get_config()->get('enablegroupladder') != default_course_world_config::GROUP_LADDER_NONE) {
            $toadd[] = [
                'in' => ['ladder'],
                'after' => [],
                'link' => [
                    'id' => 'group_ladder',
                    'url' => $urlresolver->reverse('group_ladder', ['courseid' => $courseid]),
                    'text' => get_string('teams', 'local_xp'),
                ]
            ];
        }

        if ($world->get_access_permissions()->can_manage()) {
            $toadd[] = [
                'in' => ['rules'],
                'after' => [],
                'link' => [
                    'id' => 'drops',
                    'url' => $urlresolver->reverse('drops', ['courseid' => $courseid]),
                    'text' => get_string('navdrops', 'local_xp')
                ]
            ];
        }

        $links = $parentlinks;
        while ($add = array_shift($toadd)) {
            $candidates = $links;
            $added = false;

            // Add in a certain node.
            $incandidates = !empty($add['in']) ? $add['in'] : [];
            foreach ($incandidates as $in) {
                if ($added) {
                    break;
                }
                $candidates = [];
                foreach ($links as $link) {
                    if (!$added && $link['id'] == $in) {
                        if (!isset($link['children'])) {
                            continue;
                        }
                        $link['children'][] = $add['link'];
                        $added = true;
                    }
                    $candidates[] = $link;
                }
            }

            // Add after a top-level node.
            foreach ($add['after'] as $after) {
                if ($added) {
                    break;
                }
                $candidates = [];
                foreach ($links as $link) {
                    $candidates[] = $link;
                    if (!$added && $link['id'] == $after) {
                        $candidates[] = $add['link'];
                        $added = true;
                    }
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
