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

namespace local_template\reportbuilder\datasource;

use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\course;;
use core_reportbuilder\local\entities\user;
// use core_reportbuilder\local\helpers\database;

/**
 * Template datasource
 *
 * @package    local_template
 * @copyright  2024 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template extends datasource {
    /**
     * Return user friendly name of the datasource
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('template','local_template');
    }

    /**
     * Initialise report
     *
     * @return void
     */
    protected function initialise(): void {
        global $CFG;
        require_once($CFG->dirroot.'/local/template/locallib.php');

        $templateentity = new \local_template\reportbuilder\local\entities\template();
        $templatealias = $templateentity->get_table_alias('local_template');

        $this->set_main_table('local_template', $templatealias);

        $this->add_entity($templateentity);

        // Add core user join.
        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $userjoin = "JOIN {user} {$useralias} ON {$useralias}.id = {$templatealias}.usercreated";
        $this->add_entity($userentity->add_join($userjoin));

        //Add core course join.
        $coursentity = new course();
        $coursealias = $coursentity->get_table_alias('course');
        $coursejoin = "JOIN {course} {$coursealias} ON {$coursealias}.id = {$templatealias}.templatecourseid";
        $this->add_entity($coursentity->add_join($coursejoin));

        $this->add_all_from_entities();
    }
    /**
     * Return the columns that will be added to the report once is created
     *
     * @return array
     */
    public function get_default_columns(): array {

        return ['course:fullname',
                'user:fullname',
                'template:fullname',
                'template:timemodified',
            ];

    }
    /**
     * Return the conditions that will be added to the report once is created
     *
     * @return array
     */
    public function get_default_conditions(): array {

        return [];

    }
    /**
     * Return the filters that will be added to the report once is created
     *
     * @return array
     */
    public function get_default_filters(): array {

        return [];

    }
}
