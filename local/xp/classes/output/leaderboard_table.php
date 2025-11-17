<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

namespace local_xp\output;

use action_menu_link;
use block_xp\di;
use block_xp\local\config\course_world_config;
use block_xp\local\xp\state_with_subject;
use local_xp\local\config\default_course_world_config;

/**
 * Leaderboard table.
 *
 * @package    local_xp
 * @copyright  2021 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class leaderboard_table extends \block_xp\output\leaderboard_table {

    /** @var int A LEADERBOARD_PARTICIPATION_* constant. */
    protected $ladderparticipation;

    protected function init(): void {
        $ladderparticipation = $this->config->has('ladderparticipation') ? $this->config->get('ladderparticipation') : null;
        $this->ladderparticipation = (int) ($ladderparticipation ?? default_course_world_config::LEADERBOARD_PARTICIPATION_FORCED);
        parent::init();
    }

    protected function generate_columns_definition(): array {
        $columns = parent::generate_columns_definition();
        if ($this->ladderparticipation !== default_course_world_config::LEADERBOARD_PARTICIPATION_FORCED
                && !isset($columns['actions'])
        ) {
            $columns['actions'] = '';
        }
        return $columns;
    }

    protected function get_row_actions($row) {
        $actions = parent::get_row_actions($row);

        if ($this->ladderparticipation !== default_course_world_config::LEADERBOARD_PARTICIPATION_FORCED
                && $this->userid == $row->state->get_id()
        ) {

            $state = di::get(\local_xp\local\leaderboard\participation\service_factory::class)
                ->get_for_context($this->context)
                ->get_state($this->userid);
            if (!$state->is_state_locked_until(new \DateTimeImmutable('+2 weeks'))) {
                $actions[] = new action_menu_link(
                    $this->baseurl,
                    null,
                    get_string('leave', 'block_xp'),
                    false,
                    [
                        'data-xp-action' => 'open-form',
                        'data-form-class' => 'local_xp\form\leaderboard_participation',
                        'data-form-args__contextid' => $this->context->id,
                        'data-modal-buttons__save__label' => get_string('leave', 'block_xp'),
                        'data-modal-buttons__save__disabled' => $state->is_state_locked() ? 'true' : 'false',
                        'data-modal-large' => 'false',
                        'data-modal-title' => get_string('leaveleaderboard', 'block_xp'),
                    ]
                );
            }
        }

        return $actions;
    }

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
     * Convert a rank to keyed table data.
     *
     * @param rank $rank The rank object.
     * @return array Will be passed to {@see self::add_data_keyed}.
     */
    protected function rank_to_keyed_data($rank) {
        $row = (object) [
            'rank' => $rank->get_rank(),
            'state' => $rank->get_state(),
        ];
        $data = parent::rank_to_keyed_data($rank);
        $data['actions'] = isset($this->columns['actions']) ? $this->col_actions($row) : '';
        return $data;
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
