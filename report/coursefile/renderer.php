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
 * This file contains functions used by the participation report
 *
 * @package    report
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class report_coursefile_renderer extends plugin_renderer_base {

    private function human_filesize($bytes, $decimals = 2) {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    public function filetable($files) {
        $table = new html_table();
        $table->head = array(
            get_string('filename', 'report_coursefile'),
            get_string('filesize', 'report_coursefile'),
            get_string('author', 'report_coursefile'),
        );

        foreach ($files as $file) {
            $line = array(
                $file->filename,
                $this->human_filesize($file->filesize),
                $file->author,
            );
            $table->data[] = $line;
        }
        echo html_writer::table($table);
    }

}
