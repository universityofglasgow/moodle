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
 * Contains the sectionnavigation class.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_tiles\output\courseformat\content;

use core_courseformat\base as course_format;
use core_courseformat\output\local\content\sectionnavigation as sectionnavigation_base;

/**
 * Class to render a course add section navigation inside a Tiles format course.
 *
 * @package   format_tiles
 * @copyright 2022 David Watson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sectionnavigation extends sectionnavigation_base {

    /**
     * Constructor.
     * @param course_format $format
     * @param int $sectionno
     */
    public function __construct(course_format $format, int $sectionno) {
        parent::__construct($format, $sectionno);
    }
}
