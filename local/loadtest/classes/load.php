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
            
            $info = [];
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
}