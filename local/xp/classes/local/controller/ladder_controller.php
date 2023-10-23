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
 * Ladder controller.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;
defined('MOODLE_INTERNAL') || die();

/**
 * Ladder controller class.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ladder_controller extends \block_xp\local\controller\ladder_controller {

    protected function define_optional_params() {
        $params = parent::define_optional_params();
        $params[] = ['download', '', PARAM_ALPHA, false];
        return $params;
    }

    protected function pre_content() {
        $iomad = \block_xp\di::get('iomad_facade');
        if ($iomad->exists()) {
            // Redirect the user when a company isn't selected.
            $iomad->redirect_for_company_if_needed();
        }
        parent::pre_content();

        // We must send the table before the output starts.
        $table = $this->get_table();
        if ($table->is_downloading()) {
            \core\session\manager::write_close();
            $table->out(0, false);   // Page size is irrelevant when downloading.
            die();
        }
    }

    /**
     * Get the table.
     *
     * @return flexible_table
     */
    protected function get_table() {
        global $USER;

        $table = new \local_xp\output\leaderboard_table(
            $this->get_leaderboard(),
            $this->get_renderer(),
            [
                'context' => $this->world->get_context(),
                'identitymode' => $this->world->get_config()->get('identitymode'),
                'rankmode' => $this->world->get_config()->get('rankmode'),
            ],
            $USER->id
        );
        $table->show_pagesize_selector(true);
        $table->define_baseurl($this->pageurl->get_compatible_url());

        // Managers can download the table.
        $canmanage = $this->world->get_access_permissions()->can_manage();
        if ($canmanage) {
            $table->is_downloadable(true);
            $table->is_downloading($this->get_param('download'), 'xp_ladder_' . $this->world->get_courseid()
                . '_' . $this->get_groupid());
            $table->show_download_buttons_at([TABLE_P_BOTTOM]);
        }

        return $table;
    }

}
