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
 * File server.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\file;
defined('MOODLE_INTERNAL') || die();

use context;

/**
 * File server.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class file_server extends \block_xp\local\file\file_server implements \block_xp\local\file\component_file_server {

    /**
     * Serve a file.
     *
     * @param stdClass $course The course object.
     * @param stdClass $cm The course module object.
     * @param context $context The context object.
     * @param string $filearea The file area.
     * @param array $args List of arguments.
     * @param bool $forcedownload Whether or not to force the download of the file.
     * @param array $options Array of options.
     * @return void
     */
    public function serve_component_file($course, $cm, context $context, $filearea, $args, $forcedownload, array $options = []) {
        $file = false;
        $fs = $this->fs;

        // Check context consistency.
        if ($filearea == 'currency') {
            if ($this->forwholesite && $context->contextlevel !== CONTEXT_SYSTEM) {
                return false;
            } else if (!$this->forwholesite && $context->contextlevel !== CONTEXT_COURSE) {
                return false;
            }

        } else if ($filearea == 'defaultcurrency') {
            if ($context->contextlevel !== CONTEXT_SYSTEM) {
                return false;
            }
        }

        if ($filearea == 'currency' || $filearea == 'defaultcurrency') {
            $itemid = array_shift($args);
            $filename = array_shift($args);
            $filepath = '/';

            $file = $fs->get_file($context->id, 'local_xp', $filearea, $itemid, $filepath, $filename);

            // Make sure this is an image.
            if (!$file || strpos($file->get_mimetype(), 'image/') !== 0) {
                return false;
            }
        }

        if (!$file) {
            return false;
        }

        send_stored_file($file);
    }

}
