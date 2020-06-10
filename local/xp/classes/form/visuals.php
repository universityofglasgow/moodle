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
 * Visuals.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\form;
defined('MOODLE_INTERNAL') || die();

use moodleform;

require_once($CFG->libdir . '/formslib.php');

/**
 * Visuals.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class visuals extends moodleform {

    public function definition() {
        $mform = $this->_form;
        $renderer = \block_xp\di::get('renderer');
        $repository = \block_xp\di::get('theme_repository');

        $themes = array_reduce($repository->get_themes(), function($carry, $theme) {
            $carry[$theme->code] = $theme->name;
            return $carry;
        }, ['' => get_string('themestandard', 'local_xp')]);

        $mform->addElement('select', 'badgetheme', get_string('badgetheme', 'local_xp'), $themes);
        $mform->addHelpButton('badgetheme', 'badgetheme', 'local_xp');

        $mform->addElement('filemanager', 'badges', get_string('levelbadges', 'local_xp'), null, $this->_customdata['fmoptions']);
        $mform->addHelpButton('badges', 'levelbadges', 'local_xp');
        $mform->addElement('static', '', '', get_string('levelbadgesformhelp', 'block_xp'));

        $mform->addElement('filemanager', 'currency', get_string('currencysign', 'local_xp'), null,
            $this->_customdata['currencyfmoptions']);
        $mform->addHelpButton('currency', 'currencysign', 'local_xp');
        $mform->addElement('static', '', '', get_string('currencysignformhelp', 'local_xp'));

        $this->add_action_buttons();
        $this->hook_js_in($renderer, $repository);
    }

    protected function hook_js_in($renderer, $repository) {
        global $PAGE;

        $themedata = array_reduce($repository->get_themes(), function($carry, $theme) use ($renderer, $repository) {
            $urlresolver = new \local_xp\local\xp\theme_badge_url_resolver($repository, $theme->code);
            $badge = new \block_xp\local\xp\badged_level(1, 0, '', $urlresolver);
            $badge2 = new \block_xp\local\xp\badged_level($theme->levels, 0, '', $urlresolver);
            $carry[] = [
                'code' => $theme->code,
                'supports' => get_string('uptoleveln', 'local_xp', $theme->levels),
                'sample1' => $renderer->small_level_badge($badge),
                'sample2' => $renderer->small_level_badge($badge2)
            ];
            return $carry;
        }, [[
            'code' => '',
            'supports' => '',
            'sample1' => $renderer->small_level_badge(new \block_xp\local\xp\described_level(1, 0, '')),
            'sample2' => $renderer->small_level_badge(new \block_xp\local\xp\described_level(99, 0, ''))
        ]]);

        $PAGE->requires->js_init_call("
            (function(Y, selector, themedata) {
                var node = Y.one(selector);
                if (!node) {
                    return;
                }

                var container = Y.Node.create('<div>');
                container.setStyles({
                    display: 'inline-block',
                    marginLeft: '5px',
                    marginRight: '5px'
                });

                var fn = function(themecode) {
                    themecode = themecode || '';
                    var theme = themedata.filter(function(item) {
                        return themecode == item.code;
                    }).pop();

                    container.empty();
                    if (!theme) {
                        return;
                    }

                    var supportContent = Y.Node.create('<small></small>');
                    supportContent.set('text', theme.supports);

                    var wrap1 = container.cloneNode();
                    var wrap2 = container.cloneNode();
                    var supports = container.cloneNode();
                    wrap1.append(theme.sample1);
                    wrap2.append(theme.sample2);
                    supports.append(supportContent);
                    container.append(wrap1);
                    container.append(wrap2);
                    container.append(supports);
                };

                node.ancestor().append(container);
                node.on('change', function(e) {
                    fn(e.currentTarget.get('value'));
                });

                fn(node.get('value'));
            })

        ", ['#id_badgetheme', $themedata], true);
    }

}
