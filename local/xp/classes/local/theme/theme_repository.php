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
 * Theme repository.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\theme;
defined('MOODLE_INTERNAL') || die();

use moodle_database;

/**
 * Theme repository.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_repository {   // No interface for now.

    /** @var moodle_database The moodle database. */
    protected $db;
    /** @var string The table to store them in. */
    protected $table = 'local_xp_theme';

    /**
     * Constructor.
     *
     * @param moodle_database $db The database.
     */
    public function __construct(moodle_database $db) {
        $this->db = $db;
    }

    /**
     * Get a theme.
     *
     * @param string $code The theme code.
     * @return stdClass|null
     */
    public function get_theme($code) {
        $theme = $this->db->get_record($this->table, ['code' => $code], '*', IGNORE_MISSING);
        if (!$theme) {
            return null;
        }
        return $theme;
    }

    /**
     * Get a theme.
     *
     * @param string $code The theme code.
     * @return void
     */
    public function get_themes() {
        return $this->db->get_records($this->table, null, 'name ASC');
    }

}
