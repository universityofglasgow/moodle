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
 * Currency sign URL resolver.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\currency;
defined('MOODLE_INTERNAL') || die();

/**
 * Currency sign URL resolver.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stack_sign_url_resolver implements currency_sign_url_resolver {

    /** @var currency_sign_url_resolver[] A stack of resolvers. */
    protected $resolvers;

    /**
     * Constructor.
     *
     * @param currency_sign_url_resolver[] $resolvers A stack of resolvers.
     */
    public function __construct(array $resolvers) {
        $this->resolvers = $resolvers;
    }

    /**
     * The sign.
     *
     * @return string
     */
    public function get_currency_sign_url() {
        foreach ($this->resolvers as $resolver) {
            $signurl = $resolver->get_currency_sign_url();
            if ($signurl !== null) {
                return $signurl;
            }
        }
        return null;
    }

}
