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
 * Main class for course listing
 *
 * @package    report_guid
 * @copyright  2019 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_guid\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use renderer_base;
use templatable;
use context;
use context_course;

/**
 * Class contains data for report_enhance elist
 *
 * @copyright  2018 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ldaplist implements renderable, templatable {

    protected $results;

    protected $users;

    /**
     * Constructor
     * @param array $results ldap search results
     * @param array $users existing Moodle users
     */
    public function __construct($results, $users) {
        $this->results = $results;
        $this->users = $users;
    }

    /**
     * Format ldap data for display
     * @return array formatted data
     */
    protected function format_results() {

        
    }

    public function export_for_template(renderer_base $output) {
        return [
            'ldapresultsempty' => empty($this->results),
            'toomanyldapresults' => count($this->results) > MAXIMUM_RESULTS,
            'results' => $this->results,
        ];
    }
}