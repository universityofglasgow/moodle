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
 * Rules controller.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;
defined('MOODLE_INTERNAL') || die();

use block_xp_filter;

/**
 * Rules controller class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rules_controller extends \block_xp\local\controller\rules_controller {

    protected function handle_save() {
        parent::handle_save();
        $category = block_xp_filter::CATEGORY_GRADES;
        $filters = isset($_POST['gradefilters']) ? $_POST['gradefilters'] : [];
        $this->save_filters($filters, $this->filtermanager->get_user_filters($category), $category);
    }

    protected function get_available_rules() {
        $config = \block_xp\di::get('config');
        $isforwholesite = $config->get('context') == CONTEXT_SYSTEM;
        $rules = [];

        $parentrules = parent::get_available_rules();
        foreach ($parentrules as $rule) {
            $rules[] = $rule;

            // Include the course rule.
            if (get_class($rule->rule) == 'block_xp_rule_cm') {
                $rules[] = (object) [
                    'name' => get_string('rulecmname', 'local_xp'),
                    'info' => get_string('rulecmnameinfo', 'local_xp'),
                    'rule' => new \local_xp\local\rule\cm_name(),
                ];
                if ($isforwholesite) {
                    $rules[] = (object) [
                        'name' => get_string('rulecourse', 'local_xp'),
                        'info' => get_string('rulecourseinfo', 'local_xp'),
                        'rule' => new \local_xp\local\rule\course(),
                    ];
                }
            }

            // We want to inject the completion rules right before this one. Note that we
            // cannot use instanceof as it matches on subclasses.
            if (get_class($rule->rule) == 'block_xp_rule_property') {
                array_splice($rules, -1, 0, array_filter([
                    (object) [
                        'name' => get_string('ruleactivitycompletion', 'local_xp'),
                        'info' => get_string('ruleactivitycompletioninfo', 'local_xp'),
                        'rule' => new \local_xp\local\rule\activity_completion(),
                    ],
                    (object) [
                        'name' => get_string('rulesectioncompletion', 'local_xp'),
                        'info' => get_string('rulesectioncompletioninfo', 'local_xp'),
                        'rule' => new \local_xp\local\rule\section_completion($this->courseid),
                    ],
                    (object) [
                        'name' => get_string('rulecoursecompletion', 'local_xp'),
                        'info' => get_string('rulecoursecompletioninfo', 'local_xp'),
                        'rule' => new \local_xp\local\rule\course_completion(),
                    ]
                ]));
            }

        }

        return $rules;
    }

    /**
     * Get widget group.
     *
     * @return renderable
     */
    protected function get_widget_group() {
        $config = \block_xp\di::get('config');
        $isforwholesite = $config->get('context') == CONTEXT_SYSTEM;
        $defaultgradesfilter = block_xp_filter::load_from_data([
            'category' => block_xp_filter::CATEGORY_GRADES,
            'rule' => new \block_xp_ruleset()
        ]);

        $rules = array_filter([
            (object) [
                'name' => get_string('rulegradeitem', 'local_xp'),
                'info' => get_string('rulegradeiteminfo', 'local_xp'),
                'rule' => new \local_xp\local\rule\grade_item($this->courseid),
            ],
            (object) [
                'name' => get_string('rulegradeitemtype', 'local_xp'),
                'info' => get_string('rulegradeitemtypeinfo', 'local_xp'),
                'rule' => new \local_xp\local\rule\grade_item_type(),
            ],
            $isforwholesite ? (object) [
                'name' => get_string('rulecourse', 'local_xp'),
                'info' => get_string('rulecourseinfo', 'local_xp'),
                'rule' => new \local_xp\local\rule\course(),
            ] : null,
            (object) [
                'name' => get_string('ruleset', 'block_xp'),
                'info' => get_string('rulesetinfo', 'block_xp'),
                'rule' => new \block_xp_ruleset(),
            ]
        ]);

        $gradeswidget = new \local_xp\output\grade_filters_widget(
            new \local_xp\output\grade_filter($defaultgradesfilter), $rules,
            array_map(function($f) {
                return new \local_xp\output\grade_filter($f);
            }, $this->filtermanager->get_user_filters(block_xp_filter::CATEGORY_GRADES))
        );

        return new \block_xp\output\filters_widget_group([
            new \block_xp\output\filters_widget_element(
                $gradeswidget,
                get_string('gradesrules', 'local_xp'),
                null,
                new \help_icon('gradesrules', 'local_xp')
            ),
            $this->get_events_widget_element()
        ]);
    }

}
