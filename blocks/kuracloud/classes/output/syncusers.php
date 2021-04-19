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
 * kuraCloud integration block.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_kuracloud\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;

define("MAX_LIST_SIZE", 10);

/**
 * Block Content Renderable Class
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class syncusers implements renderable, templatable {

    /**
     * Users to be deleted
     *
     * @var array
     */
    private $todelete;

    /**
     * Users to be restored
     *
     * @var array
     */
    private $torestore;

    /**
     * Users to be updated
     *
     * @var array
     */
    private $toupdate;

    /**
     * Users to be added
     *
     * @var array
     */
    private $toadd;

    /**
     * Constructor
     *
     * @param array $toadd Names of users to be added
     * @param array $toupdate Names of users to be updated
     * @param array $todelete Names of users to be deleted
     * @param array $torestore Names of users to be restored
     */
    public function __construct($toadd, $toupdate, $todelete, $torestore) {
        $this->todelete = $todelete;
        $this->torestore = $torestore;
        $this->toupdate = $toupdate;
        $this->toadd = $toadd;
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $COURSE;

        $data = new stdClass();

        $data->max_items = MAX_LIST_SIZE;

        if (!empty($this->toadd) ||
                !empty($this->toupdate) ||
                !empty($this->todelete) ||
                !empty($this->torestore)
                ) {

            $data->has_changes = true;
        }

        if (count($this->toadd) <= MAX_LIST_SIZE) {
            $data->toadd = $this->toadd;
        }
        $data->has_toadd = !empty($this->toadd);
        $data->toaddcount = count($this->toadd);

        if (count($this->toupdate) <= MAX_LIST_SIZE) {
            $data->toupdate = $this->toupdate;
        }
        $data->has_toupdate = !empty($this->toupdate);
        $data->toupdatecount = count($this->toupdate);

        if (count($this->todelete) <= MAX_LIST_SIZE) {
            $data->todelete = $this->todelete;
        }
        $data->has_todelete = !empty($this->todelete);
        $data->todeletecount = count($this->todelete);

        if (count($this->torestore) <= MAX_LIST_SIZE) {
            $data->torestore = $this->torestore;
        }
        $data->has_torestore = !empty($this->torestore);
        $data->torestorecount = count($this->torestore);

        return $data;
    }
}