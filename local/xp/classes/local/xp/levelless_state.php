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
 * Level-less state.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\xp;
defined('MOODLE_INTERNAL') || die();

use renderable;
use stdClass;
use block_xp\local\xp\described_level;
use block_xp\local\xp\state;
use block_xp\local\xp\state_with_subject;

/**
 * Level-less state.
 *
 * Simple implementation where the level is not computed, but a progress bar can
 * be computed if the total amount of points is shared.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levelless_state implements state, state_with_subject {

    /** @var int The state ID. */
    protected $id;
    /** @var string The name. */
    protected $name;
    /** @var int Total XP. */
    protected $totalxp;
    /** @var int The XP. */
    protected $xp;

    /**
     * Constructor.
     *
     * @param int $xp The cohort XP.
     * @param int $id The ID.
     * @param string $name The name.
     * @param int|null $totalxp The total amount of XP to display a progress bar.
     */
    public function __construct($xp, $id, $name, $totalxp = null) {
        $this->name = $name;
        $this->id = $id;
        $this->xp = $xp;
        $this->totalxp = $totalxp;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_level() {
        return new described_level(1, 1, '');
    }

    public function get_link() {
        return null;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_picture() {
        return null;
    }

    public function get_ratio_in_level() {
        $total = $this->get_total_xp_in_level();
        if ($total <= 0) {
            return 1;
        }
        return min(1, max(0, $this->get_xp_in_level() / $total));
    }

    public function get_total_xp_in_level() {
        return $this->totalxp ? $this->totalxp : 1;
    }

    public function get_xp() {
        return $this->xp;
    }

    public function get_xp_in_level() {
        return $this->totalxp ? min($this->xp, $this->totalxp) : 1;
    }

}
