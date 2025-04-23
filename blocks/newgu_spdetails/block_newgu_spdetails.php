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
 * Block visits reports base.
 *
 * @package    block_newgu_spdetails
 * @author     Shubhendra Diophode <shubhendra.doiphode@gmail.com>
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Block class for plugin.
 */
class block_newgu_spdetails extends block_base {

    /**
     * Initialize block instance.
     *
     * @return void
     * @throws coding_exception
     */
    public function init(): void {
        $this->title = get_string('blocktitle', 'block_newgu_spdetails');
    }

    /**
     * This block supports configuration fields.
     *
     * @return bool
     */
    public function has_config(): bool {
        return true;
    }

    /**
     * List of links to access the reports displayed on the blocks.
     *
     * @return object $content
     * @throws dml_exception
     */
    public function get_content(): object {
        global $USER, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new \stdClass();

        $viewurl = new moodle_url('/blocks/newgu_spdetails/index.php');
        $this->content->text = $OUTPUT->render_from_template('block_newgu_spdetails/block', [
            'link' => $viewurl,
        ]);

        $this->page->requires->js_call_amd('block_newgu_spdetails/assessmentsummary', 'init');

        return $this->content;
    }

    /**
     * We no longer have Student MyGrades appearing as an option in the "Add a block" menu.
     *
     * @return array
     */
    public function applicable_formats(): array {
        return [
            'admin' => true,
        ];
    }
}
