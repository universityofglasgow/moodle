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
 * Defines backup structure steps for both hvp content and hvp libraries.
 *
 * @package     mod_hvp
 * @category    backup
 * @copyright   2016 Joubel AS <contact@joubel.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete hvp structure for backup, with file and id annotations
 */
class backup_hvp_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $hvp = new backup_nested_element('hvp', array('id'), array(
            'name',
            'machine_name',
            'major_version',
            'minor_version',
            'intro',
            'introformat',
            'json_content',
            'embed_type',
            'disable',
            'content_type',
            'author',
            'license',
            'meta_keywords',
            'meta_description',
            'slug',
            'timecreated',
            'timemodified'
        ));

        // User data
        $content_user_data_entries = new backup_nested_element('content_user_data');
        $content_user_data = new backup_nested_element('entry', array(
            'user_id', // Annotated
            'sub_content_id'
            ), array(
            'data_id',
            'data',
            'preloaded',
            'delete_on_content_change',
        ));

        // Build the tree

        $hvp->add_child($content_user_data_entries);
        $content_user_data_entries->add_child($content_user_data);

        // Define sources

        // Uses library name and version instead of main_library_id.
        $hvp->set_source_sql('SELECT h.id, hl.machine_name,
                                           hl.major_version,
                                           hl.minor_version,
                                     h.name, h.intro, h.introformat, h.json_content,
                                     h.embed_type, h.disable, h.content_type, h.author,
                                     h.license, h.meta_keywords, h.meta_description,
                                     h.slug, h.timecreated, h.timemodified
                                FROM {hvp} h
                                JOIN {hvp_libraries} hl ON hl.id = h.main_library_id
                               WHERE h.id = ?', array(backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $content_user_data->set_source_table('hvp_content_user_data', array('hvp_id' => backup::VAR_PARENTID));
        }

        // Define id annotations
        $content_user_data->annotate_ids('user', 'user_id');
        // In an ideal world we would use the main_library_id and annotate that
        // but since we cannot know the required dependencies of the content
        // without parsing json_content and crawling the libraries_libraries
        // (library dependencies) table it's much easier to just include all
        // installed libraries.

        // Define file annotations
        $hvp->annotate_files('mod_hvp', 'intro', null, null);
        $hvp->annotate_files('mod_hvp', 'content', null, null);

        // Return the root element (hvp), wrapped into standard activity structure
        return $this->prepare_activity_structure($hvp);
    }
}

/**
 * Structure step in charge of constructing the hvp_libraries.xml file for
 * all the H5P libraries.
 */
class backup_hvp_libraries_structure_step extends backup_structure_step {

    protected function define_structure() {

        // Define each element separate.

        // Libraries
        $libraries = new backup_nested_element('hvp_libraries');
        $library = new backup_nested_element('library', array('id'), array(
            'title',
            'machine_name',
            'major_version',
            'minor_version',
            'patch_version',
            'runnable',
            'fullscreen',
            'embed_types',
            'preloaded_js',
            'preloaded_css',
            'drop_library_css',
            'semantics',
            'restricted',
            'tutorial_url'
        ));

        // Library translations
        $translations = new backup_nested_element('translations');
        $translation = new backup_nested_element('translation', array(
            'language_code'
        ), array(
            'language_json'
        ));

        // Library dependencies
        $dependencies = new backup_nested_element('dependencies');
        $dependency = new backup_nested_element('dependency', array(
            'required_library_id'
        ), array(
            'dependency_type'
        ));

        // Build the tree
        $libraries->add_child($library);

        $library->add_child($translations);
        $translations->add_child($translation);

        $library->add_child($dependencies);
        $dependencies->add_child($dependency);

        // Define sources

        $library->set_source_table('hvp_libraries', array());

        $translation->set_source_table('hvp_libraries_languages', array('library_id' => backup::VAR_PARENTID));

        $dependency->set_source_table('hvp_libraries_libraries', array('library_id' => backup::VAR_PARENTID));

        // Define file annotations
        $context = \context_system::instance();
        $library->annotate_files('mod_hvp', 'libraries', null, $context->id);

        // Return root element
        return $libraries;
    }
}
