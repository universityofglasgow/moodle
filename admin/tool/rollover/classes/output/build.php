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
 * @package    tool_rollover
 * @copyright  2019 Howard Miller <howardsmiller@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rollover\output;

defined('MOODLE_INTERNAL') || die;

use renderable;
use renderer_base;
use templatable;
use context;
use context_course;

class build implements renderable, templatable {

    protected $counts;

    public function __construct($counts) {
        $this->counts = $counts;

    }

    public function export_for_template(renderer_base $output) {
        return [
            'countwaiting' => $this->counts['waiting'],
            'countbackup' => $this->counts['backup'],
            'countrestore' => $this->counts['restore'],
            'urlsettings' => new \moodle_url('/admin/settings.php', ['section' => 'tool_rollover']),
            'urlbuild' => new \moodle_url('/admin/tool/rollover/index.php', ['action' => 'build']),
            'urldelete' => new \moodle_url('/admin/tool/rollover/index.php', ['action' => 'delete']),
        ];
    }

}