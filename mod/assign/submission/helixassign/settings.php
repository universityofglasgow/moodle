<?php

/**
 * This file defines the admin settings for this plugin
 *
 * @package   assignsubmission_helixassign
 * @copyright Streaming LTD 2013
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $CFG;

require_once($CFG->dirroot."/mod/assign/submission/helixassign/lib.php");

$settings->add(new admin_setting_configcheckbox('assignsubmission_helixassign/default',
                   new lang_string('default', 'assignsubmission_helixassign'),
                   new lang_string('default_help', 'assignsubmission_helixassign'), 0));

$hml=$DB->get_record("modules", array("name"=>"helixmedia"));

//The version field has been removed in Moodle 2.6, this compensates
if (property_exists($hml, "version"))
    $version=$hml->version;
else
    $version=get_config('mod_helixmedia', 'version');

if ($version<SUBMISSION_HELIXMEDIA_MIN_VERSION)
{
  $settings->add(new admin_setting_heading('assignsubmission_helixmedia/warning', get_string("version_warning_head", "assignsubmission_helixassign"),
      "<p>".get_string("version_warning_mes", "assignsubmission_helixassign")." ".SUBMISSION_HELIXMEDIA_MIN_VERSION.
      " ".get_string("version_warning_mes2", "assignsubmission_helixassign").
      "</p>"));
}