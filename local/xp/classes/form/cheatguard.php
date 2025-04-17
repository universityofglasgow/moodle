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

/**
 * Form.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cheatguard extends \block_xp\form\cheatguard {

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        parent::definition();
        $mform = $this->_form;
        $world = $this->_customdata['world'];
        $config = \block_xp\di::get('config');

        // Change the help text of the cheat guard.
        $mform->addHelpButton('enablecheatguard', 'enablecheatguard', 'local_xp');

        // Re-implement those to extend the units allowed.
        $mform->removeElement('maxactionspertime');
        $el = $mform->createElement('block_xp_form_itemspertime', 'maxactionspertime',
            get_string('maxactionspertime', 'block_xp'), [
                'maxunit' => DAYSECS,
                'itemlabel' => get_string('actions', 'block_xp'),
            ]
        );
        $mform->addElement($el);
        unset($el);
        $mform->addHelpButton('maxactionspertime', 'maxactionspertime', 'block_xp');

        $mform->removeElement('timebetweensameactions');
        $el = $mform->createElement('block_xp_form_duration', 'timebetweensameactions',
            get_string('timebetweensameactions', 'block_xp'), [
                'maxunit' => DAYSECS,
                'optional' => false,        // We must set this...
            ]
        );
        $mform->addElement($el);
        unset($el);
        $mform->addHelpButton('timebetweensameactions', 'timebetweensameactions', 'block_xp');

        // Local plugin specific option.
        $el = $mform->createElement('block_xp_form_itemspertime', 'maxpointspertime', get_string('maxpointspertime', 'local_xp'), [
            'itemlabel' => get_string('points', 'local_xp'),
        ]);
        $mform->addElement($el);
        unset($el);
        $mform->addHelpButton('maxpointspertime', 'maxpointspertime', 'local_xp');
        $mform->disabledIf('maxpointspertime', 'enablecheatguard', 'eq', 0);
    }

    /**
     * Get the data.
     *
     * @return stdClass
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }

        // Convert back from itemspertime.
        if (!isset($data->maxpointspertime) || !is_array($data->maxpointspertime)) {
            $data->maxpointspertime = 0;
            $data->timeformaxpoints = 0;

        } else {
            $data->timeformaxpoints = (int) $data->maxpointspertime['time'];
            $data->maxpointspertime = (int) $data->maxpointspertime['points'];
        }

        // When the cheat guard is disabled, we remove the config fields so that
        // we can keep the defaults and the data previously submitted by the user.
        if (empty($data->enablecheatguard)) {
            unset($data->timeformaxpoints);
            unset($data->maxpointspertime);
        }

        return $data;
    }

    /**
     * Set the data.
     *
     * @param array $data
     */
    public function set_data($data) {
        $data = (array) $data;

        // Convert to itemspertime.
        if (isset($data['maxpointspertime']) && isset($data['timeformaxpoints'])) {
            $data['maxpointspertime'] = [
                'points' => (int) $data['maxpointspertime'],
                'time' => (int) $data['timeformaxpoints'],
            ];
            unset($data['timeformaxpoints']);
        }

        parent::set_data($data);
    }

}
