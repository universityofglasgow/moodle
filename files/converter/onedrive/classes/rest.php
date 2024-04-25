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
 * Microsoft OneDrive Rest API.
 *
 * @package    fileconverter_onedrive
 * @copyright  2018 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace fileconverter_onedrive;

/**
 * Microsoft OneDrive Rest API.
 *
 * @copyright  2018 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rest extends \core\oauth2\rest {
    /** The API root for REST calls. */
    const API = 'https://graph.microsoft.com/v1.0';

    /** List of permissions needed by the user to access the web services. */
    const SCOPES = 'Files.ReadWrite.All';

    /**
     * Define the functions of the rest API.
     *
     * @return array Example:
     *  [ 'listFiles' => [ 'method' => 'get', 'endpoint' => 'http://...', 'args' => [ 'folder' => PARAM_STRING ] ] ]
     */
    public function get_api_functions() {
        return [
            // See https://docs.microsoft.com/en-gb/graph/api/driveitem-get-content-format?view=graph-rest-1.0 for API doc.
            'convert' => [
                'endpoint' => self::API . '/me/drive/items/{itemid}/content',
                'method' => 'get',
                'args' => [
                    'itemid' => PARAM_RAW,
                    'format' => PARAM_ALPHANUM,
                ],
                'response' => 'headers'
            ],
            // See: https://docs.microsoft.com/en-gb/graph/api/driveitem-delete?view=graph-rest-1.0 for API doc.
            'delete' => [
                'endpoint' => self::API . '/me/drive/items/{itemid}',
                'method' => 'delete',
                'args' => [
                    'itemid' => PARAM_RAW,
                ],
                'response' => 'headers'
            ],
            // See: https://docs.microsoft.com/en-us/graph/api/driveitem-createuploadsession?view=graph-rest-1.0 for API doc.
            'create_upload' => [
                'endpoint' => self::API . '/me/drive/items/root:/{filename}:/createUploadSession',
                'method' => 'post',
                'args' => [
                    'filename' => PARAM_RAW,
                ],
                'response' => 'json'
            ],
        ];
    }
}
