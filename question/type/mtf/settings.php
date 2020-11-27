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
 * @package     qtype_mtf
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/question/type/mtf/lib.php');

    // Introductory explanation that all the settings are defaults for the edit_mtf_form.
    $settings->add(new admin_setting_heading('configintro', '', get_string('configintro', 'qtype_mtf')));

    // Scoring methods.
    $options = array('mtfonezero' => get_string('scoringmtfonezero', 'qtype_mtf'),
        'subpoints' => get_string('scoringsubpoints', 'qtype_mtf')
    );

    $settings->add(new admin_setting_configselect('qtype_mtf/scoringmethod',
        get_string('configscoringmethod', 'qtype_mtf'),
        get_string('scoringmethod_help', 'qtype_mtf'), 'subpoints', $options));

    // Show Scoring Method in quizes.
    $settings->add(new admin_setting_configcheckbox('qtype_mtf/showscoringmethod',
        get_string('showscoringmethod', 'qtype_mtf'),
        get_string('showscoringmethod_help', 'qtype_mtf'), 0));

    // Shuffle options.
    $settings->add(new admin_setting_configcheckbox('qtype_mtf/shuffleanswers',
        get_string('shuffleanswers', 'qtype_mtf'),
        get_string('shuffleanswers_help', 'qtype_mtf'), 1));
}
