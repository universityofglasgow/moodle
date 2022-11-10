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
 * Brief Description
 *
 * More indepth description.
 *
 * @package
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $reportnname = get_string('guidreport', 'theme_hillhead40');

    $ADMIN->add('reports', new admin_externalpage('guidreport',
        $reportnname, "$CFG->wwwroot/report/guid/index.php", 'moodle/site:config'));
}

// This is used for performance, we don't need to know about these settings on every page in Moodle, only when
// we are looking at the admin settings pages.
if ($ADMIN->fulltree) {
    $currentTheme = 'theme_hillhead40';

    // Boost provides a nice setting page which splits settings onto separate tabs. We want to use it here.
    $settings = new theme_boost_admin_settingspage_tabs('themesettinghillhead40', get_string('configtitle', $currentTheme));

    // Each page is a tab - the first is the "Appearance" tab.
    $page = new admin_settingpage('theme_hillhead_general', get_string('generalsettings', $currentTheme));

    // Replicate the 'Theme Preset' setting drop down menu from Boost.
    $name = 'theme_hillhead40/preset';
    $title = get_string('preset', $currentTheme);
    $description = get_string('preset_desc', $currentTheme);
    $default = 'blue.scss';

    // We list files in our own file area to add to the dropdown. We will provide our own function to
    // load all the presets from the correct paths.
    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, $currentTheme, 'preset', 0, 'itemid, filepath, filename', false);

    $choices = [];
    foreach ($files as $file) {
        $choices[$file->get_filename()] = $file->get_filename();
    }
    // These are the presets from our child theme.
    $choices['blue.scss'] = 'University Blue';
    $choices['green.scss'] = 'Moss Green';
    $choices['red.scss'] = 'Pillarbox Red';
    $choices['grey.scss'] = 'Slate Grey';
    $choices['brown.scss'] = 'Sandstone Brown';
    $choices['orange.scss'] = 'Rust Orange';
    $choices['purple.scss'] = 'Lavender Purple';

    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // ... Next up is the "Login page" background setting.
    $name = 'theme_hillhead40/loginbackgroundimage';
    $title = get_string('loginbackgroundimage', $currentTheme);
    $description = get_string('loginbackgroundimage_desc', $currentTheme);
    // This creates the new setting.
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbackgroundimage');
    // This function will copy the image into the data_root location it can be served from.
    $setting->set_updatedcallback('theme_hillhead40_update_settings_images');
    // We always have to add the setting to a page for it to have any effect.
    $page->add($setting);

    // Must add the page after defining all the settings for this section!
    $settings->add($page);

    // ... Next, the "Notification and Alerts" tab.
    $page = new admin_settingpage('theme_hillhead_notifications', get_string('notificationsettings', $currentTheme));

    // ... Student Course Alert dropdown menu
    $name = 'theme_hillhead40/hillhead_student_course_alert';
    $title = get_string('hillhead_student_course_alert', $currentTheme);
    $description = get_string('hillhead_student_course_alert_desc', $currentTheme);
    $choices = Array(
        'enabled' => get_string('hillhead_student_course_alert_on', $currentTheme),
        'disabled' => get_string('hillhead_student_course_alert_off', $currentTheme)
    );
    $default = 'disabled';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    // ... Student Course Alert text area
    $setting = new admin_setting_configtextarea('theme_hillhead40/hillhead_student_course_alert_text',
        get_string('hillhead_student_course_alert_text', $currentTheme), get_string('hillhead_student_course_alert_text_desc', $currentTheme), '<p>Most lecturers make their courses available to students within the first week of a new semester. However, sometimes lecturers need a bit of time to update the material in their courses or to take backups of last year\'s students\' work. There might be a delay in these cases.</p><p>If you can\'t see something you expect to see in Moodle, it\'s best to double check with your lecturer. They\'re the ones who decide which courses you have access to. <strong>The IT Helpdesk can\'t add you to courses.</strong></p>', PARAM_RAW);
    $page->add($setting);

    // ... Old Browser Alert dropdown menu
    $name = 'theme_hillhead40/hillhead_old_browser_alerts';
    $title = get_string('hillhead_old_browser_alerts', $currentTheme);
    $description = get_string('hillhead_old_browser_alerts_desc', $currentTheme);
    $choices = Array(
        'enabled' => get_string('hillhead_old_browser_alerts_on', $currentTheme),
        'disabled' => get_string('hillhead_old_browser_alerts_off', $currentTheme)
    );
    $default = 'disabled';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    // ... Systemwide Notification Type dropdown menu
    $name = 'theme_hillhead40/hillhead_notification_type';
    $title = get_string('hillhead_notification_type', $currentTheme);
    $description = get_string('hillhead_notification_type_desc', $currentTheme);
    $choices = Array(
        'alert-none' => get_string('hillhead_notification_none', $currentTheme),
        'alert-danger' => get_string('hillhead_notification_danger', $currentTheme),
        'alert-warning' => get_string('hillhead_notification_warning', $currentTheme),
        'alert-success' => get_string('hillhead_notification_success', $currentTheme),
        'alert-info' => get_string('hillhead_notification_info', $currentTheme)
    );
    $default = 'alert-none';
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    // ... Systemwide Notification Text textarea
    $setting = new admin_setting_configtextarea('theme_hillhead40/hillhead_notification',
        get_string('hillhead_notification', $currentTheme), get_string('hillhead_notification_desc', $currentTheme), '', PARAM_RAW);
    $page->add($setting);

    // Must add the page after defining all the settings for this section!
    $settings->add($page);

    // Last up we have the "Advanced settings" tab...
    $page = new admin_settingpage('theme_hillhead_advanced', get_string('advancedsettings', $currentTheme));

    // ... Raw SCSS to include before the content...
    $setting = new admin_setting_configtextarea('theme_hillhead40/scsspre',
        get_string('rawscsspre', $currentTheme), get_string('rawscsspre_desc', $currentTheme), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // ... Raw SCSS to include after the content...
    $setting = new admin_setting_configtextarea('theme_hillhead40/scss', get_string('rawscss', $currentTheme),
        get_string('rawscss_desc', $currentTheme), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // ... The greeting message on the login page - being phased out in favour of Shibboleth tho...
    $setting = new admin_setting_configtextarea('theme_hillhead40/login_intro',
        get_string('login_intro', $currentTheme), get_string('login_intro_desc', $currentTheme), '', PARAM_RAW);
    $page->add($setting);

    // Must add the page after defining all the settings for this section!
    $settings->add($page);
}