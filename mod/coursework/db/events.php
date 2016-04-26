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
 * Event handlers. Mostly for dealing with auto allocation of markers.
 *
 * @package    mod
 * @subpackage coursework
 * @copyright  2012 University of London Computer Centre {@link ulcc.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$handlers = array(
    'role_assigned' => array(
        'handlerfile' => '/mod/coursework/lib.php',
        'handlerfunction' => 'coursework_role_assigned_event_handler',
        'schedule' => 'instant'
    ),
    'role_unassigned' => array(
        'handlerfile' => '/mod/coursework/lib.php',
        'handlerfunction' => 'coursework_role_unassigned_event_handler',
        'schedule' => 'instant'
    ),
    'coursework_deadline_changed' => array(
        'handlerfile' => '/mod/coursework/lib.php',
        'handlerfunction' => 'coursework_send_deadline_changed_emails',
        'schedule' => 'cron'
    ),
    'mod_updated' => array(
        'handlerfile' => '/mod/coursework/lib.php',
        'handlerfunction' => 'coursework_mod_updated',
        'schedule' => 'instant'
    ),
    'mod_created' => array(
        'handlerfile' => '/mod/coursework/lib.php',
        'handlerfunction' => 'coursework_mod_updated',
        'schedule' => 'instant'
    )
);