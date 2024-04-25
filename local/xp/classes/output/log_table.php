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

use block_xp\di;
use context;
use html_writer;
use stdClass;
use table_sql;
use block_xp\local\utils\user_utils;
use coding_exception;
use context_system;
use local_xp\local\team\team_membership_resolver;
use moodle_url;
use pix_icon;

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
    /** @var int Filter by user ID, falsy means not filtering. */
    protected $filterbyuserid;
    /** @var renderer_base The renderer. */
    protected $renderer;
    /** @var object The reason maker. */
    protected $reasonmaker;
    /** @var team_membership_resolver Team resolver. */
    protected $teamresolver;
    /** @var array Array of team objects indexed by user ID. */
    protected $teamscache = [];

    /**
     * Constructor.
     *
     * @param context $context The context.
     * @param int $groupid The group ID.
     * @param string $downloadformat The download format.
     */
    public function __construct(context $context, $groupid, $downloadformat = null,
            team_membership_resolver $teamresolver = null, $userid = null) {

                $userid = max(0, (int) $userid);
        parent::__construct('block_xp_log_' . $userid);

        $this->context = $context;
        $this->filterbyuserid = $userid;
        $this->renderer = \block_xp\di::get('renderer');
        $this->reasonmaker = new \local_xp\local\reason\maker_from_type_and_signature();
        $this->teamresolver = $teamresolver;

        // Downloadable things.
        $this->is_downloading($downloadformat, 'xp_log_' . $context->id);
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);

        // Define columns.
        $this->define_columns($this->get_columns());
        $this->define_headers($this->get_headers());

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
        $this->sql->fields = 'x.*, ' . user_utils::name_fields('u') . ', u.email, u.idnumber, u.username';
        $this->sql->from = $sqlfrom;
        $this->sql->where = 'contextid = :contextid';
        $this->sql->params = array_merge(['contextid' => $context->id], $sqlparams);
        if ($this->filterbyuserid) {
            $this->sql->where .= ' AND userid = :userid';
            $this->sql->params = array_merge($this->sql->params, ['userid' => $this->filterbyuserid]);
        }

        // Define various table settings.
        $this->sortable(true, 'time', SORT_DESC);
        $this->collapsible(false);
    }

    /**
     * Get the columns definition.
     *
     * @return array
     */
    protected function get_columns_definition() {
        global $CFG;
        $isdownloading = $this->is_downloading();

        // Log fields.
        $cols = [
            'time' => get_string('eventtime', 'block_xp'),
        ];

        // Name fields.
        if ($isdownloading) {
            $cols = array_merge($cols, [
                'firstname' => get_string('firstname', 'core'),
                'lastname' => get_string('lastname', 'core')
            ]);
        }
        $cols = array_merge($cols, [
            'fullname' => get_string('fullname'),
        ]);

        // Identity fields.
        if ($isdownloading) {

            // Additional identity fields.
            if (has_capability('moodle/site:viewuseridentity', $this->context)) {

                // Defining which fields are to be hidden.
                $forwholesite = di::get('config')->get('context') == CONTEXT_SYSTEM;
                $hiddenidentityfields = explode(',', $CFG->hiddenuserfields);
                if ($forwholesite && has_capability('moodle/user:viewhiddendetails', context_system::instance())) {
                    $hiddenidentityfields = [];
                } else if (!$forwholesite && has_capability('moodle/course:viewhiddenuserfields', $this->context)) {
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

            // Include the teams when downloading the logs.
            if ($this->teamresolver) {
                $cols['team'] = get_string('team', 'local_xp');
            }
        }

        // Log fields.
        $cols = array_merge($cols, [
            'points' => get_string('reward', 'block_xp'),
            'reason' => !$isdownloading ? '' : get_string('reason', 'local_xp'),
            'location' => !$isdownloading ? '' : get_string('reasonlocation', 'local_xp')
        ]);
        if ($isdownloading) {
            $cols['location_url'] = get_string('reasonlocationurl', 'local_xp');
        }

        return $cols;
    }

    /**
     * Get the columns.
     *
     * @return array
     */
    protected function get_columns() {
        return array_keys($this->get_columns_definition());
    }

    /**
     * Get the headers.
     *
     * @return array
     */
    protected function get_headers() {
        return array_values($this->get_columns_definition());
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
     * Formats the column time.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_fullname($row) {
        $fullname = parent::col_fullname($row);
        if (!$this->filterbyuserid && !$this->is_downloading()) {
            $fullname .= ' ' . $this->renderer->action_icon(
                new moodle_url($this->baseurl, ['userid' => $row->userid]),
                new pix_icon('i/search', get_string('filterbyuser', 'block_xp'))
            );
        }
        return $fullname;
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
            $name = !$this->is_downloading() ? s($name) : $name;
            if ($url && !$this->is_downloading()) {
                return html_writer::link($url, $name);
            }
            return $name;
        }
        return '';
    }

    /**
     * Reason location URL.
     *
     * @param stdClass $row The row.
     * @return string
     */
    protected function col_location_url($row) {
        $reason = $row->reason;
        if ($reason instanceof \local_xp\local\reason\reason_with_location) {
            $url = $reason->get_location_url();
            return $url ? $url->out(false) : '';
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
        if ($this->is_downloading()) {
            return $desc;
        }
        return \html_writer::tag('span', $desc);
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

        // We need a local cache because the current implementation of the team resolver does
        // does not implement any caching mechanism, which would cause a lot of repeated queries.
        // Using a local cache of the team object should not cause any overhead if/when the team
        // resolver implements its own caching of team objects.
        if (!isset($this->teamscache[$row->userid])) {
            $this->teamscache[$row->userid] = $this->teamresolver->get_teams_of_member($row->userid);
        }

        return implode(', ', array_map(function($team) {
            return $team->get_name();
        }, $this->teamscache[$row->userid]));
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
        if ($this->is_downloading()) {
            return $row->points;
        }
        return $this->renderer->xp($row->points);
    }

    /**
     * Override to rephrase.
     *
     * @return void
     */
    public function print_nothing_to_display() {
        $hasfilters = false;
        $showfilters = false;

        if ($this->can_be_reset()) {
            $hasfilters = true;
            $showfilters = true;
        }

        // Render button to allow user to reset table preferences, and the initial bars if some filters
        // are used. If none of the filters are used and there is nothing to display it just means that
        // the course is empty and thus we do not show anything but a message.
        echo $this->render_reset_button();
        if ($showfilters) {
            $this->print_initials_bar();
        }

        $message = get_string('nologsrecordedyet', 'block_xp');
        if ($hasfilters) {
            $message = get_string('nothingtodisplay', 'core');
        }

        echo \html_writer::div(
            \block_xp\di::get('renderer')->notification_without_close($message, 'info'),
            '',
            ['style' => 'margin: 1em 0']
        );
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
