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
 * Administrative settings
 *
 * @package     format_cards
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $ADMIN, $CFG, $PAGE;

require_once("$CFG->dirroot/course/format/cards/lib.php");

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'format_cards',
        get_string('settings:name', 'format_cards')
    );

    $settings->add(new admin_setting_heading('format_cards_defaults',
        get_string('settings:defaults', 'format_cards'),
        get_string('settings:defaults:description', 'format_cards')
    ));

    $settings->add(new admin_setting_configselect('format_cards/section0',
        get_string('form:course:section0', 'format_cards'),
        get_string('form:course:section0_help', 'format_cards'),
        FORMAT_CARDS_SECTION0_COURSEPAGE,
        [
            FORMAT_CARDS_SECTION0_COURSEPAGE => get_string('form:course:section0:coursepage', 'format_cards'),
            FORMAT_CARDS_SECTION0_ALLPAGES => get_string('form:course:section0:allpages', 'format_cards'),
        ]
    ));

    $settings->add(new admin_setting_configselect('format_cards/sectionnavigation',
        get_string('form:course:sectionnavigation', 'format_cards'),
        get_string('form:course:sectionnavigation_help', 'format_cards'),
        $PAGE->theme->usescourseindex ? FORMAT_CARDS_SECTIONNAVIGATION_NONE : FORMAT_CARDS_SECTIONNAVIGATION_BOTH,
        [
            FORMAT_CARDS_SECTIONNAVIGATION_NONE => get_string('form:course:sectionnavigation:none', 'format_cards'),
            FORMAT_CARDS_SECTIONNAVIGATION_TOP => get_string('form:course:sectionnavigation:top', 'format_cards'),
            FORMAT_CARDS_SECTIONNAVIGATION_BOTTOM => get_string('form:course:sectionnavigation:bottom', 'format_cards'),
            FORMAT_CARDS_SECTIONNAVIGATION_BOTH => get_string('form:course:sectionnavigation:both', 'format_cards'),
        ]
    ));

    $settings->add(new admin_setting_configselect('format_cards/sectionnavigationhome',
        get_string('form:course:sectionnavigationhome', 'format_cards'),
        get_string('form:course:sectionnavigationhome_help', 'format_cards'),
        FORMAT_CARDS_SECTIONNAVIGATIONHOME_HIDE,
        [
            FORMAT_CARDS_SECTIONNAVIGATIONHOME_HIDE => get_string('form:course:sectionnavigationhome:hide', 'format_cards'),
            FORMAT_CARDS_SECTIONNAVIGATIONHOME_SHOW => get_string('form:course:sectionnavigationhome:show', 'format_cards'),
        ]
    ));

    $settings->add(new admin_setting_configselect('format_cards/cardorientation',
        get_string('form:course:cardorientation', 'format_cards'),
        '',
        FORMAT_CARDS_ORIENTATION_VERTICAL,
        [
            FORMAT_CARDS_ORIENTATION_VERTICAL => get_string('form:course:cardorientation:vertical', 'format_cards'),
            FORMAT_CARDS_ORIENTATION_HORIZONTAL => get_string('form:course:cardorientation:horizontal', 'format_cards'),
            FORMAT_CARDS_ORIENTATION_SQUARE => get_string('form:course:cardorientation:square', 'format_cards'),
        ]
    ));

    $settings->add(new admin_setting_configselect('format_cards/showsummary',
        get_string('form:course:showsummary', 'format_cards'),
        '',
        FORMAT_CARDS_SHOWSUMMARY_SHOW,
        [
            FORMAT_CARDS_SHOWSUMMARY_SHOW => get_string('form:course:showsummary:show', 'format_cards'),
            FORMAT_CARDS_SHOWSUMMARY_HIDE => get_string('form:course:showsummary:hide', 'format_cards'),
        ]
    ));

    $settings->add(new admin_setting_configselect('format_cards/showprogress',
        get_string('form:course:showprogress', 'format_cards'),
        '',
        FORMAT_CARDS_SHOWPROGRESS_SHOW,
        [
            FORMAT_CARDS_SHOWPROGRESS_SHOW => get_string('form:course:showprogress:show', 'format_cards'),
            FORMAT_CARDS_SHOWPROGRESS_HIDE => get_string('form:course:showprogress:hide', 'format_cards'),
        ]
    ));

    $settings->add(new admin_setting_configselect('format_cards/progressformat',
        get_string('form:course:progressformat', 'format_cards'),
        '',
        FORMAT_CARDS_PROGRESSFORMAT_PERCENTAGE,
        [
            FORMAT_CARDS_PROGRESSFORMAT_COUNT => get_string('form:course:progressformat:count', 'format_cards'),
            FORMAT_CARDS_PROGRESSFORMAT_PERCENTAGE => get_string('form:course:progressformat:percentage', 'format_cards'),
        ]
    ));

    $settings->add(new admin_setting_configselect('format_cards/subsectionsascards',
        get_string('form:course:subsectionsascards', 'format_cards'),
        '',
        FORMAT_CARDS_SUBSECTIONS_AS_ACTIVITIES,
        [
            FORMAT_CARDS_SUBSECTIONS_AS_CARDS => get_string('form:course:subsectionsascards:cards', 'format_cards'),
            FORMAT_CARDS_SUBSECTIONS_AS_ACTIVITIES => get_string('form:course:subsectionsascards:activity', 'format_cards'),
        ]
    ));
}
