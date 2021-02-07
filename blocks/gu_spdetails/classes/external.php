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
 * The external API for block_gu_spdetails.
 *
 * @package    block_gu_spdetails
 * @copyright  2020 Accenture
 * @author     Franco Louie Magpusao <franco.l.magpusao@accenture.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_gu_spdetails;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. '/externallib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

class external extends external_api {
    /**
     * Retrieves assessments parameters.
     *
     * @return external_function_parameters
     */
    public static function retrieve_assessments_parameters() {
        return new external_function_parameters(
            array(
                'activetab' => new external_value(PARAM_ALPHA, 'The active tab', VALUE_DEFAULT),
                'page' => new external_value(PARAM_INT, 'The page number', VALUE_DEFAULT),
                'sortby' => new external_value(PARAM_ALPHA, 'Sort columns by', VALUE_DEFAULT),
                'sortorder' => new external_value(PARAM_ALPHA, 'Sort by order', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Displays paginated assessments
     *
     * @param string $activetab
     * @param int $page
     * @param string $sortby
     * @param string $sortorder
     * @return api
     */
    public static function retrieve_assessments($activetab, $page, $sortby, $sortorder) {
        $params = self::validate_parameters(self::retrieve_assessments_parameters(),
                                            ['activetab' => $activetab, 'page' => $page,
                                             'sortby' => $sortby, 'sortorder' => $sortorder]);
        return [
            'result' => api::retrieve_assessments($params['activetab'], $params['page'],
                                                  $params['sortby'], $params['sortorder'])
        ];
    }

    /**
     * Retrieves assessments return value.
     *
     * @return external_value
     */
    public static function retrieve_assessments_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_RAW, 'The processing result')
        ]);
    }
}
