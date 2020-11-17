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
 * @package     qtype_kprime
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @author      Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author      Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @copyright   2014 eDaktik GmbH {@link http://www.edaktik.at}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('QTYPE_KPRIME_NUMBER_OF_OPTIONS', 4);
define('QTYPE_KPRIME_NUMBER_OF_RESPONSES', 2);

/**
 * Checks file/image access for kprime questions.
 *
 * @category files
 *
 * @param stdClass $course        course object
 * @param stdClass $cm            course module object
 * @param stdClass $context       context object
 * @param string   $filearea      file area
 * @param array    $args          extra arguments
 * @param bool     $forcedownload whether or not force download
 * @param array    $options       additional options affecting the file serving
 *
 * @return bool
 */
function qtype_kprime_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload,
        array $options = array()) {
    global $CFG;
    require_once($CFG->libdir.'/questionlib.php');
    question_pluginfile($course, $context, 'qtype_kprime', $filearea, $args, $forcedownload,
    $options);
}
