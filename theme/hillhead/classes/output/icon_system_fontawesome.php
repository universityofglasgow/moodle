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
 * Contains class \core\output\icon_system
 *
 * @package    core
 * @category   output
 * @copyright  2016 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_hillhead\output;

use renderer_base;
use pix_icon;

defined('MOODLE_INTERNAL') || die();

/**
 * Class allowing different systems for mapping and rendering icons.
 *
 * Possible icon styles are:
 *   1. standard - image tags are generated which point to pix icons stored in a plugin pix folder.
 *   2. fontawesome - font awesome markup is generated with the name of the icon mapped from the moodle icon name.
 *   3. inline - inline tags are used for svg and png so no separate page requests are made (at the expense of page size).
 *
 * @package    core
 * @category   output
 * @copyright  2016 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class icon_system_fontawesome extends \core\output\icon_system_fontawesome {
    /**
     * @var array $map Cached map of moodle icon names to font awesome icon names.
     */
    private $map = [];
    public function get_core_icon_map() {
        $iconmap = parent::get_core_icon_map();
        
        $overrides = Array(
            'core:req'                      => 'fa-asterisk text-warning',
            'core:a/add_file'               => 'fa-file',
            'core:b/document-new'           => 'fa-file',
            'core:b/edit-copy'              => 'fa-file',
            'core:e/insert_edit_video'      => 'fa-file-video',
            'core:e/insert_file'            => 'fa-file',
            'core:e/manage_files'           => 'fa-files',
            'core:e/new_document'           => 'fa-file',
            'theme:fp/add_file'             => 'fa-file',
            'core:i/backup'                 => 'fa-file-zip',
            'core:i/files'                  => 'fa-file',
            'core:i/privatefiles'           => 'fa-file',
            'core:i/dashboard'              => 'fa-tachometer-alt',
            'core:i/badge'                  => 'fa-shield-alt',
            'core:i/competencies'           => 'fa-check-square',
            'core:a/create_folder'          => 'fa-folder',
            'core:a/view_tree_active'       => 'fa-folder',
            'theme:fp/create_folder'        => 'fa-folder',
            'theme:fp/path_folder'          => 'fa-folder',
            'theme:fp/path_folder_rtl'      => 'fa-folder',
            'theme:fp/view_tree_active'     => 'fa-folder',
            'core:i/folder'                 => 'fa-folder',
            'core:i/open'                   => 'fa-folder',
            'core:i/section'                => 'fa-folder',

        );
        
        $merged = array_merge($iconmap, $overrides);
        
        return $merged;
    }
}
