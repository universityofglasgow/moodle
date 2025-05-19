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
 * Renders a section break.
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_cards\output\courseformat\content\section;

use cache;
use cache_store;
use core\output\inplace_editable;
use core\output\named_templatable;
use core_courseformat\base as course_format;
use format_cards\section_break;
use lang_string;
use moodle_exception;
use moodle_url;
use renderable;
use renderer_base;
use section_info;

/**
 * Renders a section break.
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sectionbreak extends inplace_editable implements named_templatable, renderable {

    /**
     * @var course_format The course format
     */
    protected $format;

    /**
     * @var section_info Section info
     */
    private $section;

    /**
     * @var bool|null If the break is editable
     */
    protected $editable;

    /**
     * @var bool Whether there is a section break
     */
    protected bool $hasbreak;

    /**
     * @var string The section break's title
     */
    protected string $breaktitle;

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     * @param bool|null $editable force editable value
     */
    public function __construct(
        course_format $format,
        section_info $section,
        ?bool $editable = null
    ) {
        $this->format = $format;
        $this->section = $section;

        if ($editable === null) {
            $editable = $format->show_editor();
        }
        $this->editable = $editable;

        $break = self::get_section_break($section);

        $this->hasbreak = !is_null($break);
        $this->breaktitle = is_null($break) ? '' : $break->get('name');
        $displayvalue = $this->breaktitle;

        if ($this->editable && empty($this->breaktitle)) {
            $displayvalue = get_string('section:break:marker', 'format_cards');
        }

        // Setup inplace editable.
        parent::__construct(
            'format_cards',
            'sectionbreak',
            $section->id,
            $this->editable,
            $displayvalue,
            $this->breaktitle,
            new lang_string('section:break:edit', 'format_cards'),
            new lang_string('section:break', 'format_cards')
        );
    }

    /**
     * Renderable template name
     *
     * @param renderer_base $renderer
     * @return string
     */
    public function get_template_name(renderer_base $renderer): string {
        return 'core/inplace_editable';
    }

    /**
     * Export template context.
     *
     * @param renderer_base $output
     * @return array|boolean
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        if (!$this->hasbreak) {
            return false;
        }

        $data = parent::export_for_template($output);
        $data['id'] = $this->section->id;
        $data['deletebreakurl'] = (new moodle_url(
            '/course/format/cards/editsectionbreak.php',
            [
                'courseid' => $this->section->course,
                'sectionid' => $this->section->id,
                'action' => 'remove',
            ]
        ))->out(false);
        return $data;
    }

    /**
     * Get information about the section break for a given section, or null if not available
     *
     * @param section_info $info Section info
     * @return section_break|null
     */
    private static function get_section_break(section_info $info): ?section_break {
        $cache = cache::make_from_params(
            cache_store::MODE_APPLICATION,
            'format_cards',
            'section_breaks'
        );

        if (($breaks = $cache->get($info->course)) === false) {
            $breaks = section_break::get_breaks_for_course($info->course);
            $cache->set($info->course, $breaks);
        }

        foreach ($breaks as $break) {
            if ($break->get('section') === $info->section) {
                return $break;
            }
        }

        return null;
    }

}
