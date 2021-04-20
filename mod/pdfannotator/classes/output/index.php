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
 * The purpose of this script is to collect the output data for the index.mustache template
 * and make it available to the renderer. The data is collected via the pdfannotator model
 * and then processed. Therefore, class teacheroverview can be seen as a view controller.
 *
 * @package   mod_pdfannotator
 * @copyright 2018 RWTH Aachen (see README.md)
 * @author    Anna Heynkes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Description of index
 *
 * @author degroot
 */
defined('MOODLE_INTERNAL') || die();

class index implements renderable, templatable { // Class should be placed elsewhere.

    private $usestudenttextbox;
    private $usestudentdrawing;
    private $useprint;
    private $useprintcomments;
    private $printurl;

    public function __construct($pdfannotator, $capabilities, $file) {

        global $CFG, $USER;

        $this->usestudenttextbox = ($pdfannotator->use_studenttextbox || $capabilities->usetextbox);
        $this->usestudentdrawing = ($pdfannotator->use_studentdrawing || $capabilities->usedrawing);
        $this->useprint = ($pdfannotator->useprint || $capabilities->useprint);
        $this->useprintcomments = ($pdfannotator->useprintcomments || $capabilities->useprintcomments);

        $contextid = $file->get_contextid();
        $component = $file->get_component();
        $filearea = $file->get_filearea();
        $itemid = $file->get_itemid();
        $filename = $file->get_filename();

        $this->printurl = "$CFG->wwwroot/pluginfile.php/$contextid/$component/$filearea/$itemid/$filename?forcedownload=1";

    }

    public function export_for_template(renderer_base $output) {
        global $OUTPUT, $PAGE;
        $url = $PAGE->url;
        $data = new stdClass();
        $data->usestudenttextbox = $this->usestudenttextbox;
        $data->usestudentdrawing = $this->usestudentdrawing;
        $data->pixhide = $OUTPUT->image_url('/e/accessibility_checker');
        $data->pixopenbook = $OUTPUT->image_url('openbook', 'mod_pdfannotator');
        $data->pixsinglefile = $OUTPUT->image_url('/e/new_document');
        $data->useprint = $this->useprint;
        $data->useprintcomments = $this->useprintcomments;
        $data->printlink = $this->printurl;
        $data->pixprintdoc = $OUTPUT->image_url('download', 'mod_pdfannotator');
        $data->pixprintcomments = $OUTPUT->image_url('print_comments', 'mod_pdfannotator');

        return $data;
    }
}
