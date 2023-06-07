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
 * Drops controller.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;

use action_link;
use block_xp\di;
use block_xp\local\controller\page_controller;
use block_xp\local\routing\url;
use core_plugin_manager;
use html_writer;
use local_xp\form\drop;
use local_xp\output\drop_table;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Drops controller.
 *
 * @package    local_xp
 * @copyright  2022 Branch Up Pty Ltd
 * @author     Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class drops_controller extends page_controller {

    /** @var string The nav name. */
    protected $navname = 'rules';
    /** @var string The route name. */
    protected $routename = 'drops';
    /** @var bool Whether manage permissions are required. */
    protected $requiremanage = true;
    /** @var bool Whether drops are enabled. */
    protected $hasdependencies = true;
    /** @var moodleform The form. */
    protected $form;

    /**
     * @inheritDoc
     */
    protected function define_optional_params() {
        return [
            ['dropid', null, PARAM_INT],
            ['setupid', 0, PARAM_INT, false],
            ['deleteid', 0, PARAM_INT, false],
            ['confirm', 0, PARAM_BOOL, false],
        ];
    }

    /**
     * Get the drop record.
     *
     * @param int $id The drop ID.
     */
    protected function get_drop_record($id) {
        $db = di::get('db');
        if ($id > 0) {
            return $db->get_record('local_xp_drops', ['courseid' => $this->courseid, 'id' => $id], '*', MUST_EXIST);
        }
        return (object) ['courseid' => $this->courseid];
    }

    /**
     * @inheritDoc
     */
    protected function pre_content() {
        parent::pre_content();

        $pluginman = core_plugin_manager::instance();
        $plugininfo = $pluginman->get_plugin_info('filter_shortcodes');
        $this->hasdependencies = !empty($plugininfo) && $plugininfo->is_enabled();

        $deleteid = $this->get_param('deleteid');
        if ($deleteid && confirm_sesskey() && $this->get_param('confirm')) {
            $db = di::get('db');
            $db->delete_records('local_xp_drops', ['id' => $deleteid]);
            $this->redirect();
        }

        $dropid = $this->get_param('dropid');
        if ($this->hasdependencies && $dropid) {
            $iscreating = $dropid <= 0;
            $listurl = new url($this->pageurl);
            $listurl->remove_params('dropid');
            $form = $this->get_form($dropid);
            if ($data = $form->get_data()) {
                $record = $this->save_drop($data);
                if ($iscreating) {
                    $listurl->param('setupid', $record->id);
                }
                $this->redirect($listurl);

            } else if ($form->is_cancelled()) {
                $this->redirect($listurl);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function page_content() {
        global $PAGE;

        $output = $this->get_renderer();
        $dropid = $this->get_param('dropid');

        if ($this->hasdependencies && $dropid) {
            if ($dropid > 0) {
                echo $output->heading(get_string('editdrop', 'local_xp'), 3);
            } else {
                echo $output->heading(get_string('adddrop', 'local_xp'), 3);
            }
            $form = $this->get_form($dropid);
            $form->display();
            return;
        }

        $url = new url($this->pageurl);
        $url->param('dropid', -1);

        // If there is a drop setup to show.
        $setupid = $this->get_param('setupid');
        if ($setupid > 0) {
            $drop = $this->get_drop_record($setupid);
            if (!empty($drop->id)) {
                $editurl = new url($this->pageurl, ['editid' => $drop->id]);
                $name = format_string($drop->name, true, ['context' => $this->world->get_context()]);
                $setupdivid = html_writer::random_id();
                echo html_writer::div('', 'xp-hidden', [
                    'id' => $setupdivid,
                    'data-name' => $name,
                    'data-editurl' => $editurl->out(false),
                    'data-shortcode' => "[xpdrop {$drop->secret}]",
                ]);
                $PAGE->requires->js_call_amd('local_xp/modal-drop-setup', 'showFromSelector', ['#' . $setupdivid]);
            }
        }

        echo $output->advanced_heading(get_string('drops', 'local_xp'), [
            'actions' => $this->hasdependencies ? [
                new action_link($url, get_string('adddrop', 'local_xp'), null, ['class' => 'btn btn-secondary btn-default'])
            ] : [],
            'intro' => new \lang_string('dropsintro', 'local_xp'),
            'help' => new \help_icon('drops', 'local_xp'),
        ]);

        if (!$this->hasdependencies) {
            $pluginurl = new moodle_url('https://moodle.org/plugins/filter_shortcodes');
            $shortcodesurl = new moodle_url('https://docs.levelup.plus/xp/docs/how-to/use-shortcodes?ref=localxp_drops');
            echo $output->notification_without_close(markdown_to_html(get_string('filtershortcodesrequiredfordrops', 'local_xp', [
                'url' => $pluginurl->out(false),
                'shortcodesdocsurl' => $shortcodesurl->out(false)
            ])), 'warning');

        } else {
            echo $this->get_table()->out(20, true);
        }

    }

    /**
     * Save a drop.
     *
     * @param \stdClass $data The drop data to be persisted.
     */
    protected function save_drop($data) {
        $db = di::get('db');
        $dropid = $this->get_param('dropid');
        $record = $this->get_drop_record($dropid);

        if (!empty($record->id)) {
            $record->name = $data->name;
            $record->points = $data->points;
            $record->enabled = $data->enabled;
            $db->update_record('local_xp_drops', $record);
        } else {
            do {
                $secret = substr(bin2hex(random_bytes(128)), 0, 7);
            } while ($db->record_exists('local_xp_drops', ['secret' => $secret]));
            $record->secret = $secret;
            $record->name = $data->name;
            $record->points = $data->points;
            $record->enabled = $data->enabled;
            $record->id = $db->insert_record('local_xp_drops', $record);
        }

        return $record;
    }

    /**
     * @inheritDoc
     */
    protected function get_page_html_head_title() {
        return get_string('drops', 'local_xp');
    }

    /**
     * @inheritDoc
     */
    protected function get_page_heading() {
        return get_string('drops', 'local_xp');
    }

    /**
     * Get the drop table.
     *
     * @return \flexible_table
     */
    protected function get_table() {
        $table = new drop_table($this->world);
        $table->define_baseurl($this->pageurl);
        return $table;
    }

    /**
     * Get the drop form.
     *
     * @param int $id The drop ID.
     * @return \moodleform
     */
    protected function get_form($id = 0) {
        if (!$this->form) {
            $record = $this->get_drop_record($id);
            $this->form = new drop($this->pageurl->out(false), ['drop' => $record]);
            if ($id > 0) {
                $this->form->set_data($record);
            } else {
                $this->form->set_data(['points' => 50]);
            }
        }
        return $this->form;
    }

}
