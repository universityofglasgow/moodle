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
 * GUID Enrolment sync
 *
 * @package    local_gusync
 * @copyright  2017 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_backupcleaner\task;

defined('MOODLE_INTERNAL') || die;

class deletebackups extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('deletebackups', 'local_backupcleaner');
    }

    public function execute() {
	    global $DB;
	    
        $days = get_config('local_backupcleaner', 'min_age');
        
        $files = $DB->get_records_sql('SELECT * FROM {files} WHERE mimetype="application/vnd.moodle.backup" AND timecreated < '.(time() - ( $days * 86400)));
        
        $fs = get_file_storage();
        
        $identified = 0;
        $deleted = 0;
        
        foreach($files as $thisFile) {
	        
	        $file = $fs->get_file($thisFile->contextid, $thisFile->component, $thisFile->filearea,  $thisFile->itemid, $thisFile->filepath, $thisFile->filename);
	        
	        $identified += 1;
	        
	        if ($file) {
			    $file->delete();
			    $deleted += 1;
			}
        }
        
        mtrace('Identified '.$identified.' crusty backups and successfully deleted '.$deleted.' of them.');
    }

}
