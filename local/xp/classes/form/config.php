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
 * XP config form.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/blocks/xp/classes/form/itemspertime.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->libdir . '/grouplib.php');

use block_xp\local\config\course_world_config;
use local_xp\local\config\default_course_world_config;

/**
 * XP config form class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config extends \block_xp\form\config {

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        parent::definition();
        $mform = $this->_form;
        $world = $this->_customdata['world'];
        $renderer = \block_xp\di::get('renderer');
        $urlresolver = \block_xp\di::get('url_resolver');
        $iomad = \block_xp\di::get('iomad_facade');

        // Progress bar.
        $el = $mform->createElement('header', 'progressbarhdr', get_string('progressbar', 'block_xp'));
        $mform->insertElementBefore($el, 'hdrcheating');
        unset($el);
        $el = $mform->createElement('select', 'progressbarmode', get_string('progressbarmode', 'local_xp'), [
            default_course_world_config::PROGRESS_BAR_MODE_LEVEL => get_string('progressbarmodelevel', 'local_xp'),
            default_course_world_config::PROGRESS_BAR_MODE_OVERALL => get_string('progressbarmodeoverall', 'local_xp'),
        ]);
        $mform->insertElementBefore($el, 'hdrcheating');
        $mform->addHelpButton('progressbarmode', 'progressbarmode', 'local_xp');
        unset($el);

        // Group ladder.
        $el = $mform->createElement('header', 'groupladderhdr', get_string('teamleaderboard', 'block_xp'));
        $mform->insertElementBefore($el, 'hdrcheating');
        unset($el);

        $el = $mform->createElement('html', \html_writer::div($renderer->notification_without_close(
            strip_tags(markdown_to_html(get_string('teamladdersettingsmovednotice', 'local_xp', [
                'url' => ($urlresolver->reverse('group_ladder', ['courseid' => $world->get_courseid()]))->out(false),
            ])), '<a>'), 'info'),
            'xp-my-4'));
        $mform->insertElementBefore($el, 'hdrcheating');
        unset($el);
    }

    /**
     * Get the data.
     *
     * @return stdClass
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }

        return $data;
    }

    /**
     * Set the data.
     *
     * @param array $data
     */
    public function set_data($data) {
        $data = (array) $data;
        parent::set_data($data);
    }

}
