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
 * Atto text editor pastespecial plugin lib.
 *
 * @package    atto_pastespecial
 * @copyright  2015 Joseph Inhofer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Initialise the strings required for JS.
 *
 * @return void
 */

function atto_pastespecial_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('pastehere',
                                          'pasteview',
                                          'pastefromword',
                                          'pastefromgdoc',
                                          'pastefromlibre',
                                          'pastefromother',
                                          'pasteunformatted',
                                          'pastestraight',
                                          'paste',
                                          'cancel',
                                          'help',
                                          'help_text',
                                          'clickthebutton',
                                          'pastefrommoodle',
                                          'step2'
                                         ), 'atto_pastespecial');
}

/**
 * Send parameters to JS
 * @param $elementid
 * @param $options
 * @param $foptions
 * return Array $params that contains the plugin config settings
 */

function atto_pastespecial_params_for_js($elementid, $options, $fpoptions) {
    $params = array('wordCSS' => get_config('atto_pastespecial', 'wordCSS'),
                    'gdocCSS' => get_config('atto_pastespecial', 'gdocCSS'),
                    'libreCSS' => get_config('atto_pastespecial', 'libreCSS'),
                    'otherCSS' => get_config('atto_pastespecial', 'otherCSS'),
                    'straight' => get_config('atto_pastespecial', 'straight'),
                    'height' => get_config('atto_pastespecial', 'height'),
                    'width' => get_config('atto_pastespecial', 'width'),
                    'keys' => get_config('atto_pastespecial', 'keys'));
    return $params;
}
