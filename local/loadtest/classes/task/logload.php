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
 * Task to get loads and log them
 *
 * @package    local_loadtest
 * @copyright  2013 Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_loadtest\task;

defined('MOODLE_INTERNAL') || die;

class logload extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('logload', 'local_loadtest');
    }

    /**
     * Save the load averages in the db
     * @param string $host
     * @param float $load1 
     * @param float $load5
     * @param float $load15
     */
    protected function save_load($host, $load1, $load5, $load15) {
        global $DB;

        $local_loadtest = new \stdClass;
        $local_loadtest->host = $host;
        $local_loadtest->timestamp = time();
        $local_loadtest->load1 = $load1;
        $local_loadtest->load5 = $load5;
        $local_loadtest->load15 = $load15;
        $DB->insert_record('local_loadtest', $local_loadtest);
        mtrace('Saving data for ' . $host . ' - ' . $load1 . ' ' . $load5 . ' ' . $load15);
    }

    /**
     * Get load for remote host
     * @param string $host
     * @return array
     */
    protected function get_remote($host) {
        $url = $host . '/local/loadtest/load.php';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);
        if ($error) {
            mtrace('failed to get loads from ' . $host . ', error was ' . $error);
            return false;
        } else {
            $avgs = explode(PHP_EOL, $data);
            return $avgs;
        }
    }

    public function execute() {
        global $DB;

        // Get list of hosts
        $cfghosts = get_config('local_loadtest', 'hosts');
        $hosts = preg_split('/[\s]+/', $cfghosts);

        // Get loads for each host
        if (trim($cfghosts)) {
            foreach ($hosts as $host) {
                if (trim($host)) {
                    $avgs = $this->get_remote($host);
                    if ($avgs) {
                        $this->save_load($host, $avgs[0], $avgs[1], $avgs[2]);
                    }
                }
            }
        } else {

            // Just this server then
            $avgs = sys_getloadavg();
            $this->save_load('localhost', $avgs[0], $avgs[1], $avgs[2]);
        }
    }

}

