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
 * Database drop repository.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\drop;
defined('MOODLE_INTERNAL') || die();

use moodle_database;

/**
 * Database drop repository.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class db_drop_repository implements drop_repository {

    /** @var string $TABLE The table name. */
    const TABLE = 'local_xp_drops';
    /** @var moodle_database The DB. */
    protected $db;

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
    public function get_by_secret($secret) {
        $record = $this->db->get_record(self::TABLE, [
            'secret' => $secret,
        ]);

        return $record ? $this->drop_from_record($record) : null;
    }

    /**
     * @inheritDoc
     */
    public function get_by_id($id) {
        $record = $this->db->get_record(self::TABLE, [
            'id' => $id,
        ]);
        return $record ? $this->drop_from_record($record) : null;
    }

    /**
     * Generate a static_drop from the DB record.
     *
     * @param \stdClass $record The drop record.
     * @return drop
     */
    protected function drop_from_record($record) {
        $drop = new static_drop($record->id, $record->points, $record->secret, $record->name, $record->courseid);
        $drop->set_enabled($record->enabled);
        return $drop;
    }
}