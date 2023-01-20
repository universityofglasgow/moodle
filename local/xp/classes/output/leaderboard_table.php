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
 * Leaderboard table.
 *
 * @package    local_xp
 * @copyright  2021 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\output;

use block_xp\local\config\course_world_config;
use block_xp\local\xp\state_with_subject;

defined('MOODLE_INTERNAL') || die();

/**
 * Leaderboard table.
 *
 * @package    local_xp
 * @copyright  2021 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class leaderboard_table extends \block_xp\output\leaderboard_table {

    public function col_fullname($row) {
        if ($this->is_downloading()) {
            return $row->state instanceof state_with_subject ? $row->state->get_name() : '?';
        }
        return parent::col_fullname($row);
    }

    public function col_lvl($row) {
        if ($this->is_downloading()) {
            return $row->state->get_level()->get_level();
        }
        return parent::col_lvl($row);
    }

    public function col_progress($row) {
        if ($this->is_downloading()) {
            $state = $row->state;
            return sprintf("%d / %d", $state->get_xp_in_level(), $state->get_total_xp_in_level());
        }
        return parent::col_progress($row);
    }

    public function col_rank($row) {
        if ($this->is_downloading()) {
            $prefix = '';
            if ($this->rankmode == course_world_config::RANK_REL) {
                $prefix = $row->rank > 0 ? '+' : '';
            }
            return $prefix . $row->rank;
        }
        return parent::col_rank($row);
    }

    public function col_xp($row) {
        if ($this->is_downloading()) {
            return $row->state->get_xp();
        }
        return parent::col_xp($row);
    }

    /**
     * Own method to send the file.
     *
     * The out() method is kinda disgusting, so we just made this one to
     * hide the ugliness into a more descriptive method.
     *
     * @return void
     */
    public function send_file() {
        if (!$this->is_downloading()) {
            throw new \coding_exception('What are you doing?');
        }
        \core\session\manager::write_close();
        $this->out(-1337, false);   // Page size is irrelevant when downloading.
        die();
    }

}
