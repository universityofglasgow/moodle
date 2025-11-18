<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

namespace local_xp\local\config;

use block_xp\di;
use block_xp\local\config\config_stack;
use block_xp\local\config\table_setter_config;

/**
 * Bulk world config setter.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulk_world_config_setter extends \block_xp\local\config\bulk_world_config_setter {

    protected function create_defaults() {
        return new config_stack([
            new \local_xp\local\config\default_admin_config(),
            new \local_xp\local\config\default_course_world_config(),
            parent::create_defaults(),
        ]);
    }

    protected function create_target() {
        return new config_stack([
            new table_setter_config(di::get('db'), 'local_xp_config', 'courseid > 0', []),
            parent::create_target(),
        ]);
    }

    protected function get_excluded_keys_from_admin_defaults() {
        return array_merge(parent::get_excluded_keys_from_admin_defaults(), [
            'currencytheme', 'currencystate', 'badgetheme',
        ]);
    }

}
