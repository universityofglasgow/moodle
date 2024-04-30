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
 * Serializer.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\serializer;

use block_xp\external\external_single_structure;
use block_xp\external\external_value;
use local_xp\local\xp\level_with_badge_award;
use local_xp\local\xp\level_with_popup_message;

/**
 * Serializer.
 *
 * @package    block_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class level_serializer extends \block_xp\local\serializer\level_serializer {

    /**
     * Serialize.
     *
     * @param mixed $level The level.
     * @return array
     */
    public function serialize($level) {
        $data = parent::serialize($level);
        $data['popupmessage'] = $level instanceof level_with_popup_message ? $level->get_popup_message() : null;
        $data['badgeawardid'] = $level instanceof level_with_badge_award ? $level->get_badge_award_id() : null;
        return $data;
    }

    /**
     * Return the structure for external services.
     *
     * @param int $required Value constant.
     * @param scalar $default Default value.
     * @param int $null Whether null is allowed.
     * @return external_value
     */
    public function get_read_structure($required = VALUE_REQUIRED, $default = null, $null = NULL_ALLOWED) {
        $structure = parent::get_read_structure($required, $default, $null);
        if ($structure instanceof external_single_structure) {
            $structure->keys += [
                'popupmessage' => new external_value(PARAM_NOTAGS),
                'badgeawardid' => new external_value(PARAM_INT),
            ];
        }
        return $structure;

    }

}
