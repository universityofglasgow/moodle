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
 * Visuals controller.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;

use block_xp\di;
use context_system;
use html_writer;
use local_xp\local\config\default_course_world_config;

/**
 * Visuals controller class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class visuals_controller extends \block_xp\local\controller\visuals_controller {

    /**
     * Get manager context.
     *
     * @return context
     */
    final protected function get_currency_filemanager_context() {
        return $this->world->get_context();
    }

    /**
     * Get file manager options.
     *
     * @return array
     */
    final protected function get_currency_filemanager_options() {
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
     * Pre content.
     *
     * @return void
     */
    protected function pre_content() {
        parent::pre_content();

        // Override for the sole purpose of calling init_page_requirements after
        // we have had a chance to save the form, otherwise the redirect is botched
        // because the page requirements output stuff.
        $form = $this->get_form();
        $form->init_page_requirements();
    }

    /**
     * Get the initial form data.
     *
     * @return array
     */
    protected function get_initial_form_data() {
        $data = parent::get_initial_form_data();
        $draftitemid = file_get_submitted_draft_itemid('currency');
        $config = $this->world->get_config();

        if ($config->get('currencystate') == default_course_world_config::CURRENCY_USE_DEFAULT) {
            file_prepare_draft_area($draftitemid, context_system::instance()->id, 'local_xp', 'defaultcurrency', 0,
                $this->get_currency_filemanager_options());
        } else {
            file_prepare_draft_area($draftitemid, $this->get_currency_filemanager_context()->id, 'local_xp', 'currency', 0,
                $this->get_currency_filemanager_options());
        }

        $data['currency'] = $draftitemid;
        $data['currencytheme'] = $config->get('currencytheme');
        $data['badgetheme'] = $config->get('badgetheme');

        return $data;
    }

    /**
     * Reset visuals to defaults.
     */
    protected function reset_visuals_to_defaults() {
        parent::reset_visuals_to_defaults();
        $adminconfig = di::get('config');

        $config = $this->world->get_config();
        $config->set_many([
            'badgetheme' => $adminconfig->get('badgetheme'),
            'currencystate' => default_course_world_config::CURRENCY_USE_DEFAULT,
            'currencytheme' => $adminconfig->get('currencytheme'),
        ]);

        $fs = get_file_storage();
        $fs->delete_area_files($this->get_filemanager_context()->id, 'local_xp', 'currency', 0);
    }

    /**
     * Save the form data.
     *
     * @param stdClass $data The form data.
     * @return void
     */
    protected function save_form_data($data) {
        parent::save_form_data($data);
        $config = $this->world->get_config();

        // Save the area.
        file_save_draft_area_files($data->currency, $this->get_currency_filemanager_context()->id, 'local_xp', 'currency', 0,
            $this->get_currency_filemanager_options());

        $config->set_many([
            'currencystate' => default_course_world_config::CURRENCY_IS_CUSTOMIED,
            'currencytheme' => $data->currencytheme,
            'badgetheme' => $data->badgetheme,
        ]);
    }

    /**
     * Preview.
     *
     * @return void
     */
    protected function preview() {
        $output = $this->get_renderer();
        echo html_writer::div($output->xp_preview(rand(20, 9999), $this->courseid), 'xp-mb-4');
        parent::preview();
    }

}
