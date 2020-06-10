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
 * Log table.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');

use context;
use html_writer;
use stdClass;
use table_sql;

/**
 * Log table class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class log_table extends table_sql {

    /** @var string The key of the user ID column. */
    public $useridfield = 'userid';

    /** @var context The context. */
    protected $context;
    /** @var renderer_base The renderer. */
    protected $renderer;
    /** @var object The reason maker. */
    protected $reasonmaker;

    /**
     * Constructor.
     *
     * @param context $context The context.
     * @param int $groupid The group ID.
     */
    public function __construct(context $context, $groupid) {
        parent::__construct('block_xp_log');
        $this->context = $context;
        $this->renderer = \block_xp\di::get('renderer');
        $this->reasonmaker = new \local_xp\local\reason\maker_from_type_and_signature();

        // Define columns.
        $this->define_columns(array(
            'time',
            'fullname',
            'points',
            'reason',
            'location'
        ));
        $this->define_headers(array(
            get_string('eventtime', 'block_xp'),
            get_string('fullname'),
            get_string('reward', 'block_xp'),
            '',
            ''
        ));

        // Define SQL.
        $sqlfrom = '';
        $sqlparams = array();
        if ($groupid) {
            $sqlfrom = '{local_xp_log} x
                     JOIN {groups_members} gm
                       ON gm.groupid = :groupid
                      AND gm.userid = x.userid
                LEFT JOIN {user} u
                       ON x.userid = u.id';
            $sqlparams = array('groupid' => $groupid);
        } else {
            $sqlfrom = '{local_xp_log} x
                 LEFT JOIN {user} u
                        ON x.userid = u.id';
        }

        // Define SQL.
        $this->sql = new stdClass();
        $this->sql->fields = 'x.*, ' . get_all_user_name_fields(true, 'u');
        $this->sql->from = $sqlfrom;
        $this->sql->where = 'contextid = :contextid';
        $this->sql->params = array_merge(['contextid' => $context->id], $sqlparams);

        // Define various table settings.
        $this->sortable(true, 'time', SORT_DESC);
        $this->collapsible(false);
    }

    /**
     * Format the rows.
     *
     * We hijack this method to inject the reason object.
     *
     * @param array|object $row The rows
     * @return array
     */
    public function format_row($row) {
        $row = (object) $row;
        $row->reason = $this->reasonmaker->make_from_type_and_signature($row->type, $row->signature);
        return parent::format_row($row);
    }

    /**
     * Reason location.
     *
     * @param stdClass $row The row.
     * @return string
     */
    protected function col_location($row) {
        $reason = $row->reason;
        if ($reason instanceof \local_xp\local\reason\reason_with_location) {
            $name = $reason->get_location_name();
            $url = $reason->get_location_url();
            if (!$name) {
                return '';
            }
            if ($url) {
                return html_writer::link($url, $name);
            }
            return $name;
        }
        return '';
    }

    /**
     * Reason.
     *
     * @param stdClass $row The row.
     * @return string
     */
    protected function col_reason($row) {
        $reason = $row->reason;
        if ($reason instanceof \local_xp\local\reason\reason_with_short_description) {
            $desc = $reason->get_short_description();
        } else {
            $desc = '';
        }
        return \html_writer::tag('span', $desc);
    }

    /**
     * Formats the column time.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_time($row) {
        return userdate($row->time, get_string('strftimedatetimeshort', 'langconfig'));
    }

    /**
     * XP.
     *
     * @param stdClass $row The row.
     * @return string
     */
    protected function col_points($row) {
        return $this->renderer->xp($row->points);
    }

    /**
     * Override to rephrase.
     *
     * @return void
     */
    public function print_nothing_to_display() {
        echo \html_writer::div(
            \block_xp\di::get('renderer')->notification_without_close(
                get_string('nologsrecordedyet', 'block_xp'),
                'info'
            ),
            '',
            ['style' => 'margin: 1em 0']
        );
    }
}
