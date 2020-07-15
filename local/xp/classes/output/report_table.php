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

use coding_exception;
use moodle_database;
use moodle_url;
use pix_icon;
use renderer_base;
use block_xp\local\course_world;
use block_xp\local\xp\course_state_store;

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

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param course_world $world The world.
     * @param renderer_base $renderer The renderer.
     * @param course_state_store $store The store.
     * @param int $groupid The group ID.
     */
    public function __construct(
            moodle_database $db,
            course_world $world,
            renderer_base $renderer,
            course_state_store $store,
            $groupid,
            $downloadformat = null
        ) {

        $this->downloadformat = $downloadformat;
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
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);
    }


    /**
     * Get the columns.
     *
     * @return array
     */
    protected function get_columns() {
        if ($this->is_downloading()) {
            return [
                'fullname',
                'lvl',
                'xp',
                'progress',
            ];
        }
        return parent::get_columns();
    }

    /**
     * Get the headers.
     *
     * @return void
     */
    protected function get_headers() {
        if ($this->is_downloading()) {
            return [
                get_string('fullname'),
                get_string('level', 'block_xp'),
                get_string('total', 'block_xp'),
                get_string('progress', 'block_xp'),
            ];
        }
        return parent::get_headers();
    }

    /**
     * Formats the column actions.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_actions($row) {
        $parent = parent::col_actions($row);
        $actions = [];

        $url = new moodle_url($this->baseurl, ['action' => 'add', 'userid' => $row->id]);
        $actions[] = $this->renderer->action_icon($url, new pix_icon('t/add', get_string('add')));

        return implode(' ', $actions) . ' ' . $parent;
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
