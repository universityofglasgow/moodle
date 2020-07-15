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
 * Block.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\block;
defined('MOODLE_INTERNAL') || die();

use action_link;
use lang_string;
use pix_icon;
use stdClass;
use block_xp\local\course_world;
use block_xp\local\config\course_world_config;
use block_xp\output\notice;
use block_xp\output\dismissable_notice;
use local_xp\local\config\default_course_world_config;

/**
 * Block class.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_block extends \block_xp\local\block\course_block {

    /**
     * Applicable formats.
     *
     * @return array
     */
    public function applicable_formats() {
        $formats = parent::applicable_formats();
        $mode = \block_xp\di::get('config')->get('context');
        if ($mode == CONTEXT_SYSTEM) {
            $formats += ['user-profile' => true];
        }
        return $formats;
    }

    /**
     * Get the content for the profile.
     *
     * @param int $userid The user whose block we're showing.
     * @return string
     */
    public function get_content_for_profile($userid) {
        global $USER;

        // Neither of the users should be guests or not authenticated.
        if (!$userid || isguestuser($userid) || !$USER->id || isguestuser()) {
            return '';
        }

        // Confirm that we can view the block, and that the target user can view it too.
        $world = $this->get_world($this->page->course->id);
        $theycanview = $world->get_access_permissions()->can_access($userid);
        $icanview = $world->get_access_permissions()->can_access($USER->id);
        $icanedit = $world->get_access_permissions()->can_manage($USER->id);

        // The block should not be displayed.
        if (!$theycanview && !($icanview || $icanedit)) {
            return '';
        }

        $renderer = \block_xp\di::get('renderer');
        $urlresolver = \block_xp\di::get('url_resolver');
        $state = $world->get_store()->get_state($userid);
        $courseid = $world->get_courseid();
        $config = $world->get_config();

        // Navigation.
        $actions = [];
        if ($userid == $USER->id) {
            $actions = $this->get_block_navigation($world);
        }

        // Widget.
        $widget = new \block_xp\output\xp_widget($state, [], null, $actions);

        // When XP gain is disabled, let the teacher now.
        if (!$config->get('enabled') && $icanedit) {
            $widget->add_manager_notice(new lang_string('xpgaindisabled', 'block_xp'));
        }

        // Render the thing.
        return $renderer->render($widget);
    }

    /**
     * Get content.
     *
     * @return stdClass
     */
    public function get_content() {
        global $USER;

        if (isset($this->content)) {
            return $this->content;
        }

        // Can we show the information of other users? For now, let's assume that if the block
        // is visible on one's profile and that the ladder is enabled, and names not hidden, then
        // we can display other's information.
        $world = $this->get_world($this->page->course->id);
        $canshow = $world->get_config()->get('enableladder')
            && $world->get_config()->get('identitymode') != course_world_config::IDENTITY_OFF;

        // Do not display the block when it's not set as
        $ispercourse = \block_xp\di::get('config')->get('context') == CONTEXT_COURSE;

        // Detect whether we are on the system profile page, or the course profile page.
        $onsiteprofile = $this->page->pagetype == 'user-profile';
        $oncourseprofile = strpos($this->page->pagetype, 'course-view-') === 0
            && $this->page->context->contextlevel == CONTEXT_COURSE
            && $this->page->docspath == 'user/profile'
            && preg_match('/(^|\s)path-user(\s|$)/', $this->page->bodyclasses);

        if ($canshow && ((!$ispercourse && $onsiteprofile) || $oncourseprofile)) {
            $userid = null;

            if ($onsiteprofile) {
                // When we are on the admin's view of the default profile page, mock the user to the current one.
                $userid = $this->page->context->contextlevel == CONTEXT_USER ? $this->page->context->instanceid : $USER->id;
            } else if ($oncourseprofile) {
                // Read the ID from the URL.
                $userid = optional_param('id', null, PARAM_INT);
            }

            // If we're happy with our quest for a user ID, display proceed.
            if ($userid !== null && $USER->id != $userid) {
                $this->content = new stdClass();
                $this->content->text = '';
                $this->content->footer = '';
                $this->content->text = $this->get_content_for_profile($userid);
                return $this->content;
            }

        }

        // Normal behaviour of showing the current user's block.
        return parent::get_content();
    }

    /**
     * Get the block navigation.
     *
     * @param course_world $world The world.
     * @return action_link[]
     */
    protected function get_block_navigation(course_world $world) {
        $courseid = $world->get_courseid();
        $urlresolver = \block_xp\di::get('url_resolver');
        $config = $world->get_config();
        $adminconfig = \block_xp\di::get('config');

        $urlsmatch = function($u1, $u2) {
            return $u1->out(false) == $u2->out(false);
        };

        $actions = parent::get_block_navigation($world);

        // Add the group ladder when the ladder is not already present.
        if ($config->get('enablegroupladder') != default_course_world_config::GROUP_LADDER_NONE) {

            $ladderurl = $urlresolver->reverse('ladder', ['courseid' => $courseid]);
            $infourl = $urlresolver->reverse('infos', ['courseid' => $courseid]);
            $foundladder = false;
            $foundinfo = false;

            $candidates = $actions;
            foreach ($candidates as $index => $candidate) {
                $foundladder = $foundladder === false ? ($urlsmatch($ladderurl, $candidate->url) ? $index : false) : $foundladder;
                $foundinfo = $foundinfo === false ? ($urlsmatch($infourl, $candidate->url) ? $index : false) : $foundinfo;
            }

            if ($foundladder === false) {
                $link = new action_link(
                    $urlresolver->reverse('group_ladder', ['courseid' => $courseid]),
                    get_string('navladder', 'block_xp'), null, null,
                    new pix_icon('i/ladder', '', 'block_xp')
                );
                array_splice($actions, ($foundinfo !== false ? $foundinfo + 1 : 0), 0, [$link]);
            }

        }
        return $actions;
    }

}
