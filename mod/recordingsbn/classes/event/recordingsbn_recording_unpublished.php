<?php
/**
 * The mod_recordingsbn viewed event.
 *
 * @package   mod_recordingsbn
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @copyright 2014 Blindside Networks Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

namespace mod_recordingsbn\event;
defined('MOODLE_INTERNAL') || die();

class recordingsbn_recording_unpublished extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'recordingsbn';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_recording_unpublished', 'mod_recordingsbn');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $rid = isset($this->other['rid'])? $this->other['rid']: '';
        return "The user with id '$this->userid' has unpublished a recording with id '$rid' for " .
        "the course id '$this->contextinstanceid'.";
    }

    /**
     * Return the legacy event log data.
     *
     * @return array
     */
    protected function get_legacy_logdata() {
        return(array($this->courseid, 'recordingsbn', 'recording unpublished',
                'view.php?pageid=' . $this->objectid, get_string('event_recording_unpublished', 'recordingsbn'), $this->contextinstanceid));
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/recordingsbn/view.php', array('id' => $this->objectid));
    }
}