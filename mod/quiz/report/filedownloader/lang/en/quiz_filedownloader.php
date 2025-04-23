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
 * Quiz Filedownloader report version information.
 *
 * @package   quiz_filedownloader
 * @copyright 2019 ETH Zurich
 * @author    Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['adminsetting_accepted_qtypes'] = 'Accepted question types';
$string['adminsetting_accepted_qtypes_help'] = 'Determines which question types are included in the download.';
$string['adminsetting_accepted_qtypefileareas'] = 'Fileareas';
$string['adminsetting_accepted_qtypefileareas_help'] = 'Each question type stated above must be assigned with its filearea-value.';
$string['adminsetting_anonymizedownload'] = 'Anonymize download';
$string['adminsetting_anonymizedownload_help'] = 'Anonymizes user information within downloaded files.';
$string['adminsetting_choosefilestructure'] = 'Filestructure chooseable';
$string['adminsetting_choosefilestructure_help'] = 'Teachers can choose to download all files into one folder instead of each file into a seperate folder for student and attempt number. Note: If all files are downloaded into one folder, this will change the names of the submitted files.';
$string['adminsetting_chooseanonymize'] = 'Anonymization chooseable';
$string['adminsetting_chooseanonymize_help'] = 'Teachers can choose whether downloaded data will be anonymized.';
$string['download'] = 'Download';
$string['downloadsettings'] = 'Downloadsettings';
$string['eventupdate_log'] = 'Quiz file submissions have been downloaded';
$string['filedownloader'] = 'Download essay attachments';
$string['filedownloaderreport'] = 'Quiz Filedownloader';
$string['no'] = 'No';
$string['pluginname'] = 'Quiz Filedownloader';
$string['plugindescription'] = 'Downloads the files that were submitted to the quiz as responses.<br>';
$string['privacy:metadata'] = 'The Filedownloader plugin does not store any personal data.';
$string['response_invalidfilearea'] = 'In the plugin preferences an invalid filearea-value is set for following questiontypes:<br>';
$string['response_noattempts'] = 'There are no evaluable attempts in this quiz.';
$string['response_noconfigfileareas'] = 'The number of question types does not match the number of filearea-values within the plugin preferences. A filearea-value must be assigned to each question type.';
$string['response_noconfigqtypes'] = 'Either no question types have been specified in the plugin presets, or the quiz does not include any of the specified question types.';
$string['response_nofilearea'] = 'In the plugin preferences no filearea-value is set for following question types:<br>';
$string['response_nofiles'] = 'No files were downloaded.';
$string['response_noquestions'] = 'The quiz does not contain questions.';
$string['response_nosuchqtype'] = 'The following question types are not installed or disabled on the system and are not included in the download: <br>';
$string['texfile_anonymized'] = '-anonymized- ';
$string['textfile_notavailable'] = '-not available-';
$string['yes'] = 'Yes';
$string['zip_inonefolder'] = 'Download files into a single folder for each question';
$string['zip_inonefolder_help'] = 'For each question submitted files will be stored together in a single folder.<br> No additional subfolders for students and attempts will be created.<br><b>(not recommended for summative exams)</b>';
