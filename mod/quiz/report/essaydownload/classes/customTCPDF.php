<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace quiz_essaydownload;

use pdf;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/pdflib.php');

/**
 * Override TCPDF in order to have a custom footer that includes the page number.
 *
 * @package    quiz_essaydownload
 * @copyright  2024 Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customTCPDF extends pdf {
    /** @var int footer's distance from bottom of page in millimeters */
    const FOOTER_POSITION = 15;

    // @codingStandardsIgnoreLine
    public function Footer() {
        $this->SetY(-self::FOOTER_POSITION);

        // We cannot use getAliasNumPage(), because there seems to be a bug in TCPDF that will cause the
        // footer to be badly centered. The same is true for getAliasNbPages(), but we don't need that
        // here.
        if (empty($this->pagegroups)) {
            $pageno = $this->PageNo();
        } else {
            $pageno = $this->getGroupPageNo();
        }

        $this->Cell(0, 10, get_string('pagenumber', 'quiz_essaydownload', $pageno), 'T', 0, 'C');
    }
}
