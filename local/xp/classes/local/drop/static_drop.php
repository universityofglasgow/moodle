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
 * Static drop.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\drop;
defined('MOODLE_INTERNAL') || die();

/**
 * Static drop.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class static_drop implements drop {

    /** @var int $id The ID of the drop. */
    protected $id;
    /** @var int $points The points associated with this drop. */
    protected $points;
    /** @var string $secret The generated secret for the drop. */
    protected $secret;
    /** @var int $courseid The world this belongs to. */
    protected $courseid;
    /** @var string $name The name of the drop. */
    protected $name;
    /** @var bool $enabled Whether the drop is enabled. */
    protected $enabled = true;

    /**
     * Constructor.
     *
     * @param $id The ID of the drop.
     * @param $points The points for the drop.
     * @param $secret The unique usable ID of the drop.
     * @param $name A plain text of the name.
     * @param $courseid The course ID.
     */
    public function __construct($id, $points, $secret, $name, $courseid) {
        $this->id = $id;
        $this->points = $points;
        $this->courseid = $courseid;
        $this->secret = $secret;
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function get_points() {
        return $this->points;
    }

    /**
     * @inheritDoc
     */
    public function get_secret() {
        return $this->secret;
    }

    /**
     * @inheritDoc
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * @inheritDoc
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function is_enabled() {
        return $this->enabled;
    }

    /**
     * Set whether enabled.
     *
     * @param bool $enabled Whether enabled.
     */
    public function set_enabled($enabled) {
        $this->enabled = (bool) $enabled;
    }

}