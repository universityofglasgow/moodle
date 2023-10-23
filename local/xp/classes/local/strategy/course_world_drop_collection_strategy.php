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
 * The drop collection strategy.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\strategy;

use block_xp\local\logger\reason_collection_logger;
use block_xp\local\xp\state_store_with_reason;
use DateTime;
use local_xp\local\strategy\drop_collection_strategy;
use local_xp\local\drop\drop;
use local_xp\local\logger\reason_occurance_indicator;
use local_xp\local\reason\drop_collected_reason;

/**
 * The drop collection strategy.
 *
 * @package    block_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_world_drop_collection_strategy implements drop_collection_strategy {

    /** @var reason_collection_logger $logger */
    protected $logger;
    /** @var course_user_state_store $store */
    protected $store;

    /**
     * Constructor.
     *
     * @param course_user_state_store $store The user_state_store.
     * @param reason_collection_logger $logger The logger that should implement collection and occurance.
     */
    public function __construct(state_store_with_reason $store, reason_occurance_indicator $logger) {
        $this->logger = $logger;
        $this->store = $store;
    }

    /**
     * @inheritDoc
     */
    public function collect_drop_for_user(drop $drop, $userid) {
        if ($this->can_collect($drop, $userid)) {
            $event = new drop_collected_reason($drop->get_id());
            $this->store->increase_with_reason($userid, $drop->get_xp(), $event);
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function can_collect(drop $drop, $userid) {
        // We can't do anything if we can't check whether the drop was found previously.
        if (!$this->logger instanceof reason_occurance_indicator) {
            return false;
        }

        $event = new drop_collected_reason($drop->get_id());
        return !$this->logger->has_reason_happened_since($userid, $event, new DateTime('@0'));
    }
}
