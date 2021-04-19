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
 * Currency sign URL resolver.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\currency;
defined('MOODLE_INTERNAL') || die();

use context_system;
use moodle_url;

/**
 * Currency sign URL resolver.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_sign_url_resolver implements currency_sign_url_resolver {

    /**
     * The sign.
     *
     * @return string
     */
    public function get_currency_sign_url() {
        $fs = get_file_storage();
        $context = context_system::instance();
        $files = $fs->get_area_files($context->id, 'local_xp', 'defaultcurrency', 0, '', false);
        $file = null;

        foreach ($files as $candidate) {
            if (strpos($candidate->get_mimetype(), 'image/') !== 0) {
                continue;
            }
            $file = $candidate;
            break;
        }

        $url = null;
        if ($file) {
            $url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename() . '/' . $file->get_timemodified()
            );
        }

        return $url;
    }

}
