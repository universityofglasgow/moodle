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

class formation_form extends moodleform {

    public function definition() {
        global $CFG, $COURSE;

        $mform =& $this->_form;

        $att = 'size=40';

        // Hidden stuff.
        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'form', 'formation');
        $mform->setType('form', PARAM_ALPHA);

        // Form elements.
        $mform->addElement('static', 'wi', '', "<h3>National Federation of Women's Institutes");
        $mform->addElement('static', 'formation', '', '<h3>Notice of Formation of WI</h3>');

        $mform->addElement('text', 'federation', 'Federation', $att);
        $mform->addRule('federation', null, 'required', null, 'client');
        $mform->setType('federation', PARAM_TEXT);

        $mform->addElement('text', 'nameofwi', "Name of WI", $att);
        $mform->addRule('nameofwi', null, 'required', null, 'client');
        $mform->setType('nameofwi', PARAM_TEXT);

        $mform->addElement('date_selector', 'dateoffirstmeeting', 'Date of first meeting');

        $mform->addElement('date_selector', 'officialformationdate', 'Official formation date');

        $mform->addElement('text', 'place', "Workplace/University/Urban/Other", $att);
        $mform->addRule('place', null, 'required', null, 'client');
        $mform->setType('place', PARAM_TEXT);

        $mform->addElement('text', 'secretary', 'WI Adviser/Federation Secretary (Mrs/Miss)', $att);
        $mform->addRule('secretary', null, 'required', null, 'client');
        $mform->setType('secretary', PARAM_TEXT);

        $mform->addElement('text', 'address1', 'Address (line 1)', $att);
        $mform->addRule('address1', null, 'required', null, 'client');
        $mform->setType('address1', PARAM_TEXT);

        $mform->addElement('text', 'address2', 'Address (line 2)', $att);
        $mform->setType('address2', PARAM_TEXT);

        $this->add_action_buttons(true, 'Send');
    }

    public function format_html( $data ) {
        $html = "<h3>National Federation of Women's Institutes</h3>";
        $html .= "<h3>Notice of Formation of a WI</h3>\n";
        $html .= '<table cellspacing="0" cellpadding="5" >';
        $html .= "<tr><th>Federation:</th><td>{$data->federation}</td></tr>\n";
        $html .= "<tr><th>Name of WI:</th><td>{$data->nameofwi}</td></tr>\n";
        $html .= "<tr><th>Date of first meeting:</th><td>" . userdate($data->dateoffirstmeeting, '%A, %e %B %G') . "</td></tr>\n";
        $html .= "<tr><th>Official formation date:</th><td>" .
            userdate($data->officialformationdate, '%A, %e %B %G') . "</td></tr>\n";
        $html .= "<tr><th>Workplace/University/Urban/Other:</th><td>{$data->place}</td></tr>\n";
        $html .= "<tr><th>WI Adviser/Federation Secretary:</th><td>{$data->secretary}</td></tr>\n";
        $html .= "<tr><th>Address (line 1):</th><td>{$data->address1}</td></tr>\n";
        $html .= "<tr><th>Address (line 2):</th><td>{$data->address2}</td></tr>\n";
        $html .= "</table>\n";

        return $html;
    }

}
