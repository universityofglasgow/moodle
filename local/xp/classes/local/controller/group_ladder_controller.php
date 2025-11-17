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
 * Group ladder controller.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;

use block_xp\local\utils\text_utils;
use help_icon;
use moodle_exception;
use local_xp\local\config\default_course_world_config;
use local_xp\local\shortcode\handler;

/**
 * Group ladder controller class.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group_ladder_controller extends \block_xp\local\controller\page_controller {

    /** @var bool */
    protected $requiremanage = false;
    /** @var bool */
    protected $supportsgroups = false;
    /** @var string */
    protected $routename = 'group_ladder';
    /** @var string */
    protected $navname = 'ladder';

    protected function define_optional_params() {
        return [
            ['download', '', PARAM_ALPHA, false],
            ['downloadfilename', '', PARAM_NOTAGS, false],
        ];
    }

    protected function is_visible_to_viewers() {
        return $this->world->get_config()->get('enablegroupladder') != default_course_world_config::GROUP_LADDER_NONE;
    }

    protected function page_setup() {
        global $PAGE;
        parent::page_setup();
        $PAGE->add_body_class('block_xp-ladder');
        $PAGE->add_body_class('block_xp-group-ladder');
    }

    protected function pre_content() {
        parent::pre_content();

        // We must send the table before the output starts.
        $table = $this->get_table();
        if ($table->is_downloading()) {
            \core\session\manager::write_close();
            $table->out(0, false);   // Page size is irrelevant when downloading.
            die();
        }
    }

    protected function get_download_filename(): string {
        return $this->get_param('downloadfilename') ?: 'xp-team-leaderboard-' . $this->world->get_context()->id;
    }

    protected function get_highlighted_ids() {
        global $USER;
        $helper = \block_xp\di::get('grouped_leaderboard_helper');
        return $helper->get_user_group_ids($USER, $this->world);
    }

    protected function get_leaderboard() {
        $factory = \block_xp\di::get('course_world_grouped_leaderboard_factory');
        return $factory->get_course_grouped_leaderboard($this->world);
    }

    protected function get_table() {
        global $USER;
        $table = new \local_xp\output\group_leaderboard_table(
            $this->get_leaderboard(),
            $this->get_renderer(),
            $this->get_highlighted_ids()
        );
        $table->define_baseurl($this->pageurl->get_compatible_url());

        // Managers can download the table.
        $canmanage = $this->world->get_access_permissions()->can_manage();
        if ($canmanage) {
            $table->is_downloadable(true);
            $table->is_downloading($this->get_param('download'), $this->get_download_filename());
            $table->show_download_buttons_at([]);
        }

        return $table;
    }

    protected function get_page_html_head_title() {
        return get_string('teamleaderboard', 'block_xp');
    }

    protected function get_page_heading() {
        return get_string('teamleaderboard', 'block_xp');
    }

    protected function page_content() {
        global $PAGE;
        $output = $this->get_renderer();
        $PAGE->requires->js_call_amd('block_xp/modal', 'registerSimpleOpenModalActionObserver');

        $canmanage = $this->world->get_access_permissions()->can_manage();
        if ($canmanage) {
            echo $output->advanced_heading(get_string('teamleaderboard', 'block_xp'), [
                'intro' => new \lang_string('teamleaderboardintro', 'block_xp'),
                'help' => new help_icon('teamleaderboard', 'block_xp'),
                'visible' => $this->is_visible_to_viewers(),
                'menu' => $this->get_page_menu_items(),
            ]);

            if ($this->world->get_config()->get('enablegroupladder') == default_course_world_config::GROUP_LADDER_NONE) {
                echo $output->notification_without_close(get_string('leaderboardnotsetup', 'local_xp'), 'info');
                return;
            }
        }

        echo $this->get_table()->out(20, false);
    }

    /**
     * Get page menu items.
     *
     * @return array
     */
    protected function get_page_menu_items() {
        if (method_exists(parent::class, 'get_page_menu_items')) {
            $items = parent::get_page_menu_items();
        } else {
            $items = [];
        }

        $randomid = \html_writer::random_id();
        $plugman = \core_plugin_manager::instance();
        $shortcodes = $plugman->get_plugin_info('filter_shortcodes');
        $context = $this->world->get_context();
        $embeddata = [
            'isavailable' => (bool) $shortcodes && ($shortcodes->is_enabled() ?? true),
            'isenabled' => true,
            'introformatted' => text_utils::markdown_light(get_string('shortcodexpteamladderembedintro', 'block_xp')),
            'pluginrequiredformatted' => text_utils::markdown_light(get_string('pluginshortcodesrequiredtousefeature', 'block_xp')),
            'snippet' => "[xpteamladder ctx={$context->id} secret=" . handler::get_xpteamladder_secret($context) . "]",
        ];
        echo $this->get_renderer()->json_script($embeddata, $randomid);

        return array_merge($items, [
            [
                'label' => get_string('pagesettings', 'block_xp'),
                'data-xp-action' => 'open-form',
                'data-form-class' => 'local_xp\form\team_leaderboard',
                'data-form-args__contextid' => $this->world->get_context()->id,
                'href' => '#',
            ],
            [
                'label' => get_string('embedleaderboard', 'block_xp'),
                'data-xp-action' => 'open-modal',
                'data-template' => 'local_xp/shortcode-xpteamladder-embed',
                'data-template-data' => $randomid,
                'data-modal-title' => get_string('embedleaderboard', 'block_xp'),
                'href' => '#',
            ],
            [
                'label' => get_string('export', 'block_xp'),
                'data-xp-action' => 'open-form',
                'data-form-class' => 'local_xp\form\table_download',
                'data-form-args__contextid' => $this->world->get_context()->id,
                'data-form-args__filename' => $this->get_download_filename(),
                'data-form-args__pageurl' => $this->pageurl->out_as_local_url(false),
                'data-modal-buttons__save__label' => get_string('export', 'block_xp'),
                'href' => '#',
            ],
        ]);
    }
}
