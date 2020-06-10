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

namespace local_xp;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/externallib.php');

use block_base;
use context;
use context_course;
use core_text;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use moodle_exception;
use moodle_url;
use block_xp\di;
use block_xp\local\activity\activity;
use block_xp\local\activity\activity_with_xp;
use block_xp\local\config\block_config;
use block_xp\local\config\course_world_config;
use block_xp\local\config\config_stack;
use block_xp\local\config\filtered_config;
use block_xp\local\config\mapped_config;
use block_xp\local\iterator\map_iterator;
use block_xp\local\sql\limit;
use block_xp\local\xp\level;
use block_xp\local\xp\level_with_badge;
use block_xp\local\xp\level_with_description;
use block_xp\local\xp\level_with_name;
use block_xp\local\xp\levels_info;
use block_xp\local\xp\state;
use block_xp\local\xp\state_rank;
use block_xp\local\xp\state_with_subject;
use block_xp\local\xp\user_state;
use local_xp\local\config\default_course_world_config;
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
class external extends external_api {

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_course_group_info_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function get_course_group_info($courseid) {
        global $USER;
        $params = self::validate_parameters(self::get_course_group_info_parameters(), compact('courseid'));
        extract($params);

        $userid = $USER->id;
        $context = context_course::instance($courseid);
        self::validate_context($context);

        $course = get_course($courseid);
        $groupmode = groups_get_course_groupmode($course);
        $aag = has_capability('moodle/site:accessallgroups', $context);

        if ($groupmode == NOGROUPS && !$aag) {
            $allowedgroups = [];
            $usergroups = [];
        } else if ($groupmode == VISIBLEGROUPS || $aag) {
            $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
            $usergroups = groups_get_all_groups($course->id, $userid, $course->defaultgroupingid);
        } else {
            $allowedgroups = groups_get_all_groups($course->id, $userid, $course->defaultgroupingid);
            $usergroups = $allowedgroups;
        }

        $canseeallparticipants = empty($allowedgroups) || $groupmode == VISIBLEGROUPS || $aag;

        return [
            'canaccessallgroups' => $aag,
            'canseeallparticipants' => $canseeallparticipants,
            'groupmode' => $groupmode,
            'groups' => array_values(array_map(function($group) use ($usergroups, $context) {
                return [
                    'id' => $group->id,
                    'name' => self::format_string($group->name, $context),
                    'ismember' => array_key_exists($group->id, $usergroups)
                ];
            }, $allowedgroups))
        ];
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_course_group_info_returns() {
        return new external_single_structure([
            'canaccessallgroups' => new external_value(PARAM_BOOL, 'Whether the user has the permission to access all groups.'),
            'canseeallparticipants' => new external_value(PARAM_BOOL, 'Whether the user can see "All participants".'),
            'groupmode' => new external_value(PARAM_INT),
            'groups' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT),
                'name' => new external_value(PARAM_RAW),
                'ismember' => new external_value(PARAM_BOOL),
            ]), 'The groups that the current user can access.')
        ]);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_course_world_ladder_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'groupid' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
            'page' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 50),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function get_course_world_ladder($courseid, $groupid, $page, $perpage) {
        global $USER;
        $params = self::validate_parameters(self::get_course_world_ladder_parameters(),
            compact('courseid', 'groupid', 'page', 'perpage'));
        extract($params);

        $worldfactory = di::get('course_world_factory');
        $world = $worldfactory->get_world($courseid);
        $config = $world->get_config();
        $courseid = $world->get_courseid(); // Ensure that we get the real course ID.
        $userid = $USER->id;
        self::validate_context($world->get_context());

        // Permission checks: can manage, or can access, and the ladder is enabled.
        $perms = $world->get_access_permissions();
        if (!$perms->can_manage()) {
            $perms->require_access();
            if (!$config->get('enableladder')) {
                throw new moodle_exception('nopermissions', '', '', 'view_ladder_page');
            }
        }

        // Check access to group.
        self::require_group_access($userid, $courseid, $groupid);
        $isgroupmember = $groupid && groups_is_member($groupid, $userid);

        // Config.
        $neighbours = $config->get('neighbours');
        $rankmode = $config->get('rankmode');
        $identitymode = $config->get('identitymode');
        $isanon = $identitymode == course_world_config::IDENTITY_OFF;

        // Leaderboard.
        $leaderboard = \block_xp\di::get('course_world_leaderboard_factory')->get_course_leaderboard($world, $groupid);

        // Determine what page to show first.
        if (!$neighbours && !$page) {
            $pos = $leaderboard->get_position($userid);
            $page = ceil(($pos + 1) / $perpage);
        }

        // Pagination.
        $limit = new limit($perpage);
        if ($neighbours > 0) {
            $page = 1;
        } else {
            $limit = new limit($perpage, ($page - 1) * $perpage);
        }

        $data = [
            'columns' => array_keys($leaderboard->get_columns()),
            'page' => $page,
            'total' => $leaderboard->get_count(),
            'ranking' => iterator_to_array(new map_iterator(
                    $leaderboard->get_ranking($limit),
                    function($rank) use ($userid, $isanon) {
                        return [
                            'rank' => $rank->get_rank(),
                            'state' => self::serialize_state(
                                $rank->get_state(),
                                true,
                                $isanon && $rank->get_state()->get_id() != $userid
                            )
                        ];
                    }
            ), false),
            'identitymode' => $identitymode,
            'rankmode' => $rankmode,
            'neighbours' => $neighbours
        ];

        return $data;
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_course_world_ladder_returns() {
        return new external_single_structure([
            'columns' => new external_multiple_structure(new external_value(PARAM_ALPHANUMEXT)),
            'page' => new external_value(PARAM_INT),
            'total' => new external_value(PARAM_INT),
            'ranking' => new external_multiple_structure(
                new external_single_structure([
                    'rank' => new external_value(PARAM_INT),
                    'state' => self::state_description(VALUE_OPTIONAL)
                ])
            ),
            'identitymode' => new external_value(PARAM_INT),
            'rankmode' => new external_value(PARAM_INT),
            'neighbours' => new external_value(PARAM_INT)
        ]);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_course_world_group_ladder_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'page' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 50),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function get_course_world_group_ladder($courseid, $page, $perpage) {
        global $USER;
        $params = self::validate_parameters(self::get_course_world_group_ladder_parameters(),
            compact('courseid', 'page', 'perpage'));
        extract($params);

        $adminconfig = di::get('config');
        $worldfactory = di::get('course_world_factory');
        $world = $worldfactory->get_world($courseid);
        $config = $world->get_config();
        $courseid = $world->get_courseid(); // Ensure that we get the real course ID.
        $userid = $USER->id;
        self::validate_context($world->get_context());

        // Permission checks: can manage, or can access.
        $perms = $world->get_access_permissions();
        if (!$perms->can_manage()) {
            $perms->require_access();
        }

        // Permission checks: The group ladder must be enabled.
        if ($config->get('enablegroupladder') == default_course_world_config::GROUP_LADDER_NONE) {
            throw new moodle_exception('nopermissions', '', '', 'view_group_ladder_page');
        }

        // Leaderboard.
        $helper = di::get('grouped_leaderboard_helper');
        $factory = di::get('course_world_grouped_leaderboard_factory');
        $leaderboard = $factory->get_course_grouped_leaderboard($world);
        $myids = $helper->get_user_group_ids($USER, $world);

        // Determine what page to show first.
        if (!$page) {
            $page = 1;
        }

        // Pagination.
        $limit = new limit($perpage, ($page - 1) * $perpage);

        $data = [
            'columns' => array_keys($leaderboard->get_columns()),
            'page' => $page,
            'total' => $leaderboard->get_count(),
            'ranking' => iterator_to_array(new map_iterator(
                    $leaderboard->get_ranking($limit),
                    function($rank) use ($userid, $myids) {
                        return [
                            'rank' => $rank->get_rank(),
                            'state' => self::serialize_state(
                                $rank->get_state(),
                                false,
                                false,
                                $myids
                            )
                        ];
                    }
            ))
        ];

        return $data;
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_course_world_group_ladder_returns() {
        return new external_single_structure([
            'columns' => new external_multiple_structure(new external_value(PARAM_ALPHANUMEXT)),
            'page' => new external_value(PARAM_INT),
            'total' => new external_value(PARAM_INT),
            'ranking' => new external_multiple_structure(
                new external_single_structure([
                    'rank' => new external_value(PARAM_INT),
                    'state' => self::state_description(false, true, true)
                ])
            ),
        ]);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_levels_info_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function get_levels_info($courseid) {
        global $USER;
        $params = self::validate_parameters(self::get_levels_info_parameters(), compact('courseid'));
        extract($params);

        $worldfactory = di::get('course_world_factory');
        $world = $worldfactory->get_world($courseid);
        $courseid = $world->get_courseid(); // Ensure that we get the real course ID.
        self::validate_context($world->get_context());

        // Permission checks: can manage, or can access, and the info page is enabled.
        $perms = $world->get_access_permissions();
        if (!$perms->can_manage()) {
            $perms->require_access();
            if (!$world->get_config()->get('enableinfos')) {
                throw new moodle_exception('nopermissions', '', '', 'view_infos_page');
            }
        }

        return self::serialize_levels_info($world->get_levels_info());
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_levels_info_returns() {
        return self::levels_info_description();
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_recent_activity_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function get_recent_activity($courseid) {
        global $USER;
        $params = self::validate_parameters(self::get_recent_activity_parameters(), compact('courseid'));
        extract($params);

        $worldfactory = di::get('course_world_factory');
        $world = $worldfactory->get_world($courseid);
        $courseid = $world->get_courseid(); // Ensure that we get the real course ID.
        self::validate_context($world->get_context());

        // Permission checks.
        $perms = $world->get_access_permissions();
        $perms->require_access();

        // We must find the block to get how many recent activity to return.
        // This is not ideal, but it is inline with what setup returns.
        $bifinder = di::get('course_world_block_any_instance_finder_in_context');

        // Find the block instance, we use the course context because the front page is a course.
        $bi = $bifinder->get_any_instance_in_context('xp', $world->get_context());
        if (!$bi) {
            return [];
        }

        $blockconfig = self::make_block_config($bi);
        $recentactivity = $blockconfig->get('recentactivity');
        if ($recentactivity < 1) {
            return [];
        }

        $repo = $world->get_user_recent_activity_repository();
        $recentactivityitems = $repo->get_user_recent_activity($USER->id, $recentactivity);

        return array_values(array_map('local_xp\external::serialize_activity', $recentactivityitems));
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_recent_activity_returns() {
        return new external_multiple_structure(self::activity_description());
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_setup_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function get_setup($courseid) {
        global $USER;

        $params = self::validate_parameters(self::get_setup_parameters(), compact('courseid'));
        extract($params);

        $adminconfig = di::get('config');
        $worldfactory = di::get('course_world_factory');
        $currencyfactory = di::get('course_currency_factory');
        $bifinder = di::get('course_world_block_any_instance_finder_in_context');

        $world = $worldfactory->get_world($courseid);
        $courseid = $world->get_courseid(); // Ensure that we get the real course ID.
        self::validate_context($world->get_context());

        // Find the block instance, we use the course context because the front page is a course.
        $bi = $bifinder->get_any_instance_in_context('xp', $world->get_context());
        $perms = $world->get_access_permissions();
        $publicdata = [
            'contextmode' => $adminconfig->get('context'),
            'perms' => [
                'canaccess' => $perms->can_access(),
                'canmanage' => $perms->can_manage(),
            ],
            'block' => [
                'visible' => false,
            ],
        ];

        if (!$perms->can_access() || isguestuser() || !$USER->id) {
            // Early bail if the user cannot see anything.
            return $publicdata;
        } else if (!$bi) {
            // We could not find the block, so let's skip it.
            return $publicdata;
        }

        $blockconfig = self::make_block_config($bi);
        $config = $world->get_config();
        $currency = $currencyfactory->get_currency($courseid);

        // Description.
        // TODO Remove hardcoded indicator and notice name.
        $indicator = \block_xp\di::get('user_notice_indicator');
        $introname = 'block_intro_' . $courseid;
        $description = '';
        if ($perms->can_manage() || !$indicator->user_has_flag($USER->id, $introname)) {
            $description = self::format_string($blockconfig->get('description'), $world->get_context());
        }

        // Phew, we're done. Note that this is NOT a recursive merge.
        return [
            'block' => [
                'title' => self::format_string($blockconfig->get('title'), $world->get_context()),
                'description' => $description,
                'recentactivity' => $blockconfig->get('recentactivity'),
                // TODO Implement better visibility check. E.g. we should look at the block_position instead, if any.
                'visible' => true,
            ],
            'config' => [
                'enableinfos' => $config->get('enableinfos'),
                'enableladder' => $config->get('enableladder'),
                'enablegroupladder' => $config->get('enablegroupladder') != default_course_world_config::GROUP_LADDER_NONE,
            ],
            'visuals' => [
                'currency' => self::serialize_currency($currency)
            ],
        ] + $publicdata;
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_setup_returns() {
        return new external_single_structure([
            'contextmode' => new external_value(PARAM_INT),
            'perms' => new external_single_structure([
                'canaccess' => new external_value(PARAM_BOOL),
                'canmanage' => new external_value(PARAM_BOOL),
            ]),
            'block' => new external_single_structure([
                'title' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
                'description' => new external_value(PARAM_TEXT, '', VALUE_OPTIONAL),
                'recentactivity' => new external_value(PARAM_INT, 0, VALUE_OPTIONAL),
                'visible' => new external_value(PARAM_BOOL),
            ]),
            'config' => new external_single_structure([
                'enableinfos' => new external_value(PARAM_BOOL),
                'enableladder' => new external_value(PARAM_BOOL),
                'enablegroupladder' => new external_value(PARAM_INT),
            ], '', VALUE_OPTIONAL),
            'visuals' => new external_single_structure([
                'currency' => self::currency_description()
            ], '', VALUE_OPTIONAL),
        ]);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_user_state_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'userid' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @param int $userid The user ID.
     * @return object
     */
    public static function get_user_state($courseid, $userid) {
        global $USER;

        $params = self::validate_parameters(self::get_user_state_parameters(), compact('courseid', 'userid'));
        extract($params);

        $userid = $userid ? $userid : $USER->id;
        $world = di::get('course_world_factory')->get_world($courseid);
        $courseid = $world->get_courseid();
        self::validate_context($world->get_context());

        $world->get_access_permissions()->require_access();
        if ($userid != $USER->id) {
            $world->get_access_permissions()->require_manage();
        }

        $store = $world->get_store();
        $state = $store->get_state($userid);

        return self::serialize_state($state);
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_user_state_returns() {
        return self::state_description();
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function search_grade_items_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
            'query' => new external_value(PARAM_RAW),
        ]);
    }

    /**
     * Search grade items.
     *
     * @param int $courseid The course ID.
     * @param string $query The query.
     * @return array
     */
    public static function search_grade_items($courseid, $query) {
        $params = self::validate_parameters(self::search_grade_items_parameters(), compact('courseid', 'query'));
        $courseid = $params['courseid'];
        $query = core_text::strtolower(trim($params['query']));

        $world = di::get('course_world_factory')->get_world($courseid);
        self::validate_context($world->get_context());
        $world->get_access_permissions()->require_manage();

        // Iterate over the tree, reduce the items, fitler out the null values,
        // then reduce a in flattened arary.
        $tree = \grade_category::fetch_course_tree($courseid, true);
        $data = array_reduce(array_filter(array_map(function($treeitem) use ($query) {
            return static::grade_item_tree_reducer($treeitem, $query);
        }, $tree['children'])), function($carry, $items) {
            return array_merge($carry, !is_array($items) ? [$items] : $items);
        }, []);

        if (empty($data)) {
            return [];
        }

        $firstresult = array_shift($data);
        if ($firstresult->type == 'course') {
            $data[] = $firstresult;
        }
        return $data;
    }

    /**
     * External function return values.
     *
     * @return external_value
     */
    public static function search_grade_items_returns() {
        $item = new external_single_structure([
            'id' => new external_value(PARAM_INT, 'The grade item id'),
            'type' => new external_value(PARAM_ALPHANUMEXT, 'The grade item type, see grade_item::$itemtype.'),
            'name' => new external_value(PARAM_RAW, 'The name of this item.'),
            'module' => new external_value(PARAM_ALPHANUMEXT, 'The module of this item, if any.'),
            'min' => new external_value(PARAM_FLOAT, 'The min value of this item.'),
            'max' => new external_value(PARAM_FLOAT, 'The max value of this item.'),
            'categories' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The grade category ID'),
                'name' => new external_value(PARAM_RAW, 'The grade category name'),
            ]), 'The list of parent categories ordered by depth ascending.')
        ]);
        return new external_multiple_structure($item);
    }

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
                'name' => $item->fullname
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
        if (function_exists('external_format_string')) {
            return external_format_string($text, $context->id);
        }
        return format_string($text, true, [
            'context' => $context,
        ]);
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
                'value' => $array[$key]
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
            'levels' => new external_multiple_structure(self::level_description()),
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
            'signurl' => self::serialize_url($currency->get_sign_url()),
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
            'badgeurl' => $level instanceof level_with_badge ? self::serialize_url($level->get_badge_url()) : null,
            'description' => $level instanceof level_with_description ? $level->get_description() : null,
            'name' => $level instanceof level_with_name ? $level->get_name() : null
        ];
    }

    /**
     * Serialise level.
     *
     * @param level $level The level.
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
     * @return array
     */
    public static function serialize_state(state $state, $withuser = false, $anonymously = false, array $myids = []) {
        global $PAGE;

        $data = [
            'id' => $state->get_id(),
            'level' => self::serialize_level($state->get_level()),
            'ratioinlevel' => $state->get_ratio_in_level(),
            'totalxpinlevel' => $state->get_total_xp_in_level(),
            'xp' => $state->get_xp(),
            'xpinlevel' => $state->get_xp_in_level(),
        ];

        if ($withuser && $state instanceof user_state) {
            $user = $state->get_user();
            if ($anonymously !== false) {
                $data['id'] = 0;
                $userid = 0;
                $fullname = get_string('someoneelse', 'block_xp');
            } else {
                $userid = $user->id;
                $fullname = fullname($user);
            }
            $userpicture = new \user_picture($user);
            $userpicture->size = 1;
            $profileimageurl = $userpicture->get_url($PAGE)->out(false);
            $data['user'] = [
                'id' => $userid,
                'fullname' => $fullname,
                'profileimageurl' => $profileimageurl,
            ];

        } else if ($state instanceof state_with_subject) {
            $data['subject'] = [
                'id' => $state->get_id(),
                'name' => $state->get_name(),
                'imageurl' => self::serialize_url($state->get_picture()),
                'ismine' => in_array($state->get_id(), $myids)
            ];

        }

        // Keep this as legacy support for groups, other types should use subjects. This is so that older
        // versions of the Mobile app may still work with the key 'group'. However, as the latest levelless_group_state
        // now implements 'state_with_subject', we basically call its subjet methods.
        if ($state instanceof levelless_group_state) {
            $group = $state->get_group();
            $data['group'] = [
                'id' => $state->get_id(),
                'name' => $state->get_name(),
                'imageurl' => self::serialize_url($state->get_picture()),
                'ismine' => in_array($state->get_id(), $myids)
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
            'level' => self::level_description(),
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
                'ismine' => new external_value(PARAM_BOOL)
            ]);
        }

        if ($withsubject !== false) {
            $params['group'] = new external_single_structure([
                'id' => new external_value(PARAM_INT),
                'name' => new external_value(PARAM_RAW),
                'imageurl' => new external_value(PARAM_URL),
                'ismine' => new external_value(PARAM_BOOL)
            ]);
        }

        return new external_single_structure($params, '', $withuser);
    }

}
