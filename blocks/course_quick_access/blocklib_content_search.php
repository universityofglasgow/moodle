<?php
// This file is part of course-quick-access plugin for Moodle
// http://moodle.org/
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
 * Contains all functions required by the blocks search-form.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/consts.php');

/**
 * Returns a HTML-form, with a search field and an optional submit-button,
 * as a string.
 *
 * @param boolean $showsubmitbutton wether to show a submit button in the 
 * the search form or not
 * @param boolean $submit2line wether to show a submit button on the 
 * second or the first line
 * @return string The HTML form element as a string.
 * 
 */
function get_search_form($showsubmitbutton=true, $submit2line=false) {
    global $CFG;
    // HTML search field
    $fieldparams = array(
        'id' => 'qa-search',
        'name' => 'search',
        'class' => 'qa-search',
        'style' => 'width: 134px',
        // Do not need browser autocompletion because of own js,
        // also overlapps with the course list
        'autocomplete' => 'off'
    ); 
    $searchfield = html_writer::empty_tag('input', $fieldparams);
    // HTML (search-) submit button
    $buttonparams = array(
        'type' => 'submit',
        'id' => 'qa-searchsubmit',
        'class' => 'qa-searchsubmit',
        'value' => get_string('search')//'Search'
    );
    $submitbutton = html_writer::empty_tag('input', $buttonparams);
    // HTML search-form parameters
    $formparams = array(
        'id' => 'qa-searchform',
        'action' => MOODLE_COURSE_SEARCH_URL,
        'method' => 'get',
        'style' => 'text-align: center'
    );
    // Create form and return it
    if ($showsubmitbutton == true)  {
        if ($submit2line == true) {
            $formcontent = $searchfield.'<br/>'.$submitbutton;
        } else {
            $formcontent = $searchfield.$submitbutton;
        }
    } else {
        $formcontent = $searchfield;
    }
    $search_form = html_writer::tag('form', $formcontent, $formparams);
    return $search_form;
}