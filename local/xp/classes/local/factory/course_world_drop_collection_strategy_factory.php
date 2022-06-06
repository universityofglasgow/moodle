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

namespace local_xp\local\factory;
defined('MOODLE_INTERNAL') || die();

use \block_xp\local\course_world;

/**
 * Drop strategry factory interface.
 *
 * @package    block_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface course_world_drop_collection_strategy_factory {

    /**
     * Get the collection strategy.
     *
     * @param course_world $world The world.
     * @return drop_collection_strategy
     */
    public function get_course_drop_collection_strategy(course_world $world);

}