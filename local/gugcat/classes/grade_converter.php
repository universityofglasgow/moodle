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
 * Class containing helper methods for Grade Convertion page.
 * 
 * @package    local_gugcat
 * @copyright  2020x
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_gugcat;

defined('MOODLE_INTERNAL') || die();
require_once('gcat_item.php');

class grade_converter{

    public static function save_grade_converter($modid, $scale, $grades){
        global $DB;

        $DB->insert_records('gcat_grade_converter', $grades);
        $DB->set_field('grade_items', 'iteminfo', $scale, array('id'=>$modid));
    }

}