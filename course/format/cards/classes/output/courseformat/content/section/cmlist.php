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

namespace format_cards\output\courseformat\content\section;

use core\output\renderer_base;
use core_courseformat\output\local\content\section\cmlist as cmlist_base;
use html_writer;
use stdClass;

/**
 * Renders a list of course modules within a section.
 *
 * @package   format_cards
 * @copyright 2025 University of Essex
 * @author    John Maydew <jdmayd@essex.ac.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cmlist extends cmlist_base {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {
        global $CFG;

        // Use default behaviour for versions before Moodle 4.5, or in editing mode.
        if ($CFG->version < 2024100700 || $this->format->show_editor()) {
            return parent::export_for_template($output);
        }

        // If we've chosen to display subsections as activities, don't do anything else.
        if ($this->format->get_format_option('subsectionsascards') == FORMAT_CARDS_SUBSECTIONS_AS_ACTIVITIES) {
            return parent::export_for_template($output);
        }

        // If we're displaying subsections as cards we want to aggregate consecutive subsections together into a
        // single cmitem. This way they can be rendered together in the same card deck, otherwise all the cards
        // are rendered vertically.
        // To do this, we can iterate through the exported cms, pick out any subsections, and then merge consecutive
        // subsections together.
        $data = parent::export_for_template($output);

        $cmcount = count($data->cms);
        $subsectionindex = -1;

        $foundsubsections = [];

        for ($i = 0; $i < $cmcount; $i++) {

            $cm = $data->cms[$i];

            // We only care about subsection modules.
            if (!object_property_exists($cm, 'cmitem')
                || !object_property_exists($cm->cmitem, 'module')
                || $cm->cmitem->module !== 'subsection') {
                $subsectionindex = -1;
                continue;
            }

            // We've found a new subsection. Make a note of it's position and continue to the next cm in the list.
            if ($subsectionindex === -1) {
                $subsectionindex = $i;
                $foundsubsections[] = $i;
                continue;
            }

            // At this point, we're looking at a subsection, and we have an index for another subsection that appears
            // before it, Take the altcontent for this subsection, append it to the altcontent of the first subsection
            // in this group, and then remove this item from the list.
            $data->cms[$subsectionindex]->cmitem->cmformat->altcontent .= $cm->cmitem->cmformat->altcontent;
            unset($data->cms[$i]);
        }

        // Return early if there aren't any subsections.
        if (empty($foundsubsections)) {
            return $data;
        }

        // For each of the subsection groups we've found, wrap the cards in a card-deck.
        foreach ($foundsubsections as $index) {
            $data->cms[$index]->cmitem->cmformat->altcontent = html_writer::tag(
                'ul',
                $data->cms[$index]->cmitem->cmformat->altcontent,
                [ 'class' => 'card-deck dashboard-card-deck' ]
            );
        }

        // Array indexes should be sequential.
        $data->cms = array_values($data->cms);

        return $data;
    }
}
