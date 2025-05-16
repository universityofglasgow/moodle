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
 * Course content renderer
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_cards\output\courseformat;

use coding_exception;
use format_cards\versionable_template;
use format_topics\output\courseformat\content as content_base;
use moodle_exception;
use renderer_base;

/**
 * Course content renderer
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends content_base {
    use versionable_template;

    /**
     * If the user is editing the page, just use the default renderer for format_topics
     * Otherwise, override the renderer to add our own sections onto the page
     *
     * @param renderer_base $renderer
     * @return string
     * @throws coding_exception
     */
    public function get_template_name(renderer_base $renderer): string {
        return "format_cards/local/content";
    }

    /**
     * Export template data
     *
     * @param renderer_base $output
     * @return object
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        // Is this a single section page?
        $singlesection = $this->format->get_sectionnum();

        $this->hasaddsection = !$singlesection;

        $data = parent::export_for_template($output);

        // Add version variables.
        $this->add_version_variables($data);

        // Rather than rolling our own empty placeholder, we can just re-use the "no courses" template
        // from block_myoverview and change the text to be "No activities" instead.
        $data->nocoursesimg = $output->image_url('courses', 'block_myoverview')->out();

        $data->userisediting = $PAGE->user_is_editing();

        $data->subsectionsascards = $this->format->get_format_option("subsectionsascards") == FORMAT_CARDS_SUBSECTIONS_AS_CARDS;

        if (!$singlesection) {
            return $data;
        }

        if ($PAGE->user_is_editing()) {
            $data->initialsection = '';
        } else if ($this->format->get_format_option('section0') == FORMAT_CARDS_SECTION0_COURSEPAGE) {
            $data->initialsection = '';
        } else if (empty($data->initialsection)) {
            $section0 = new $this->sectionclass($this->format, $this->format->get_section(0));
            $data->initialsection = $section0->export_for_template($output);
        }

        $this->add_section_navigation($data, $output);

        return $data;
    }

    /**
     * Adds section navigation data to the template
     *
     * @param object $data Current template context
     * @param renderer_base $output Output renderer
     * @return void $data is modified directly
     */
    private function add_section_navigation(&$data, renderer_base $output): void {
        $singlesection = $this->format->get_sectionnum();

        if (!$singlesection) {
            return;
        }

        $navigationoption = $this->format->get_format_option('sectionnavigation');

        // Remove section navigation if it's set in the options.
        if ($navigationoption == FORMAT_CARDS_SECTIONNAVIGATION_NONE) {
            $data->sectionnavigation = false;
            $data->sectionselector = false;

            return;
        }

        $sectionnavigation = new $this->sectionnavigationclass($this->format, $singlesection);
        $sectionselector = new $this->sectionselectorclass($this->format, $sectionnavigation);

        // Add top navigation.
        switch ($navigationoption) {
            case FORMAT_CARDS_SECTIONNAVIGATION_TOP:
                $data->sectionnavigation = $sectionnavigation->export_for_template($output);
                $data->sectionselector = false;
                break;
            case FORMAT_CARDS_SECTIONNAVIGATION_BOTTOM:
                $data->sectionselector = $sectionselector->export_for_template($output);
                $data->sectionnavigation = false;
                break;
            default:
            case FORMAT_CARDS_SECTIONNAVIGATION_BOTH:
                $data->sectionnavigation = $sectionnavigation->export_for_template($output);
                $data->sectionselector = $sectionselector->export_for_template($output);
                break;
        }

        if ($data->sectionselector || $data->sectionnavigation) {
            $data->hasnavigation = true;
            $data->sectionreturn = $singlesection;
        }
    }

}
