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
 * @package moodlecore
 * @subpackage backup-controller
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class implementing the controller of any restore process
 *
 * This final class is in charge of controlling all the restore architecture, for any
 * type of backup.
 *
 * TODO: Finish phpdocs
 */

namespace local_template\core;
use backup;
use backup_controller_dbops;
use backup_factory;
use output_controller;
use restore_check;
use restore_controller_exception;
use backup_general_helper;


class local_template_restore_controller extends \restore_controller {

    /**
     * Constructor.
     *
     * If you specify a progress monitor, this will be used to report progress
     * while loading the plan, as well as for future use. (You can change it
     * for a different one later using set_progress.)
     *
     * @param string $tempdir Directory under $CFG->backuptempdir awaiting restore
     * @param int $courseid Course id where restore is going to happen
     * @param bool $interactive backup::INTERACTIVE_YES[true] or backup::INTERACTIVE_NO[false]
     * @param int $mode backup::MODE_[ GENERAL | HUB | IMPORT | SAMESITE ]
     * @param int $userid
     * @param int $target backup::TARGET_[ NEW_COURSE | CURRENT_ADDING | CURRENT_DELETING | EXISTING_ADDING | EXISTING_DELETING ]
     * @param \core\progress\base $progress Optional progress monitor
     * @param \stdClass $copydata Course copy data, required when in MODE_COPY
     * @param bool $releasesession Should release the session? backup::RELEASESESSION_YES or backup::RELEASESESSION_NO
     */
    public function __construct($tempdir, $courseid, $interactive, $mode, $userid, $target,
                                \core\progress\base $progress = null, $releasesession = backup::RELEASESESSION_NO, ?\stdClass $copydata = null) {

        if ($mode == backup::MODE_COPY && is_null($copydata)) {
            throw new restore_controller_exception('cannot_instantiate_missing_copydata');
        }

        $this->copy = $copydata;
        $this->tempdir = $tempdir;
        $this->courseid = $courseid;
        $this->interactive = $interactive;
        $this->mode = $mode;
        $this->userid = $userid;
        $this->target = $target;
        $this->releasesession = $releasesession;

        // Apply some defaults
        $this->type = '';
        $this->format = backup::FORMAT_UNKNOWN;
        $this->operation = backup::OPERATION_RESTORE;
        $this->executiontime = 0;
        $this->samesite = false;
        $this->checksum = '';
        $this->precheck = null;

        // Apply current backup version and release if necessary
        backup_controller_dbops::apply_version_and_release();

        // Check courseid is correct
        restore_check::check_courseid($this->courseid);

        // Check user is correct
        restore_check::check_user($this->userid);

        // Calculate unique $restoreid
        $this->calculate_restoreid();

        // Default logger chain (based on interactive/execution)
        $this->logger = backup_factory::get_logger_chain($this->interactive, $this->execution, $this->restoreid);

        // Set execution based on backup mode.
        if ($mode == backup::MODE_ASYNC || $mode == backup::MODE_COPY) {
            $this->execution = backup::EXECUTION_DELAYED;
        } else {
            $this->execution = backup::EXECUTION_INMEDIATE;
        }

        // By default there is no progress reporter unless you specify one so it
        // can be used during loading of the plan.
        if ($progress) {
            $this->progress = $progress;
        } else {
            $this->progress = new \core\progress\none();
        }
        $this->progress->start_progress('Constructing restore_controller');

        // Instantiate the output_controller singleton and active it if interactive and immediate.
        $oc = output_controller::get_instance();
        if ($this->interactive == backup::INTERACTIVE_YES && $this->execution == backup::EXECUTION_INMEDIATE) {
            $oc->set_active(true);
        }

        $this->log('instantiating restore controller', backup::LOG_INFO, $this->restoreid);

        // Set initial status
        $this->set_status(backup::STATUS_CREATED);

        // Calculate original restore format
        $this->format = backup_general_helper::detect_backup_format($tempdir);

        // If format is not moodle2, set to conversion needed
        if ($this->format !== backup::FORMAT_MOODLE) {
            $this->set_status(backup::STATUS_REQUIRE_CONV);

            // Else, format is moodle2, load plan, apply security and set status based on interactivity
        } else {
            // Load plan
            $this->load_plan();

            // Apply all default settings (based on type/format/mode).
            $this->apply_defaults();

            // Perform all initial security checks and apply (2nd param) them to settings automatically
            restore_check::check_security($this, true);

            if ($this->interactive == backup::INTERACTIVE_YES) {
                $this->set_status(backup::STATUS_SETTING_UI);
            } else {
                $this->set_status(backup::STATUS_NEED_PRECHECK);
            }
        }

        // Tell progress monitor that we finished loading.
        $this->progress->end_progress();
    }


}