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
 * Main class for menu listing
 *
 * @package    report_enhance
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_guenrol\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use renderer_base;
use templatable;

/**
 * Class contains data for report_guenrol list
 *
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userlist implements renderable, templatable {

    protected $course;

    protected $users;

    protected $heading;

    /**
     * Constructor
     */
    public function __construct($course, $users, $heading) {
        $this->course = $course;
        $this->users = $users;
        $this->heading = $heading;
    }

    /**
     * Export data for template
     */
    public function export_for_template(renderer_base $output) {

        return [
            'courseid' => $this->course->id,
            'heading' => $this->heading,
            'isusers' => !empty($this->users),
            'users' => array_values($this->users),
        ];
    }

}
