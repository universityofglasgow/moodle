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
 * First name initial last name anonymiser.
 *
 * @package    local_xp
 * @copyright  2021 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\xp;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\utils\user_utils;
use block_xp\local\xp\anonymised_state;
use block_xp\local\xp\state;
use block_xp\local\xp\state_anonymiser;
use block_xp\local\xp\user_state;
use core_text;

/**
 * First name initial last name anonymiser.
 *
 * @package    local_xp
 * @copyright  2021 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class firstname_initial_lastname_anonymiser implements state_anonymiser {

    /** @var int[] The object IDs not to anonymise. */
    protected $exceptids;
    /** @var \moodle_url The pic URL. */
    protected $pic;

    /**
     * Constructor.
     *
     * @param int[] $exceptids The object IDS to skip.
     */
    public function __construct($exceptids = []) {
        $this->exceptids = $exceptids;
        $this->pic = user_utils::default_picture();
    }

    /**
     * Return an anonymised state.
     *
     * @return state
     */
    public function anonymise_state(state $state) {
        $keepasis = in_array($state->get_id(), $this->exceptids);
        if ($keepasis) {
            return $state;
        }

        $name = get_string('someoneelse', 'block_xp');

        if ($state instanceof user_state) {
            $user = (object) (array) $state->get_user();
            $user->lastname = core_text::strtoupper(core_text::substr($user->lastname, 0, 1)) . '.';
            $user->firstnamephonetic = '';
            $user->lastnamephonetic = '';
            $user->middlename = '';
            $user->alternatename = '';

            // We use the fullname function to prevent leaks of personal information
            // should the rules to display user names prevent the last name altogether.
            $name = fullname($user);
        }

        return new anonymised_state($state, $name, $this->pic);
    }

}
