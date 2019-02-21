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
 * @package    local_corehr
 * @copyright  2016 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/locallib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage(
            'local_corehr', get_string('pluginname', 'local_corehr'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext(
            'local_corehr/wsdltraining', get_string('wsdltraining', 'local_corehr'),
            get_string('configwsdltraining', 'local_corehr'), '', PARAM_URL));

    $settings->add(new admin_setting_configtext(
            'local_corehr/wsdlextract', get_string('wsdlextract', 'local_corehr'),
            get_string('configwsdlextract', 'local_corehr'), '', PARAM_URL));

    $settings->add(new admin_setting_configtext(
            'local_corehr/username', get_string('username', 'local_corehr'),
            '', '', PARAM_ALPHANUM));

    $settings->add(new admin_setting_configpasswordunmask(
            'local_corehr/password', get_string('password', 'local_corehr'),
            '', '', PARAM_RAW));
}
