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
 * Privacy Subsystem implementation for mod_hsuforum.
 *
 * @package    mod_hsuforum
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hsuforum\privacy;

use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\writer;
use \core_privacy\local\metadata\collection;

defined('MOODLE_INTERNAL') || die();

/**
 * Subcontext helper trait.
 */
trait subcontext_info {
    /**
     * Get the discussion part of the subcontext.
     *
     * @param   \stdClass   $discussion
     * @return  array
     */
    protected static function get_discussion_area(\stdClass $discussion) : Array {
        $pathparts = [];
        if (!empty($discussion->groupname)) {
            $pathparts[] = get_string('groups');
            $pathparts[] = $discussion->groupname;
        }

        $parts = [
            $discussion->id,
            $discussion->name,
        ];

        $discussionname = implode('-', $parts);

        $pathparts[] = get_string('discussions', 'mod_hsuforum');
        $pathparts[] = $discussionname;

        return $pathparts;
    }

    /**
     * Get the post part of the subcontext.
     *
     * @param   \stdClass   $post
     * @return  array
     */
    protected static function get_post_area(\stdClass $post) : Array {
        $parts = [
            $post->created,
            $post->subject,
            $post->id,
        ];
        $area[] = implode('-', $parts);

        return $area;
    }

    protected static function get_post_area_for_parent(\stdClass $post) {
        global $DB;

        $subcontext = [];
        if ($parent = $DB->get_record('hsuforum_posts', ['id' => $post->parent], 'id, created, subject')) {
            $subcontext = array_merge($subcontext, static::get_post_area($parent));
        }
        $subcontext = array_merge($subcontext, static::get_post_area($post));

        return $subcontext;
    }

    protected static function get_subcontext($forum, $discussion = null, $post = null) {
        $subcontext = [];
        if (null !== $discussion) {
            $subcontext += self::get_discussion_area($discussion);

            if (null !== $post) {
                $subcontext[] = get_string('posts', 'mod_hsuforum');
                $subcontext = array_merge($subcontext, static::get_post_area_for_parent($post));
            }
        }

        return $subcontext;

    }
}
