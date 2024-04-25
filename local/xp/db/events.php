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
 * Messages.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Note that this is a simple implementation of events in local_xp, but there is
// an advanced mechanism (observer_rules_maker) that we could use to override, or
// extend the events from block_xp. We could change this in the future so that
// the course deletion hook (to purge data) is called from block_xp, or from
// an extension of its observer.
$observers = [
    [
        'eventname' => '\\core\\event\\course_deleted',
        'callback' => 'local_xp\\local\\observer\\observer::course_deleted'
    ]
];
