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
 * Configure local_gugrades admingrade
 *
 * @package     local_gugrades
 * @author      Howard Miller
 * @copyright   2025
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\adminsetting;

use html_writer;

/**
 * Custom admit setting.
 */
class admin_setting_admingrade extends \admin_setting {

    /**
     * Returns current value of this setting
     * @return mixed array or string depending on instance, NULL means not set yet
     */
    public function get_setting() {
        $setting = $this->config_read($this->name);
        if ($setting) {
            return json_decode($setting);
        } else {
            return null;
        }
    }

    /**
     * Store new setting
     *
     * @param mixed $data string or array, must not be NULL
     * @return string empty string if ok, string error message otherwise
     */
    public function write_setting($data) {
        $result = $this->config_write($this->name, json_encode($data));
        return ($result ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Output HTML
     * @param object $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query='') {
        global $OUTPUT;

        $default = $this->get_defaultsetting();
        if (!$data) {
            $data = (object) [
                'code' => $default['code'],
                'description' => $default['description'],
            ];
        }

        if (!$default) {
            throw new \moodle_exception('Default admingrade data must be provided');
        }
        $defaultstring = '[' . $default['code'] . '] ' . $default['description'];

        $context = (object) [
            'name' => $this->get_full_name(),
            'id' => $this->get_id(),
            'code' => $data->code,
            'description' => $data->description,
            'value-code' => $data->code,
            'value-description' => $data->description,
            'readonly' => $this->is_readonly(),
        ];
        $element = $OUTPUT->render_from_template('local_gugrades/setting_admingrade', $context);

        return format_admin_setting($this, $this->visiblename, $element, $this->description, false, '', $defaultstring, $query);
    }
}
