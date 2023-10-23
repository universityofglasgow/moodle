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
 * Section completion rule.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

use backup;
use base_logger;
use block_xp\di;
use block_xp_rule;
use completion_info;
use html_writer;
use restore_dbops;

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/completionlib.php');

/**
 * Section completion rule.
 *
 * @package    local_xp
 * @copyright  2022 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_completion extends block_xp_rule {

    /** @var int The course ID during setup. */
    protected $courseid;
    /** @var config The config. */
    protected $config;
    /** @var int The section number, -1 means any section. */
    protected $sectionnum = -1;

    /**
     * Constructor.
     *
     * @param int $courseid The course ID.
     * @param int $contextid The context ID.
     */
    public function __construct($courseid = 0) {
        global $COURSE;
        $this->courseid = empty($courseid) ? $COURSE->id : $courseid;
        $this->config = di::get('config');
    }

    /**
     * Export the properties and their values.
     *
     * @return array Keys are properties, values are the values.
     */
    public function export() {
        $data = parent::export();
        $data['courseid'] = $this->courseid;
        $data['sectionnum'] = $this->sectionnum;
        return $data;
    }

    /**
     * Returns a string describing the rule.
     *
     * @return string
     */
    public function get_description() {
        return get_string('rulesectioncompletiondesc', 'local_xp', ['sectionname' => $this->get_section_name($this->sectionnum)]);
    }

    /**
     * Get display name.
     *
     * @return string
     */
    protected function get_display_name() {
        return $this->get_section_name($this->sectionnum);
    }

    /**
     * Returns a form element for this rule.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_form($basename) {
        return $this->get_simple_form($basename);
    }

    /**
     * Returns a simple element for this rule.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_simple_form($basename) {
        $o = parent::get_form($basename);

        $sections = null;
        if ($this->config->get('context') != CONTEXT_SYSTEM && $this->courseid) {
            try {
                $modinfo = get_fast_modinfo($this->courseid);
                $sectionsinfo = $modinfo ? $modinfo->get_section_info_all() : [];
                $sections = array_map(function($sectioninfo) {
                    return $this->get_section_name($sectioninfo->section);
                }, $sectionsinfo);
            } catch (\moodle_exception $e) {
                $sections = null;
            }
        }

        if ($sections === null) {
            $sections = array_map(function($i) {
                if (!$i) {
                    return get_string('colon', 'block_xp', [
                        'a' => '#' . $i,
                        'b' => get_string('general', 'core'),
                    ]);
                }
                return '#' . $i;
            }, array_keys(array_fill(0, 21, 1)));
        }

        $sections = ['-1' => get_string('anysection', 'local_xp')] + $sections;

        // Append the value to the list if we cannot find it any more.
        if (!array_key_exists($this->sectionnum, $sections)) {
            $sections[$this->sectionnum] = get_string('unknownsectiona', 'local_xp', $this->sectionnum);
        }

        $select = html_writer::select($sections, $basename . '[sectionnum]', $this->sectionnum, '',
            ['id' => '', 'class' => '']);
        $helpicon = $this->get_renderer()->help_icon('rulesectioncompletion', 'local_xp');

        $o .= html_writer::start_div('xp-flex xp-gap-1 xp-min-full');
        $o .= html_writer::start_div('xp-flex xp-items-center');
        $o .= get_string('sectiontocompleteis', 'local_xp', '');
        $o .= html_writer::end_div();
        $o .= html_writer::div($select . $helpicon, 'xp-flex xp-items-center xp-min-w-px xp-whitespace-nowrap');
        $o .= html_writer::end_div();

        return $o;
    }

    /**
     * Get the renderer.
     *
     * @return \renderer_base
     */
    protected function get_renderer() {
        return di::get('renderer');
    }

    /**
     * Get the section name.
     *
     * @param int $sectionnum The section num.
     */
    protected function get_section_name($sectionnum) {
        if ($sectionnum <= -1) {
            return get_string('anysection', 'local_xp');
        }

        $name = '';
        try {
            $modinfo = get_fast_modinfo($this->courseid);
            $name = $modinfo ? get_section_name($modinfo->courseid, $sectionnum) : '';
        } catch (\moodle_exception $e) {
            throw $e;
        }
        $name = $name === '' ? get_string('unknownsection', 'local_xp', $sectionnum) : $name;

        return get_string('colon', 'block_xp', [
            'a' => '#' . $sectionnum,
            'b' => $name,
        ]);
    }

    /**
     * Does the $subject match the rule?
     *
     * @param mixed $subject The subject of the comparison.
     * @return bool Whether or not it matches.
     */
    public function match($subject) {
        if (!$subject instanceof \local_xp\event\section_completed) {
            return false;
        } else if ($subject->courseid != $this->courseid) {
            return false;
        }
        return $this->sectionnum <= -1 || $subject->other['sectionnum'] == $this->sectionnum;
    }

    /**
     * Update the rule after a restore.
     *
     * @return void
     */
    public function update_after_restore($restoreid, $courseid, base_logger $logger) {
        $this->courseid = (int) $courseid;
    }
}
