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
 * Theme updater.
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
 * Theme updater.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_updater {   // No interface for now.

    /** @var moodle_database The moodle database. */
    protected $db;
    /** @var DirectoryIterator Ready to walk through the themes. */
    protected $dir;
    /** @var string The table to store them in. */
    protected $table = 'local_xp_theme';

    /**
     * Constructor.
     *
     * @param moodle_database $db The database.
     * @param \DirectoryIterator $dir The theme directory iterator.
     */
    public function __construct(moodle_database $db, \DirectoryIterator $dir) {
        $this->db = $db;
        $this->dir = $dir;
    }

    /**
     * Update the themes.
     *
     * @return void
     */
    public function update_themes() {
        foreach ($this->dir as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot() || !$fileinfo->isReadable()) {
                continue;
            }

            $themecode = $fileinfo->getFilename();
            $themefile = $fileinfo->getPathname() . '/theme.json';
            if (strlen($themecode) > 32 || $themecode !== clean_param($themecode, PARAM_SAFEDIR) || !is_readable($themefile)) {
                continue;
            }
            $infos = json_decode(file_get_contents($themefile));
            if (!$infos) {
                continue;
            }
            if (empty($infos->name) || empty($infos->count)) {
                continue;
            }

            $this->update_theme($themecode, $infos);
            $seen[] = $themecode;
        }

        // Delete the extra ones.
        if (!empty($seen)) {
            list($insql, $inparams) = $this->db->get_in_or_equal($seen, SQL_PARAMS_QM, 'param', false);
            $this->db->delete_records_select($this->table, "code $insql", $inparams);
        } else {
            $this->db->delete_records($this->table);
        }
    }

    /**
     * Update theme.
     *
     * @param string $themecode The theme code.
     * @param stdClass $infos The theme infos.
     * @return void
     */
    protected function update_theme($themecode, $infos) {
        $record = $this->db->get_record($this->table, ['code' => $themecode]);
        if (!$record) {
            $record = (object) ['code' => $themecode];
        }

        $haschanged = $this->update_record($record, $infos);
        if (!$haschanged) {
            return;
        }

        $record->timemodified = time();
        if (empty($record->id)) {
            $this->db->insert_record($this->table, $record);
        } else {
            $this->db->update_record($this->table, $record);
        }
    }

    /**
     * Update the record.
     *
     * @param stdClass $record The record.
     * @param stdClass $infos The infos.
     * @return bool True when it was updated.
     */
    protected function update_record($record, $infos) {
        $haschanged = false;

        if (empty($record->name) || $infos->name != $record->name) {
            $record->name = $infos->name;
            $haschanged = true;
        }
        if (empty($record->levels) || $infos->count != $record->levels) {
            $record->levels = $infos->count;
            $haschanged = true;
        }

        return $haschanged;
    }

}
