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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . "/../../lib.php");

/**
 * Backs up courses that use the cards format. Ensures that card images are included in the backup
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_format_cards_plugin extends backup_format_plugin {

    /**
     * Defines the backup structure for format_cards
     *
     * @return backup_plugin_element
     * @throws base_element_struct_exception
     */
    protected function define_section_plugin_structure(): backup_plugin_element {
        $parent = $this->get_plugin_element(null, $this->get_format_condition(), 'cards');

        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Create a nested element under each backed up section, this is just a dummy container.
        $imageswrapper = new backup_nested_element(
            'cardimage',
            [ 'id' ],
            [ 'contenthash', 'pathnamehash', 'filename', 'mimetype' ]
        );
        $imageswrapper->set_source_table(
            'files',
            [
                'itemid' => backup::VAR_SECTIONID,
                'component' => backup_helper::is_sqlparam('format_cards'),
                'filearea' => backup_helper::is_sqlparam(FORMAT_CARDS_FILEAREA_IMAGE),
            ]);

        // Annotate files in the format_cards/image filearea for this course's context ID
        // The itemid doesn't get mapped to the new section id, if it changes.
        $imageswrapper->annotate_files(
            'format_cards',
            FORMAT_CARDS_FILEAREA_IMAGE,
            null
        );

        $pluginwrapper->add_child($imageswrapper);

        // We need a wrapper for section breaks as well.
        $sectionbreakwrapper = new backup_nested_element('sectionbreak', [ 'id' ], [ 'name', 'section' ]);

        // We store section information as courseid / section number, so we need to map the section ID we're backing
        // up to a section number.
        $sectionbreakwrapper->set_source_sql("
SELECT section_break.id, section_break.name, section_break.section
FROM {format_cards_break} section_break
JOIN {course_sections} section
    ON section.course = section_break.courseid
    AND section.section = section_break.section
WHERE section.id = ?",
        [ backup::VAR_SECTIONID ]);

        $pluginwrapper->add_child($sectionbreakwrapper);

        $parent->add_child($pluginwrapper);

        return $parent;
    }

}
