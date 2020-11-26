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
 * Cli lib.
 *
 * @package    core
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Get the language root directory.
 *
 * @return string ending with /
 */
function local_xp_cli_get_language_root() {
    global $CFG;
    return $CFG->dirroot . '/local/xp/lang/';
}

/**
 * Get the language paths.
 *
 * @param string $lang The language.
 * @return Array with absolute language dir and file.
 */
function local_xp_cli_get_language_paths($lang) {
    $root = local_xp_cli_get_language_root();
    $langdir = $root . $lang;
    $file = $langdir . '/local_xp.php';
    return [$langdir, $file];
}

/**
 * Check whether the language exists.
 *
 * @param string $lang The language.
 * @return bool
 */
function local_xp_cli_has_language($lang) {
    list($langdir, $file) = local_xp_cli_get_language_paths($lang);
    return is_dir($langdir) && is_file($file);
}

/**
 * Get the language root directory.
 *
 * @return string ending with /
 */
function local_xp_cli_get_languages() {
    $root = local_xp_cli_get_language_root();
    $iter = new DirectoryIterator($root);
    $dirs = [];
    foreach ($iter as $file) {
        if (!$file->isDir()) {
            continue;
        }
        $lang = clean_param($file->getFilename(), PARAM_SAFEDIR);
        if ($lang !== $file->getFilename()) {
            continue;
        }
        $dirs[] = $lang;
    }
    return $dirs;
}

/**
 * Load the strings from the plugin.
 *
 * @param string $lang The language.
 * @return array
 */
function local_xp_cli_load_strings($lang) {
    global $CFG;
    $string = [];
    list($unused, $file) = local_xp_cli_get_language_paths($lang);
    if (!is_file($file)) {
        return $string;
    }
    include($file);
    return $string;
}
