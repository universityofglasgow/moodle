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
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;

use block_xp_filter;
use block_xp\local\controller\page_controller;
use block_xp\local\controller\rules_controller;
use block_xp\local\routing\url;
use html_writer;

/**
 * Rules controller class.
 *
 * @package    local_xp
 * @copyright  2023 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_rules_controller extends page_controller {

    /** @var string The nav name. */
    protected $navname = 'rules';
    /** @var string The route name. */
    protected $routename = 'graderules';
    /** @var \block_xp\local\course_filter_manager The filter manager. */
    protected $filtermanager;

    protected function define_optional_params() {
        return [
            ['reset', false, PARAM_BOOL, false],
            ['confirm', false, PARAM_BOOL, false],
        ];
    }

    protected function post_login() {
        parent::post_login();
        $this->filtermanager = $this->world->get_filter_manager();
    }

    protected function pre_content() {

        // Reset course rules to defaults.
        if ($this->get_param('reset') && confirm_sesskey()) {
            if ($this->get_param('confirm')) {
                $this->world->reset_filters_to_defaults(block_xp_filter::CATEGORY_GRADES);
                $this->redirect(new url($this->pageurl));
            }
        }

        // Saving the data.
        if (!empty($_POST['save'])) {
            require_sesskey();
            $this->handle_save();
            $this->redirect(null, get_string('changessaved'));

        } else if (!empty($_POST['cancel'])) {
            $this->redirect();
        }
    }

    protected function handle_save() {
        $category = block_xp_filter::CATEGORY_GRADES;
        $filters = isset($_POST['gradefilters']) ? $_POST['gradefilters'] : [];
        rules_controller::save_rules_filters($this->world, $filters, $this->filtermanager->get_user_filters($category), $category);
    }

    protected function get_page_html_head_title() {
        return get_string('graderules', 'block_xp');
    }

    protected function get_page_heading() {
        return get_string('graderules', 'block_xp');
    }

    /**
     * Get widget group.
     *
     * @return \renderable
     */
    protected function get_widget_group() {
        $config = \block_xp\di::get('config');
        $isforwholesite = $config->get('context') == CONTEXT_SYSTEM;
        $defaultgradesfilter = block_xp_filter::load_from_data([
            'category' => block_xp_filter::CATEGORY_GRADES,
            'rule' => new \block_xp_ruleset(),
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
            ],
        ]);

        $gradeswidget = new \local_xp\output\grade_filters_widget(
            new \local_xp\output\grade_filter($defaultgradesfilter), $rules,
            array_map(function($f) {
                return new \local_xp\output\grade_filter($f);
            }, $this->filtermanager->get_user_filters(block_xp_filter::CATEGORY_GRADES))
        );

        return new \block_xp\output\filters_widget_group([
            new \block_xp\output\filters_widget_element($gradeswidget),
        ]);
    }

    protected function page_content() {
        $output = $this->get_renderer();

        if ($this->get_param('reset')) {
            echo $output->confirm(
                get_string('reallyresetcourserulestodefaults', 'block_xp'),
                new url($this->pageurl->get_compatible_url(), ['reset' => 1, 'confirm' => 1, 'sesskey' => sesskey()]),
                new url($this->pageurl->get_compatible_url())
            );
            return;
        }

        echo $output->advanced_heading(get_string('graderules', 'block_xp'), [
            'intro' => new \lang_string('graderulesintro', 'block_xp'),
            'help' => new \help_icon('graderules', 'block_xp'),
        ]);
        echo $output->render($this->get_widget_group());

        $this->page_danger_zone_content();
    }

    protected function page_danger_zone_content() {
        $output = $this->get_renderer();

        echo $output->heading_with_divider(get_string('dangerzone', 'block_xp'));

        $url = new url($this->pageurl, ['reset' => 1, 'sesskey' => sesskey()]);
        echo html_writer::tag('p',
            $output->render($output->make_single_button(
                $url->get_compatible_url(),
                get_string('resetcourserulestodefaults', 'block_xp'),
                ['danger' => true]
            ))
        );
    }
}
