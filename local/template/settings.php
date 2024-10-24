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
 * Settings
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use core_admin\local\settings\autocomplete;

defined('MOODLE_INTERNAL') || die;

/** @var stdClass $CFG */
global $CFG;

/** @var admin_root $ADMIN */
global $ADMIN;

/** @var boolean $hassiteconfig */
if ($hassiteconfig) {

    $settings = new admin_settingpage('local_template', get_string('pluginname', 'local_template'));

    global $ADMIN;
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configselect('local_template/addnewcoursehook',
        get_string('addnewcoursehook', 'local_template'),
        get_string('addnewcoursehook_desc', 'local_template'),
        1, [0 => get_string('no'), 1 => get_string('yes')]
    ));

    $settings->add(new admin_setting_configselect('local_template/addnewcoursenavigation',
        get_string('addnewcoursenavigation', 'local_template'),
        get_string('addnewcoursenavigation_desc', 'local_template'),
        1, [0 => get_string('no'), 1 => get_string('yes')]
    ));

    $settings->add(new admin_setting_configselect('local_template/addnewcoursecategoryactionbar',
        get_string('addnewcoursecategoryactionbar', 'local_template'),
        get_string('addnewcoursecategoryactionbar_desc', 'local_template'),
        1, [0 => get_string('no'), 1 => get_string('yes')]
    ));

    $settings->add(new admin_setting_configselect('local_template/addnewcoursecoursemanagement',
        get_string('addnewcoursecoursemanagement', 'local_template'),
        get_string('addnewcoursecoursemanagement_desc', 'local_template'),
        1, [0 => get_string('no'), 1 => get_string('yes')]
    ));

    $options = [
        0 => get_string('availableviews_slider', 'local_template'),
        1 => get_string('availableviews_staticdisplay', 'local_template'),
        2 => get_string('availableviews_highcompatabilitymode','local_template'),
    ];
    $settings->add(new admin_setting_configmultiselect('local_template/availableviews',
        get_string('availableviews', 'local_template'),
        get_string('availableviews_desc', 'local_template'),
        [0],
        $options
    ));

    $categories = core_course_category::make_categories_list('moodle/course:create');
    $settings->add(new autocomplete('local_template/categories',
        get_string('categories', 'local_template'),
        get_string('categories_desc', 'local_template'),
        [],
        $categories,
        ['manageurl' => false, 'managetext' => false])
    );

    $settings->add(
        new admin_setting_confightmleditor(
            'local_template/introduction',
            get_string('introduction', 'local_template'),
            get_string('introduction_desc', 'local_template'),
            get_string('introduction_default', 'local_template')
        )
    );

    // Fetch roles that are assignable.
    $assignableroles = get_assignable_roles(context_system::instance());

    // Fetch roles that have the capability to use templates.
    $capableroles = get_roles_with_capability('local/template:usetemplate');

    $roles = [];
    foreach ($capableroles as $key => $role) {
       if (array_key_exists($key, $assignableroles)) {
           $roles[$key] = $assignableroles[$key];
       }
    }
    if (!empty($roles)) {
       $settings->add(new admin_setting_configselect('local_template/syncrole',
               new lang_string('syncrole', 'local_template'),
               new lang_string('syncrole_desc', 'local_template'), null, $roles)
       );
    }

    $ADMIN->add('courses',
        new admin_externalpage('addnewcourseviatemplate', new lang_string('addnewcourseviatemplate', 'local_template'),
            new moodle_url('/local/template/index.php'),
            ['moodle/category:manage']
        )
    );
}