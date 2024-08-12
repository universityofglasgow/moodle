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
 * Date select for admin pages.
 *
 * @package     local_slcodesnippets
 * @author      Davo Smith
 * @copyright   2022 Synergy Learning
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\adminsetting;

use html_writer;

defined('MOODLE_INTERNAL') || die();

class admin_setting_configdate extends \admin_setting {

    /**
     * Returns current value of this setting
     * @return mixed array or string depending on instance, NULL means not set yet
     */
    public function get_setting() {
        return $this->config_read($this->name);
    }

    /**
     * Store new setting
     *
     * @param mixed $data string or array, must not be NULL
     * @return string empty string if ok, string error message otherwise
     */
    public function write_setting($data) {
        if (!is_array($data)) {
            $timestamp = (int)$data; // Called with an integer when setting the initial default.
        } else {
            $timestamp = make_timestamp($data['year'], $data['mon'], $data['mday'], $data['hours'], $data['minutes']);
        }
        $result = $this->config_write($this->name, $timestamp);
        return ($result ? '' : get_string('errorsetting', 'admin'));
    }

    public function output_html($data, $query='') {
        $default = $this->get_defaultsetting();
        if ($default) {
            $defaultinfo = userdate($default, get_string('strftimedatetime', 'langconfig'));
        } else {
            $defaultinfo = 0;
        }

        if (!is_array($data)) {
            $data = usergetdate($data);
        }

        $yearnow = intval(userdate(time(), '%Y'));
        $monopts = array();
        for ($i = 1; $i <= 12; $i++) {
            $monopts[$i] = userdate(gmmktime(12, 0, 0, $i, 15, 2000), "%B");
        }
        $opts = array(
            'mday' => range(1, 31),
            'mon' => $monopts,
            'year' => range($yearnow - 10, $yearnow + 5),
            ' ',
            'hours' => range(0, 23),
            ':',
            'minutes' => range(0, 59),
        );

        $out = '';
        foreach ($opts as $type => $range) {
            if (!is_array($range)) {
                $out .= $range;
                continue;
            }
            if ($type != 'mon') {
                $range = array_combine($range, $range);
            }
            if ($type == 'hours' || $type == 'minutes') {
                $range = array_map(function($item) {
                    return sprintf('%02d', $item);
                }, $range);
            }
            $out .= html_writer::select($range, $this->get_full_name().'['.$type.']', $data[$type], null);
        }
        $out = html_writer::tag('div', $out, ['class' => 'form-date defaultsnext']);

        return format_admin_setting($this, $this->visiblename, $out, $this->description, false, '', $defaultinfo, $query);
    }
}