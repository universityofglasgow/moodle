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

use renderer_base;

/**
 * Currency sign URL resolver.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_sign_url_resolver implements currency_sign_url_resolver {

    /** @var string The theme code. */
    protected $code;
    /** @var renderer_base The renderer. */
    protected $renderer;

    /**
     * Constructor.
     *
     * @param renderer_base $renderer
     * @param string $code The theme code.
     */
    public function __construct(renderer_base $renderer, $code) {
        $this->code = clean_param($code, PARAM_SAFEDIR);
        $this->renderer = $renderer;
    }

    /**
     * The sign.
     *
     * @return string
     */
    public function get_currency_sign_url() {
        return $this->renderer->pix_url('sign/' . $this->code . '/icon', 'local_xp');
    }

}
