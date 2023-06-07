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
 * Usage report maker.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\plugin;

use block_xp\di;

defined('MOODLE_INTERNAL') || die();

/**
 * Usage report maker class.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class usage_report_maker extends \block_xp\local\plugin\usage_report_maker {

    /** @var string Hash. */
    protected $lkh = 'b634b90203f576041529a7ec5a390baaa07c9431';

    /**
     * Make usage report.
     *
     * @return object Where keys represent usage.
     */
    public function make() {
        $data = parent::make();

        $config = di::get('config');

        $data->lkh = $this->lkh;
        $data->xp_drops = $this->db->count_records_select('local_xp_drops', 'enabled != ?', [0]);
        $data->xp_teamladders = $this->db->count_records_select('local_xp_config', 'enablegroupladder != ?', [0]);

        $mobilelastinit = $config->get('mobilelastinit');
        $mobilelastview = $config->get('mobilelastview');
        $data->xp_mobile_last_init_ago = $mobilelastinit ? max(0, time() - $mobilelastinit) : null;
        $data->xp_mobile_last_view_ago = $mobilelastview ? max(0, time() - $mobilelastview) : null;

        return $data;
    }

}
