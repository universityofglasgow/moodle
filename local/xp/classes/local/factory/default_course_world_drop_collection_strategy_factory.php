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

use block_xp\di;
use block_xp\local\course_world;

/**
 * The drop collection strategy factory.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class default_course_world_drop_collection_strategy_factory implements course_world_drop_collection_strategy_factory {

    /**
     * @inheritDoc
     */
    public function get_course_drop_collection_strategy(course_world $world) {
        // We need to do this because the logger is not exposed to public from a world.
        $logger = di::get('course_collection_logger_factory')->get_collection_logger($world);
        return new course_world_drop_collection_strategy($world->get_store(), $logger);
    }
}