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
 * Contains the default activity list from a section.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\output\courseformat\content;

use core_courseformat\base as course_format;
use core_courseformat\output\local\content\cm as core_cm;

/**
 * Class to render a course module inside a Tiles course format.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cm extends core_cm {

    /**
     * Add activity information to the data structure.
     *
     * @param \stdClass $data the current cm data reference
     * @param bool[] $haspartials the result of loading partial data elements
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has format data
     */
    protected function add_format_data(\stdClass &$data, array $haspartials, \renderer_base $output): bool {
        $moodlerelease = \format_tiles\util::get_moodle_release();
        $data->ismoodle42minus = $moodlerelease <= 4.2;
        $data->ismoodle41minus = $moodlerelease <= 4.1;
        $data->ismoodle40 = $moodlerelease === 4.0;
        $data->modcontextid = $this->mod->context->id;
        return parent::add_format_data($data, $haspartials, $output);
    }


    /**
     * Add course editor attributes to the data structure.
     * We override this so we can use local control menu class.
     *
     * @param \stdClass $data the current cm data reference
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has editor data
     */
    protected function add_editor_data(\stdClass &$data, \renderer_base $output): bool {

        parent::add_editor_data($data, $output);

        if (!$this->format->show_editor()) {
            return false;
        }
        $returnsection = $this->format->get_section_number();
        // Edit actions.
        $sectioninfo = get_fast_modinfo($this->mod->course)->get_section_info($this->mod->sectionnum);
        $controlmenu = new \format_tiles\output\courseformat\content\cm\controlmenu (
            $this->format,
            $sectioninfo,
            $this->mod,
            $this->displayoptions
        );

        $data->controlmenu = $controlmenu->export_for_template($output);
        if (!$this->format->supports_components()) {
            // Add the legacy YUI move link.
            $data->moveicon = course_get_cm_move($this->mod, $returnsection);
        }
        return true;
    }
}
