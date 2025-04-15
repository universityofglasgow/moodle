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

use block_xp\di;
use block_xp\local\division\division;
use DateTimeImmutable;
use local_xp\local\config\default_course_world_config;

/**
 * Ladder controller class.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ladder_controller extends \block_xp\local\controller\ladder_controller {

    /** @var bool */
    protected $isdefaultiso = true;

    protected function define_optional_params() {
        $params = parent::define_optional_params();
        $params[] = ['download', '', PARAM_ALPHA, false];
        $params[] = ['downloadfilename', '', PARAM_NOTAGS, false];
        return $params;
    }

    protected function post_login() {
        parent::post_login();

        $ladderiso = (int) $this->world->get_config()->get('ladderiso');
        $this->isdefaultiso = $ladderiso === default_course_world_config::LEADERBOARD_ISO_DEFAULT;
    }

    protected function is_supporting_groups() {
        return $this->isdefaultiso;
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

    protected function get_division(): ?division {
        return $this->isdefaultiso ? parent::get_division() : null;
    }

    protected function get_download_filename(): string {
        $filename = $this->get_param('downloadfilename');
        if ($filename) {
            return $filename;
        }

        $division = $this->get_division();
        return 'xp-leaderboard-' . $this->world->get_context()->id . ($division ? ('-' . $division->get_id()) : '');
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
                'config' => $this->world->get_config(),
            ],
            $USER->id
        );
        $table->show_pagesize_selector(true);
        $table->define_baseurl($this->pageurl->get_compatible_url());

        // Managers can download the table.
        $canmanage = $this->world->get_access_permissions()->can_manage();
        if ($canmanage) {
            $table->is_downloadable(true);
            $table->is_downloading($this->get_param('download'), $this->get_download_filename());
            $table->show_download_buttons_at([]);
        }

        return $table;
    }

    protected function print_group_menu() {
        if (!$this->is_supporting_groups()) {
            return;
        }
        parent::print_group_menu();
    }

    /**
     * Get the menu items.
     *
     * @return array
     */
    protected function get_page_menu_items() {
        $items = parent::get_page_menu_items();
        return array_merge($items, [
            [
                'label' => get_string('export', 'block_xp'),
                'data-xp-action' => 'open-form',
                'data-form-class' => 'local_xp\\form\\table_download',
                'data-form-args__contextid' => $this->world->get_context()->id,
                'data-form-args__filename' => $this->get_download_filename(),
                'data-form-args__pageurl' => $this->pageurl->out_as_local_url(false),
                'data-modal-buttons__save__label' => get_string('export', 'block_xp'),
                'href' => '#',
            ],
        ]);
    }

    protected function page_ranking() {
        global $USER;

        $output = $this->get_renderer();
        $canmanage = $this->world->get_access_permissions()->can_manage();
        $state = di::get(\local_xp\local\leaderboard\participation\service_factory::class)
            ->get_for_context($this->world->get_context())
            ->get_state($USER->id);

        if (!$state->is_participating()) {
            if (!$canmanage) {
                $canjoin = !$state->is_state_locked();
                $lockeduntil = $state->get_state_locked_until();
                $showjoin = $canjoin || ($lockeduntil && $lockeduntil < (new DateTimeImmutable("+2 weeks")));
                $dateformat = get_string('strftimedayshort', 'core_langconfig');
                echo $output->render_from_template('local_xp/leaderboard-not-participating', [
                    'contextid' => $this->world->get_context()->id,
                    'showjoin' => $showjoin,
                    'canjoin' => $canjoin,
                    'joindateformatted' => $lockeduntil ? userdate($lockeduntil->getTimestamp(), $dateformat) : '',
                ]);
                return;
            }
            // Managers who opt-out are presently not being informated that they have left the leaderboard.
        }

        parent::page_ranking();
    }

}
