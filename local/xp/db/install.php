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
 * Local XP install.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Local XP install function.
 *
 * @return true
 */
function xmldb_local_xp_install() {

    // Force update of block_xp, because it won't update automatically.
    \core\task\manager::reset_scheduled_tasks_for_component('block_xp');

    // We unset the length for which logs are kept, to force the admin to set it again.
    // The local plugin needs logs to be kept for a longer time.
    unset_config('keeplogs', 'block_xp');

    // Force the themes upgrade.
    try {
        $themeupdater = \block_xp\di::get('theme_updater');
        $themeupdater->update_themes();
    } catch (Exception $e) {
        debugging('Exception caught during call to local_xp::theme_updater.');
    }

}
