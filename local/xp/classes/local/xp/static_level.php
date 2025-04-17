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
 * Level.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\xp;

/**
 * Level.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class static_level extends \block_xp\local\xp\static_level implements level_with_badge_award, level_with_popup_message {

    /** @var string|null The popup message. */
    protected $popupmessage;
    /** @var int|null The badge to award ID. */
    protected $badgeawardid;
    /** @var int|null The badge issuer ID. */
    protected $badgeissuerid;

    public function __construct($level, $xprequired, $badgeurlresolver = null, $metadata = []) {
        parent::__construct($level, $xprequired, $badgeurlresolver, $metadata);

        $keys = ['popupmessage', 'badgeawardid', 'badgeissuerid'];
        foreach ($keys as $key) {
            if (!empty($metadata[$key])) {
                $this->{$key} = $metadata[$key];
            }
        }
    }

    public function get_badge_award_id() {
        return $this->badgeawardid;
    }

    public function get_badge_award_issuer_id() {
        return $this->badgeissuerid;
    }

    public function get_popup_message() {
        return $this->popupmessage;
    }

}
