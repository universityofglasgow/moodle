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
 * List of translatation ready strings used on the free and pro versions.
 *
 * @package   mod_pulse
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Pulse';
$string['modulename'] = 'Pulse';
$string['modulenameplural'] = 'Pulses';
$string['pulse:addinstance'] = 'Add a new pulse';
$string['pulse:view'] = 'View pulse';
$string['content'] = 'Content';
$string['modulename_help'] = 'TPulse is the teacher\'s Swiss army knife to improve student engagement and compliance in moodle courses. <br><br>
(1) Notifications:<br>
Each Pulse activity can be configured to send a notification once it becomes available to the student. There are a number of placeholders that can be used to personalize the message, like the first name of the student or the name of the course. The notification can be sent to the student, the teacher, the non-editing teacher or the manager. Other course context or user context roles are supported as well, e.g. parent or staff manager. <br><br>
(2) Completion workflows: <br>
Pulse supports activity completion in three ways (in addition to moodle core ones): upon availability, when marked complete by student and when approved by other role.';
$string['modulename_link'] = 'mod/pulse/view';
$string['privacy:metadata'] = 'The pulse plugin does not store any personal data.';
$string['pluginadministration'] = 'Pulse administration';
$string['search:activity'] = 'Pulse';
$string['sendnotificaton'] = 'Send notification';
$string['diffnotification'] = 'Use notification content instead of general content';
$string['enable:disable'] = 'Enable / Disable';
$string['pulsenotification'] = 'Pulse notification';

$string['pulse_subject'] = 'Pulse from {Course_FullName} ({Site_FullName}) ';
$string['notifyusers'] = 'Notify course students';
$string['pulse:notifyuser'] = 'Send notification';
$string['messageprovider:mod_pulse'] = 'Send notifcation';
$string['instancename'] = 'Pulse';
$string['resendnotification'] = 'Re-send Notification';
$string['resendnotificationdesc'] = 'Invitation has been scheduled for re-sending';
$string['completionrequireapproval'] = 'Require approval by one of the following roles ';
$string['completewhenavaialble'] = 'Completion when available';

$string['completionapproverules'] = 'Completionapproverules';
$string['completionself'] = 'Mark as complete by student to complete this activity';
$string['approved'] = 'Approved';
$string['declined'] = 'Declined';
$string['decline'] = 'Decline';
$string['approve'] = 'Approve';
$string['approveuser'] = 'Approve users - {$a->course}';
$string['approveuserbtn'] = 'Approve users';

$string['restrictionmet'] = 'Restriction Met';

$string['markcomplete'] = 'Mark complete';
$string['noreaction'] = 'No Reaction';
$string['rate'] = 'Rate';
$string['updatecompletion'] = 'Update pulse modules completion';
$string['approvedsuccess'] = 'User approved successfully';
$string['approvedeclined'] = 'User completion declined';
$string['selfmarked'] = 'Self marked complete on {$a->date}';
$string['approveduser'] = 'Approved by: {$a->user}';
$string['approvedon'] = 'Approved on {$a->date} by {$a->user}';

$string['completion:self'] = 'Mark complete';
$string['completion:approval'] = 'Approval required';
$string['completion:available'] = 'Restrictions must be met';

$string['reactions'] = 'Reaction';
$string['displaytype:notificationonly'] = 'Notification only';
$string['displaytype:contentonly'] = 'Content only';
$string['displaytype:notificationcontent'] = 'Notification and Content';

$string['head:firstreminder'] = 'First reminder';
$string['head:secondreminder'] = 'Second reminder';
$string['head:recurringreminder'] = 'Recurring reminder';
$string['remindersubject'] = 'Notification subject';
$string['remindercontent'] = 'Notification content';
$string['recipients'] = 'Notification recipients';
$string['reminderschedule'] = 'Notification schedule';
$string['schedule:fixeddate'] = 'Fixed date';
$string['schedule:relativedate'] = 'Relative date';
$string['courserole'] = 'courserole';
$string['userrole'] = 'userrole';
$string['reactiondisplaytype'] = 'Location';
$string['title'] = 'Title';
$string['reaction:markcomplete'] = ' <a href="{$a->reactionurl}" style="color: #fff;background: #0f6fc5;padding: .375rem .75rem;text-decoration-line: none;" >Mark Complete</a> ';
$string['reaction:rate'] = '';
$string['reaction:approve'] = ' <a href="{$a->reactionurl}" style="color: #fff;background: #0f6fc5;padding:.375rem .75rem;text-decoration-line: none;" > Approve </a> ';
$string['title_help'] = 'The title is used as activity name. It is used as subject to send the invitation.';
$string['content_help'] = 'Content will be displayed on the course page and used as message body content for the invitation.';
$string['completewhenavaialble_help']  = 'If enabled, the activity will be considered completed when the user has access to it (i.e. when it is available based on availability restricitions).';
$string['completionself_help']  = 'If enabled, the activity will be considered completed when the student marks it as complete on the course page.';
$string['completionrequireapproval_help']  = 'If enabled, the activity will be considered completed when any of the selected roles approves the user.';

