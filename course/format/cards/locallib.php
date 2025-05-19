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
 * Cards format local plugin functions
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Removes any card images in the files table that are for sections in courses
 * that either no longer exist, or don't use the cards format
 *
 * @param progress_bar|null $progress
 * @return void
 * @throws dml_exception
 * @throws moodle_exception
 */
function format_cards_tidy_orphaned_images(?progress_bar $progress = null): void {
    global $DB;

    require_once(__DIR__ . "/lib.php");

    $filestorage = get_file_storage();

    $filerecords = $DB->get_records(
        'files',
        [
            'component' => 'format_cards',
            'filearea' => FORMAT_CARDS_FILEAREA_IMAGE,
        ]
    );

    $filerecords = array_map(
        function($record) use ($filestorage) {
            return new stored_file($filestorage, $record);
        },
        $filerecords
    );

    $i = 0;
    $count = count($filerecords);
    if (!is_null($progress)) {
        $progress->update($i, $count, "");
    }

    foreach ($filerecords as $record) {
        $section = $DB->get_record('course_sections', [ 'id' => $record->get_itemid() ]);

        // If the section doesn't exist, delete the image.
        if (!$section) {
            $record->delete();
            $progress->update(++$i, $count, "Removed image for deleted section " . $record->get_itemid());
            continue;
        }

        // Is the course still format_cards?
        $format = $DB->get_field('course', 'format', [ 'id' => $section->course ]);
        if ($format != 'cards') {
            $record->delete();
            $progress->update(++$i, $count, "Removed image for other course format " . $record->get_itemid());
            continue;
        }

        $progress->update(++$i, $count, "");
    }
}
