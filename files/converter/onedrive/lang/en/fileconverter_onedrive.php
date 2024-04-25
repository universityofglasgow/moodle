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
 * Strings for plugin 'fileconverter_onedrive'
 *
 * @package   fileconverter_onedrive
 * @copyright 2018 University of Nottingham
 * @author    Neill Magill <neill.magill@nottingham.ac.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Microsoft OneDrive';
$string['disabled'] = 'Disabled';
$string['downloadfailed'] = 'Moodle could not download the converted file from Microsoft.';
$string['issuer'] = 'OAuth 2 service';
$string['issuer_help'] = 'The OAuth 2 service used to access Microsoft OneDrive.';
$string['nodownloadurl'] = 'The Microsoft Graph conversion API did not send a URL to the converted document';
$string['privacy:metadata:fileconverter_onedrive:externalpurpose'] = 'This information is sent to Microsoft OneDrive API in order the file to be converted to an alternative format. The file is temporarily kept on Microsoft OneDrive Drive and gets deleted after the conversion is done.';
$string['privacy:metadata:fileconverter_onedrive:filecontent'] = 'The content of the file.';
$string['privacy:metadata:fileconverter_onedrive:params'] = 'The query parameters passed to Microsoft OneDrive API.';
$string['test_converter'] = 'Test this converter is working properly.';
$string['test_conversion'] = 'Test document conversion';
$string['test_conversionready'] = 'This document converter is configured properly.';
$string['test_conversionnotready'] = 'This document converter is not configured properly.';
$string['test_issuerinvalid'] = 'The OAuth service in the document converter settings is set to an invalid value.';
$string['test_issuernotenabled'] = 'The OAuth service set in the document converter settings is not enabled.';
$string['test_issuernotconnected'] = 'The OAuth service set in the document converter settings does not have a system account connected.';
$string['test_issuernotset'] = 'The OAuth service needs to be set in the document converter settings.';
$string['uploadfailed'] = 'The file was not uploaded to Microsoft OneDrive.';
$string['uploadprepfailed'] = 'An upload session could not be created.';
$string['missingfileextension'] = 'The file to be converted does not seem to have an extension at the end of its name.';
$string['missinguploadid'] = 'Upload attempt failed. There was no upload Id present in the response of the upload REST call.';
$string['chunkfileopenfail'] = 'Unable to open file to enable chunking.';
$string['remotedeletefailed'] = 'Failed to delete remote file in OneDrive because {$a}.';
$string['conversionrequestfailed'] = 'Request to convert file in OneDrive failed because {$a}.';
$string['conversionfailed'] = 'Conversion test failed: {$a}';
