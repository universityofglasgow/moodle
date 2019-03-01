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
 * Workshop allocator class.
 *
 * @copyright 2014-2017 Albert Gasset <albertgasset@fsfe.org>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   workshopallocation_live
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/workshop/allocation/lib.php');

/**
 * Workshop allocator class.
 *
 * @copyright 2014-2017 Albert Gasset <albertgasset@fsfe.org>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   workshopallocation_live
 */
class workshop_live_allocator implements workshop_allocator {

    /** @var workshop $workshop */
    protected $workshop;

    /** @var workshopallocation_live\settings_form $mform */
    protected $mform;

    /**
     * Initializes the allocator instance.
     *
     * @param workshop $workshop Workshop API object
     */
    public function __construct(workshop $workshop) {
        $this->workshop = $workshop;
    }

    /**
     * Prepares or processes the settings form.
     *
     * @return workshop_allocation_result
     */
    public function init() {
        global $PAGE, $DB;

        $result = new workshop_allocation_result($this);

        $record = $DB->get_record('workshopallocation_live', ['workshopid' => $this->workshop->id]);
        if (!$record) {
            $record = new stdClass;
            $record->workshopid = $this->workshop->id;
            $record->enabled = false;
            $record->settings = '{}';
        }

        $customdata = ['workshop' => $this->workshop];
        $this->mform = new \workshopallocation_live\settings_form($PAGE->url, $customdata);

        if ($this->mform->is_cancelled()) {
            redirect($this->workshop->view_url());

        } else if ($data = $this->mform->get_data()) {
            $record->enabled = !empty($data->enabled);
            $record->settings = json_encode([
                'numofreviews' => $data->numofreviews,
                'numper' => $data->numper,
                'excludesamegroup' => !empty($data->excludesamegroup),
                'addselfassessment' => !empty($data->addselfassessment),
            ]);
            if (isset($record->id)) {
                $DB->update_record('workshopallocation_live', $record);
            } else {
                $DB->insert_record('workshopallocation_live', $record);
            }
            if ($record->enabled) {
                $msg = get_string('resultenabled', 'workshopallocation_live');
            } else {
                $msg = get_string('resultdisabled', 'workshopallocation_live');
            }
            $result->set_status(workshop_allocation_result::STATUS_CONFIGURED, $msg);

        } else {
            $data = json_decode($record->settings);
            $data->enabled = $record->enabled;
            $this->mform->set_data($data);
            $result->set_status(workshop_allocation_result::STATUS_VOID);
        }

        return $result;
    }

    /**
     * Renders the settings form.
     *
     * @return string
     */
    public function ui() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_workshop');

        $html = $output->container_start('live-allocator');
        ob_start();
        $this->mform->display();
        $html .= ob_get_contents();
        ob_end_clean();
        $html .= $output->container_end();

        return $html;
    }

    /**
     * Deletes the settings related to a workshop instance.
     *
     * @param int $workshopid
     */
    public static function delete_instance($workshopid) {
        global $DB;

        $DB->delete_records('workshopallocation_live', ['workshopid' => $workshopid]);
    }
}
