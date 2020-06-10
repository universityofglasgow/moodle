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
 * Shortcode handler.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\shortcode;
defined('MOODLE_INTERNAL') || die();

use context_course;
use block_xp\di;
use block_xp\local\sql\limit;
use local_xp\local\config\default_course_world_config;

/**
 * Shortcode handler class.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class handler {

    /**
     * Get the world from the env.
     *
     * Also check whether the current user has access to the world.
     *
     * @param object $env The environment.
     * @return world|null
     */
    protected static function get_world_from_env($env) {
        $config = di::get('config');
        $courseid = SITEID;

        if ($config->get('context') == CONTEXT_COURSE) {
            $context = $env->context->get_course_context(false);
            if (!$context) {
                return null;
            }
            $courseid = $context->instanceid;
        }

        $world = di::get('course_world_factory')->get_world($courseid);
        $perms = $world->get_access_permissions();
        return $perms->can_access() ? $world : null;
    }

    /**
     * Handle the shortcode.
     *
     * @param string $shortcode The shortcode.
     * @param object $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function xpteamladder($shortcode, $args, $content, $env, $next) {
        global $PAGE, $USER;
        $world = static::get_world_from_env($env);
        if (!$world) {
            return;
        } else if ($world->get_config()->get('enablegroupladder') == default_course_world_config::GROUP_LADDER_NONE) {
            return;
        }

        // Find the groups the user belong to.
        $helper = di::get('grouped_leaderboard_helper');
        $teamids = $helper->get_user_group_ids($USER, $world);

        // Fetch the leaderboard.
        $factory = di::get('course_world_grouped_leaderboard_factory');
        $leaderboard = $factory->get_course_grouped_leaderboard($world);

        // Figure out the team to use as reference, we will try to get the one with the best rank.
        $pos = 0;
        if (!empty($teamids)) {
            $reduced = array_reduce($teamids, function($carry, $teamid) use ($leaderboard) {
                if ($carry === null) {
                    // Record the position of the first team found.
                    $pos = $leaderboard->get_position($teamid);
                    if ($pos !== null) {
                        return [$teamid, $pos];
                    }

                } else if ($carry[1] !== 0) {
                    // When the best team is not already ranked 1, return this team if it's got a better ranking.
                    $pos = $leaderboard->get_position($teamid);
                    if ($pos !== null) {
                        return $pos < $carry[1] ? [$teamid, $pos] : $carry;
                    }
                }
                return $carry;
            }, null);
            $pos = is_array($reduced) ? $reduced[1] : 0;
        }

        if (!empty($args['top'])) {
            // Show the top n users.
            if ($args['top'] === true) {
                $count = 5;
            } else {
                $count = max(1, intval($args['top']));
            }
            $limit = new limit($count, 0);

        } else {
            // Determine what part of the leaderboard to show and fence it.
            $before = 3;
            $after = 2;
            $offset = max(0, $pos - $before);
            $count = $before + $after + 1;
            $limit = new limit($count + min(0, $pos - $before), $offset);
        }

        // Output the table.
        $baseurl = $PAGE->url;
        $table = new \local_xp\output\group_leaderboard_table(
            $leaderboard,
            di::get('renderer'),
            $teamids,
            [
                'discardcolumns' => !empty($args['withprogress']) ? [] : ['progress'],
                'fence' => $limit,
            ]
        );
        $table->define_baseurl($baseurl);
        ob_start();
        $table->out($count);
        $html = ob_get_contents();
        ob_end_clean();

        // Output.
        $urlresolver = di::get('url_resolver');
        $link = '';
        $withlink = empty($args['hidelink']);
        if ($withlink && $leaderboard->get_count() > $limit->get_count()) {
            $link = \html_writer::div(
                \html_writer::link(
                    $urlresolver->reverse('group_ladder', ['courseid' => $world->get_courseid()]),
                    get_string('gotofullladder', 'block_xp')
                ),
                'xp-link-to-full-ladder'
            );
        }
        return \html_writer::div(
            $html . $link,
            'shortcode-xpteamladder'
        );
    }

}