$string['completereaction'] = 'Complete reaction';
$string['reminders:first'] = 'First reminder';
$string['reminders:second'] = 'Second reminder';
$string['reminders:recurring'] = 'Recurring reminder';
$string['reminders:availabletime'] = 'Availability time';

$string['completioncriteria'] = 'Completion criteria';
$string['self'] = 'Self';
$string['teacher'] = 'Teacher';
$string['reaction'] = 'Reaction';
$string['reports'] = 'Pulse Reports';
$string['selectpulse'] = 'Select pulse instance';
$string['generatereport'] = 'Generate report';
$string['like'] = 'Like';
$string['dislike'] = 'Dislike';
$string['enablereminder:invitation'] = 'Enable invitation';
$string['enablereminder:first'] = 'Enable first reminder';
$string['enablereminder:second'] = 'Enable second reminder';
$string['enablereminder:recurring'] = 'Enable recurring reminder';
$string['invitation'] = 'Invitation';
$string['invitationsubject'] = 'Notification subject';

$string['invitation_help'] = 'Send the invitation to all users with the selected roles.';
$string['sendnotificaton_help'] = 'If enabled, the invitation will be sent.';
$string['resendnotification_help'] = 'If enabled, Invitation reminder will rescheduled and sends the invitation to already notified users too.';
$string['diffnotification_help'] = 'If enabled, the invitation will use notification content and subject (instead of general content and title).';
$string['invitationsubject_help'] = 'Add the subject for the invitation here.';
$string['remindercontent_help'] = 'If notification content enabled, then this content will used as main content for invitations.';
$string['recipients_help'] = 'Please choose the roles which you want to send the notification to. Only users enrolled in this course and with the selected role will receeive notifications. Please note that users with a user context role users don\'t need to be enrolled in the course. ';

$string['reactiontype'] = 'Type';
$string['reactiontype_help'] = 'List of reaction types.';
$string['reactiondisplaytype_help'] = 'Please choose where the reaction should be displayed.';
$string['enablereminder:first_help'] = 'If enabled, Pulse will send the first reminder.';
$string['remindersubject_help'] = 'Content will used as subject for the Reminder notifications.';
$string['remindercontent_help'] = 'Entered content will send to recipients, Use the placeholders to use the receipients data dynamically.';

$string['reminderschedule_help'] = 'Define the reminder notification schedule type, <br>
                                    If fixed date enabled, Then reminder will send to the selected roles when the selected date will reached. <br>
                                    If Relative date enabled, then the reminder will send to the users once when the given duration will matched the user duration from when the activity available to user.';
$string['enablereminder:second_help'] = 'If enabled, Pulse will send the Second reminder to selected recipients based on the schedule.';
$string['enablereminder:recurring_help'] = 'If enabled, Pulse will send the Recurring reminder to selected recipients. Recurring reminders will send to the user in the given interval untill the user enrolment end or suspended.';

$string['notsameuser'] = 'You are not the correct user to apply reaction';
$string['previousreminders'] = 'Previous reminders';
$string['reportsfilename'] = 'Pulse reports - {$a->name}';
$string['viewreport'] = 'View report';
$string['pulsenotavailable'] = 'Pulse instance not added in course';
$string['notassignedgroup'] = 'User must be part of a group to filter by participants.';
$string['pulsepro:viewreports'] = 'View Pulse Pro reports';
$string['reactionthankmsg'] = 'Thank you! Your response is saved.<br><br> <span>You can now close this window</span>';

