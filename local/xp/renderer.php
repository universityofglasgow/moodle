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
 * Local renderer.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/xp/renderer.php');

use local_xp\local\factory\course_currency_factory;
use local_xp\local\currency\currency;

/**
 * Local renderer class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_renderer extends block_xp_renderer {

    protected $currencyfactory;
    protected $currencyrendercache;

    public function currency(currency $currency) {
        $sign = $currency->get_sign();
        $classes = 'sign';

        if ($signurl = $currency->get_sign_url()) {
            $classes .= ' sign-img';
            $sign = html_writer::empty_tag('img', ['src' => $signurl, 'alt' => '']);

        } else if ($currency->use_sign_as_superscript()) {
            $classes .= ' sign-sup';
        }

        $o = '';
        $o .= html_writer::div($sign, $classes);
        return $o;
    }

    /**
     * Get the currency factory.
     *
     * We can't inject the factory here or we have circular dependency issues.
     *
     * @return course_currency_factory
     */
    protected function get_course_currency_factory() {
        if (!$this->currencyfactory) {
            $this->currencyfactory = \block_xp\di::get('course_currency_factory');
        }
        return $this->currencyfactory;
    }

    /**
     * Return the group picture.
     *
     * This is a legacy implementation that depends on more recent Moodle
     * versions, do not use. Instead refer to state_with_subject::get_picture,
     * and self::team_picture.
     *
     * @param stdClass $group The group.
     * @return string
     */
    public function group_picture($group) {
        if (!function_exists('get_group_picture_url')) {
            return;
        }
        $pic = get_group_picture_url($group, $group->courseid);
        if (empty($pic)) {
            return;
        }
        return $this->team_picture($pic, format_string($group->name, true, [
            'context' => context_course::instance($group->courseid)
        ]));
    }

    /**
     * Return the team picture.
     *
     * @param moodle_url $url The URL.
     * @return string
     */
    public function team_picture(moodle_url $url, $alt = '') {
        return html_writer::empty_tag('img', [
            'src' => $url->out(false),
            'class' => 'grouppic',
            'alt' => $alt
        ]);
    }

    /**
     * Render the grade filter.
     *
     * This MUST match very closely the render_block_xp_filter method, but had to define
     * this here because the grade filter looks different and does not support the same
     * options.
     *
     * @param block_xp_filter $filter The filter.
     * @return string
     */
    public function render_grade_filter(renderable $gradefilter) {
        $filter = $gradefilter->filter;

        static $i = 0;
        $o = '';
        $basename = 'gradefilters[' . $i++ . ']';

        $o .= html_writer::start_tag('li', array('class' => 'filter', 'data-basename' => $basename));

        $content = '';
        $content .= 'Students earn points for grades when:';

        $o .= html_writer::tag('p', $content);
        $o .= html_writer::empty_tag('input', array(
                'type' => 'hidden',
                'value' => $filter->get_id(),
                'name' => $basename . '[id]'));
        $o .= html_writer::empty_tag('input', array(
                'type' => 'hidden',
                'value' => $filter->get_sortorder(),
                'name' => $basename . '[sortorder]'));
        $basename .= '[rule]';

        $o .= html_writer::start_tag('ul', array('class' => 'filter-rules'));
        $o .= $this->render($filter->get_rule(), ['iseditable' => true, 'basename' => $basename]);
        $o .= html_writer::end_tag('ul');
        $o .= html_writer::end_tag('li');

        return $o;
    }

    /**
     * Render the grade filters widget.
     *
     * @param renderable $widget The widget
     * @return void
     */
    public function render_grade_filters_widget(renderable $widget) {
        $containerid = html_writer::random_id();

        // Prepare Javascript.
        $this->page->requires->yui_module('moodle-block_xp-filters', 'Y.M.block_xp.Filters.init', [[
            'containerSelector' => '#' . $containerid,
            'filter' => $this->render($widget->filter),
            'rules' => array_reduce($widget->rules, function($carry, $rule) {
                $carry[] = [
                    'name' => $rule->name,
                    'template' => $this->render($rule->rule, ['iseditable' => true, 'basename' => 'XXXXX'])
                ];
                return $carry;
            }, [])
        ]]);
        $this->page->requires->strings_for_js(array('pickaconditiontype'), 'block_xp');

        echo html_writer::start_div('block-xp-filters-wrapper block-xp-grade-filters-wrapper', ['id' => $containerid]);
        echo html_writer::start_tag('ul', ['class' => 'filters-list filters-editable']);

        // We always want an empty rule set in there.
        if (empty($widget->filters)) {
            echo $this->render($widget->filter);
        }

        foreach ($widget->filters as $filter) {
            echo $this->render($filter);
        }
        echo html_writer::end_tag('ul');
        echo html_writer::end_tag('div');
    }

    public function xp($points, currency $currency = null) {
        if (!$currency) {
            $courseid = $this->page->course->id;
            $currency = $this->get_course_currency_factory()->get_currency($courseid);
        }
        $o = '';
        $o .= html_writer::start_div('block_xp-xp');
        $o .= html_writer::div($this->xp_amount($points), 'pts');
        $o .= $this->currency($currency);
        $o .= html_writer::end_div();
        return $o;
    }

    private function xp_amount($points) {
        $xp = (int) $points;
        if ($xp > 999) {
            $thousandssep = get_string('thousandssep', 'langconfig');
            $xp = number_format($xp, 0, '.', $thousandssep);
        }
        return $xp;
    }

    public function xp_preview($points, $courseid = null) {
        if (!$courseid) {
            $currency = \block_xp\di::get('currency');
        } else {
            $currency = $this->get_course_currency_factory()->get_currency($courseid);
        }
        return $this->xp($points, $currency);
    }

}
