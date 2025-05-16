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

namespace format_cards\output\courseformat\content;

use core_courseformat\base as course_format;
use core_courseformat\output\local\content\delegatedsection as delegatedsection_base;
use moodle_exception;
use renderer_base;
use section_info;
use stdClass;

/**
 * Renders a delegated / nested section
 *
 * @package     format_cards
 * @copyright   2025 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delegatedsection extends delegatedsection_base {

    /**
     * @var section The format_cards section renderer
     */
    private section $sectionoutput;

    /**
     * Constructor.
     *
     * @param course_format $format
     * @param section_info $section
     */
    public function __construct(course_format $format, section_info $section) {
        parent::__construct($format, $section);

        $this->sectionoutput = new section($format, $section);
    }

    /**
     * If we're in edit mode, or the user has chosen to display subsections as activities, we
     * want to just use the default renderer.
     *
     * @return bool
     */
    protected function use_default_renderer(): bool {
        return $this->format->show_editor()
            || $this->format->get_format_option('subsectionsascards') == FORMAT_CARDS_SUBSECTIONS_AS_ACTIVITIES;
    }

    /**
     * Is this considered a stealth section
     *
     * @return bool
     */
    #[\Override]
    public function is_stealth(): bool {
        return $this->use_default_renderer()
            ? parent::is_stealth()
            : $this->sectionoutput->is_stealth();
    }

    /**
     * Hides the section title
     *
     * @return void
     */
    #[\Override]
    public function hide_title(): void {
        $this->use_default_renderer()
            ? parent::hide_title()
            : $this->sectionoutput->hide_title();
    }

    /**
     * Hides section controls
     *
     * @return void
     */
    #[\Override]
    public function hide_controls(): void {
        $this->use_default_renderer()
            ? parent::hide_controls()
            : $this->sectionoutput->hide_controls();
    }

    /**
     * Adds section header to the data structure
     *
     * @param stdClass $data
     * @param renderer_base $output
     * @return bool
     */
    #[\Override]
    protected function add_header_data(stdClass &$data, renderer_base $output): bool {
        return $this->use_default_renderer()
            ? parent::add_header_data($data, $output)
            : $this->sectionoutput->add_header_data($data, $output);
    }

    /**
     * Adds course_module to the data structure
     *
     * @param stdClass $data
     * @param renderer_base $output
     * @return bool
     */
    #[\Override]
    protected function add_cm_data(stdClass &$data, renderer_base $output): bool {
        return $this->use_default_renderer()
            ? parent::add_cm_data($data, $output)
            : $this->sectionoutput->add_cm_data($data, $output);
    }

    /**
     * Adds availability to the data structure
     *
     * @param stdClass $data
     * @param renderer_base $output
     * @return bool
     */
    #[\Override]
    protected function add_availability_data(stdClass &$data, renderer_base $output): bool {
        return $this->use_default_renderer()
            ? parent::add_availability_data($data, $output)
            : $this->sectionoutput->add_availability_data($data, $output);
    }

    /**
     * Adds visibility to the data structure
     *
     * @param stdClass $data
     * @param renderer_base $output
     * @return bool
     */
    #[\Override]
    protected function add_visibility_data(stdClass &$data, renderer_base $output): bool {
        return $this->use_default_renderer()
            ? parent::add_visibility_data($data, $output)
            : $this->sectionoutput->add_visibility_data($data, $output);
    }

    /**
     * Adds editor to the data structure
     *
     * @param stdClass $data
     * @param renderer_base $output
     * @return bool
     */
    #[\Override]
    protected function add_editor_data(stdClass &$data, renderer_base $output): bool {
        return $this->use_default_renderer()
            ? parent::add_editor_data($data, $output)
            : $this->sectionoutput->add_editor_data($data, $output);
    }

    /**
     * Adds format to the data structure
     *
     * @param stdClass $data
     * @param array $haspartials
     * @param renderer_base $output
     * @return bool
     */
    #[\Override]
    protected function add_format_data(stdClass &$data, array $haspartials, renderer_base $output): bool {
        return $this->use_default_renderer()
            ? parent::add_format_data($data, $haspartials, $output)
            : $this->sectionoutput->add_format_data($data, $haspartials, $output);
    }

    /**
     * In editing mode, just use the default template. Otherwise we want to include some of the other context
     * keys that we generate for a normal section.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output): stdClass {
        return $this->use_default_renderer()
            ? parent::export_for_template($output)
            : (object) array_merge(
                (array)parent::export_for_template($output),
                (array)$this->sectionoutput->export_for_template($output)
            );

    }

    /**
     * Is this section collapsed?
     *
     * @return bool
     */
    #[\Override]
    protected function is_section_collapsed(): bool {
        return $this->use_default_renderer()
            ? parent::is_section_collapsed()
            : $this->sectionoutput->is_section_collapsed();
    }

    /**
     * Fetch the template name
     *
     * @param renderer_base $renderer
     * @return string
     */
    public function get_template_name(renderer_base $renderer): string {
        return $this->use_default_renderer()
            ? parent::get_template_name($renderer)
            : 'format_cards/local/content/delegatedsection';
    }

}
