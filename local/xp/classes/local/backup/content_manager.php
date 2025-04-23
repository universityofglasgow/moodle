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
 * Backup content manager.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\backup;

/**
 * Backup content manager.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_manager extends \block_xp\local\backup\content_manager {

    /**
     * Encode content links.
     *
     * @param string $content The content.
     * @return string
     */
    public function encode_content_links($content) {
        $content = parent::encode_content_links($content);
        $content = xpdrop_decode_rule::encode_content($content);
        return $content;
    }

    /**
     * Get the decode rules.
     *
     * @return \restore_decode_rule[]
     */
    public function get_decode_rules() {
        $rules = parent::get_decode_rules();
        $rules[] = new xpdrop_decode_rule();
        return $rules;
    }

}
