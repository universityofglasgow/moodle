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
 * Upgrade Script for filter_jsxgraph
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

/**
 * xmldb_filter_jsxgraph_install
 *
 * @return bool result
 */
function xmldb_filter_jsxgraph_install() {

    $release = 'v1.10.1 - additions'; // This value should be the same as in version.php!
    $recommendedjsx = 'v1.10.1';

    $versions = [
        ["id" => "auto"],
        ["id" => 'v1.11.0beta2', "label" => 'v1.11.0beta2', "file" => 'jsxgraphcore-v1.11.0beta2-lazy.js'],
        ["id" => 'v1.11.0beta1', "label" => 'v1.11.0beta1', "file" => 'jsxgraphcore-v1.11.0beta1-lazy.js'],
        ["id" => '1.10.1', "label" => 'v1.10.1', "file" => 'jsxgraphcore-v1.10.1-lazy.js'],
        ["id" => '1.10.0', "label" => 'v1.10.0', "file" => 'jsxgraphcore-v1.10.0-lazy.js'],
        ["id" => '1.9.2', "label" => 'v1.9.2', "file" => 'jsxgraphcore-v1.9.2-lazy.js'],
        ["id" => '1.9.1', "label" => 'v1.9.1', "file" => 'jsxgraphcore-v1.9.1-lazy.js'],
        ["id" => '1.9.0', "label" => 'v1.9.0', "file" => 'jsxgraphcore-v1.9.0-lazy.js'],
        ["id" => '1.8.0', "label" => 'v1.8.0', "file" => 'jsxgraphcore-v1.8.0-lazy.js'],
        ["id" => '1.7.0', "label" => 'v1.7.0', "file" => 'jsxgraphcore-v1.7.0-lazy.js'],
        ["id" => '1.6.2', "label" => 'v1.6.2', "file" => 'jsxgraphcore-v1.6.2-lazy.js'],
        ["id" => '1.6.1', "label" => 'v1.6.1', "file" => 'jsxgraphcore-v1.6.1-lazy.js'],
        ["id" => '1.6.0', "label" => 'v1.6.0', "file" => 'jsxgraphcore-v1.6.0-lazy.js'],
        ["id" => '1.5.0', "label" => 'v1.5.0', "file" => 'jsxgraphcore-v1.5.0-lazy.js'],
        ["id" => '1.4.6', "label" => 'v1.4.6', "file" => 'jsxgraphcore-v1.4.6-lazy.js'],
        ["id" => '1.4.5', "label" => 'v1.4.5', "file" => 'jsxgraphcore-v1.4.5-lazy.js'],
        ["id" => '1.4.4', "label" => 'v1.4.4', "file" => 'jsxgraphcore-v1.4.4-lazy.js'],
        ["id" => '1.4.3', "label" => 'v1.4.3', "file" => 'jsxgraphcore-v1.4.3-lazy.js'],
        ["id" => '1.4.2', "label" => 'v1.4.2', "file" => 'jsxgraphcore-v1.4.2-lazy.js'],
        ["id" => '1.4.1', "label" => 'v1.4.1', "file" => 'jsxgraphcore-v1.4.1-lazy.js'],
        ["id" => '1.4.0', "label" => 'v1.4.0', "file" => 'jsxgraphcore-v1.4.0-lazy.js'],
        ["id" => '1.3.2', "label" => 'v1.3.2', "file" => 'jsxgraphcore-v1.3.2-lazy.js'],
        ["id" => '1.3.1', "label" => 'v1.3.1', "file" => 'jsxgraphcore-v1.3.1-lazy.js'],
        ["id" => '1.3.0', "label" => 'v1.3.0', "file" => 'jsxgraphcore-v1.3.0-lazy.js'],
        ["id" => '1.2.3', "label" => 'v1.2.3', "file" => 'jsxgraphcore-v1.2.3-lazy.js'],
        ["id" => '1.2.2', "label" => 'v1.2.2', "file" => 'jsxgraphcore-v1.2.2-lazy.js'],
        ["id" => '1.2.1', "label" => 'v1.2.1', "file" => 'jsxgraphcore-v1.2.1-lazy.js'],
        ["id" => '1.2.0', "label" => 'v1.2.0', "file" => 'jsxgraphcore-v1.2.0-lazy.js'],
        ["id" => '1.1.0', "label" => 'v1.1.0', "file" => 'jsxgraphcore-v1.1.0-lazy.js'],
        ["id" => '1.0.0', "label" => 'v1.0.0', "file" => 'jsxgraphcore-v1.0.0-lazy.js'],
        ["id" => '0.99.7', "label" => 'v0.99.7', "file" => 'jsxgraphcore-v0.99.7-lazy.js'],
        ["id" => '0.99.6', "label" => 'v0.99.6', "file" => 'jsxgraphcore-v0.99.6-lazy.js'],
        ["id" => '0.99.5', "label" => 'v0.99.5', "file" => 'jsxgraphcore-v0.99.5-lazy.js'],
        ["id" => '0.99.4', "label" => 'v0.99.4', "file" => 'jsxgraphcore-v0.99.4-lazy.js'],
        ["id" => '0.99.3', "label" => 'v0.99.3', "file" => 'jsxgraphcore-v0.99.3-lazy.js'],
        ["id" => '0.99.2', "label" => 'v0.99.2', "file" => 'jsxgraphcore-v0.99.2-lazy.js'],
        ["id" => '0.99.1', "label" => 'v0.99.1', "file" => 'jsxgraphcore-v0.99.1-lazy.js'],
        ["id" => '0.98', "label" => 'v0.98', "file" => 'jsxgraphcore-v0.98-lazy.js'],
        ["id" => '0.97', "label" => 'v0.97', "file" => 'jsxgraphcore-v0.97-lazy.js'],
        ["id" => '0.96', "label" => 'v0.96', "file" => 'jsxgraphcore-v0.96-lazy.js'],
        ["id" => '0.95', "label" => 'v0.95', "file" => 'jsxgraphcore-v0.95-lazy.js'],
        ["id" => '0.94', "label" => 'v0.94', "file" => 'jsxgraphcore-v0.94-lazy.js'],
        ["id" => '0.93', "label" => 'v0.93', "file" => 'jsxgraphcore-v0.93-lazy.js'],
        ["id" => '0.92', "label" => 'v0.92', "file" => 'jsxgraphcore-v0.92-lazy.js'],
        ["id" => '0.91', "label" => 'v0.91', "file" => 'jsxgraphcore-v0.91-lazy.js'],
        ["id" => '0.90', "label" => 'v0.90', "file" => 'jsxgraphcore-v0.90-lazy.js'],
    ];

    try {
        set_config('release', $release, 'filter_jsxgraph');
        set_config('recommendedJSX', $recommendedjsx, 'filter_jsxgraph');
        set_config('versions', json_encode($versions), 'filter_jsxgraph');
    } catch (Exception $e) {
        // Exception is not handled because it is not necessary.
        // This has to be here for code prechecks.
        echo '';
    }

    return true;
}
