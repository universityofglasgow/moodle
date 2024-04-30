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

use block_base;
use context;
use block_xp\external\external_description;
use block_xp\external\external_function_parameters;
use block_xp\external\external_single_structure;
use block_xp\external\external_value;
use moodle_url;
use block_xp\local\activity\activity;
use block_xp\local\xp\level;
use block_xp\local\xp\levels_info;
use block_xp\local\xp\state;
use local_xp\local\currency\currency;

/**
 * Local external class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends \block_xp\external\external_api {

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_course_group_info_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function get_course_group_info($courseid) {
        throw new \coding_exception('Method deprecated, use local_xp\external\get_course_group_info instead.');
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_course_group_info_returns() {
        return new external_value(PARAM_BOOL);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_course_world_ladder_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @param int $groupid The group ID.
     * @param int $page The page.
     * @param int $perpage The number of items per page.
     * @return object
     */
    public static function get_course_world_ladder($courseid, $groupid, $page, $perpage) {
        throw new \coding_exception('Method deprecated, use local_xp\external\get_course_world_ladder instead.');
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_course_world_ladder_returns() {
        return new external_value(PARAM_BOOL);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_course_world_group_ladder_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @param int $page The page.
     * @param int $perpage The number of items per page.
     * @return object
     */
    public static function get_course_world_group_ladder($courseid, $page, $perpage) {
        throw new \coding_exception('Method deprecated, use local_xp\external\get_course_world_group_ladder instead.');
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_course_world_group_ladder_returns() {
        return new external_value(PARAM_BOOL);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_levels_info_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function get_levels_info($courseid) {
        throw new \coding_exception('Method deprecated, use local_xp\external\get_levels_info instead.');
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_levels_info_returns() {
        return new external_value(PARAM_BOOL);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_recent_activity_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function get_recent_activity($courseid) {
        throw new \coding_exception('Method deprecated, use local_xp\external\get_recent_activity instead.');
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_recent_activity_returns() {
        return new external_value(PARAM_BOOL);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_setup_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @return object
     */
    public static function get_setup($courseid) {
        throw new \coding_exception('Method deprecated, use local_xp\external\get_setup instead.');
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_setup_returns() {
        return new external_value(PARAM_BOOL);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_user_state_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * External function.
     *
     * @param int $courseid The course ID.
     * @param int $userid The user ID.
     * @return object
     */
    public static function get_user_state($courseid, $userid) {
        throw new \coding_exception('Method deprecated, use local_xp\external\get_user_state instead.');
    }

    /**
     * External function return definition.
     *
     * @return external_description
     */
    public static function get_user_state_returns() {
        return new external_value(PARAM_BOOL);
    }

    /**
     * External function parameters.
     *
     * @return external_function_parameters
     */
    public static function search_grade_items_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Search grade items.
     *
     * @param int $courseid The course ID.
     * @param string $query The query.
     * @return array
     */
    public static function search_grade_items($courseid, $query) {
        throw new \coding_exception('Method deprecated, use local_xp\external\search_grade_items instead.');
    }

    /**
     * External function return values.
     *
     * @return external_value
     */
    public static function search_grade_items_returns() {
        return new external_value(PARAM_BOOL);
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
        return external\search_grade_items::grade_item_tree_reducer($treeitem, $query, $categories);
    }

    /**
     * Activity description.
     *
     * @param int $required VALUE_* constant.
     * @return external_description
     */
    public static function activity_description($required = VALUE_REQUIRED) {
        debugging('Method deprecated, use local_xp\external_api::activity_description instead.', DEBUG_DEVELOPER);
        return external\external_api::activity_description($required);
    }

    /**
     * Get currency description.
     *
     * @param int $required VALUE_* constant.
     * @return external_description
     */
    public static function currency_description($required = VALUE_REQUIRED) {
        debugging('Method deprecated, use local_xp\external_api::currency_description instead.', DEBUG_DEVELOPER);
        return external\external_api::currency_description($required);
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
        debugging('Method deprecated, use local_xp\external_api::format_string instead.', DEBUG_DEVELOPER);
        return external\external_api::format_string($text, $context);
    }

    /**
     * Flatten associative array.
     *
     * @param array $array The array.
     * @return array With keys name and value.
     */
    public static function flatten_associative_array(array $array) {
        debugging('Method deprecated, use local_xp\external_api::flatten_associative_array instead.', DEBUG_DEVELOPER);
        return external\external_api::flatten_associative_array($array);
    }

    /**
     * Get level description.
     *
     * @param int $required VALUE_* constant.
     * @return external_description
     */
    public static function level_description($required = VALUE_REQUIRED) {
        debugging('Method deprecated, use local_xp\external_api::level_description instead.', DEBUG_DEVELOPER);
        return external\external_api::level_description($required);
    }

    /**
     * Get levels description.
     *
     * @param int $required VALUE_* constant.
     * @return external_description
     */
    public static function levels_info_description($required = VALUE_REQUIRED) {
        debugging('Method deprecated, use local_xp\external_api::levels_info_description instead.', DEBUG_DEVELOPER);
        return external\external_api::levels_info_description($required);
    }

    /**
     * Make block config.
     *
     * @param block_base|null $bi The block instance.
     * @return config
     */
    public static function make_block_config(block_base $bi = null) {
        debugging('Method deprecated, use local_xp\external_api::make_block_config instead.', DEBUG_DEVELOPER);
        return external\external_api::make_block_config($bi);
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
        debugging('Method deprecated, use local_xp\external_api::require_group_access instead.', DEBUG_DEVELOPER);
        return external\external_api::require_group_access($userid, $courseid, $groupid);
    }

    /**
     * Serialise activity.
     *
     * @param activity $activity The activity.
     * @return array
     */
    public static function serialize_activity(activity $activity) {
        debugging('Method deprecated, use local_xp\external_api::serialize_activity instead.', DEBUG_DEVELOPER);
        return external\external_api::serialize_activity($activity);
    }

    /**
     * Serialise currency.
     *
     * @param currency $currency The currency.
     * @return array
     */
    public static function serialize_currency(currency $currency) {
        debugging('Method deprecated, use local_xp\external_api::serialize_currency instead.', DEBUG_DEVELOPER);
        return external\external_api::serialize_currency($currency);
    }

    /**
     * Serialise level.
     *
     * @param level $level The level.
     * @return array
     */
    public static function serialize_level(level $level) {
        debugging('Method deprecated, use local_xp\external_api::serialize_level instead', DEBUG_DEVELOPER);
        return external\external_api::serialize_level($level);
    }

    /**
     * Serialise level's info.
     *
     * @param levels_info $info The info.
     * @return array
     */
    public static function serialize_levels_info(levels_info $info) {
        debugging('Method deprecated, use local_xp\external_api::serialize_levels_info instead.', DEBUG_DEVELOPER);
        return external\external_api::serialize_levels_info($info);
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
        debugging('Method deprecated, use local_xp\external_api::serialize_state instead', DEBUG_DEVELOPER);
        return external\external_api::serialize_state($state, $withuser, $anonymously, $myids);
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
        debugging('Method deprecated, use local_xp\external_api::serialize_url instead.', DEBUG_DEVELOPER);
        return external\external_api::serialize_url($url);
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
        debugging('Method deprecated, use local_xp\external_api::state_description instead.', DEBUG_DEVELOPER);
        return external\external_api::state_description($withuser, $withgroup, $withsubject);
    }

}
