<?php
/**
 * This file is part of Level Up XP+.
 *
 * Level Up XP+ is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Level Up XP+ is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
 *
 * https://levelup.plus
 */

/**
 * Rules controller.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;

use block_xp\di;
use block_xp\local\controller\page_controller;
use block_xp\local\routing\url;
use block_xp\local\rulefilter\rulefilter;
use context;
use help_icon;
use moodle_url;

/**
 * Rules controller class.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_rules_controller extends page_controller {

    /** @var string The nav name. */
    protected $navname = 'rules';
    /** @var string The route name. */
    protected $routename = 'completionrules';

    protected function define_optional_params() {
        return [
            ['reset', false, PARAM_BOOL, false],
            ['confirm', false, PARAM_BOOL, false],
            ['childcontextid', null, PARAM_INT],
        ];
    }

    protected function pre_content() {
        // Reset course rules to defaults.
        if ($this->get_param('reset') && confirm_sesskey()) {
            if ($this->get_param('confirm')) {
                // This has not yet been implemented. We need to be wary of a few things. Firstly,
                // if there is a childcontext, we should only reset that child context. Resetting
                // a child context actually means deleting everything, so we should tell the user.
                // Then, when not in a child context, this should also remove all rules in child
                // contexts, which we should also mention.
                $this->redirect(new url($this->pageurl));
            }
        }
    }

    /**
     * Whether having a child context is allowed.
     *
     * @return bool
     */
    protected function can_have_child_context() {
        $worldcontext = $this->world->get_context();
        return $worldcontext->contextlevel == CONTEXT_SYSTEM;
    }

    /**
     * Get the child context.
     *
     * @return context|null
     */
    protected function get_child_context() {
        if (!$this->can_have_child_context()) {
            return null;
        }

        $childcontextid = $this->get_param('childcontextid');
        if (!$childcontextid) {
            return null;
        }

        // Confirm is a valid context.
        $context = context::instance_by_id($childcontextid, IGNORE_MISSING);
        $worldcontext = $this->world->get_context();
        if (!$context || $context->contextlevel != CONTEXT_COURSE ||
                !$worldcontext->is_parent_of($context, false)) {
            return null;
        }

        // Validate access to the course by the current user.
        $modinfo = get_fast_modinfo($context->instanceid);
        if (!can_access_course($modinfo->get_course(), null, '', true)) {
            return null;
        }

        return $context;
    }

    protected function get_page_html_head_title() {
        return get_string('completionrules', 'block_xp');
    }

    protected function get_page_heading() {
        return get_string('completionrules', 'block_xp');
    }

    protected function has_legacy_completion_rules() {
        $filtermanager = $this->world->get_filter_manager();
        return $filtermanager->has_filters_using_rules([
            'local_xp\\local\\rule\\activity_completion',
            'local_xp\\local\\rule\\course_completion',
            'local_xp\\local\\rule\\section_completion',
        ]);
    }

    protected function page_content() {
        $output = $this->get_renderer();
        $childcontext = $this->get_child_context();
        $currentcontext = $childcontext ?? $this->world->get_context();

        if ($this->get_param('reset')) {
            echo $output->confirm(
                get_string('reallyresetcourserulestodefaults', 'block_xp'),
                new url($this->pageurl->get_compatible_url(), ['reset' => 1, 'confirm' => 1, 'sesskey' => sesskey()]),
                new url($this->pageurl->get_compatible_url())
            );
            return;
        }

        echo $output->advanced_heading(get_string('completionrules', 'block_xp'), [
            'intro' => new \lang_string('completionrulesintro', 'block_xp'),
            'help' => new help_icon('completionrules', 'block_xp'),
        ]);

        if ($this->has_legacy_completion_rules()) {
            echo $output->notification_without_close(get_string('completionruleslegacyusednotice', 'block_xp'), 'warning');
        }

        if ($this->can_have_child_context()) {
            $childctxhelp = new help_icon('rulesscope', 'block_xp');

            $scopeurl = null;
            $sitewideurl = new url($this->pageurl);
            $sitewideurl->remove_params(['childcontextid']);
            $courseurltemplate = new url($this->pageurl);
            $courseurltemplate->param('childcontextid', "CONTEXTID");

            if ($childcontext) {
                $scopeurl = new moodle_url('/course/view.php', ['id' => $childcontext->instanceid]);
                $scopename = get_string('coursea', 'block_xp', $childcontext->get_context_name(false, true));
            } else {
                $scopename = get_string('sitewide', 'block_xp');
            }

            echo $output->render_from_template('block_xp/completion-rules-scope-switcher', [
                'isincourse' => (bool) $childcontext,
                'sitewideurl' => $sitewideurl->out(false),
                'contexturl' => $courseurltemplate->out(false),
                'courseurltemplate' => $courseurltemplate->out(false),
                'scopename' => $scopename,
                'scopeurl' => $scopeurl ? $scopeurl->out(false) : null,
                'helpicon' => $childctxhelp->export_for_template($output),
            ]);
        }

        $typeresolver = di::get('rule_type_resolver');
        $ruletypes = array_values(array_map(function($type) use ($typeresolver) {
            return [
                'name' => $typeresolver->get_type_name($type),
                'label' => (string) $type->get_display_name(),
                'description' => (string) $type->get_short_description(),
                'filters' => $type->get_compatible_filters(),
            ];
        }, array_filter([
            $typeresolver->get_type('cm_completion'),
            $typeresolver->get_type('section_completion'),
            $typeresolver->get_type('course_completion'),
        ])));

        $filterhandler = di::get('rule_filter_handler');
        $filters = array_values(array_map(function($filter) use ($filterhandler) {
            return [
                'name' => $filterhandler->get_filter_name($filter),
                'label' => (string) $filter->get_display_name(),
                'description' => (string) $filter->get_short_description(),
                'ismultipleallowed' => $filter->is_multiple_allowed(),
                'weight' => $filterhandler->get_filter_priority($filter),
            ];
        }, array_filter($filterhandler->get_filters(), function(rulefilter $filter) use ($currentcontext) {
            return in_array((int) $currentcontext->contextlevel, $filter->get_compatible_context_levels());
        })));

        $childcontextdata = null;
        if ($childcontext) {
            $childcontextdata = [
                "id" => (int) $childcontext->id,
                'contextlevel' => (int) $childcontext->contextlevel,
                'instanceid' => (int) $childcontext->instanceid,
            ];
        }

        echo $output->react_module('block_xp/ui-completion-rules-lazy', [
            'world' => [
                'contextlevel' => (int) $this->world->get_context()->contextlevel,
                'contextid' => (int) $this->world->get_context()->id,
                'courseid' => $this->world->get_courseid(),
            ],
            'childcontext' => $childcontextdata,
            'currentcontext' => [
                'id' => (int) $currentcontext->id,
                'contextlevel' => (int) $currentcontext->contextlevel,
                'instanceid' => (int) $currentcontext->instanceid,
            ],
            'ruletypes' => $ruletypes,
            'rulefilters' => $filters,
            'addon' => [
                'activated' => di::get('addon')->is_activated(),
                'enablepromo' => (bool) di::get('config')->get('enablepromoincourses'),
                'promourl' => $this->urlresolver->reverse('promo', ['courseid' => $this->world->get_courseid()])->out(false),
            ],
        ]);
    }

}
