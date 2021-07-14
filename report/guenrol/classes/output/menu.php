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
 * Class contains data for report_guenrol menu
 *
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class menu implements renderable, templatable {

    protected $course;

    protected $codes;


    protected $lastupdate;

    /**
     * Constructor
     */
    public function __construct($course, $codes, $lastupdate) {
        $this->course = $course;
        $this->codes = $codes;
        $this->lastupdate = $lastupdate;
    }

    /**
     * Get number of instances of a particular code
     * @param string $countcode
     * @return int
     */
    protected function code_count($countcode) {
        $count = 0;
        foreach ($this->codes as $code) {
            if ($code->code == $countcode) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Add links and stuff to the list of codes.
     * @return array formatted array
     */
    protected function format_codes() {
        $newcodes = $this->codes;
        foreach ($newcodes as $code) {
            if ($code->location) {
                if ($code->location == 'plugin') {
                    $link = new \moodle_url('/enrol/editinstance.php', ['courseid' => $this->course->id, 'id' => $code->instanceid, 'type' => 'gudatabase']);
                    $code->formattedlocation = '<a class="btn btn-sm btn-info" href="' . $link . '">' . get_string('plugin', 'report_guenrol') . '</a>';
                } else {
                    $link = new \moodle_url('/course/edit.php', ['id' => $this->course->id]);
                    $code->formattedlocation = '<a class="btn btn-sm btn-info" href="' . $link . '">' . get_string($code->location, 'report_guenrol') . '</a>';
                }
            } else {
                $code->formattedlocation = get_string('locationnotdefined', 'report_guenrol');
            }
            $code->duplicate = $this->code_count($code->code) > 1;
            $morelink = new \moodle_url('/report/guenrol/index.php', ['id' => $this->course->id, 'codeid' => $code->id]);
            $code->more = '<a class="btn btn-sm btn-success" href="' . $morelink . '">' . get_string('more', 'report_guenrol'). '</a>';
        }

        return array_values($newcodes);
    }

    /**
     * Export data for list of enhancements
     */
    public function export_for_template(renderer_base $output) {

        $showall = count($this->codes) > 1 ? new \moodle_url('/report/guenrol/index.php', ['id' => $this->course->id, 'action' => 'showall']) : '';

        return [
            'courseid' => $this->course->id,
            'iscodes' => !empty($this->codes),
            'codes' => $this->format_codes($this->codes),
            'visible' => $this->course->visible,
            'afterenddate' => $this->course->enddate && (time() > $this->course->enddate),
            'beforestartdate' => time() < $this->course->startdate,
            'lastupdate' => $this->lastupdate,
            'showall' => $showall,
            'linksync' => new \moodle_url('/report/guenrol/index.php', ['id' => $this->course->id, 'action' => 'sync']),
            'linkremove' => new \moodle_url('/report/guenrol/index.php', ['id' => $this->course->id, 'action' => 'remove']),
            'linkrevert' => new \moodle_url('/report/guenrol/index.php', ['id' => $this->course->id, 'action' => 'revert']),
        ];
    }

}
