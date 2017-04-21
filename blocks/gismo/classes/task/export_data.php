<?php

/**
 * GISMO block
 * This class represents the task that must be executed in order 
 * to export all the data from moodle tables to gismo tables
 * 
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gismo\task;

use block_gismo;

class export_data extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('export_data_task', 'block_gismo');
    }

    public function execute() {
        global $CFG;

        // trace start
        mtrace("\nGISMO - task (start)!");

        $gdm = new block_gismo\GISMOdata_manager(false);

        // purge
        $purge_check = $gdm->purge_data();
        if ($purge_check === true) {
            mtrace("Gismo data has been purged successfully!");
        } else {
            mtrace($purge_check);
        }

        // sync
        $sync_check = $gdm->sync_data();
        if ($sync_check === true) {
            mtrace("Gismo data has been syncronized successfully!");
        } else {
            mtrace($sync_check);
        }

        // trace end
        mtrace("GISMO - task (end)!");

        // ok     
        return true;
    }

}
