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
 * This file contains a renderer for the he assignsubmission_automaticextension plugin
 *
 * @package    assignsubmission_automaticextension
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the assignsubmission_automaticextension plugin.
 *
 * @package    assignsubmission_automaticextension
 * @author     Rossco Hellmans <rosscohellmans@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignsubmission_automaticextension_renderer extends plugin_renderer_base {
    /**
     * Renders the HTML for the request button.
     *
     * @param integer $cmid the cmid
     * @return string
     */
    public function render_request_button($cmid) {
        $label = get_string('requestextension', 'assignsubmission_automaticextension');
        $url = new moodle_url('/mod/assign/submission/automaticextension/request.php', ['cmid' => $cmid]);
        $options = ['class' => 'singlebutton requestextensionbutton'];
        return $this->output->single_button($url, $label, 'get', $options);
    }

    /**
     * Renders the HTML for the request page.
     *
     * @param assign $assign the assign object
     * @return string
     */
    public function render_request_page(assign $assign) {
        $html = '';

        $instance = $assign->get_instance();
        $context = $assign->get_context();
        $cm = $assign->get_course_module();

        $html .= $this->output->box_start('generalbox');

        // Start modal.
        $attributes = [
            'role' => 'alertdialog',
            'aria-labelledby' => 'modal-header',
            'aria-describedby' => 'modal-body',
            'aria-modal' => 'true'
        ];
        $classes = 'generalbox modal modal-dialog modal-in-page show modal-extension-request';
        $html .= $this->output->box_start($classes, 'notice', $attributes);
        $html .= $this->output->box_start('modal-content', 'modal-content');

        // Header.
        $html .= $this->output->box_start('modal-header p-x-1', 'modal-header');
        $html .= html_writer::tag('h3', get_string('extensionrequest', 'assignsubmission_automaticextension'));
        $html .= $this->output->box_end();

        // Body.
        $attributes = [
            'role' => 'alert',
            'data-aria-autofocus' => 'true'
        ];
        $html .= $this->output->box_start('modal-body', 'modal-body', $attributes);

        $courseshortname = format_string($assign->get_course()->shortname, false, ['context' => $context]);
        $assigntitle = format_string($instance->name, false, ['context' => $context]);
        $html .= html_writer::tag('h5', $courseshortname);
        $html .= html_writer::tag('h5', $assigntitle);
        $html .= html_writer::empty_tag('hr');

        $conditions = get_config('assignsubmission_automaticextension', 'conditions');
        $html .= clean_text($conditions);

        $html .= $this->output->box_end();

        // Footer.
        $html .= $this->box_start('modal-footer', 'modal-footer');
        $cancelurl = new moodle_url('/mod/assign/view.php', ['id' => $cm->id]);
        $cancel = new single_button($cancelurl, get_string('cancel', 'assignsubmission_automaticextension'), 'get');
        $params = ['cmid' => $cm->id, 'confirm' => 1];
        $confirmurl = new moodle_url('/mod/assign/submission/automaticextension/request.php', $params);
        $confirm = new single_button($confirmurl, get_string('accept', 'assignsubmission_automaticextension'), 'post', true);
        $html .= html_writer::tag('div', $this->render($confirm) . $this->render($cancel), ['class' => 'buttons']);
        $html .= $this->output->box_end();

        // Close modal content.
        $html .= $this->output->box_end();
        // Close modal.
        $html .= $this->output->box_end();
        // Close generalbox.
        $html .= $this->output->box_end();

        return $html;
    }
}
