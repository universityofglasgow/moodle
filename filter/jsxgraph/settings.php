<?php
// This file is part of JSXGraph Moodle Filter.
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
 * This is a plugin to enable function plotting and dynamic geometry constructions with JSXGraph within a Moodle platform.
 *
 * JSXGraph is a cross-browser JavaScript library for interactive geometry,
 * function plotting, charting, and data visualization in the web browser.
 * JSXGraph is implemented in pure JavaScript and does not rely on any other
 * library. Special care has been taken to optimize the performance.
 *
 * @package    filter_jsxgraph
 * @copyright  2023 JSXGraph team - Center for Mobile Learning with Digital Technology – Universität Bayreuth
 *             Matthias Ehmann,
 *             Michael Gerhaeuser,
 *             Carsten Miller,
 *             Andreas Walter <andreas.walter@uni-bayreuth.de>,
 *             Alfred Wassermann <alfred.wassermann@uni-bayreuth.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Add the placeholder to the description of a setting that should be separated from the following setting.
    // Deprecated.
    $styles = '<style type="text/css">' .
        ' code { ' .
        '    padding: .2em .4em;' .
        '    margin: 0;' .
        '    font-size: 85%;' .
        '    background-color: rgba(175,184,193,0.2);' .
        '    color: #212529;' .
        '    border-radius: 6px;' .
        ' }' .
        '</style>';

    $versionsconfig = json_decode(get_config('filter_jsxgraph', 'versions'), true);
    if (!$versionsconfig) {
        $versionsconfig = [["label" => "auto"]];
    }
    $versions = [];
    foreach ($versionsconfig as $v) {
        $versions[$v["id"]] = $v["id"] === "auto" ? get_string('versionJSXGraph_auto', 'filter_jsxgraph') : $v["label"];
    }

    if (!function_exists('get_jsxfilter_version')) {
        /**
         * Get the filter version as a HTML-String.
         *
         * @return string
         */
        function get_jsxfilter_version() {
            $release = get_config('filter_jsxgraph', 'release');
            $version = get_config('filter_jsxgraph', 'version');
            if (substr($version, 8, 2) === '00') {
                $version = substr($version, 0, 8);
            } else {
                $version = substr_replace($version, ' (', 8, 0) . ')';
            }
            $version = substr_replace($version, '-', 6, 0);
            $version = substr_replace($version, '-', 4, 0);

            return '<div style="text-align: center;margin-top: -0.75rem;margin-bottom: 1rem;">' .
                ($release !== "" ?
                    '<i><b>' . $release . '</b><br><small>' . $version . '</small></i>' :
                    '<b><i>' . $version . '</i></b>'
                ) .
                '</div>';
        }
    }

    if (!function_exists('get_recommended_version_with_prefix')) {
        /**
         * Get the recommended JSXGraph version as a HTML-String.
         *
         * @return string
         */
        function get_recommended_version_with_prefix() {
            $recommended = get_config('filter_jsxgraph', 'recommendedJSX');

            if (!$recommended) {
                return '';
            } else {
                return get_string('recommendedversion_pre', 'filter_jsxgraph') .
                    $recommended .
                    get_string('recommendedversion_post', 'filter_jsxgraph');
            }
        }
    }

    $settings->add(new admin_setting_heading('filter_jsxgraph/styles', '', $styles));

    $settings->add(new admin_setting_heading(
                       'filter_jsxgraph/docs',
                       get_string('header_docs', 'filter_jsxgraph'),
                       get_string('docs', 'filter_jsxgraph')
                   ));

    $settings->add(new admin_setting_heading(
                       'filter_jsxgraph/versions_info',
                       get_string('header_versions', 'filter_jsxgraph'),
                       get_string('filterversion', 'filter_jsxgraph') .
                       get_jsxfilter_version() .
                       get_recommended_version_with_prefix()
                   ));

    $settings->add(new admin_setting_heading(
                       'filter_jsxgraph/version_header',
                       get_string('header_jsxversion', 'filter_jsxgraph'),
                       ''
                   ));

    $settings->add(new admin_setting_configselect(
                       'filter_jsxgraph/versionJSXGraph',
                       get_string('versionJSXGraph', 'filter_jsxgraph'),
                       get_string('versionJSXGraph_desc', 'filter_jsxgraph'),
                       "auto",
                       $versions
                   ));

    $settings->add(new admin_setting_heading(
                       'filter_jsxgraph/libs',
                       get_string('header_libs', 'filter_jsxgraph'),
                       ''
                   ));

    $settings->add(new admin_setting_configselect(
                       'filter_jsxgraph/formulasextension',
                       get_string('formulasextension', 'filter_jsxgraph'),
                       get_string('formulasextension_desc', 'filter_jsxgraph'),
                       '1',
                       [get_string('off', 'filter_jsxgraph'), get_string('on', 'filter_jsxgraph')]
                   ));

    $settings->add(new admin_setting_heading(
                       'filter_jsxgraph/codingbetweentags',
                       get_string('header_codingbetweentags', 'filter_jsxgraph'),
                       ''
                   ));

    $settings->add(new admin_setting_configselect(
                       'filter_jsxgraph/html_entities',
                       get_string('html_entities', 'filter_jsxgraph'),
                       get_string('html_entities_desc', 'filter_jsxgraph'),
                       '1',
                       [get_string('no', 'filter_jsxgraph'), get_string('yes', 'filter_jsxgraph')]
                   ));

    $settings->add(new admin_setting_configselect(
                       'filter_jsxgraph/convertencoding',
                       get_string('convertencoding', 'filter_jsxgraph'),
                       get_string('convertencoding_desc', 'filter_jsxgraph'),
                       '1',
                       [get_string('no', 'filter_jsxgraph'), get_string('yes', 'filter_jsxgraph')]
                   ));

    $settings->add(new admin_setting_heading(
                       'filter_jsxgraph/globaljs',
                       get_string('header_globaljs', 'filter_jsxgraph'),
                       ''
                   ));

    $settings->add(new admin_setting_configtextarea(
                       'filter_jsxgraph/globalJS',
                       get_string('globalJS', 'filter_jsxgraph'),
                       get_string('globalJS_desc', 'filter_jsxgraph'),
                       '', PARAM_RAW, 60, 20
                   ));

    $settings->add(new admin_setting_heading(
                       'filter_jsxgraph/dimensions',
                       get_string('header_dimensions', 'filter_jsxgraph'),
                       get_string('dimensions', 'filter_jsxgraph')
                   ));

    $settings->add(new admin_setting_configtext(
                       'filter_jsxgraph/aspectratio',
                       get_string('aspectratio', 'filter_jsxgraph'),
                       get_string('aspectratio_desc', 'filter_jsxgraph'),
                       '', PARAM_TEXT
                   ));

    $settings->add(new admin_setting_configtext(
                       'filter_jsxgraph/fixwidth',
                       get_string('fixwidth', 'filter_jsxgraph'),
                       get_string('fixwidth_desc', 'filter_jsxgraph'),
                       get_config('filter_jsxgraph', 'width') ?? '', PARAM_TEXT
                   ));

    $settings->add(new admin_setting_configtext(
                       'filter_jsxgraph/fixheight',
                       get_string('fixheight', 'filter_jsxgraph'),
                       get_string('fixheight_desc', 'filter_jsxgraph'),
                       get_config('filter_jsxgraph', 'height') ?? '', PARAM_TEXT
                   ));

    $settings->add(new admin_setting_configtext(
                       'filter_jsxgraph/maxwidth',
                       get_string('maxwidth', 'filter_jsxgraph'),
                       get_string('maxwidth_desc', 'filter_jsxgraph'),
                       '', PARAM_TEXT
                   ));

    $settings->add(new admin_setting_configtext(
                       'filter_jsxgraph/maxheight',
                       get_string('maxheight', 'filter_jsxgraph'),
                       get_string('maxheight_desc', 'filter_jsxgraph'),
                       '', PARAM_TEXT
                   ));

    $settings->add(new admin_setting_configtext(
                       'filter_jsxgraph/fallbackaspectratio',
                       get_string('fallbackaspectratio', 'filter_jsxgraph'),
                       get_string('fallbackaspectratio_desc', 'filter_jsxgraph'),
                       '1 / 1', PARAM_TEXT
                   ));

    $settings->add(new admin_setting_configtext(
                       'filter_jsxgraph/fallbackwidth',
                       get_string('fallbackwidth', 'filter_jsxgraph'),
                       get_string('fallbackwidth_desc', 'filter_jsxgraph'),
                       '100%', PARAM_TEXT
                   ));

    $settings->add(new admin_setting_heading(
                       'filter_jsxgraph/deprecated',
                       get_string('header_deprecated', 'filter_jsxgraph'),
                       ''
                   ));

    $settings->add(new admin_setting_configselect(
                       'filter_jsxgraph/usedivid',
                       get_string('usedivid', 'filter_jsxgraph'),
                       get_string('usedivid_desc', 'filter_jsxgraph'),
                       '0',
                       [get_string('no', 'filter_jsxgraph'), get_string('yes', 'filter_jsxgraph')]
                   ));

    $settings->add(new admin_setting_configtext(
                       'filter_jsxgraph/divid',
                       get_string('divid', 'filter_jsxgraph'),
                       get_string('divid_desc', 'filter_jsxgraph'),
                       'box'
                   ));

    $settings->add(new admin_setting_heading(
                       'filter_jsxgraph/last',
                       '',
                       '<br><br>'
                   ));
}
