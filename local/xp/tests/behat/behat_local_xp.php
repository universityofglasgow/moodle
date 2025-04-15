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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use block_xp\di;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Behat steps.
 *
 * @package    local_xp
 * @category   test
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_xp extends behat_base {

    /**
     * I add XP drop snippet to the field.
     *
     * @Given /^I set the field "(?P<field>(?:[^"]|\\")*)" to the XP drop snippet "(?P<drop>(?:[^"]|\\")*)"$/
     * @param $fieldname
     * @param $dropname
     */
    public function i_set_field_to_xp_drop($fieldname, $dropname) {
        global $DB;

        // Hack by using the DB to resolve by name.
        $dropid = (int) ($DB->get_field('local_xp_drops', 'id', ['name' => $dropname], MUST_EXIST) ?: 0);
        $repo = di::get('drop_repository');
        $drop = $repo->get_by_id($dropid);
        $shortcode = "[xpdrop id={$drop->get_id()} secret={$drop->get_secret()}]";

        $this->execute('behat_forms::i_set_the_field_to', [$fieldname, $shortcode]);
    }

    /**
     * Reset caches.
     *
     * @AfterScenario
     */
    public function reset_caches() {
        \block_xp\di::set_container(new \local_xp\local\container());
    }

}
