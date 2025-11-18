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
 * Report controller.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;

use block_xp\di;
use local_xp\local\config\default_course_world_config;
use local_xp\local\userflag\deletion_service;

/**
 * Report controller class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_controller extends \block_xp\local\controller\report_controller {

    /** @var moodleform The form to add points. */
    protected $addform;

    protected function define_optional_params() {
        $params = parent::define_optional_params();
        $params[] = ['download', '', PARAM_ALPHA, false];
        $params[] = ['downloadfilename', '', PARAM_NOTAGS, false];
        return $params;
    }

    /**
     * Get the form to add points.
     *
     * @param int $userid The target ID.
     * @return moodleform
     * @deprecated Since XP+ 1.17
     */
    protected function get_add_form($userid) {
        if (!$this->addform) {
            $state = $this->world->get_store()->get_state($userid);
            $form = new \local_xp\form\user_xp_add($this->pageurl->out(false));
            $form->set_data(['userid' => $userid, 'total' => $state->get_xp()]);
            $this->addform = $form;
        }
        return $form;
    }

    protected function get_download_filename(): string {
        $groupid = $this->is_supporting_groups() ? $this->get_groupid() : null;
        $defaultfilename = 'xp-report-' . $this->world->get_context()->id;
        if ($groupid !== null) {
            $defaultfilename .= '-' . (string) (int) $groupid;
        }
        $defaultfilename .= '-' . userdate(time(), '%Y-%m-%d');
        return $this->get_param('downloadfilename') ?: $defaultfilename;
    }

    protected function get_table() {
        if (!$this->table) {
            $teamresolver = null;
            $withteams = $this->world->get_config()->get('enablegroupladder') != default_course_world_config::GROUP_LADDER_NONE;
            if ($withteams) {
                $teamresolverfactory = \block_xp\di::get('team_membership_resolver_factory');
                $teamresolver = $teamresolverfactory->get_course_team_membership_resolver($this->world);
            }
            $this->table = new \local_xp\output\report_table(
                \block_xp\di::get('db'),
                $this->world,
                $this->get_renderer(),
                $this->world->get_store(),
                $this->get_groupid(),
                [$this->get_param('download'), $this->get_download_filename()],
                $teamresolver
            );
            // We must use a compatible URL for the download button to work.
            $this->table->define_baseurl($this->pageurl->get_compatible_url());

            $filterset = $this->get_filterset();
            if ($filterset) {
                $this->table->set_filterset($filterset);
            }
        }
        return $this->table;
    }

    protected function pre_content() {
        // We must send the table before the output starts.
        $table = $this->get_table();
        if ($table->is_downloading()) {
            $table->send_file();
        }
        parent::pre_content();
    }

    protected function perform_user_deletion(int $userid): void {
        parent::perform_user_deletion($userid);

        di::get(deletion_service::class)->delete_for_user_in_context($userid, $this->world->get_context()->id);
    }

    /**
     * Get the advanced heading options.
     *
     * @return array
     */
    protected function get_advanced_heading_options() {
        $options = parent::get_advanced_heading_options();
        $options['menu'] = array_values($options['menu'] ?? []);

        $addatkey = 0;
        foreach ($options['menu'] as $key => $item) {
            if (empty($item)) {
                $addatkey = $key;
                break;
            } else if (($item['danger'] ?? false) === true) {
                $addatkey = max(0, $key - 1);
                break;
            }
        }

        $importurl = $this->urlresolver->reverse('import', ['courseid' => $this->courseid]);
        $options['menu'] = array_merge(
            array_slice($options['menu'], 0, $addatkey),
            [
                [
                    'label' => get_string('importpoints', 'block_xp'),
                    'href' => $importurl,
                ],
                [
                    'label' => get_string('exportdata', 'block_xp'),
                    'data-xp-action' => 'open-form',
                    'data-form-class' => 'local_xp\\form\\table_download',
                    'data-form-args__contextid' => $this->world->get_context()->id,
                    'data-form-args__filename' => $this->get_download_filename(),
                    'data-form-args__pageurl' => $this->pageurl->out_as_local_url(false),
                    'data-modal-buttons__save__label' => get_string('export', 'block_xp'),
                    'href' => '#',
                ],
            ],
            array_slice($options['menu'], $addatkey),
        );

        return $options;
    }

    /**
     * Send award notification.
     *
     * @param int $userid The user to send to.
     * @param int $points The number of points they received.
     * @param string|null $message The message, if any.
     * @deprecated Since XP+ 1.17
     */
    protected function send_award_notification($userid, $points, $message) {
    }

}
