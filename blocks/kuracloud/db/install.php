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
defined('MOODLE_INTERNAL') || die();

/**
 * Generate encryption keys on plugin install.
 *
 * @return void
 */
function xmldb_block_kuracloud_install() {

    // Generate encryption key.

    $fs = get_file_storage();

    // Prepare file record object.
    $fileinfo = array(
        'contextid' => context_system::instance()->id,
        'component' => 'block_kuracloud',
        'filearea' => 'tokenkey',
        'itemid' => 0,
        'filepath' => '/',
        'filename' => 'token.key');

    $fs->create_file_from_string($fileinfo, openssl_random_pseudo_bytes(32));
}
