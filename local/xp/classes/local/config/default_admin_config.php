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
 * Default admin config.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\config;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\config\course_world_config;
use block_xp\local\config\immutable_config;
use block_xp\local\config\static_config;

/**
 * Default admin config.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class default_admin_config extends immutable_config {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(new static_config([
            // Override block_xp's.
            'enablepromoincourses' => false,
            'keeplogs' => 90,

            // Other settings.
            'badgetheme' => '',
            'maxpointspertime' => 0,
            'timeformaxpoints' => 0,

            'enablegroupladder' => default_course_world_config::GROUP_LADDER_NONE,
            'groupidentitymode' => course_world_config::IDENTITY_ON,
            'groupladdercols' => 'xp',
            'grouporderby' => default_course_world_config::GROUP_ORDER_BY_POINTS,

            'progressbarmode' => default_course_world_config::PROGRESS_BAR_MODE_LEVEL,
        ]));
    }

}
