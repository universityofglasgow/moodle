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
 * Mobile renderer.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\output;

defined('MOODLE_INTERNAL') || die();

use DateTime;
use block_xp\di;
use block_xp\local\config\course_world_config;
use block_xp\local\course_world;
use block_xp\local\world;
use local_xp\external;
use local_xp\local\config\default_course_world_config;

/**
 * Mobile renderer class.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {

    /**
     * Init the course options.
     *
     * @return array
     */
    public static function init_course_options() {
        global $USER;

        $config = di::get('config');
        if ($config->get('context') != CONTEXT_COURSE) {
            throw new \moodle_exception('Not accessible per course.');
        } else if (isguestuser() || !$USER->id) {
            throw new \moodle_exception('User not allowed.');
        }

        $worldfactory = di::get('course_world_factory');
        $bifinder = di::get('course_world_block_any_instance_finder_in_context');
        $courseids = di::get('db')->get_fieldset_select('block_xp_config', 'courseid', '');
        return [
            'restrict' => [
                'courses' => array_filter($courseids, function($courseid) use ($bifinder, $worldfactory) {
                    $world = $worldfactory->get_world($courseid);
                    return $world->get_access_permissions()->can_access()
                        && $bifinder->get_any_instance_in_context('xp', $world->get_context());
                })
            ]
        ];
    }

    /**
     * Init the main menu.
     *
     * @return void
     */
    public static function init_mainmenu() {
        global $USER;

        $config = di::get('config');
        if ($config->get('context') != CONTEXT_SYSTEM) {
            throw new \moodle_exception('Not accessible site-wide.');
        } else if (isguestuser() || !$USER->id) {
            throw new \moodle_exception('User not allowed.');
        }

        $worldfactory = di::get('course_world_factory');
        $world = $worldfactory->get_world(SITEID);
        if (!$world->get_access_permissions()->can_access()) {
            throw new \moodle_exception('User cannot view the block.');
        }
    }

    /**
     * Enhance the data to pass to a page template.
     *
     * @param course_world $world The world.
     * @param array $data The data.
     * @param int $page The page number.
     * @return array
     */
    protected static function enhance_page_data(course_world $world, $data, $page) {
        $currencyfactory = di::get('course_currency_factory');
        $courseid = $world->get_courseid();
        return array_merge($data, [
            'courseid' => $courseid,
            'css' => static::get_css_data(),
            'currency' => external::serialize_currency($currencyfactory->get_currency($courseid)),
            'tabs' => static::get_tabs_data($world, $page),
        ]);
    }

    /**
     * Get the CSS data for template.
     *
     * @return array
     */
    protected static function get_css_data() {
        $renderer = di::get('renderer');
        return [
            'badges' => array_reduce(range(1, 10), function($carry, $i) use ($renderer) {
                $carry["level$i"] = $renderer->pix_url($i, 'block_xp')->out(false);
                return $carry;
            }, [])
        ];
    }

    /**
     * Get the group selector data for template.
     *
     * @param int $courseid The course ID.
     * @param int $currentgroupid The current group ID (-1 for undefined).
     * @param int $userid The user ID.
     * @return array
     */
    protected static function get_group_selector_data($courseid, $currentgroupid, $userid) {
        $data = [
            'enabled' => false,
            'currentgroupid' => max(0, $currentgroupid)
        ];

        $modinfo = get_fast_modinfo($courseid, $userid);
        if (groups_get_course_groupmode($modinfo->get_course()) != NOGROUPS) {
            $groupinfo = external::get_course_group_info($courseid);
            $data = array_merge(
                $data,
                $groupinfo,
                [
                    'mygroups' => array_values(array_filter($groupinfo['groups'], function($group) {
                        return $group['ismember'];
                    })),
                    'othergroups' => array_values(array_filter($groupinfo['groups'], function($group) {
                        return !$group['ismember'];
                    }))
                ]
            );
            $canseeallpart = $data['canseeallparticipants'];
            $data['firstseparator'] = !empty($data['mygroups']);
            $data['secondseparator'] = ($canseeallpart || !empty($data['mygroups'])) && !empty($data['othergroups']);
            $data['canswitch'] = count($data['groups']) > 1 || (count($data['groups']) == 1 && $canseeallpart);
            $data['usesvisiblegroups'] = $data['groupmode'] == VISIBLEGROUPS;
            $data['usesseparategroups'] = $data['groupmode'] == SEPARATEGROUPS;
            $data['enabled'] = true;

            // Select the first group that they can access.
            if ($currentgroupid < 0 && !$data['canaccessallgroups']) {
                if ($data['usesseparategroups']) {
                    $data['currentgroupid'] = $data['mygroups'][0]['id'];
                } else {
                    $data['currentgroupid'] = !empty($data['mygroups']) ? $data['mygroups'][0]['id'] : 0;
                }
            }

            // Set the group name.
            $data['groupname'] = $data['currentgroupid'] ? array_reduce($data['groups'], function($carry, $group) use ($data) {
                if ($data['currentgroupid'] == $group['id']) {
                    $carry = $group['name'];
                }
                return $carry;
            }, '?') : get_string('allparticipants', 'core');
        }

        return $data;
    }

    /**
     * Get the tabs data for template.
     *
     * @param world $world The world.
     * @param string $currenttab An identifier of the current tab.
     * @return array
     */
    protected static function get_tabs_data(world $world, $currenttab) {
        $forwholesite = di::get('config')->get('context') == CONTEXT_SYSTEM;
        $hasinfo = $world->get_config()->get('enableinfos');
        $hasladder = $world->get_config()->get('enableladder');
        $hasgroupladder = $world->get_config()->get('enablegroupladder') != default_course_world_config::GROUP_LADDER_NONE;
        return [
            'enabled' => $hasinfo || $hasladder,
            'hasinfo' => $hasinfo,
            'hasladder' => $hasladder,
            'hasgroupladder' => $hasgroupladder,
            'infoactive' => $currenttab == 'info',
            'ladderactive' => $currenttab == 'ladder',
            'groupladderactive' => $currenttab == 'groupladder',
            'stateactive' => $currenttab == 'state',
        ];
    }

    /**
     * Group ladder page.
     *
     * @param array $args The arguments.
     * @return array
     */
    public static function group_ladder_page(array $args) {
        global $USER;
        $courseid = isset($args['courseid']) ? $args['courseid'] : SITEID;
        $page = isset($args['page']) ? $args['page'] : 0;
        $perpage = 50;
        $userid = $USER->id;

        $worldfactory = di::get('course_world_factory');
        $renderer = di::get('renderer');
        $world = $worldfactory->get_world($courseid);

        // Ladder.
        $ladder = external::get_course_world_group_ladder($courseid, $page, $perpage);
        $ladder['prevpage'] = $ladder['page'] - 1;
        $ladder['nextpage'] = $ladder['page'] + 1;
        $ladder['hasbefore'] = $ladder['page'] > 1;
        $ladder['hasmore'] = $ladder['page'] * $perpage < $ladder['total'];
        $ladder['ranking'] = array_map(function($entry) use ($userid) {
            $state = $entry['state'];
            return array_merge($entry, [
                'rankpositive' => $entry['rank'] > 0,
                'state' => $state,

                // Custom.
                'percentage' => $state['ratioinlevel'] * 100,
                'xptogo' => $state['totalxpinlevel'] - $state['xpinlevel'],
            ]);
        }, $ladder['ranking']);
        $ladder['showpoints'] = in_array('xp', $ladder['columns']);
        $ladder['showprogress'] = in_array('progress', $ladder['columns']);

        // Permissions are handled in the external functions.
        $infodata = static::enhance_page_data($world, [
            'ladder' => $ladder
        ], 'groupladder');

        return [
            'templates' => [[
                'id' => 'page',
                'html' => $renderer->render_from_template('local_xp/mobile-group-ladder', $infodata)
            ]],
            'javascript' => null,
            'otherdata' => null,
            'files' => [],
        ];
    }

    /**
     * Render the info page.
     *
     * @param array $args The arguments.
     * @return array
     */
    public static function info_page(array $args) {
        $courseid = isset($args['courseid']) ? $args['courseid'] : 0;

        $worldfactory = di::get('course_world_factory');
        $renderer = di::get('renderer');
        $world = $worldfactory->get_world($courseid);
        $config = $world->get_config();

        $instructions = $config->get('instructions');
        $instructionsformat = $config->get('instructions_format');
        $cleanedinstructions = trim(strip_tags($instructions));
        $hasinstructions = !empty($cleanedinstructions);
        $instructionsformatted = null;
        if ($hasinstructions) {
            $ctx = $world->get_context();
            list($instructionsformatted, $unused) = external_format_text($instructions, $instructionsformat, $ctx);
        }

        // Levels info.
        $levelsinfo = external::get_levels_info($courseid);
        $alwaysshowname = array_reduce($levelsinfo['levels'], function($carry, $level) {
            return $carry || !empty($level['name']);
        }, false);
        if ($alwaysshowname) {
            $levelsinfo['levels'] = array_map(function($level) {
                if (empty($level['name'])) {
                    $level['name'] = get_string('levelx', 'block_xp', $level['level']);
                }
                return $level;
            }, $levelsinfo['levels']);
        }

        // Permissions are handled in the external function.
        $infodata = static::enhance_page_data($world, [
            'levelsinfo' => $levelsinfo,
            'instructions' => $instructionsformatted,
        ], 'info');

        return [
            'templates' => [[
                'id' => 'page',
                'html' => $renderer->render_from_template('local_xp/mobile-info', $infodata)
            ]],
            'javascript' => null,
            'otherdata' => null,
            'files' => [],
        ];
    }

    /**
     * The ladder page.
     *
     * @param array $args The arguments.
     * @return array
     */
    public static function ladder_page(array $args) {
        global $USER;
        $courseid = isset($args['courseid']) ? $args['courseid'] : SITEID;
        $groupid = isset($args['groupid']) ? $args['groupid'] : -1;
        $page = isset($args['page']) ? $args['page'] : 0;
        $perpage = 50;
        $userid = $USER->id;

        $worldfactory = di::get('course_world_factory');
        $renderer = di::get('renderer');
        $world = $worldfactory->get_world($courseid);

        // Group stuff.
        $groupselector = static::get_group_selector_data($courseid, $groupid, $userid);
        $groupid = $groupselector['currentgroupid'];

        // Ladder.
        $ladder = external::get_course_world_ladder($courseid, $groupid, $page, $perpage);
        $ladder['prevpage'] = $ladder['page'] - 1;
        $ladder['nextpage'] = $ladder['page'] + 1;
        $ladder['hasbefore'] = $ladder['page'] > 1;
        $ladder['hasmore'] = !$ladder['neighbours'] && ($ladder['page'] * $perpage < $ladder['total']);
        $ladder['showrank'] = $ladder['rankmode'] == course_world_config::RANK_ON;
        $ladder['showrelrank'] = $ladder['rankmode'] == course_world_config::RANK_REL;
        $ladder['showtotal'] = in_array('xp', $ladder['columns']);
        $ladder['ranking'] = array_map(function($entry) use ($userid) {
            return array_merge($entry, [
                'rankpositive' => $entry['rank'] > 0,
                'state' => array_merge($entry['state'], [
                    'user' => array_merge($entry['state']['user'], [
                        'isme' => $entry['state']['user']['id'] == $userid
                    ])
                ])
            ]);
        }, $ladder['ranking']);

        // Permissions are handled in the external functions.
        $infodata = static::enhance_page_data($world, [
            'ladder' => $ladder,
            'groupselector' => $groupselector
        ], 'ladder');

        return [
            'templates' => [[
                'id' => 'page',
                'html' => $renderer->render_from_template('local_xp/mobile-ladder', $infodata)
            ]],
            'javascript' => null,
            'otherdata' => [
                'groupid' => $groupid
            ],
            'files' => [],
        ];
    }

    /**
     * The main entry page.
     *
     * @param array $args The arguments.
     * @return array
     */
    public static function main_page(array $args) {
        return static::state_page($args);
    }

    /**
     * Render the state page.
     *
     * @param array $args The arguments.
     * @return array
     */
    public static function state_page(array $args) {
        global $USER;
        $courseid = isset($args['courseid']) ? $args['courseid'] : 0;
        $userid = $USER->id;

        $worldfactory = di::get('course_world_factory');
        $renderer = di::get('renderer');
        $world = $worldfactory->get_world($courseid);

        // Permissions are handled in the external function.
        $setup = external::get_setup($courseid);
        $state = external::get_user_state($courseid, $userid);
        $activity = external::get_recent_activity($courseid);
        $infodata = static::enhance_page_data($world, [
            'activity' => array_map(function($activity) use ($renderer) {
                return array_merge($activity, [
                    'timeago' => $renderer->tiny_time_ago(new DateTime("@{$activity['date']}"))
                ]);
            }, $activity),
            'showactivity' => !empty($setup['block']['recentactivity']),
            'setup' => $setup,
            'state' => $state,

            // Custom.
            'percentage' => $state['ratioinlevel'] * 100,
            'xptogo' => $state['totalxpinlevel'] - $state['xpinlevel'],
        ], 'state');

        return [
            'templates' => [[
                'id' => 'page',
                'html' => $renderer->render_from_template('local_xp/mobile-state', $infodata)
            ]],
            'javascript' => null,
            'otherdata' => null,
            'files' => [],
        ];
    }

}
