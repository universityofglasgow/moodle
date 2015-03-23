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

require_once(dirname(__FILE__).'/../../config.php');
require_once("$CFG->libdir/formslib.php");

// Parameters.
$id = required_param('id', PARAM_INT);
$formname = required_param('form', PARAM_ALPHA);

// Housekeeping.
require_login($id);
$context = context_course::instance($COURSE->id);

// Page stuff.
$url = new moodle_url('/blocks/wiforms/email.php', array('id'=>$id, 'form'=>$formname));
$PAGE->set_url($url);

// Navigation
$PAGE->set_title('title');
$PAGE->set_heading('heading');
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_wiforms'));
$PAGE->navbar->add(get_string('notice'.$formname, 'block_wiforms'));

// Check capability.
require_capability('block/wiforms:access', $context  );

// Load correct form.
$filename = "forms/$formname.php";
if (!file_exists( $filename ) ) {
    print_error( 'noform', 'block_wiforms', '', $filename );
}
require_once( $filename );

// Instantiate the form.
$formclass = "{$formname}_form";
$mform = new  $formclass();

// Was form cancelled?
if ($mform->is_cancelled()) {
    redirect( "{$CFG->wwwroot}/course/view.php?id=$id", get_string('emailcancelled', 'block_wiforms'), 2 );
}

// Was form submitted?
if ($formdata = $mform->get_data()) {
    $html = $mform->format_html( $formdata );

    // Send as email.
    $mailer = get_mailer();
    $mailer->From = $CFG->supportemail;
    $mailer->FromName = $CFG->supportname;
    $mailer->AddAddress( $CFG->block_wiforms_email);
    $mailer->AddReplyTo( $CFG->noreplyaddress );
    $mailer->Subject = $CFG->block_wiforms_subject;
    $mailer->IsHTML(true);
    $mailer->Body = $html;
    if (!$mailer->Send()) {
        print_error( 'mailerror', 'block_wiforms', '', $mailer->ErrorInfo );
    }

    // Back to main course page.
    redirect( new moodle_url('/course/view.php', array('id'=>$id)), get_string('emailsent', 'block_wiforms'), 5 );
}

// Display the form.
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
