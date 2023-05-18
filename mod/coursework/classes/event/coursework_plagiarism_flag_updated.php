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
 * ccoursework_plagiarism_flag_updated
 *
 * @package    mod
 * @subpackage coursework
 * @copyright  2016 University of London Computer Centre {@link ulcc.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursework\event;

defined('MOODLE_INTERNAL') || die();


class coursework_plagiarism_flag_updated extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {

        $this->data['crud'] = 'u'; //One of [crud] letters - indicating 'c'reate, 'r'ead, 'u'pdate or 'd'elete operation. Statically declared in the event class method init().
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'coursework';
    }

}
