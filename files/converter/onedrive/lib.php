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
 * This plugin is used to convert documents with Microsoft Onedrive.
 *
 * @package    fileconverter_onedrive
 * @copyright  2018 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Callback to get the required scopes for system account.
 *
 * @param \core\oauth2\issuer $issuer
 * @return string
 */
function fileconverter_onedrive_oauth2_system_scopes(\core\oauth2\issuer $issuer) {
    if ($issuer->get('id') == get_config('fileconverter_onedrive', 'issuerid')) {
        return \fileconverter_onedrive\rest::SCOPES;
    }
    return '';
}