$string['privacy:completion'] = 'Completion';
$string['approvedby'] = 'approvedby';
$string['completionfor'] = 'completionfor';
$string['privacy:metadata:pulsecompletion'] = 'Pulse user activity completions';
$string['privacy:invitation'] = 'Inviation';
$string['privacy:metadata:completion:userid'] = 'ID of the user';
$string['privacy:metadata:completion:approvalstatus'] = 'User approved status';
$string['privacy:metadata:completion:approveduser'] = 'ID of the user who approved the student user';
$string['privacy:metadata:completion:approvaltime'] = 'Time when the user approved by other.';
$string['privacy:metadata:completion:selfcompletion'] = 'Status of the user completion by self';
$string['privacy:metadata:completion:selfcompletiontime'] = 'Time when the user completion pulse by self';
$string['privacy:metadata:completion:timemodified'] = 'Time of completion modified';
$string['privacy:metadata:users:userid'] = 'ID of notified user';
$string['privacy:metadata:users:status'] = 'Status of the invitation to find the notification is previous or current one';
$string['privacy:metadata:users:timecreated'] = 'Time of the invitation send to user.';
$string['privacy:metadata:pulseusers'] = 'List of users invitation notified';
$string['privacy:metadata:pulsemessageexplanation'] = 'Invitations are sent to students through the messaging system.';

// Lang strings for the presets.
$string['presets'] = "Pulse presets";
$string['usepreset'] = 'Use preset';
$string['managepresets'] = 'Manage Presets';
$string['title'] = 'Title';
$string['description'] = 'Description';
$string['instruction'] = 'Instruction';
$string['preseticon'] = 'Preset icon';
$string['createpreset'] = 'Create Preset';
$string['statuslabel'] = 'Enable / Disable';
$string['preset_template'] = 'Backup file of pulse activity';
$string['preset_template_help'] = 'Upload the backup file of pulse module course activity';
$string['presetstatus'] = 'Display this preset in list';
$string['presetorder'] = 'Preset template order';
$string['configrableparams'] = 'Configurable Params';
$string['disabled'] = 'Disabled';
$string['enabled'] = 'Enabled';
$string['preset_template'] = 'Preset Template';
$string['title'] = 'Title';
$string['configparams'] = 'Config Params';
$string['presetlist'] = 'Presets List';
$string['presetcreated'] = 'Pulse presets created successfully';
$string['presetupdated'] = 'Pulse presets updated successfully';
$string['update_preset'] = 'Update Preset Template';
$string['deletepreset'] = 'Delete pulsepro preset template';
$string['confirmdeletetemplate'] = 'Are you sure! do you want to delete the preset';
$string['presetdeleted'] = 'Preset deleted successfully';
$string['apply_customize'] = 'Apply and Customize';
$string['apply_save'] = 'Apply and Save';
$string['promotionaltext'] = 'With Pulse Pro you get powerful reminders, In-email reactions and you can create your own presets. ';
$string['learnmore'] = 'Learn More';
$string['presetmodaltitle'] = 'Use preset {$a->title}';
// Credits lang strings.
$string['credits'] = 'Credits';
$string['notificationheader'] = 'Notification Header';
$string['notificationheaderdesc'] = '{$a->placeholders}';
$string['notificationfooter'] = 'Notification Footer';
$string['notificationfooterdesc'] = '{$a->placeholders}';
$string['creditsfield'] = 'Credits user profile field';
$string['creditsfielddesc'] = 'Select any of the user custom profile field to maintain the user credits records <br>
NOTE: Make the selected field hidden for students, otherwise student can able to change their credit scores';
$string['setupcredit'] = 'In order to use this feature, you need to configure the user profile field first.
Please ask your local administrator to set this up.';
$string['creditesgroup'] = 'Credit score';
$string['setup'] = 'Setup Field';
$string['actions'] = 'Actions';
$string['cssclass'] = 'CSS class';
$string['normal'] = 'Normal';
$string['box'] = 'Box';
$string['displaymode'] = 'Display mode';
$string['primary'] = 'Primary';
$string['secondary'] = 'Secondary';
$string['danger'] = 'Danger';
$string['warning'] = 'Warning';
$string['light'] = 'Light';
$string['dark'] = 'Dark';
$string['success'] = 'Success';
$string['boxicon'] = 'Box Icon';
$string['boxtype'] = 'Box Type';
$string['updateusercredits'] = 'Update user credits';
$string['logintoreact'] = 'Login before apply reaction';
$string['tokenexpired'] = "Token expired! Can't able to save your response.";
