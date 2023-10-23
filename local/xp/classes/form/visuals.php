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

use html_writer;
use local_xp\local\currency\builtin_sign_url_resolver;
use local_xp\local\currency\default_currency;
use local_xp\local\currency\theme_sign_url_resolver;
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
        $updater = \block_xp\di::get('theme_updater');
        $repository = \block_xp\di::get('theme_repository');
        $currencyrepository = \block_xp\di::get('currency_repository');

        $updater->update_themes_if_required();
        $themes = array_reduce($repository->get_themes(), function($carry, $theme) {
            $carry[$theme->code] = $theme->name;
            return $carry;
        }, ['' => get_string('themestandard', 'local_xp')]);

        $mform->addElement('select', 'badgetheme', get_string('badgetheme', 'local_xp'), $themes);
        $mform->addHelpButton('badgetheme', 'badgetheme', 'local_xp');

        $mform->addElement('filemanager', 'badges', get_string('levelbadges', 'local_xp'), null, $this->_customdata['fmoptions']);
        $mform->addHelpButton('badges', 'levelbadges', 'local_xp');
        $mform->addElement('static', '', '', get_string('levelbadgesformhelp', 'block_xp'));

        $currencies = array_reduce($currencyrepository->get_currencies(), function($carry, $data) {
            $carry[$data->code] = $data->name;
            return $carry;
        }, ['' => get_string('currencysignxp', 'local_xp')]);
        $mform->addElement('select', 'currencytheme', get_string('currencysign', 'local_xp'), $currencies);
        $mform->addHelpButton('currencytheme', 'currencysign', 'local_xp');

        $mform->addElement('filemanager', 'currency', get_string('currencysignoverride', 'local_xp'), null,
            $this->_customdata['currencyfmoptions']);
        $mform->addElement('static', 'currencyfilenote', '', get_string('currencysignformhelp', 'local_xp'));

        $this->add_action_buttons();
    }

    /**
     * Initialise the page requirements.
     */
    public function init_page_requirements() {
        global $PAGE;

        $renderer = \block_xp\di::get('renderer');
        $repository = \block_xp\di::get('theme_repository');
        $currencyrepository = \block_xp\di::get('currency_repository');

        $themedata = array_reduce($repository->get_themes(), function($carry, $theme) use ($renderer, $repository) {
            $urlresolver = new \local_xp\local\xp\theme_badge_url_resolver($repository, $theme->code);
            $badge = new \block_xp\local\xp\badged_level(1, 0, '', $urlresolver);
            $badge2 = new \block_xp\local\xp\badged_level($theme->levels, 0, '', $urlresolver);
            $carry[] = [
                'value' => $theme->code,
                'annotation' => html_writer::div(
                        html_writer::div($renderer->small_level_badge($badge))
                        . html_writer::div($renderer->small_level_badge($badge2))
                        . html_writer::tag('small', get_string('uptoleveln', 'local_xp', $theme->levels)),
                    'xp-flex xp-items-center xp-space-x-1')
            ];
            return $carry;
        }, [[
            'value' => '',
            'annotation' => html_writer::div(
                    html_writer::div($renderer->small_level_badge(new \block_xp\local\xp\described_level(1, 0, '')))
                    . html_writer::div($renderer->small_level_badge(new \block_xp\local\xp\described_level(99, 0, ''))),
                'xp-flex xp-items-center xp-space-x-1')
        ]]);

        $jsonid = html_writer::random_id();
        echo $renderer->json_script($themedata, $jsonid);
        $PAGE->requires->js_call_amd('local_xp/select-annotator', 'initWithJson', ['id_badgetheme', '#' . $jsonid]);

        $currencydata = array_reduce([''] + $currencyrepository->get_currencies(), function($carry, $currency) use ($renderer) {
            $resolver = null;
            $value = is_object($currency) ? $currency->code : $currency;
            if ($value) {
                $resolver = new theme_sign_url_resolver($renderer, $value);
            }
            $currency = new default_currency($resolver);
            $carry[] = [
                'value' => $value,
                'annotation' => html_writer::div($renderer->xp(1337, $currency), 'xp-mt-1')
            ];
            return $carry;
        });

        $jsonid = html_writer::random_id();
        echo $renderer->json_script($currencydata, $jsonid);
        $PAGE->requires->js_call_amd('local_xp/select-annotator', 'initWithJson', ['id_currencytheme', '#' . $jsonid]);
    }

}
