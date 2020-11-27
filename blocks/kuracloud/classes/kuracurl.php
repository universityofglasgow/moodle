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

namespace block_kuracloud;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');

/**
 * kuraCurl backports some curl functionality from Moodle 3.3
 *
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class kuraCurl extends \Curl {

    /**
     * HTTP PUT method
     *
     * Method copied form Moodle 3.3 as 3.1 version is lacking support for POST fields
     *
     * @param string $url
     * @param array $params
     * @param array $options
     * @return bool
     */
    public function put($url, $params = array(), $options = array()) {
        $file = '';
        $fp = false;
        if (isset($params['file'])) {
            $file = $params['file'];
            if (is_file($file)) {
                $fp   = fopen($file, 'r');
                $size = filesize($file);
                $options['CURLOPT_PUT']        = 1;
                $options['CURLOPT_INFILESIZE'] = $size;
                $options['CURLOPT_INFILE']     = $fp;
            } else {
                return null;
            }
            if (!isset($this->options['CURLOPT_USERPWD'])) {
                $this->setopt(array('CURLOPT_USERPWD' => 'anonymous: noreply@moodle.org'));
            }
        } else {
            $options['CURLOPT_CUSTOMREQUEST'] = 'PUT';
            $options['CURLOPT_POSTFIELDS'] = $params;
        }

        $ret = $this->request($url, $options);
        if ($fp !== false) {
            fclose($fp);
        }
        return $ret;
    }

}