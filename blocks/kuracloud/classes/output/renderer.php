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

use plugin_renderer_base;
use renderable;


/**
 * Block kuraCloud renderer class.
 *
 * @package    block_kuracloud
 * @copyright  2017 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render list of API tokens/endpoints
     *
     * @param renderable $tokenlist
     * @return void
     */
    public function render_token_list(renderable $tokenlist) {
        $data = $tokenlist->export_for_template($this);
        return parent::render_from_template('block_kuracloud/tokenlist', $data);
    }

    /**
     * Render the block contents
     *
     * @param renderable $blockcontent
     * @return void
     */
    public function render_block_content(renderable $blockcontent) {
        $data = $blockcontent->export_for_template($this);
        return parent::render_from_template('block_kuracloud/blockcontent', $data);
    }

    /**
     * Render sync-users dialog
     *
     * @param renderable $syncusers
     * @return void
     */
    public function render_syncusers(renderable $syncusers) {
        $data = $syncusers->export_for_template($this);
        return parent::render_from_template('block_kuracloud/syncusers', $data);
    }
}