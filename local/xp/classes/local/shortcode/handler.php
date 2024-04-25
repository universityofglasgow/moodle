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

use block_xp\di;
use block_xp\local\sql\limit;
use block_xp\local\xp\state_store_with_reason;
use local_xp\local\config\default_course_world_config;
use local_xp\local\logger\reason_occurance_indicator;
use local_xp\local\reason\drop_collected_reason;

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
     * @param string $drop The shortcode.
     * @param array $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function xpdrop($drop, $args, $content, $env, $next) {
        global $USER;

        if (!di::get('addon')->is_activated()) {
            return;
        }

        $secret = null;
        if (!empty($args['secret'])) {
            $secret = $args['secret'];
        } else if (count($args) === 1) {
            $candidate = array_keys($args)[0];
            if ($args[$candidate] === true) {
                $secret = $candidate;
            }
        }
        if (empty($secret)) {
            return;
        }

        $repo = di::get('drop_repository');
        $drop = $repo->get_by_secret($secret);
        if (!$drop) {
            return;
        }

        // The drop belongs to a course while the plugin is set in whole site mode. Or vice-versa.
        $worldfactory = di::get('course_world_factory');
        $world = $worldfactory->get_world($drop->get_courseid());
        $perms = $world->get_access_permissions();
        $canmanage = $perms->can_manage();
        if ($world->get_courseid() != $drop->get_courseid()) {
            return;
        }

        // Only display the process the drop when points are enabled, or person is manager.
        $config = $world->get_config();
        $earnallowed = $config->get('enabled') && $drop->is_enabled();
        $canearn = !isguestuser() && !is_siteadmin() && has_capability('block/xp:earnxp', $world->get_context());
        if ($earnallowed && $canearn) {
            $loggerfactory = di::get('course_collection_logger_factory');
            $logger = $loggerfactory->get_collection_logger($world->get_courseid());
            $reason = new drop_collected_reason($drop->get_id());

            // Check whether the logger supports what we need. We can remove this when we have
            // a factory for getting the reason_occurance_indicator for a given course.
            if (!$logger instanceof reason_occurance_indicator) {
                debugging('Collection logger must implement reason_occurance_indicator to support drops.', DEBUG_DEVELOPER);
                return;
            }

            // Check whether we've already processed this drop, and increase.
            if (!$logger->has_reason_happened_since($USER->id, $reason, new \DateTime('@0'))) {
                $store = $world->get_store();
                if (!$store instanceof state_store_with_reason) {
                    debugging('Store implement state_store_with_reason to support drops.', DEBUG_DEVELOPER);
                    return;
                }
                $store->increase_with_reason($USER->id, $drop->get_points(), $reason);
            }

        }

        // Managers are shown something.
        if ($canmanage) {
            $name = format_string($drop->get_name(), true, ['context' => $world->get_context()]);
            $suffix = !$drop->is_enabled() ? ' (' . get_string('disabled', 'core_admin') . ')' : '';
            return get_string('dropherea', 'local_xp', $name) . $suffix;
        }
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

        if (!di::get('addon')->is_activated()) {
            return;
        }

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
