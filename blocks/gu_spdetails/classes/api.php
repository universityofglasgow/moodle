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
 * Class containing helper methods for processing data requests.
 *
 * @package    block_gu_spdetails
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_gu_spdetails;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/blocks/gu_spdetails/lib.php');
use assessments_details;

class api {
    /**
     * Displays paginated assessments
     *
     * @param string $activetab
     * @param int $page
     * @param string $sortby
     * @param string $sortorder
     * @return string HTML containing view for paginated assessments
     */
    public static function retrieve_assessments($activetab, $page, $sortby, $sortorder) {
        $assessments = assessments_details::retrieve_assessments($activetab, $page, $sortby, $sortorder);
        return $assessments;
    }
}
