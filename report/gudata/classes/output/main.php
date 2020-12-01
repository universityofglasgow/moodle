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
 * Main class for selection tabs
 *
 * @package    report_gudata
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_gudata\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use renderer_base;
use templatable;
use context;
use context_course;

/**
 * Class contains data for report_gudata main page
 *
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    private $baseurl;

    private $course;

    private $action;

    private $form;

    public function __construct($baseurl, $course, $action, $form) {
        $this->baseurl = $baseurl;
        $this->course = $course;
        $this->action = $action;
        $this->form = $form;
    }

    public function export_for_template(renderer_base $output) {
        return [
            'userdownloadlink' => new \moodle_url($this->baseurl, ['id' => $this->course->id, 'action' => 'userdownload'] ),
            'logsdownloadlink' => new \moodle_url($this->baseurl, ['id' => $this->course->id, 'action' => 'logsdownload'] ),
            'isuserdownload' => $this->action == 'userdownload',
            'islogsdownload' => $this->action == 'logsdownload',
            'form' => $this->form->render(),
        ];
    }

}