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
 * Grade item type rule.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\rule;
defined('MOODLE_INTERNAL') || die();

use block_xp_rule_base;
use core_plugin_manager;
use grade_grade;
use html_writer;

/**
 * Grade item type rule.
 *
 * @package    local_xp
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_item_type extends block_xp_rule_base {

    /**
     * Constructor.
     *
     * @param string $value The type.
     */
    public function __construct($value = null) {
        parent::__construct(self::EQ, $value);
    }

    /**
     * Returns a string describing the rule.
     *
     * @return string
     */
    public function get_description() {
        $type = $this->value;

        $name = null;
        if ($type === '_course_total') {
            $name = get_string('coursetotal', 'core_grades');
        } else {
            $plugmanager = core_plugin_manager::instance();
            $modules = $plugmanager->get_installed_plugins('mod');
            if (array_key_exists($type, $modules)) {
                $name = $modules[$type]->displayname;
            }
        }

        if ($name === null) {
            $name = get_string('unknowngradeitemtype', 'local_xp', $this->value);
        }

        return get_string('rulegradeitemtypedesc', 'local_xp', ['type' => $name]);
    }

    /**
     * Returns a form element for this rule.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_form($basename) {
        $output = \block_xp\di::get('renderer');
        $o = parent::get_form($basename);

        $plugmanager = core_plugin_manager::instance();
        $modules = array_filter($plugmanager->get_plugins_of_type('mod'), function($mod) {
            return plugin_supports('mod', $mod->name, FEATURE_GRADE_HAS_GRADE, false);
        });

        $list = ['_course_total' => get_string('coursetotal', 'core_grades')];
        $list = array_merge($list, array_reduce($modules, function($carry, $mod) {
            $carry[$mod->name] = $mod->displayname;
            return $carry;
        }, []));

        // Append the value to the list if we cannot find it any more.
        if (!empty($this->value) && !array_key_exists($this->value, $list)) {
            $list[] = [$this->value => get_string('unknowngradeitemtype', 'local_xp', $this->value)];
        }

        $itemtypes = html_writer::select($list, $basename . '[value]', $this->value, '',
            ['id' => '', 'class' => '', 'style' => 'max-width: 150px;']);
        $o .= get_string('gradeitemtypeis', 'local_xp', $itemtypes);
        $o .= $output->help_icon('rulegradeitemtype', 'local_xp');
        return $o;
    }

    /**
     * Get the subject value.
     *
     * @param mixed $subject The subject.
     * @return void
     */
    protected function get_subject_value($subject) {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');
        $impossiblematch = '400 Bad Request';

        if (!$subject instanceof \core\event\user_graded) {
            return $impossiblematch;
        } else if ($subject->is_restored()) {
            return $impossiblematch;
        }

        $gradeobject = $subject->get_grade();
        if (!$gradeobject) {
            $gradeobject = grade_grade::fetch(['id' => $event->objectid]);
        }
        $gradeitem = $gradeobject->load_grade_item();
        if (!$gradeitem) {
            return $impossiblematch;
        }

        if ($gradeitem->itemtype === 'course') {
            return '_course_total';
        }
        return $gradeitem->itemtype === 'mod' ? $gradeitem->itemmodule : $impossiblematch;
    }

}
