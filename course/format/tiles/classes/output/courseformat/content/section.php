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
 * Contains the default section course format output class.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\output\courseformat\content;

use core_courseformat\output\local\content\section as section_base;
use format_tiles\tile_photo;

/**
 * Base class to render a course section.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends section_base {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return \stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): \stdClass {
        global $DB;
        $data = parent::export_for_template($output);

        // TODO class to handle this.
        $data->hasphoto = 0;
        // If photo tile backgrounds are allowed by site admin, prepare the image for this section.
        if (get_config('format_tiles', 'allowphototiles')) {
            $tilephoto = new tile_photo($this->section->course, $this->section->id);
            $tilephotourl = $tilephoto->get_image_url();
            if ($tilephotourl) {
                $data->hasphoto = 1;
                $data->phototileinlinestyle = 'style = "background-image: url(' . $tilephotourl . ');"';
                $data->hastilephoto = $tilephotourl ? 1 : 0;
                $data->phototileurl = $tilephotourl;
                $data->phototileediturl = new \moodle_url(
                    '/course/format/tiles/editimage.php',
                    array('courseid' => $this->section->course, 'sectionid' => $this->section->id)
                );
            }

        }
        // TODO OPTIMISE THIS.
        if (!$data->hasphoto) {
            $data->tileicon = $DB->get_field(
                'course_format_options', 'value', ['format' => 'tiles', 'sectionid' => $this->section->id, 'name' => 'tileicon']
            );
            if (!$data->tileicon) {
                $formatoptions = $this->format->get_format_options();
                $data->tileicon = $formatoptions['defaulttileicon'];
            }
        }

        if (!$this->format->get_section_number()) {
            $addsectionclass = $this->format->get_output_classname('content\\addsection');
            $addsection = new $addsectionclass($this->format);
            $data->numsections = $addsection->export_for_template($output);
            $data->insertafter = true;
        }
        if ($data->num === 0) {
            $data->collapsemenu = true;
        }
        return $data;
    }
}
