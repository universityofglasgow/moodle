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
 * Currency repository.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\currency;
defined('MOODLE_INTERNAL') || die();

use core_collator;
use DirectoryIterator;

/**
 * Currency repository.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class currency_repository {   // No interface for now.

    /** @var DirectoryIterator The directory iterator. */
    protected $dir;

    /**
     * Constructor.
     */
    public function __construct(DirectoryIterator $dir) {
        $this->dir = $dir;
    }

    /**
     * Get all the currencies.
     *
     * @return object[] Indexed by code, including properties name and code.
     */
    public function get_currencies() {
        return $this->read_currencies();
    }

    /**
     * Read the currencies.
     *
     * @return object[] Indexed by code, including properties name and code.
     */
    protected function read_currencies() {
        $data = [];
        foreach ($this->dir as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot() || !$fileinfo->isReadable()) {
                continue;
            }

            $code = $fileinfo->getFilename();
            $firstchar = substr($code, 0, 1);
            if ($firstchar === '.' || $firstchar === '_') {
                continue;
            }
            $metafile = $fileinfo->getPathname() . '/meta.json';
            if (strlen($code) > 32 || $code !== clean_param($code, PARAM_SAFEDIR) || !is_readable($metafile)) {
                continue;
            }
            $infos = json_decode(file_get_contents($metafile));
            if (!$infos) {
                continue;
            }
            if (empty($infos->name)) {
                continue;
            }

            $infos->code = $code;
            $data[$code] = $infos;
        }
        core_collator::asort_objects_by_property($data, 'name', core_collator::SORT_NATURAL);
        return $data;
    }

}
