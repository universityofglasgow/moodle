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

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$context = \context_system::instance();
require_capability('block/kuracloud:configureapi', $context);

admin_externalpage_setup('kuracloudconfig');
$url = new \moodle_url('/blocks/kuracloud/manageendpoints.php');

$manageendpointsstr = get_string('manageendpoints', 'block_kuracloud');


$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($manageendpointsstr);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($manageendpointsstr, $url);

$output = $PAGE->get_renderer('block_kuracloud');
echo $output->header();
echo $output->heading($manageendpointsstr);

$allendpoints = endpoints::get_all();

foreach ($allendpoints as $key => $endpoint) {
    $err = null;

    try {
        $resp = $endpoint->api->get_instance();
    } catch (\Exception $e) {
        $err = $e->getMessage();
    }

    if (!is_null($err)) {
        $endpoint->status = clean_param($err, PARAM_TEXT);
        $endpoint->lmsenabled = get_string('disabled', 'block_kuracloud');
        $endpoint->name = isset($endpoint->name) ? $endpoint->name : get_string('unknown', 'block_kuracloud');
    } else {
        $endpoint->status = get_string('apistatus:ok', 'block_kuracloud');
        $endpoint->lmsenabled = get_string($resp->lmsEnabled == 1 ? 'enabled' : 'disabled', 'block_kuracloud');
        $endpoint->name = clean_param($resp->displayName, PARAM_TEXT);
    }

    $allendpoints[$key] = $endpoint;
}

$list = new \block_kuracloud\output\token_list($allendpoints);
echo $output->render($list);

echo $output->footer();