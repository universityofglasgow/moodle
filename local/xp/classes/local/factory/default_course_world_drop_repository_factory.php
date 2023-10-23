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
 * Drop repository factory.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\factory;
defined('MOODLE_INTERNAL') || die();

use local_xp\local\drop\course_drop_repository;
use moodle_database;

/**
 * Drop repository factory.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class default_course_world_drop_repository_factory implements course_world_drop_repository_factory {

    /** @var moodle_database $db The DB. */
    protected $db;
    /** @var array $repositories Repositories local cache. */
    protected $repositories = [];

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     */
    public function __construct(moodle_database $db) {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function get_course_world_drop_repository($world) {
        $courseid = $world->get_courseid();
        if (!isset($this->worlds[$courseid])) {
            $this->repositories[$courseid] = new course_drop_repository($this->db, $courseid);
        }

        return $this->repositories[$courseid];
    }
}