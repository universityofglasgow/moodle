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
 * class for voters list
 *
 * @package    report_enhance
 * @copyright  2019 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_enhance\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use renderer_base;
use templatable;

/**
 * Class contains data for report_enhance votes
 *
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class voters implements renderable, templatable {

    private $courseid;

    private $request;

    private $voters;

    /**
     * Constructor
     */
    public function __construct($courseid, $request, $voters) {
        $this->courseid = $courseid;
        $this->request = $request;
        $this->voters = $voters;
    }

    /** 
     * Export data for list of enhancements
     */
    public function export_for_template(renderer_base $output) {
        global $USER, $DB;

        foreach ($this->voters as $voter) {
            $user = $DB->get_record('user', ['id' => $voter->userid]);
            $voter->userpicture = $output->user_picture($user, ['size' => 32, 'alttext' => false]);
            $voter->userlink = new \moodle_url('/user/view.php', ['id' => $voter->userid]);
            $voter->fullname = fullname($user);
        }

        return [
            'voters' => array_values($this->voters),
            'request' => $this->request,
            'back' => new \moodle_url('/report/enhance/more.php', ['courseid' => $this->courseid, 'id' => $this->request->id]),
        ];
    }

}

