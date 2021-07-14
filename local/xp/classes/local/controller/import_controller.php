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
 * Import controller.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/csvlib.class.php');

use coding_exception;
use csv_import_reader;
use html_writer;
use moodle_url;
use block_xp\di;
use block_xp\local\controller\page_controller;
use block_xp\local\routing\url;
use block_xp\local\xp\state_store_with_reason;
use local_xp\local\reason\manual_reason;
use local_xp\local\provider\user_resolver;
use local_xp\local\provider\user_state_store_points;

/**
 * Import controller class.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import_controller extends page_controller {

    protected $confirmform;
    protected $form;
    protected $routename = 'import';

    protected function define_optional_params() {
        return [
            ['iid', null, PARAM_INT],
            ['action', -1, PARAM_INT],
            ['notify', false, PARAM_BOOL],
            ['preview', 10, PARAM_INT, false],
            ['confirm', false, PARAM_BOOL, false],
        ];
    }

    protected function get_form() {
        if (!$this->form) {
            $iid = csv_import_reader::get_new_iid('local_xp_import');
            $cir = new csv_import_reader($iid, 'local_xp_import');
            $this->form = new \local_xp\form\csv_import($this->pageurl->out(false), [
                'iid' => $iid,
                'cir' => $cir,
                'makeimporter' => function($cir, $action) {
                    return $this->get_provider($cir, $action);
                }
            ]);
        }
        return $this->form;
    }

    protected function get_provider($cir, $action) {
        global $USER;
        $action = (int) $action;
        return new \local_xp\local\provider\csv_user_state_store_points_provider($cir, new user_resolver(di::get('db')),
            $action, $action === user_state_store_points::ACTION_INCREASE ? new manual_reason($USER->id) : null);
    }

    protected function get_page_html_head_title() {
        return get_string('importpoints', 'local_xp');
    }

    protected function get_page_heading() {
        return get_string('importpoints', 'local_xp');
    }

    protected function pre_content() {
        $form = $this->get_form();
        if ($form->is_cancelled()) {
            return redirect($this->urlresolver->reverse('report', ['courseid' => $this->courseid]));

        } else if ($data = $form->get_data()) {
            $previewurl = new url($this->pageurl, ['iid' => $data->iid, 'action' => $data->resetoradd,
                'notify' => $data->sendnotification, 'sesskey' => sesskey()]);
            return redirect($previewurl->get_compatible_url());
        }
    }

    protected function get_ul_errors_list_from_array($errors) {
        return html_writer::tag(
            'ul',
            '<li>' . implode('</li><li>', array_map('s', $errors)) . '</li>',
            ['style' => 'margin: 0']
        );
    }

    protected function page_content_import() {
        global $USER;

        $output = $this->get_renderer();
        $iid = $this->get_param('iid');
        $action = $this->get_param('action');
        $notify = $this->get_param('notify');

        // Just in case.
        if (!confirm_sesskey() || !$this->get_param('confirm') || !$iid) {
            throw new coding_exception('Unexpected request.');
        }

        echo $output->heading(get_string('importresults', 'local_xp'), 3);

        $notifier = new \local_xp\local\notification\award_notifier(di::get('config'), $this->world, $USER);
        $store = $this->world->get_store();
        $supportsreason = $store instanceof state_store_with_reason;

        $cir = new csv_import_reader($iid, 'local_xp_import');
        $provider = $this->get_provider($cir, $action);

        $rows = [];
        $total = 0;
        $successful = 0;
        $failed = 0;
        foreach ($provider->getIterator() as $entry) {
            $total++;

            if (!$entry->is_valid()) {
                $rows[] = new \html_table_row([
                    $output->pix_icon('i/invalid', ''),
                    $entry->get_reference(),
                    $this->get_ul_errors_list_from_array($entry->get_errors())
                ]);
                $failed++;
                continue;
            }

            $usp = $entry->get_object();
            if ($usp->get_action() === user_state_store_points::ACTION_SET) {
                if ($supportsreason && $usp->get_reason()) {
                    $store->set_with_reason($usp->get_id(), $usp->get_points(), $usp->get_reason());
                } else {
                    $store->set($usp->get_id(), $usp->get_points());
                }
                $successful++;

            } else if ($usp->get_action() === user_state_store_points::ACTION_INCREASE) {
                if ($supportsreason && $usp->get_reason()) {
                    $store->increase_with_reason($usp->get_id(), $usp->get_points(), $usp->get_reason());
                } else {
                    $store->increase($usp->get_id(), $usp->get_points());
                }
                $successful++;

                if ($notify) {
                    $notifier->notify($usp->get_id(), $usp->get_points(), $usp->get_message());
                }
            }

        }

        $cir->cleanup();

        echo markdown_to_html(get_string('importresultsintro', 'local_xp', ['total' => $total, 'successful' => $successful,
            'failed' => $failed]));

        if (!empty($failed)) {
            $table = new \html_table();
            $table->head = ['', get_string('csvline', 'local_xp'), ''];
            $table->data = $rows;
            echo html_writer::table($table);
        }

        echo $output->single_button(
            $this->urlresolver->reverse('report', ['courseid' => $this->courseid])->get_compatible_url(),
            get_string('continue', 'core'),
            'get'
        );
    }

    protected function page_content_preview() {
        $output = $this->get_renderer();
        $iid = $this->get_param('iid');
        $action = $this->get_param('action');
        $npreview = max(1, $this->get_param('preview'));

        echo $output->heading(get_string('importpreview', 'local_xp'), 3);

        $reason = null;
        $store = $this->world->get_store();
        $cir = new csv_import_reader($iid, 'local_xp_import');
        $provider = $this->get_provider($cir, $action, $reason);

        $rows = [];
        $i = 0;
        foreach ($provider->getIterator() as $entry) {
            if ($i++ >= $npreview) {
                break;
            }

            if (!$entry->is_valid()) {
                $cell = new \html_table_cell($this->get_ul_errors_list_from_array($entry->get_errors()));
                $cell->colspan = 3;
                $rows[] = new \html_table_row([
                    $output->pix_icon('i/invalid', ''),
                    $entry->get_reference(),
                    $cell
                ]);
                continue;
            }

            $usp = $entry->get_object();
            $nowpoints = $store->get_state($usp->get_id())->get_xp();
            $newpoints = $nowpoints;
            if ($usp->get_action() === user_state_store_points::ACTION_SET) {
                $newpoints = $usp->get_points();
            } else if ($usp->get_action() === user_state_store_points::ACTION_INCREASE) {
                $newpoints += $usp->get_points();
            }

            $rows[] = new \html_table_row([
                $output->pix_icon('i/valid', ''),
                $entry->get_reference(),
                fullname($usp->get_user()),
                $nowpoints,
                $newpoints,
            ]);
        }

        $hasmore = $i >= $npreview;

        $table = new \html_table();
        $table->head = ['', get_string('csvline', 'local_xp'), get_string('fullname', 'core'),
            get_string('currentpoints', 'local_xp'), get_string('afterimport', 'local_xp')];
        $table->data = $rows;

        echo markdown_to_html(get_string('importpreviewintro', 'local_xp', $npreview));
        echo html_writer::table($table);

        if ($hasmore) {
            echo $output->single_button(
                (new url($this->pageurl, ['preview' => $npreview * 2, 'sesskey' => sesskey()]))->get_compatible_url(),
                get_string('previewmore', 'local_xp'),
                'get'
            );
        }

        echo $output->single_button(
            (new url($this->pageurl, ['iid' => null]))->get_compatible_url(),
            get_string('cancel', 'core'),
            'get'
        );

        echo $output->single_button(
            (new url($this->pageurl, ['confirm' => 1, 'sesskey' => sesskey()]))->get_compatible_url(),
            get_string('confirm', 'core'),
            'get',
            ['primary' => true]
        );
    }

    protected function page_content() {
        $output = $this->get_renderer();

        // Main form.
        $form = $this->get_form();

        // Whether to perform the import or preview.
        $iid = $this->get_param('iid');
        $action = $this->get_param('action');
        if (!$form->is_submitted() && $iid && $action != -1) {
            require_sesskey();

            if ($this->get_param('confirm')) {
                $this->page_content_import();
                return;
            }

            $this->page_content_preview();
            return;
        }

        echo markdown_to_html(get_string('importcsvintro', 'local_xp', [
            'docsurl' => (new moodle_url('https://levelup.plus/docs/article/importing-points-from-csv', [
                'ref' => 'localxp_help'
            ]))->out(false),
            'sampleurl' => (new moodle_url('/local/xp/samples/points-import.csv'))->out(false),
        ]));
        $form->display();
    }

}
