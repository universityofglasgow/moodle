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
 * Theme functions.
 *
 * @package    theme_hillhead40
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function theme_hillhead40_get_main_scss_content($theme) {

    global $CFG;
    $scss = '';
    $sheets = ['config'];

    // These scss files should declare default values for "variables" that will be used by Moodle...
    foreach ($sheets as $sheet) {
        $scss .= file_get_contents($CFG->dirroot . '/theme/hillhead40/scss/'.$sheet.'.scss');
    }

    // ...now append the main scss file style rules...
    $scss .= theme_boost_get_main_scss_content($theme);

    $sheets = ['hillhead40', 'accessibility', 'login'];

    // ...these scss files should declare more specific css "rules"...
    foreach ($sheets as $sheet) {
        $scss .= file_get_contents($CFG->dirroot . '/theme/hillhead40/scss/'.$sheet.'.scss');
    }

    // ...finally append the "preset" scss "vars" and "rules" from the settings,
    // which will override the ones used in the Moodle and Bootstrap SCSS files...
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : 'blue.scss';
    $scss .= file_get_contents($CFG->dirroot . '/theme/hillhead40/scss/'.$filename);

    return $scss;
}

function theme_hillhead40_update_settings_images($settingname) {
    global $CFG;

    // The setting name that was updated comes as a string like 's_theme_photo_loginbackgroundimage'.
    // We split it on '_' characters.
    $parts = explode('_', $settingname);
    // And get the last one to get the setting name..
    $settingname = end($parts);

    // Admin settings are stored in system context.
    $syscontext = context_system::instance();
    // This is the component name the setting is stored in.
    $component = 'theme_hillhead40';

    // This is the value of the admin setting which is the filename of the uploaded file.
    $filename = get_config($component, $settingname);
    // We extract the file extension because we want to preserve it.
    $extension = substr($filename, strrpos($filename, '.') + 1);

    // This is the path in the moodle internal file system.
    $fullpath = "/{$syscontext->id}/{$component}/{$settingname}/0{$filename}";
    // Get an instance of the moodle file storage.
    $fs = get_file_storage();
    // This is an efficient way to get a file if we know the exact path.
    if ($file = $fs->get_file_by_hash(sha1($fullpath))) {
        // We got the stored file - copy it to dataroot.
        // This location matches the searched for location in theme_config::resolve_image_location.
        $pathname = $CFG->dataroot . '/pix_plugins/theme/hillhead40/' . $settingname . '.' . $extension;

        // This pattern matches any previous files with maybe different file extensions.
        $pathpattern = $CFG->dataroot . '/pix_plugins/theme/hillhead40/' . $settingname . '.*';

        // Make sure this dir exists.
        @mkdir($CFG->dataroot . '/pix_plugins/theme/hillhead40/', $CFG->directorypermissions, true);

        // Delete any existing files for this setting.
        foreach (glob($pathpattern) as $filename) {
            @unlink($filename);
        }

        // Copy the current file to this location.
        $file->copy_content_to($pathname);
    }

    // Reset theme caches.
    theme_reset_all_caches();
}

/**
 * Serves any files associated with the theme settings.
 * This ^crucial^ bit is helpfully missed out in the tutorial
 * https://docs.moodle.org/dev/Creating_a_theme_based_on_boost#How_do_we_refer_to_an_image_in_SCSS_.3F
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_hillhead40_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'logo' || $filearea === 'backgroundimage' ||
            $filearea === 'loginbackgroundimage')) {
        $theme = theme_config::load('hillhead40');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Returns whether the course selector plugin /local/template is present.
 *
 * @return boolean Whether the plugin is available.
 */
function theme_hillhead40_exists_template_plugin() {
    global $CFG;

    if (file_exists("{$CFG->dirroot}/local/template/version.php")) {
        return is_readable("{$CFG->dirroot}/local/template/version.php");
    }
    return false;
}

if (theme_hillhead40_exists_template_plugin()) {
    global $CFG;

    // Moodle codechecker incorrectly asserts require_once must use parenthesis.
    // @codingStandardsIgnoreLine
    require_once $CFG->dirroot . '/local/template/locallib.php';
    local_template_add_new_course_hook();
}
