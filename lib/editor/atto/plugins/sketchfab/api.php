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
 * Atto text editor integration API file.
 *
 * @package    atto_sketchfab
 * @copyright  2015 Jetha Chan <jetha@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../../config.php');
require_once($CFG->libdir . '/filelib.php');

defined('MOODLE_INTERNAL') || die();

require_login();

$modelid = required_param('modelid', PARAM_RAW);

// Create a new instance of the Moodle cURL class to use.
$curl = new curl();

// Build a Sketchfab embed.
// - make a curl request to get metadata.
$metadata = $curl->get(
    'https://sketchfab.com/oembed',
    array(
        'url' => 'https://sketchfab.com/models/' . $modelid
    )
);

header('Content-Type: application/json; charset: utf-8', true, $curl->info['http_code']);
echo $metadata;