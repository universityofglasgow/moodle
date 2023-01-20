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
 * Addon.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\plugin;

defined('MOODLE_INTERNAL') || die();

/**
 * Addon class.
 *
 * Deferred activation of the plugin is supported, but there are some caveats to
 * this feature which should be understand. Firstly, this class does not define
 * whether the addon is activated or not. The block is applying the logic that
 * will load local_xp\local\container or not. If the local_xp container is not
 * loaded, then this addon class will never be used.
 *
 * When our container is loaded, then this class will be used and basic checks
 * such as is_activated will return a positive value. Again, this class does not
 * activate the plugin, this class gets activated when the plugin is activated.
 *
 * Moreover, while we can use the container to decide whether or not local_xp
 * is "active" we cannot prevent Moodle from running the plugin's installation,
 * detecting its files (through hooks) etc. nor should we! As the plugin activation
 * can happen at a random time, we should let as much as possible of the normal
 * installation flow take place.
 *
 * However, some systems (shortcodes, mobile, tasks, event observers, ...) bypass
 * the container and thus can be triggered even if the plugin is not "activated".
 * In those cases, local_xp must on its own check whether it has been activated
 * by checking the di::get('addon')->is_activated() response. When it has not been
 * activated, it should either gracefully quit, or trigger an exception.
 *
 * For example, when the plugin is triggered during an event processing, or
 * in a shortcode, we should not abruptly crash if the plugin is not activated
 * and therefore should gracefully handle the process. However, for webservices
 * or the mobile app, we could more agressively trigger an exception as the
 * feature is not meant to be used.
 *
 * Note that when the plugin is not activated, a call to a locally defined
 * dependency (only defined in local_xp\local\container) would throw an exception
 * as the fallback from block_xp would be unaware of said object. One more reason
 * to ensure that the logic bypassing DI is handled.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class addon extends \block_xp\local\plugin\addon {

    /**
     * Whether the plugin is activated.
     *
     * @return bool
     */
    public function is_activated() {
        return true;
    }

    /**
     * Whether the plugin is installed and upgraded.
     *
     * @return bool
     */
    public function is_installed_and_upgraded() {
        return $this->get_plugin_info()->is_installed_and_upgraded();
    }

    /**
     * Whether the plugin is out of sync.
     *
     * @return bool
     */
    public function is_out_of_sync() {
        $pluginman = \core_plugin_manager::instance();
        $blockxp = $pluginman->get_plugin_info('block_xp');
        $localxp = static::get_plugin_info();

        if (!$localxp || !$localxp->is_installed_and_upgraded()) {
            return false;
        } else if (!$blockxp || !$blockxp->is_installed_and_upgraded()) {
            return false;
        }

        // Versions should have the same date.
        $blockxpversion = floor($blockxp->versiondb / 100);
        $localxpversion = floor($localxp->versiondb / 100);
        return $blockxpversion > $localxpversion;
    }

}