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
 * Local XP report table.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\output;

defined('MOODLE_INTERNAL') || die();

use action_menu_link;
use block_xp\di;
use coding_exception;
use moodle_database;
use moodle_url;
use pix_icon;
use renderer_base;
use block_xp\local\course_world;
use block_xp\local\xp\course_state_store;
use context_system;
use local_xp\local\team\team_membership_resolver;

/**
 * Local XP report table class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_table extends \block_xp\output\report_table {

    /** @var string Download format. */
    private $downloadformat;
    /** @var team_membership_resolver Team resolver. */
    protected $teamresolver;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param course_world $world The world.
     * @param renderer_base $renderer The renderer.
     * @param course_state_store $store The store.
     * @param int $groupid The group ID.
     * @param string $downloadformat The download format.
     */
    public function __construct(
            moodle_database $db,
            course_world $world,
            renderer_base $renderer,
            course_state_store $store,
            $groupid,
            $downloadformat = null,
            team_membership_resolver $teamresolver = null
        ) {

        $this->downloadformat = $downloadformat;
        $this->teamresolver = $teamresolver;
        parent::__construct($db, $world, $renderer, $store, $groupid);
    }

    /**
     * Init function.
     *
     * @return void
     */
    protected function init() {
        $this->is_downloading($this->downloadformat, 'xp_report_' . $this->world->get_courseid());
        parent::init();
        $this->no_sorting('team');
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);
    }

    /**
     * Generate the columns definition.
     *
     * @return array
     */
    protected function generate_columns_definition() {
        global $CFG;

        $origcols = parent::generate_columns_definition();
        $cols = $origcols;

        if ($this->is_downloading()) {

            // Name fields.
            $cols = array_intersect_key($origcols, [
                'fullname' => true,
            ]);
            $cols = array_merge([
                'firstname' => get_string('firstname', 'core'),
                'lastname' => get_string('lastname', 'core')
            ], $cols);

            // Additional identity fields.
            if (has_capability('moodle/site:viewuseridentity', $this->world->get_context())) {

                // Defining which fields are to be hidden.
                $forwholesite = di::get('config')->get('context') == CONTEXT_SYSTEM;
                $hiddenidentityfields = explode(',', $CFG->hiddenuserfields);
                if ($forwholesite && has_capability('moodle/user:viewhiddendetails', context_system::instance())) {
                    $hiddenidentityfields = [];
                } else if (!$forwholesite && has_capability('moodle/course:viewhiddenuserfields', $this->world->get_context())) {
                    $hiddenidentityfields = [];
                }

                // Gathering the additional identity fields.
                $showuseridentity = explode(',', $CFG->showuseridentity);
                $identityfields = array_diff_key(array_intersect_key([
                    'username' => get_string('username', 'core'),
                    'idnumber' => get_string('idnumber', 'core'),
                    'email' => get_string('email', 'core'),
                ], array_flip($showuseridentity)), array_flip($hiddenidentityfields));
                $cols = array_merge($cols, $identityfields);
            }

            // Report fields.
            $cols = array_merge($cols, array_intersect_key($origcols, [
                'lvl' => true,
                'xp' => true,
                'progress' => true,
            ]));
        }

        if (!$this->teamresolver) {
            return $cols;
        }

        return array_reduce(array_keys($cols), function($carry, $col) use ($cols) {
            $header = $cols[$col];
            if ($col === 'lvl') {
                $carry['team'] = get_string('team', 'local_xp');
            }
            return array_merge($carry, [$col => $header]);
        }, []);
    }

    /**
     * Get the actions for row.
     *
     * @param stdClass $row Table row.
     * @return action_menu_link[] List of actions.
     */
    protected function get_row_actions($row) {
        $actions = parent::get_row_actions($row);

        $actions = array_merge([
            new action_menu_link(
                new moodle_url($this->baseurl, ['action' => 'add', 'userid' => $row->id]),
                new pix_icon('t/add', get_string('add', 'core')),
                get_string('awardpoints', 'local_xp')
            )
        ], $actions);

        return $actions;
    }

    /**
     * Formats the column level.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_lvl($row) {
        if ($this->is_downloading()) {
            return $row->state->get_level()->get_level();
        }
        return parent::col_lvl($row);
    }

    /**
     * Formats the column progress.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_progress($row) {
        if ($this->is_downloading()) {
            $state = $row->state;
            return sprintf("%d / %d", $state->get_xp_in_level(), $state->get_total_xp_in_level());
        }
        return parent::col_progress($row);
    }

    /**
     * Formats the column.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_team($row) {
        if (!$this->teamresolver) {
            return '';
        }
        return implode(', ', array_map(function($team) {
            return $team->get_name();
        }, $this->teamresolver->get_teams_of_member($row->id)));
    }

    /**
     * Formats the column total.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_xp($row) {
        if ($this->is_downloading()) {
            return $row->state->get_xp();
        }
        return parent::col_xp($row);
    }

    /**
     * Set, or get, whether the table should be downloaded.
     *
     * We override this method to ensure it is not called after the table was
     * initialised in {@link self::init()}. Setting the table to be downloaded
     * after the initialisation will cause unexpected results.
     *
     * @param string $download The download format.
     * @param string $filename The file name.
     * @param string $sheettitle The sheet name.
     * @return bool
     */
    public function is_downloading($download = null, $filename = '', $sheettitle = '') {
        if (!empty($this->columns) && $download !== null) {
            throw new coding_exception('The table was already initialised, you may not change its download state.');
        }
        return parent::is_downloading($download, $filename, $sheettitle);
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
            throw new coding_exception('What are you doing?');
        }
        \core\session\manager::write_close();
        $this->out(-1337, false);   // Page size is irrelevant when downloading.
        die();
    }
}
