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
defined('MOODLE_INTERNAL') || die();

use core_user;
use moodle_url;
use single_button;
use block_xp\di;
use block_xp\local\routing\url;

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
        return $params;
    }

    /**
     * Get the form to add points.
     *
     * @param int $userid The target ID.
     * @return moodleform
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

    protected function get_table() {
        if (!$this->table) {
            $this->table = new \local_xp\output\report_table(
                \block_xp\di::get('db'),
                $this->world,
                $this->get_renderer(),
                $this->world->get_store(),
                $this->get_groupid(),
                $this->get_param('download')
            );
            // We must use a compatible URL for the download button to work.
            $this->table->define_baseurl($this->pageurl->get_compatible_url());
        }
        return $this->table;
    }

    protected function pre_content() {
        global $USER;

        // Check for our actions.
        $userid = $this->get_param('userid');
        $action = $this->get_param('action');
        if ($action === 'add' && !empty($userid)) {
            $form = $this->get_add_form($userid);
            $nexturl = new url($this->pageurl, ['userid' => null]);
            if ($data = $form->get_data()) {
                $store = $this->world->get_store();
                $reason = new \local_xp\local\reason\manual_reason($USER->id);
                if ($store instanceof \block_xp\local\xp\state_store_with_reason) {
                    $store->increase_with_reason($userid, $data->xp, $reason);
                } else {
                    $store->increase($userid, $data->xp);
                }
                if ($data->sendnotification) {
                    $this->send_award_notification($userid, $data->xp, !empty($data->message) ? $data->message : null);
                }
                $this->redirect($nexturl);
            } else if ($form->is_cancelled()) {
                $this->redirect($nexturl);
            }
        }

        // We must send the table before the output starts.
        $table = $this->get_table();
        if ($table->is_downloading()) {
            $table->send_file();
        }

        parent::pre_content();
    }

    /**
     * Get the bottom action buttons.
     *
     * @return single_button[]
     */
    protected function get_bottom_action_buttons() {
        $actions = parent::get_bottom_action_buttons();

        $importurl = $this->urlresolver->reverse('import', ['courseid' => $this->courseid]);
        $actions[] = new single_button($importurl->get_compatible_url(), get_string('importpoints', 'local_xp'), 'get');

        return $actions;
    }

    protected function page_content() {
        $output = $this->get_renderer();

        // Add points form.
        if (!empty($this->addform)) {
            $user = core_user::get_user($this->get_param('userid'));
            echo $output->heading(fullname($user), 3);
            $this->addform->display();
        }

        return parent::page_content();
    }

    /**
     * Send award notification.
     *
     * @param int $userid The user to send to.
     * @param int $points The number of points they received.
     * @param string|null $message The message, if any.
     */
    protected function send_award_notification($userid, $points, $message) {
        global $USER;
        $notifier = new \local_xp\local\notification\award_notifier(di::get('config'), $this->world, $USER);
        $notifier->notify($userid, $points, $message);
    }
}
