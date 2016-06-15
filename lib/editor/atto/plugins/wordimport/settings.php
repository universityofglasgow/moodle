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
 * Atto text editor import Microsoft Word files - settings.
 *
 * @package    atto_wordimport
 * @copyright  2015 Eoin Campbell
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_wordimport', new lang_string('pluginname', 'atto_wordimport')));

$settings = new admin_settingpage('atto_wordimport_settings', new lang_string('settings', 'atto_wordimport'));

if ($ADMIN->fulltree) {
    // What HTML heading element should be used for the Word Heading 1 style?
    $name = new lang_string('heading1stylelevel', 'atto_wordimport');
    $desc = new lang_string('heading1stylelevel_desc', 'atto_wordimport');
    $default = 3;
    $options = array_combine(range(1, 6), array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'));

    $setting = new admin_setting_configselect('atto_wordimport/heading1stylelevel',
                                              $name,
                                              $desc,
                                              $default,
                                              $options);
    $settings->add($setting);


}
