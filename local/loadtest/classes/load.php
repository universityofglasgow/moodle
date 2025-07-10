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
 * @package    local_loadtest
 * @copyright  2025 Howard miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_loadtest;

class load {

    /**
     * Write load for this server
     */
    public static function write_load() {

        // Get address of server
        $serverip = $_SERVER['SERVER_ADDR'];

        // Get load average samples
        $loads = sys_getloadavg();

        // Get list of IP addresses
        $cache = \cache::make('local_loadtest', 'load');
        if ($iplist = $cache->get('ips')) {
            $ips = explode(',', $iplist);
        } else {
            $ips = [];
        }
        $ips[] = $serverip;
        $ips = array_unique($ips);
        $cache->set('ips', implode(',', $ips));

        // Write the load array for this IP
        $cache->set($serverip, implode(',', $loads));
    }

    /**
     * Get loads in suitable format for web service
     * [
     *     [
     *         'ip' => 'ip address',
     *         'load1' => '1 minute load',
     *         'load5' => '5 minute load',
     *         'load15' => '15 minute load',
     *     ]
     * ]
     * @return array
     */
    public static function get_loads() {
        $cache = \cache::make('local_loadtest', 'load');
        if ($iplist = $cache->get('ips')) {
            $ips = explode(',', $iplist);
        } else {
            $ips = [];
        }

        // Format for WS.
        $list = [];
        foreach ($ips as $ip) {
            if ($loads = $cache->get($ip)) {
                [$load1, $load5, $load15] = explode(',', $loads);
                $list[] = [
                    'ip' => $ip,
                    'load1' => $load1,
                    'load5' => $load5,
                    'load15' => $load15,
                ];
            }
        }

        return $list;
    }

    /**
     * Get Redis stats
     * @return array
     */
    public static function get_redis() {
        $factory = \core_cache\factory::instance();
        $config = $factory->create_config_instance();
        $stores = $config->get_all_stores();

        $servers = [];
        foreach ($stores as $key => $store) {
            if ($store['plugin'] != 'redis') {
                continue;
            }

            $configuration = $store['configuration'];

            $redis = new \Redis([
                'host' => $configuration['server'],
            ]);
            
            $count = $redis->dbSize();
            $infoitems = $redis->info();
            
            $info = [
                [
                    'name' => 'keycount',
                    'value' => $count,
                ]
            ];
            foreach ($infoitems as $name => $value) {
                $info[] = [
                    'name' => $name,
                    'value' => $value,
                ];
            }

            $servers[] = [
                'server' => $key,
                'info' => $info,
            ];
        }

        return $servers;
    }

    /** 
     * Get event counts
     * @param int $startime
     * @param array $events
     * @return array
     */
    public static function get_eventcounts(int $starttime, array $events) {
        global $DB;

        $counts = [];
        foreach ($events as $event) {
            $component = $event['component'];
            $action = $event['action'];
            $target = $event['target'];
            $sql = "
                SELECT COUNT(*) FROM {logstore_standard_log} 
                WHERE component = :component 
                AND action = :action 
                AND target = :target
                AND timecreated > :starttime";
            $count = $DB->count_records_sql($sql, [
                'component' => $component,
                'action' => $action,
                'target' => $target,
                'starttime' => $starttime,
            ]);
            $counts[] = [
                'component' => $component,
                'action' => $action,
                'target' => $target,
                'count' => $count
            ];
        }

        return $counts;
    }

    /**
     * Get various stats
     * @return array
     */
    public static function get_stats() {
        global $CFG, $DB;

        // User count (not deleted).
        $usercount = $DB->count_records('user', ['deleted' => 0]);

        // User count (deleted).
        $deletedusers = $DB->count_records('user', ['deleted' => 1]);

        // Suspended users (not deleted).
        $suspendedusercount = $DB->count_records('user', ['deleted' => 0, 'suspended' => 1]);

        // Moodledata size
        $moodledatasize = get_directory_size($CFG->dataroot);

        // Filedir size
        $filedirsize = get_directory_size($CFG->dataroot . '/filedir');

        // Number of courses
        $coursecount = $DB->count_records('course');

        $stats = [
            [
                'name' => 'usercount',
                'value' => $usercount,
            ],
            [
                'name' => 'deletedusercount',
                'value' => $deletedusers,
            ],
            [
                'name' => 'suspendedusercount',
                'value' => $suspendedusercount,
            ],
            [
                'name' => 'moodledatasize',
                'value' => $moodledatasize,
            ],
            [
                'name' => 'filedirsize',
                'value' => $filedirsize,
            ],
            [
                'name' => 'coursecount',
                'value' => $coursecount,
            ],
        ];

        return $stats;
    }

    /**
     * Get database connections and so forth.
     * @return array
     */
    public static function get_database() {
        global $DB;

        // Processes
        $sql = 'SHOW FULL PROCESSLIST';
        $processes = $DB->get_records_sql($sql);

        // Status
        $sql = 'SHOW GLOBAL STATUS';
        $statusitems = $DB->get_records_sql($sql);
        $status = [];
        foreach ($statusitems as $item) {
            $status[] = [
                'name' => $item->variable_name,
                'value' => $item->value,
            ];
        }

        return [
            'processes' => array_values($processes),
            'status' => $status,
        ];
    }
}