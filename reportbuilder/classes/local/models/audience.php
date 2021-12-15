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

declare(strict_types=1);

namespace core_reportbuilder\local\models;

use lang_string;
use core\persistent;
use core_reportbuilder\local\helpers\audience as helper;

/**
 * Persistent class to represent a report audience
 *
 * @package     core_reportbuilder
 * @copyright   2021 David Matamoros <davidmc@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class audience extends persistent {

    /** @var string Table name */
    public const TABLE = 'reportbuilder_audience';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() : array {
        return [
            'reportid' => [
                'type' => PARAM_INT,
            ],
            'classname' => [
                'type' => PARAM_TEXT,
            ],
            'configdata' => [
                'type' => PARAM_RAW,
                'default' => '{}',
            ],
            'usercreated' => [
                'type' => PARAM_INT,
                'default' => static function(): int {
                    global $USER;

                    return (int) $USER->id;
                },
            ],
        ];
    }

    /**
     * Validate reportid property
     *
     * @param int $reportid
     * @return bool|lang_string
     */
    protected function validate_reportid(int $reportid) {
        if (!report::record_exists($reportid)) {
            return new lang_string('invaliddata', 'error');
        }

        return true;
    }

    /**
     * Hook to execute after creation
     */
    protected function after_create(): void {
        helper::purge_caches();
    }

    /**
     * Hook to execute after update
     *
     * @param bool $result
     */
    protected function after_update($result): void {
        if ($result) {
            helper::purge_caches();
        }
    }

    /**
     * Hook to execute after deletion
     *
     * @param bool $result
     */
    protected function after_delete($result): void {
        if ($result) {
            helper::purge_caches();
        }
    }

    /**
     * Return the report this audience belongs to
     *
     * @return report
     */
    public function get_report(): report {
        return new report($this->get('reportid'));
    }
}
