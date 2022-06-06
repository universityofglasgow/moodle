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
 * Drop table.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\output;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

use moodle_url;
use pix_icon;
use stdClass;
use table_sql;
use block_xp\local\course_world;
use confirm_action;
use html_writer;

/**
 * Drop table.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class drop_table extends table_sql {

    /** @var course_world $world The world. */
    protected $world;
    /** @var renderer_base $renderer The renderer. */
    protected $renderer;

    /**
     * Constructor.
     *
     * @param course_world $world The world.
     * @param int $groupid The group ID.
     */
    public function __construct(course_world $world) {
        parent::__construct('block_xp_drops');
        $this->world = $world;
        $this->renderer = \block_xp\di::get('renderer');
        $courseid = $world->get_courseid();

        $headers = [
            'name' => get_string('dropname', 'local_xp'),
            'points' => get_string('droppoints', 'local_xp'),
            'enabled' => get_string('dropenabled', 'local_xp'),
            'actions' => '',
        ];

        $this->define_columns(array_keys($headers));
        $this->define_headers(array_values($headers));
        $this->sortable(true, 'name');
        $this->no_sorting('points');
        $this->no_sorting('enabled');
        $this->no_sorting('actions');
        $this->collapsible(false);

        // Define SQL.
        $this->sql = new stdClass();
        $this->sql->fields = 'x.*';
        $this->sql->from = '{local_xp_drops} x';
        $this->sql->where = 'x.courseid = :courseid';
        $this->sql->params = ['courseid' => $courseid];
    }

    /**
     * Column.
     *
     * @param stdClass $row The row.
     * @return string
     */
    protected function col_name($row) {
        $name = format_string($row->name);
        $url = new moodle_url($this->baseurl, ['dropid' => $row->id]);
        return html_writer::link($url, $name, [
            'data-action' => 'drop-setup',
            'data-name' => $name,
            'data-editurl' => $url->out(false),
            'data-shortcode' => "[xpdrop {$row->secret}]",
        ]);
    }

    /**
     * Column.
     *
     * @param stdClass $row The row.
     * @return string
     */
    protected function col_enabled($row) {
        return (bool) $row->enabled ? get_string('yes', 'core') : get_string('no', 'core');
    }

    /**
     * Column.
     *
     * @param stdClass $row The row.
     * @return string
     */
    protected function col_points($row) {
        return $this->renderer->xp($row->points);
    }

    /**
     * Actions link.
     * @param $row
     * @throws \coding_exception
     */
    protected function col_actions($row) {
        $actions = [];

        $url = new moodle_url($this->baseurl, ['dropid' => $row->id]);
        $actions[] = $this->renderer->action_icon($url, new pix_icon('t/edit', get_string('edit', 'core')));

        $url = new moodle_url($this->baseurl, ['deleteid' => $row->id, 'sesskey' => sesskey(), 'confirm' => 1]);
        $actions[] = $this->renderer->action_icon($url, new pix_icon('t/delete', get_string('delete', 'core')),
            new confirm_action(get_string('reallyedeletedrop', 'local_xp')));

        return implode(' ', $actions);
    }

    /**
     * Finish HTML.
     */
    public function finish_html() {
        global $PAGE;
        parent::finish_html();
        $PAGE->requires->js_call_amd('local_xp/modal-drop-setup', 'delegateClick', [".block_xp", '[data-action="drop-setup"]']);
    }

    /**
     * Override to rephrase.
     *
     * @return void
     */
    public function print_nothing_to_display() {
        $message = get_string('nothingtodisplay', 'core');

        echo \html_writer::div(
            \block_xp\di::get('renderer')->notification_without_close($message, 'info'),
            '',
            ['style' => 'margin: 1em 0']
        );
    }
}
