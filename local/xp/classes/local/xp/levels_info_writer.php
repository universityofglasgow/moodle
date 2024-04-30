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
 * Levels info writer.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\xp;

use block_xp\local\backup\restore_context;
use block_xp\local\course_world;
use block_xp\local\world;
use core_text;
use local_xp\local\badge\badge_manager;

/**
 * Levels info writer.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levels_info_writer extends \block_xp\local\xp\levels_info_writer {

    /** @var badge_manager */
    protected $badgemanager;
    /** @var array */
    protected $badgescache;
    /** @var \moodle_database */
    protected $db;

    /**
     * Set the badge manager.
     *
     * @param badge_manager $badgemanager
     */
    public function set_badge_manager(badge_manager $badgemanager) {
        $this->badgemanager = $badgemanager;
    }

    /**
     * Get the compatible badges.
     *
     * @param \context $context The context.
     * @param int $userid The user ID.
     * @return array
     */
    protected function get_compatible_badges(\context $context, $userid) {
        if (!$this->badgemanager) {
            return [];
        }

        $cachekey = $context->id . '-' . $userid;
        if (!isset($this->badgescache[$cachekey])) {
            $this->badgescache[$cachekey] = $this->badgemanager->get_compatible_badges($context, $userid);
        }
        return $this->badgescache[$cachekey];
    }

    /**
     * Get the metadata for the level.
     *
     * @param int $level The level number.
     * @param array $metadata The metadata before processing.
     * @param world|null $world The world, if any.
     */
    protected function get_metadata_for_level($level, $metadata, world $world = null) {
        global $USER;

        $finaldata = parent::get_metadata_for_level($level, $metadata, $world);

        // We can only deal with this type of world at the moment.
        $world = $world instanceof course_world ? $world : null;

        // Popup message for all levels except first.
        if (!empty($metadata['popupmessage']) && $level > 1) {
            $finaldata['popupmessage'] = core_text::substr($metadata['popupmessage'], 0, 280);
        }

        // Only allowed on level > 1, and not in defaults.
        if (!empty($metadata['badgeawardid']) && $level > 1 && $world) {
            $origlevelsinfo = $world ? $world->get_levels_info() : null;
            $origlevel = $origlevelsinfo && $origlevelsinfo->get_count() <= $level ? $origlevelsinfo->get_level($level) : null;

            // Checks whether the user is changing the badge. This is to prevent someone from removing the badge
            // that someone else has set, in case they don't have the permission to set it. So if someone else send
            // the same data, we essentially do not save anything. This also ensures that the issuer ID does not change.
            $ischanging = !$origlevel || !($origlevel instanceof level_with_badge_award)
                || $origlevel->get_badge_award_id() !== $metadata['badgeawardid'];

            // If we are not changing the badge, we need to keep the original values.
            if (!$ischanging) {
                if ($origlevel && $origlevel instanceof level_with_badge_award) {
                    $finaldata['badgeawardid'] = $origlevel->get_badge_award_id();
                    $finaldata['badgeissuerid'] = $origlevel->get_badge_award_issuer_id();
                }
            } else {
                $compatiblebadges = $world ? $this->get_compatible_badges($world->get_context(), $USER->id) : [];
                if (array_key_exists($metadata['badgeawardid'], $compatiblebadges)) {
                    $finaldata['badgeawardid'] = $metadata['badgeawardid'];
                    $finaldata['badgeissuerid'] = $metadata['badgeawardid'] ? $USER->id : null;
                }
            }
        }

        return $finaldata;
    }

    /**
     * Get the metadata for the level after restore.
     *
     * @param restore_context $restore The context.
     * @param int $level The level number.
     * @param array $metadata The metadata before processing.
     * @param world|null $world The world, if any.
     */
    protected function get_metadata_for_level_after_restore(restore_context $restore, $level, $metadata, world $world = null) {
        $metadata = parent::get_metadata_for_level_after_restore($restore, $level, $metadata, $world);

        // No badges, nothing to do!
        if (empty($metadata['badgeawardid'])) {
            return $metadata;
        }

        // Updating the mapping of the badges.
        $badgeid = $restore->get_mapping_id('badge', $metadata['badgeawardid']);
        if (!$badgeid && $restore->is_same_site() && $this->badgemanager
                && $this->badgemanager->is_site_badge($metadata['badgeawardid'])) {

            // If the badge is a system badge, it won't be included in the backup, in which
            // case we only restore if we're restoring in the same site.
            $badgeid = $metadata['badgeawardid'];
        }

        // When we've resolved a badge, save both values.
        if ($badgeid) {
            $badgeissuerid = $restore->get_mapping_id('user', $metadata['badgeissuerid'] ?? 0);
            $metadata['badgeawardid'] = $badgeid ? (int) $badgeid : null;
            $metadata['badgeissuerid'] = $badgeissuerid ? (int) $badgeissuerid : $restore->get_user_id();
        }

        return $metadata;
    }

}
