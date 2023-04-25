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
 * View
 *
 * @package   block_newgu_spdetails
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$heading = get_string('pluginname', 'block_newgu_spdetails');
$url = new \moodle_url('/blocks/newgu_spdetails/view.php');

require_login();

require_once('locallib.php');

$context = \context_system::instance();


// Page setup.
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_heading($heading);

$returnurl = optional_param('returnurl', '', PARAM_URL);

echo $OUTPUT->header();



    $html = html_writer::start_tag('div', array('id' => 'spdetails'));
    $html .= html_writer::tag('p', '<img src="img/loader.gif">', array('style' => 'text-align:center;'));
    $html .= html_writer::end_tag('div');

    echo $html;

    $PAGE->requires->js_amd_inline("
                                    require(['jquery'], function(\$) {

                                    $.ajax({
                                    url: 'ajax.php',
                                    type: 'POST',
                                    data: {request: 'loadspdetails'},
                                    success: function (data) {
                                        if (data !== '') {
                                            $('#spdetails').html(data);
                                        }
                                    }
                                    });

                                    });
                                    ");

echo $OUTPUT->footer();
