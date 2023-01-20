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
 * Course collection logger factory.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\factory;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\config\config;
use block_xp\local\course_world;
use block_xp\local\logger\collection_logger;
use context_course;
use context_system;
use moodle_database;

/**
 * Course collection logger factory.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_collection_logger_factory { // No interface for now, maybe later...

    /** @var config The config. */
    protected $config;
    /** @var bool Whether we're on for the whole site mode. */
    protected $forwholesite;
    /** @var moodle_database The database. */
    protected $db;
    /** @var collection_logger[] Local cache of loggers. */
    protected $loggers;

    /**
     * Constructor.
     *
     * @param config $config The config.
     * @param moodle_database $db The DB.
     */
    public function __construct(config $config, moodle_database $db) {
        $this->config = $config;
        $this->db = $db;
        $this->forwholesite = $config->get('context') == CONTEXT_SYSTEM;
    }

    /**
     * Get the collection logger.
     *
     * @param int $courseid Course ID.
     * @return collection_logger
     */
    public function get_collection_logger($courseid) {

        // This logic is replicated from the course_world_factory.
        if ($this->forwholesite) {
            $courseid = SITEID;
        }
        $courseid = intval($courseid);

        if (!isset($this->loggers[$courseid])) {

            // This logic is copied from course_world.
            if ($courseid == SITEID) {
                $context = context_system::instance();
            } else {
                $context = context_course::instance($courseid);
            }

            // Note that at this moment, the course_world very much expects this class.
            // Swapping it for another one will raise exceptions.
            $this->loggers[$courseid] = new \local_xp\local\logger\context_collection_logger(
                $this->db,
                $context,
                new \local_xp\local\reason\maker_from_type_and_signature()
            );
        }

        return $this->loggers[$courseid];
    }

}
