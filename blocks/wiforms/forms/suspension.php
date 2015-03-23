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
 * wiforms
 *
 * @package   block
 * @subpackage wiforms
 * @copyright 2013 Howard Miller
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class suspension_form extends moodleform {

    public function definition() {
        global $CFG, $COURSE;

        $mform =& $this->_form;

        $att = 'size=40';

        // Hidden stuff.
        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'form', 'suspension');
        $mform->setType('form', PARAM_ALPHA);

        // Form elements.
        $mform->addElement('static', 'wi', '', "<h3>National Federation of Women's Institutes" );
        $mform->addElement('static', 'suspension', '', '<h3>Notice of Suspension of a WI</h3>');

        $mform->addElement('text', 'federation', 'Federation', $att);
        $mform->addRule('federation', null, 'required', null, 'client');
        $mform->setType('federation', PARAM_TEXT);

        $mform->addElement('text', 'nameofwi', "Name of WI", $att);
        $mform->addRule('nameofwi', null, 'required', null, 'client');
        $mform->setType('nameofwi', PARAM_TEXT);

        $mform->addElement('text', 'reason', "Reason for Suspension", $att);
        $mform->addRule('reason', null, 'required', null, 'client');
        $mform->setType('reason', PARAM_TEXT);

        $mform->addElement('date_selector', 'datesuspension', 'Date of Suspension');

        $mform->addElement('text', 'funds', 'Funds held (if known)', $att);
        $mform->addRule('funds', null, 'required', null, 'client');
        $mform->setType('funds', PARAM_TEXT);

        $mform->addElement('text', 'advisor', 'WI Adviser', $att);
        $mform->addRule('advisor', null, 'required', null, 'client');
        $mform->setType('advisor', PARAM_TEXT);

        $this->add_action_buttons(true, 'Send');
    }

    public function format_html( $data ) {
        $html = "<h3>National Federation of Women's Institutes</h3>";
        $html .= "<h3>Notice of Suspension of a WI</h3>\n";
        $html .= '<table cellspacing="0" cellpadding="5" >';
        $html .= "<tr><th>Federation:</th><td>{$data->federation}</td></tr>\n";
        $html .= "<tr><th>Name of WI:</th><td>{$data->nameofwi}</td></tr>\n";
        $html .= "<tr><th>Reason for Suspension:</th><td>{$data->reason}</td></tr>\n";
        $html .= "<tr><th>Date of Suspension:</th><td>" . userdate($data->datesuspension, '%A, %e %B %G') . "</td></tr>\n";
        $html .= "<tr><th>Funds held (if known):</th><td>{$data->funds}</td></tr>\n";
        $html .= "<tr><th>WI Adviser:</th><td>{$data->advisor}</td></tr>\n";
        $html .= "</table>\n";

        return $html;
    }

}
