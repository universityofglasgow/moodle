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
 * course_overview block settings
 *
 * @package    block_course_overview
 * @copyright  2012 Adam Olley <adam.olley@netspot.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/locallib.php');

if ($ADMIN->fulltree) {
    $showcategories = array(
        BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE => new lang_string('none', 'block_course_overview'),
        BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_ONLY_PARENT_NAME => new lang_string('onlyparentname', 'block_course_overview'),
        BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_FULL_PATH => new lang_string('fullpath', 'block_course_overview')
    );
    $settings->add(new admin_setting_configselect(
        'block_course_overview/showcategories',
        new lang_string('showcategories', 'block_course_overview'),
        new lang_string('showcategoriesdesc', 'block_course_overview'),
        BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE,
        $showcategories
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_course_overview/keepfavourites',
        new lang_string('keepfavourites', 'block_course_overview'),
        new lang_string('keepfavouritesdesc', 'block_course_overview'),
        0
    ));
}
