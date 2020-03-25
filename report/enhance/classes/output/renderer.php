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
 * course_overview block rendrer
 *
 * @package    block_course_overview
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_enhance\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

/**
 * Course_overview block rendrer
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render list of enhancement requests
     * @param elist $elist
     */
    public function render_elist(elist $elist) {
        return $this->render_from_template('report_enhance/elist', $elist->export_for_template($this));
    }

    /**
     * Render edit/new enhancement
     * @param elist $elist
     */
    public function render_edit(edit $edit) {
        return $this->render_from_template('report_enhance/edit', $edit->export_for_template($this));
    }

    /**
     * Render 'more' page
     * @param more $more
     */
     public function render_more(more $more) {
         return $this->render_from_template('report_enhance/more', $more->export_for_template($this));
     }

    /**
     * Render 'voters' page
     * @param voters $voters
     */
     public function render_voters(voters $voters) {
         return $this->render_from_template('report_enhance/voters', $voters->export_for_template($this));
     }

    /**
     * Review  enhancement
     * @param review $review
     */
    public function render_review(review $review) {
        return $this->render_from_template('report_enhance/review', $review->export_for_template($this));
    }
}   
