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
 * Defined cache used internally by the plugin.
 *
 * @package    block_newgu_spdetails
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2023 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$definitions = [
    'assignmentsduequery' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => false,
        'simpledata' => false,
        'ttl' => 300, // 5 minutes expiry time.
    ],
    'forumduequery' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => false,
        'simpledata' => false,
        'ttl' => 300, // 5 minutes expiry time.
    ],
    'kalvidassignmentsduequery' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => false,
        'simpledata' => false,
        'ttl' => 300, // 5 minutes expiry time.
    ],
    'lessonsduequery' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => false,
        'simpledata' => false,
        'ttl' => 300, // 5 minutes expiry time.
    ],
    'peerworkduequery' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => false,
        'simpledata' => false,
        'ttl' => 300, // 5 minutes expiry time.
    ],
    'quizduequery' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => false,
        'simpledata' => false,
        'ttl' => 300, // 5 minutes expiry time.
    ],
    'scormduequery' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => false,
        'simpledata' => false,
        'ttl' => 300, // 5 minutes expiry time.
    ],
    'studentdashboarddata' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => false,
        'simpledata' => false,
        'ttl' => 300, // 5 minutes expiry time.
    ],
    'workshopduequery' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => false,
        'simpledata' => false,
        'ttl' => 300, // 5 minutes expiry time.
    ],
];
