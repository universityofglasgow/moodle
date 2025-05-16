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
 * Renders a course section header
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_cards\output\courseformat\content\section;

use coding_exception;
use context_course;
use core_courseformat\output\local\content\section\header as header_base;
use core_geopattern;
use moodle_url;
use renderer_base;
use section_info;
use stdClass;
use stored_file;

/**
 * Renders a course section header
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class header extends header_base {

    /**
     * In-process cache of section images
     *
     * @var stored_file[]
     */
    private static array $images = [];

    /**
     * We don't want section titles to have clickable links
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $PAGE, $CFG;

        $data = parent::export_for_template($output);

        $format = $this->format;
        $section = $this->section;
        $course = $format->get_course();

        // On the course main page, display this section as a card unless the
        // user is currently editing the page. Section #0 should never be
        // displayed as a card.
        $issinglesectionpage = $this->format->get_sectionnum() != 0;
        $showascard = !$issinglesectionpage
            && !$PAGE->user_is_editing()
            && !$this->section->section == 0;

        $issubsection = method_exists($this->section, 'is_delegated')
            && $this->section->is_delegated()
            && $this->section->component == 'mod_subsection';

        $data->title = $issubsection
            ? $output->section_title($section, $course)
            : $output->section_title_without_link($section, $course);

        $data->url = course_get_url(
            $this->section->course,
            $this->section->section,
            [ 'sr' => true ]
        );

        // We want to hide the "go to section" link on Moodle 4.4+.
        if ($CFG->version >= 2024042200 && (!isset($data->controlmenu) || !$data->controlmenu)) {
            $data->controlmenu = true;
        }

        // Try and fetch the image.
        $image = $this->get_section_image($this->section);
        if (!is_null($image)) {
            $data->image = moodle_url::make_pluginfile_url(
                $image->get_contextid(),
                $image->get_component(),
                $image->get_filearea(),
                $image->get_itemid(),
                $image->get_filepath(),
                $image->get_filename(),
                false
            )->out(false);
        } else {
            $pattern = new core_geopattern();
            $pattern->setColor($this->get_course_colour());
            $pattern->patternbyid($this->section->id);
            $data->image = $pattern->datauri();
        }

        return $data;
    }

    /**
     * Generates a semi-random colour based on the course's ID
     *
     * @see \block_myoverview\output\courses_view::coursecolor()
     * @return string
     */
    public function get_course_colour(): string {
        // The colour palette is hardcoded for now. It would make sense to combine it with theme settings.
        $basecolours = [
            '#81ecec', '#74b9ff', '#a29bfe', '#dfe6e9', '#00b894',
            '#0984e3', '#b2bec3', '#fdcb6e', '#fd79a8', '#6c5ce7',
        ];

        return $basecolours[$this->format->get_course()->id % 10];
    }

    /**
     * Fetch all the section images for the current course
     *
     * @return stored_file[] Array of image files
     */
    public function get_section_images(): array {

        $course = $this->format->get_course();

        if (!array_key_exists($course->id, self::$images)) {
            $context = context_course::instance($course->id);
            $filestorage = get_file_storage();

            try {
                $files = $filestorage->get_area_files($context->id,
                    'format_cards',
                    FORMAT_CARDS_FILEAREA_IMAGE,
                    false,
                    'itemid, filepath, filename',
                    false
                );
            } catch (coding_exception $e) {
                return [];
            }

            self::$images[$course->id] = [];

            foreach ($files as $file) {
                self::$images[$course->id][$file->get_itemid()] = $file;
            }
        }

        return self::$images[$course->id];
    }

    /**
     * Fetch the image file for a given section
     *
     * @param section_info $section
     * @return stored_file|null
     */
    public function get_section_image(section_info $section): ?stored_file {
        $images = $this->get_section_images();

        if (array_key_exists($section->id, $images)) {
            return $images[$section->id];
        }

        return null;
    }
}
