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
 * Student Dashboard - Assessments Details web service definitions.
 *
 * @package    block_gu_spdetails
 * @copyright  2021 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$functions = array(
    'block_gu_spdetails_retrieve_assessments' => array(
        'classname'   => 'block_gu_spdetails\external',
        'methodname'  => 'retrieve_assessments',
        'description' => 'Display assessments on Student Dashboard',
        'type'        => 'read',
        'ajax'          => true,
        'loginrequired' => true,
    ),
);
