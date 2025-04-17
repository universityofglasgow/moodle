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
 * Admin visuals controller.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;

use block_xp\di;
use block_xp\local\world;
use context_system;
use html_writer;
use local_xp\local\config\default_course_world_config;

/**
 * Admin visuals controller class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_visuals_controller extends \block_xp\local\controller\admin_visuals_controller {

    /** @var config The admin config. */
    protected $adminconfig;

    /**
     * File manager context.
     *
     * @return context
     */
    protected function get_currency_filemanager_context() {
        return context_system::instance();
    }

    /**
     * File manager options.
     *
     * @return array
     */
    protected function get_currency_filemanager_options() {
        return [
            'subdirs' => 0,
            'accepted_types' => ['.jpg', '.png', '.svg'],
            'maxfiles' => 1,
        ];
    }

    /**
     * Define the form.
     *
     * @return moodleform
     */
    protected function define_form() {
        return new \local_xp\form\visuals($this->pageurl->out(false), [
            'fmoptions' => $this->get_filemanager_options(),
            'currencyfmoptions' => $this->get_currency_filemanager_options(),
        ]);
    }

    /**
     * Get the initial form data.
     *
     * @return array
     */
    protected function get_initial_form_data() {
        $data = parent::get_initial_form_data();

        $draftitemid = file_get_submitted_draft_itemid('currency');
        file_prepare_draft_area($draftitemid, $this->get_currency_filemanager_context()->id, 'local_xp', 'defaultcurrency',
            0, $this->get_currency_filemanager_options());
        $data['currency'] = $draftitemid;
        $data['currencytheme'] = $this->adminconfig->get('currencytheme');
        $data['badgetheme'] = $this->adminconfig->get('badgetheme');

        return $data;
    }

    protected function pre_content() {
        $this->adminconfig = \block_xp\di::get('config');
        parent::pre_content();
        $form = $this->get_form();
        $form->init_page_requirements();
    }

    protected function reset_world_to_defaults(world $world) {
        parent::reset_world_to_defaults($world);

        $config = $world->get_config();
        $config->set_many([
            'badgetheme' => $this->adminconfig->get('badgetheme'),
            'currencystate' => default_course_world_config::CURRENCY_USE_DEFAULT,
            'currencytheme' => $this->adminconfig->get('currencytheme'),
        ]);

        $fs = get_file_storage();
        $fs->delete_area_files($world->get_context()->id, 'local_xp', 'currency', 0);
    }

    /**
     * Save the form data.
     *
     * @param stdClass $data The form data.
     * @return void
     */
    protected function save_form_data($data) {
        parent::save_form_data($data);
        file_save_draft_area_files($data->currency, $this->get_currency_filemanager_context()->id, 'local_xp',
            'defaultcurrency', 0, $this->get_currency_filemanager_options());
        $this->adminconfig->set('badgetheme', $data->badgetheme);
        $this->adminconfig->set('currencytheme', $data->currencytheme);
    }

    /**
     * Print the preview part.
     *
     * @return void
     */
    protected function preview() {
        $output = $this->get_renderer();
        echo html_writer::div($output->xp_preview(rand(20, 9999)), 'xp-mb-4');
        parent::preview();
    }


}
