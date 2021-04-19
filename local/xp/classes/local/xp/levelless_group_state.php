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
 * Level-less group state.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\xp;
defined('MOODLE_INTERNAL') || die();

use context_course;
use moodle_url;
use renderable;
use stdClass;
use block_xp\local\xp\described_level;
use block_xp\local\xp\state;
use block_xp\local\xp\state_with_subject;

/**
 * Level-less group state.
 *
 * Simple implementation where the level is not computed.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levelless_group_state extends levelless_state implements renderable  {

    /** @var stdClass The group object. */
    protected $group;

    /**
     * Constructor.
     *
     * @param stdClass $group The group object.
     * @param int $xp The group XP.
     * @param int|null $totalxp The total amount of XP.
     */
    public function __construct(stdClass $group, $xp, $totalxp = null) {
        parent::__construct($xp, $group->id, '', $totalxp);
        $this->group = $group;
    }

    public function get_name() {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');
        $group = $this->get_group();
        return external_format_string($group->name, context_course::instance($group->courseid)->id);
    }

    public function get_picture() {
        $pic = $this->get_group_picture_url();
        return empty($pic) ? null : $pic;
    }

    /**
     * Return the group object.
     *
     * @return stdClass
     */
    public function get_group() {
        return $this->group;
    }

    /**
     * Get the group picture.
     *
     * This tries to use Moodle's functions if they exist, else it reverts
     * to our own implementation based on the same Moodle function.
     *
     * @return moodle_url|null
     */
    protected function get_group_picture_url() {
        $group = $this->get_group();

        // This function was introduced in Moodle 3.5.
        if (function_exists('get_group_picture_url')) {
            return get_group_picture_url($group, $group->courseid, true);
        }


        // The following code is adapted from get_group_picture_url.
        $context = context_course::instance($group->courseid);

        // If there is no picture, do nothing.
        if (!$group->picture) {
            return;
        }

        // If picture is hidden, only show to those with course:managegroups.
        if ($group->hidepicture and !has_capability('moodle/course:managegroups', $context)) {
            return;
        }

        $grouppictureurl = moodle_url::make_pluginfile_url($context->id, 'group', 'icon', $group->id, '/', 'f1', false);
        $grouppictureurl->param('rev', $group->picture);
        return $grouppictureurl;
    }

}
