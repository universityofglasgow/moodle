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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_kuracloud\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;

/**
 * Block Content Renderable Class
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_content implements renderable, templatable {

    /**
     * List of api endpoints/tokens
     *
     * @var array
     */
    private $tokens;

    /**
     * Constructor
     *
     * @param stdClass $mapping
     */
    public function __construct($mapping) {
        $this->mapping = $mapping;
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $COURSE;

        $context = \context_course::instance($COURSE->id);

        $data = new stdClass();
        if (has_capability('block/kuracloud:mapcourses', $context)) {
            $data->editmapping_url = $CFG->wwwroot."/blocks/kuracloud/coursemapping.php?course=".$COURSE->id;
            $data->deletemapping_url = $CFG->wwwroot."/blocks/kuracloud/deletecoursemapping.php?course=".$COURSE->id;
        }

        if (has_capability('block/kuracloud:syncusers', $context)) {
            $data->syncusers_url = $CFG->wwwroot."/blocks/kuracloud/syncusers.php?course=".$COURSE->id;
        }

        if (has_capability('block/kuracloud:syncgrades', $context)) {
            $data->syncgrades_url = $CFG->wwwroot."/blocks/kuracloud/syncgrades.php?course=".$COURSE->id;
        }

        if (!empty($this->mapping)) {
            $data->remote_name = $this->mapping->remote_name;
            $data->status_ok = $this->mapping->status_ok;
            $data->status_message = $this->mapping->status_message;
        }

        return $data;
    }
}