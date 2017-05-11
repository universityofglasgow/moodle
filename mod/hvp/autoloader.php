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
 * @package    mod_hvp
 * @copyright  2016 Joubel AS <contact@joubel.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * A simple autoloader which makes it easy to load classes when you need them.
 *
 * @param string $class name
 */
function hvp_autoloader($class) {
    global $CFG;
    static $classmap;
    if (!isset($classmap)) {
        $classmap = array(
        // Core.
        'H5PCore' => 'library/h5p.classes.php',
        'H5PFrameworkInterface' => 'library/h5p.classes.php',
        'H5PContentValidator' => 'library/h5p.classes.php',
        'H5PValidator' => 'library/h5p.classes.php',
        'H5PStorage' => 'library/h5p.classes.php',
        'H5PExport' => 'library/h5p.classes.php',
        'H5PDevelopment' => 'library/h5p-development.class.php',
        'H5PFileStorage' => 'library/h5p-file-storage.interface.php',
        'H5PDefaultStorage' => 'library/h5p-default-storage.class.php',
        'H5PEventBase' =>  'library/h5p-event-base.class.php',

        // Editor.
        'H5peditor' => 'editor/h5peditor.class.php',
        'H5PEditorAjax' => 'editor/h5peditor-ajax.class.php',
        'H5PEditorAjaxInterface' => 'editor/h5peditor-ajax.interface.php',
        'H5peditorFile' => 'editor/h5peditor-file.class.php',
        'H5peditorStorage' => 'editor/h5peditor-storage.interface.php',

        // Plugin specific classes are loaded by Moodle.
        );
    }

    if (isset($classmap[$class])) {
        require_once($CFG->dirroot . '/mod/hvp/' . $classmap[$class]);
    }
}
spl_autoload_register('hvp_autoloader');
