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
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');

use context;
use context_course;
use renderer_base;
use flexible_table;
use block_xp\local\leaderboard\leaderboard;
use block_xp\local\sql\limit;
use block_xp\local\xp\state_with_subject;
use local_xp\local\xp\levelless_group_state;

/**
 * Leaderboard table.
 *
 * Generic group leaderboard table to display the leaderboard of an aggregate of users.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group_leaderboard_table extends flexible_table {

    /** @var limit An SQL limit to 'fence' the leaderboard. */
    protected $fence;
    /** @var leaderboard The leaderboard. */
    protected $leaderboard;
    /** @var block_xp_renderer XP Renderer. */
    protected $xpoutput = null;
    /** @var int[] The IDs to highlight. */
    protected $objectids;

    /**
     * Constructor.
     *
     * @param leaderboard $leaderboard The leaderboard.
     * @param renderer_base $renderer The renderer.
     * @param int[] $groupid The current group.
     * @param array $options Options.
     */
    public function __construct(
            leaderboard $leaderboard,
            renderer_base $renderer,
            array $objectids,
            array $options = []
        ) {

        global $CFG, $USER;
        parent::__construct('block_xp_ladder');

        // The object IDs we're viewing the ladder for.
        $this->objectids = $objectids;

        // Block XP stuff.
        $this->leaderboard = $leaderboard;
        $this->xpoutput = $renderer;

        // Options.
        $leaderboardcols = $this->leaderboard->get_columns();
        if (isset($options['discardcolumns'])) {
            $leaderboardcols = array_diff_key($leaderboardcols, array_flip($options['discardcolumns']));
        }
        if (isset($options['fence'])) {
            $this->fence = $options['fence'];
        }

        // Define columns, and headers.
        $columns = array_keys($leaderboardcols);
        $headers = array_map(function($header) {
            return (string) $header;
        }, array_values($leaderboardcols));
        $this->define_columns($columns);
        $this->define_headers($headers);

        // Define various table settings.
        $this->sortable(false);
        $this->collapsible(false);
        $this->set_attribute('class', 'block_xp-table block_xp-group-ladder');
        $this->column_class('rank', 'col-rank');
        $this->column_class('grouppic', 'col-grouppic');
    }

    /**
     * Output the table.
     */
    public function out($pagesize) {
        $this->setup();

        // Compute where to start from.
        if (empty($this->fence)) {
            $this->pagesize($pagesize, $this->leaderboard->get_count());
            $limit = new limit($pagesize, (int) $this->get_page_start());

        } else {
            $this->pagesize($this->fence->get_count(), $this->fence->get_count());
            $limit = $this->fence;
        };

        $methods = array_reduce(array_keys($this->columns), function($carry, $column) {
            if (method_exists($this, 'col_' . $column)) {
                $carry[$column] = 'col_' . $column;
            }
            return $carry;
        });

        $ranking = $this->leaderboard->get_ranking($limit);
        foreach ($ranking as $rank) {
            $row = (object) [
                'rank' => $rank->get_rank(),
                'state' => $rank->get_state()
            ];
            $classes = (in_array($rank->get_state()->get_id(), $this->objectids)) ? 'highlight-row' : '';
            $data = array_map(function($method) use ($row) {
                return $this->{$method}($row);
            }, $methods);
            $this->add_data_keyed($data, $classes);
        }
        $this->finish_output();
    }

    /**
     * Formats the column.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_name($row) {
        $o = '?';
        $state = $row->state;

        if ($state instanceof state_with_subject) {
            $o = $this->col_grouppic($row);
            $o .= $state->get_name();

        } else if ($state instanceof levelless_group_state) {
            $group = $state->get_group();
            $o = $this->col_grouppic($row);
            $o .= format_string($group->name, true, ['context' => context_course::instance($group->courseid)]);
        }

        return $o;
    }

    /**
     * Formats the column progress.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_progress($row) {
        return $this->xpoutput->progress_bar($row->state);
    }

    /**
     * Formats the column.
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_rank($row) {
        return $row->rank;
    }

    /**
     * Formats the column.
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_xp($row) {
        return $this->xpoutput->xp($row->state->get_xp());
    }

    /**
     * Formats the column.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_grouppic($row) {
        $pic = null;

        if ($row->state instanceof state_with_subject) {
            $pic = $row->state->get_picture();
            $pic = $pic ? $this->xpoutput->team_picture($pic) : null;

        } else if ($row->state instanceof levelless_group_state) {
            $pic = $this->xpoutput->group_picture($row->state->get_group());
        }

        return $pic ? $pic : '';
    }

    /**
     * Override to rephrase.
     *
     * @return void
     */
    public function print_nothing_to_display() {
        echo \html_writer::div(
            \block_xp\di::get('renderer')->notification_without_close(
                get_string('ladderempty', 'block_xp'),
                'info'
            ),
            '',
            ['style' => 'margin: 1em 0']
        );
    }
}
