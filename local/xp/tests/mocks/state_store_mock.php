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
 * State store mock.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

use block_xp\local\reason\reason;
use block_xp\local\xp\state_store;
use block_xp\local\xp\state_store_with_delete;
use block_xp\local\xp\state_store_with_reason;
use local_xp\local\logger\dummy_collection_logger;
use local_xp\local\xp\levelless_state;

require_once(__DIR__ . '/collection_logger_mock.php');

/**
 * State store mock.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_state_store_mock implements state_store, state_store_with_reason, state_store_with_delete {

    /** @var array The local state. */
    public $state;
    /** @var reason_collection_logger The logger. */
    public $logger;

    /**
     * Constructor.
     */
    public function __construct(reason_collection_logger $logger = null) {
        $this->logger = !empty($logger) ? $logger : new local_xp_collection_logger_mock();
        $this->state = [];
    }

    /**
     * Delete a state.
     *
     * @param int $id The object ID.
     * @return void
     */
    public function delete($id) {
        unset($this->state[$id]);
    }

    /**
     * Get a state.
     *
     * @param int $id The object ID.
     * @return state
     */
    public function get_state($id) {
        $pts = isset($this->state[$id]) ? $this->state[$id] : 0;
        return new levelless_state($pts, $id, '?');
    }

    /**
     * Add a certain amount of experience points.
     *
     * @param int $id The receiver.
     * @param int $amount The amount.
     */
    public function increase($id, $amount) {
        if (!isset($this->state[$id])) {
            $this->state[$id] = 0;
        };
        $this->state[$id] += $amount;
    }

    /**
     * Add a certain amount of experience points.
     *
     * @param int $id The receiver.
     * @param int $amount The amount.
     * @param reason $reason A reason.
     */
    public function increase_with_reason($id, $amount, reason $reason) {
        if (!isset($this->state[$id])) {
            $this->state[$id] = 0;
        };
        $this->state[$id] += $amount;
        $this->logger->log_reason($id, $amount, $reason);
    }

    /**
     * Reset all experience points.
     *
     * @return void
     */
    public function reset() {
        $this->state = [];
        $this->logger->reset();
    }

    /**
     * Set the amount of experience points.
     *
     * @param int $id The receiver.
     * @param int $amount The amount.
     */
    public function set($id, $amount) {
        $this->state[$id] = 0;
    }

    /**
     * Set the amount of experience points.
     *
     * @param int $id The receiver.
     * @param int $amount The amount.
     * @param reason $reason A reason.
     */
    public function set_with_reason($id, $amount, reason $reason) {
        $this->state[$id] = 0;
        $this->logger->log_reason($id, $amount, $reason);
    }

}
