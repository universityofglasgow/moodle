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

namespace local_xp\form;

use block_xp\di;
use block_xp\form\dynamic_world_trait;
use block_xp\form\html;
use core_form\dynamic_form;
use DateTimeImmutable;
use local_xp\local\leaderboard\participation\service;

/**
 * Form.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class leaderboard_participation extends dynamic_form {

    use dynamic_world_trait;

    protected $routename = 'ladder';

    /** @var ?service */
    protected $service;

    #[\Override]
    protected function check_access_for_dynamic_submission(): void {
        $perms = $this->get_world()->get_access_permissions();
        $perms->require_access();
    }

    protected function definition() {
        global $USER;
        $mform = $this->_form;

        $state = $this->get_participation_service()->get_state($USER->id);
        $dt = new \DateTimeImmutable('+2 weeks');
        $message = null;

        if ($state->is_participating()) {
            $message = get_string('leaveleadeboardconfirmnote', 'block_xp');

            if ($state->is_state_locked()) {
                $lockeduntil = $state->get_state_locked_until();
                if ($state->is_state_locked_until($dt) || !$lockeduntil) {
                    $message = get_string('leaveleadeboardlockednote', 'block_xp');

                } else {
                    $dateformat = 'strftimedateshort';
                    if ($lockeduntil < new DateTimeImmutable('+1 day')) {
                        $dateformat = 'strftimerecent';
                    }
                    $message = get_string('leaveleadeboardlockeduntilnote', 'block_xp',
                        userdate($lockeduntil->getTimestamp(), get_string($dateformat, 'core_langconfig')));
                }
            }

        } else {
            $message = get_string('joinleadeboardconfirmnote', 'block_xp');

            if ($state->is_state_locked()) {
                $lockeduntil = $state->get_state_locked_until();
                if ($state->is_state_locked_until($dt) || !$lockeduntil) {
                    $message = get_string('joinleadeboardlockednote', 'block_xp');

                } else {
                    $dateformat = 'strftimedateshort';
                    if ($lockeduntil < new DateTimeImmutable('+1 day')) {
                        $dateformat = 'strftimerecent';
                    }
                    $message = get_string('canjoinfromdatex', 'block_xp',
                        userdate($lockeduntil->getTimestamp(), get_string($dateformat, 'core_langconfig')));
                }
            }
        }

        $mform->addElement(html::name(), 'message', \html_writer::div(markdown_to_html($message), 'last:[&_p]:xp-m-0'));
        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
    }

    protected function get_participation_service(): service {
        if (!$this->service) {
            $this->service = di::get(\local_xp\local\leaderboard\participation\service_factory::class)
                ->get_for_context($this->get_world()->get_context());
        }
        return $this->service;
    }

    public function set_data_for_dynamic_submission(): void {
        $this->set_data((object) [
            'contextid' => $this->optional_param('contextid', 0, PARAM_INT),
        ]);
    }

    public function process_dynamic_submission() {
        global $USER;
        if ($data = $this->get_data()) {

            $service = $this->get_participation_service();
            $state = $service->get_state($USER->id);
            if ($state->is_state_locked()) {
                throw new \moodle_exception('invaliddata', 'core_error');
            }

            if ($state->is_participating()) {
                $service->remove_participant($USER->id);
            } else {
                $service->add_participant($USER->id);
            }

            return;
        }
    }

    public function validation($data, $files) {
        $errors = [];
        return $errors;
    }

}
