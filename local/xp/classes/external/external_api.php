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
 * Local external.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\external;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/gradelib.php');

use block_base;
use context;
use context_course;
use core_text;
use block_xp\external\external_description;
use block_xp\external\external_multiple_structure;
use block_xp\external\external_single_structure;
use block_xp\external\external_value;
use moodle_exception;
use moodle_url;
use block_xp\di;
use block_xp\local\activity\activity;
use block_xp\local\activity\activity_with_xp;
use block_xp\local\config\block_config;
use block_xp\local\config\config_stack;
use block_xp\local\config\filtered_config;
use block_xp\local\config\mapped_config;
use block_xp\local\utils\external_utils;
use block_xp\local\xp\anonymised_state;
use block_xp\local\xp\level;
use block_xp\local\xp\level_with_badge;
use block_xp\local\xp\level_with_description;
use block_xp\local\xp\level_with_name;
use block_xp\local\xp\levels_info;
use block_xp\local\xp\state;
use block_xp\local\xp\state_with_subject;
use block_xp\local\xp\user_state;
use local_xp\local\currency\currency;
use local_xp\local\xp\levelless_group_state;

/**
 * Local external class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external_api extends \block_xp\external\external_api {

    /**
     * Reduces a tree of grade items in a flat list.
     *
     * @param array $treeitem Containing what is returned from grade_category::fetch_course_tree.
     * @param string $query A string to filter items on, or '*'.
     * @param object[] $categories A list of parent categories, containing id and name.
     * @return object|array|null Null when nothing matches.
     */
    public static function grade_item_tree_reducer($treeitem, $query, $categories = []) {
        $item = $treeitem['object'];
        $iscategory = in_array($treeitem['type'], ['category']);

        if (!$iscategory && in_array($item->gradetype, [GRADE_TYPE_NONE, GRADE_TYPE_TEXT])) {
            return null;
        }

        if ($iscategory) {
            $children = $treeitem['children'];

            // Place the total last.
            $categorytotal = array_shift($children);
            $children[] = $categorytotal;

            $categories[] = (object) [
                'id' => $item->id,
                'name' => $item->fullname,
            ];
            return array_reduce($children, function($carry, $child) use ($query, $categories) {
                $child = static::grade_item_tree_reducer($child, $query, $categories);
                if ($child === null) {
                    return $carry;
                } else if (!is_array($child)) {
                    $child = [$child];
                }
                return array_merge($carry, $child);
            }, []);
        }

        $data = (object) ['id' => $item->id];
        $data->type = $item->itemtype;
        $data->name = $item->itemname;
        $data->module = $item->itemmodule;
        $data->modulename = $item->itemmodule;
        $data->min = $item->grademin;
        $data->max = $item->grademax;
        $data->categories = $categories;

        // Not all of them have a name.
        if ($item->itemtype === 'course') {
            $data->name = get_string('coursetotal', 'core_grades');
        } else if ($item->itemtype === 'category') {
            $data->name = get_string('categorytotal', 'core_grades');
        }

        if ($query === '*') {
            return $data;
        }

        $candidates = [core_text::strtolower($data->name)];
        if ($item->itemtype === 'course' || $item->itemtype === 'category') {
            $lastcategory = end($categories);
            if ($lastcategory) {
                $candidates[] = core_text::strtolower($lastcategory->name);
            }
        }

        $matchesquery = array_reduce($candidates, function($carry, $candidate) use ($query) {
            return $carry || strpos($candidate, $query) !== false;
        }, false);
        if (!$matchesquery) {
            return null;
        }

        return $data;
    }


    /**
     * Activity description.
     *
     * @param int $required VALUE_* constant.
     * @return external_description
     */
    public static function activity_description($required = VALUE_REQUIRED) {
        return new external_single_structure([
            'date' => new external_value(PARAM_INT),
            'desc' => new external_value(PARAM_NOTAGS),
            'xp' => new external_value(PARAM_INT, null, VALUE_OPTIONAL),
        ], '', $required);
    }

    /**
     * Get currency description.
     *
     * @param int $required VALUE_* constant.
     * @return external_description
     */
    public static function currency_description($required = VALUE_REQUIRED) {
        return new external_single_structure([
            'sign' => new external_value(PARAM_RAW),
            'signurl' => new external_value(PARAM_URL, '', VALUE_OPTIONAL),
            'usesignassuperscript' => new external_value(PARAM_BOOL),
        ], '', $required);
    }

    /**
     * Format string.
     *
     * Backwards compatibility method, older Moodles will not observe external_settings.
     *
     * @param string $text The text.
     * @param context $context The context.
     * @return string
     */
    public static function format_string($text, context $context) {
        return external_utils::format_string($text, $context);
    }

    /**
     * Flatten associative array.
     *
     * @param array $array The array.
     * @return array With keys name and value.
     */
    public static function flatten_associative_array(array $array) {
        return array_reduce(array_keys($array), function($carry, $key) use ($array) {
            $carry[] = [
                'name' => $key,
                'value' => $array[$key],
            ];
        }, []);
    }

    /**
     * Get level description.
     *
     * @param int $required VALUE_* constant.
     * @return external_description
     */
    public static function level_description($required = VALUE_REQUIRED) {
        return new external_single_structure([
            'level' => new external_value(PARAM_INT),
            'xprequired' => new external_value(PARAM_INT),
            'badgeurl' => new external_value(PARAM_URL),
            'description' => new external_value(PARAM_NOTAGS),
            'name' => new external_value(PARAM_NOTAGS),
        ], '', $required);
    }

    /**
     * Get levels description.
     *
     * @param int $required VALUE_* constant.
     * @return external_description
     */
    public static function levels_info_description($required = VALUE_REQUIRED) {
        return new external_single_structure([
            'count' => new external_value(PARAM_INT),
            'levels' => new external_multiple_structure(static::level_description()),
        ], '', $required);
    }

    /**
     * Make block config.
     *
     * @param block_base|null $bi The block instance.
     * @return config
     */
    public static function make_block_config(block_base $bi = null) {
        // TODO All of this method would be better in block_xp.
        $adminconfig = di::get('config');

        // Wrap a config around the admin to only authorise a precise set of keys,
        // and map the names to the names in the block.
        $adminblockconfig = new filtered_config(
            new mapped_config($adminconfig, [
                'title' => 'blocktitle',
                'description' => 'blockdescription',
                'recentactivity' => 'blockrecentactivity',
            ]),
            ['title', 'description', 'recentactivity']
        );

        if (!empty($bi)) {
            // The block has config, so we create a stack of the block first, and then the admin.
            $blockconfig = new config_stack([new block_config($bi), $adminblockconfig]);
        } else {
            // The block does not have config, we return the admin.
            $blockconfig = $adminblockconfig;
        }

        return $blockconfig;
    }

    /**
     * Require group access.
     *
     * @param int $userid The user ID.
     * @param int $courseid The course ID.
     * @param int $groupid The group ID.
     * @return void
     */
    public static function require_group_access($userid, $courseid, $groupid) {
        $groupmode = groups_get_course_groupmode(get_course($courseid, false));
        if ($groupmode == SEPARATEGROUPS) {
            $context = context_course::instance($courseid);
            $aag = has_capability('moodle/site:accessallgroups', $context, $userid);
            if (!$aag && (!$groupid || !groups_is_member($groupid, $userid))) {
                throw new moodle_exception('nopermissions', '', '', 'access_group');
            }
        }
    }

    /**
     * Serialise activity.
     *
     * @param activity $activity The activity.
     * @return array
     */
    public static function serialize_activity(activity $activity) {
        return [
            'date' => $activity->get_date()->getTimestamp(),
            'desc' => $activity->get_description(),
            'xp' => $activity instanceof activity_with_xp ? $activity->get_xp() : null,
        ];
    }

    /**
     * Serialise currency.
     *
     * @param currency $currency The currency.
     * @return array
     */
    public static function serialize_currency(currency $currency) {
        return [
            'sign' => $currency->get_sign(),
            'signurl' => static::serialize_url($currency->get_sign_url()),
            'usesignassuperscript' => $currency->use_sign_as_superscript(),
        ];
    }

    /**
     * Serialise level.
     *
     * @param level $level The level.
     * @return array
     */
    public static function serialize_level(level $level) {
        return [
            'level' => $level->get_level(),
            'xprequired' => $level->get_xp_required(),
            'badgeurl' => $level instanceof level_with_badge ? static::serialize_url($level->get_badge_url()) : null,
            'description' => $level instanceof level_with_description ? $level->get_description() : null,
            'name' => $level instanceof level_with_name ? $level->get_name() : null,
        ];
    }

    /**
     * Serialise level.
     *
     * @param levels_info $info The levels info.
     * @return array
     */
    public static function serialize_levels_info(levels_info $info) {
        return [
            'count' => $info->get_count(),
            'levels' => array_values(array_map('local_xp\external::serialize_level', $info->get_levels())),
        ];
    }

    /**
     * Serialise state.
     *
     * @param state $state The state.
     * @param bool $withuser True when the user info has to be added.
     * @param bool $anonymously No longer used.
     * @param int[] $myids The user IDs.
     * @return array
     */
    public static function serialize_state(state $state, $withuser = false, $anonymously = false, array $myids = []) {
        global $PAGE;

        $data = [
            'id' => $state->get_id(),
            'level' => static::serialize_level($state->get_level()),
            'ratioinlevel' => $state->get_ratio_in_level(),
            'totalxpinlevel' => $state->get_total_xp_in_level(),
            'xp' => $state->get_xp(),
            'xpinlevel' => $state->get_xp_in_level(),
        ];

        if ($state instanceof state_with_subject) {
            $data['subject'] = [
                'id' => $state->get_id(),
                'name' => $state->get_name(),
                'imageurl' => static::serialize_url($state->get_picture()),
                'ismine' => in_array($state->get_id(), $myids),
            ];
        }

        // This is the legacy support for users, other types should use subjects. This is so that
        // older versions of the Mobile app may still work with the key 'user'. However, as the latest
        // user_state now implements state_with_subject, and that anonymity is built-in the leaderboard
        // factory, we basically just defer to calling the state_with_subject methods. We throw in the
        // anonymised state if a user was requested, because anonymous users are not necessarily user_state.
        if ($withuser) {
            if ($state instanceof user_state || $state instanceof anonymised_state) {
                $data['user'] = [
                    'id' => $state->get_id(),
                    'fullname' => $state->get_name(),
                    'profileimageurl' => static::serialize_url($state->get_picture()),
                    'isme' => in_array($state->get_id(), $myids),
                ];
            }
        }

        // Keep this as legacy support for groups, other types should use subjects. This is so that older
        // versions of the Mobile app may still work with the key 'group'. However, as the latest levelless_group_state
        // now implements 'state_with_subject', we basically call its subjet methods.
        if ($state instanceof levelless_group_state) {
            $data['group'] = [
                'id' => $state->get_id(),
                'name' => $state->get_name(),
                'imageurl' => static::serialize_url($state->get_picture()),
                'ismine' => in_array($state->get_id(), $myids),
            ];
        }

        return $data;
    }

    /**
     * Serialise URL.
     *
     * Replicate output of moodle_url::make_webservice_pluginfile_url().
     *
     * @param moodle_url|null $url The URL.
     * @return array
     */
    public static function serialize_url(moodle_url $url = null) {
        global $CFG;
        $baseurl = (!empty($CFG->httpswwwroot) ? $CFG->httpswwwroot : $CFG->wwwroot) . '/webservice';
        $url = is_object($url) ? (string) $url : null;

        if (is_string($url) && strpos($url, '/pluginfile.php') > 0 && strpos($url, '/webservice/pluginfile.php') === false) {
            $url = $baseurl . substr($url, strpos($url, '/pluginfile.php'));
        }

        return $url;
    }

    /**
     * State description.
     *
     * @param mixed $withuser When not false, VALUE_* constant.
     * @param mixed $withgroup When not false, VALUE_* constant.
     * @param mixed $withsubject When not false, VALUE_* constant.
     * @return external_single_structure
     */
    public static function state_description($withuser = false, $withgroup = false, $withsubject = false) {
        $params = [
            'id' => new external_value(PARAM_INT),
            'level' => static::level_description(),
            'ratioinlevel' => new external_value(PARAM_FLOAT),
            'totalxpinlevel' => new external_value(PARAM_INT),
            'xp' => new external_value(PARAM_INT),
            'xpinlevel' => new external_value(PARAM_INT),
        ];

        if ($withuser !== false) {
            $params['user'] = new external_single_structure([
                'id' => new external_value(PARAM_INT),
                'fullname' => new external_value(PARAM_RAW),
                'profileimageurl' => new external_value(PARAM_URL),
            ]);
        }

        if ($withgroup !== false) {
            $params['group'] = new external_single_structure([
                'id' => new external_value(PARAM_INT),
                'name' => new external_value(PARAM_RAW),
                'imageurl' => new external_value(PARAM_URL),
                'ismine' => new external_value(PARAM_BOOL),
            ]);
        }

        if ($withsubject !== false) {
            $params['group'] = new external_single_structure([
                'id' => new external_value(PARAM_INT),
                'name' => new external_value(PARAM_RAW),
                'imageurl' => new external_value(PARAM_URL),
                'ismine' => new external_value(PARAM_BOOL),
            ]);
        }

        return new external_single_structure($params, '', $withuser);
    }

}
