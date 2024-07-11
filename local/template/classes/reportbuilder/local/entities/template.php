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

namespace local_template\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\filter;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\filters\date;

use lang_string;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Template entity class implementation
 *
 * This entity defines all the Template columns and filters to be used in any report
 *
 * @package    local_template
 * @copyright  2024 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template  extends base {

    /**
     * Database tables that this entity uses
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'local_template',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('template', 'local_template');
    }
    /**
     * Initialise the entity, add all fields
     *
     * @return base
     */
    public function initialise(): base {

        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this->add_filter($filter);
        }

        // Conditions are not different from filters.
        $conditions = $this->get_all_filters();
        foreach ($conditions as $condition) {
            $this->add_condition($condition);
        }

        return $this;
    }
    /**
     * Returns list of all available columns
     *
     * These are all the columns available to use in any report that uses this entity.
     *
     * @return array
     */
    protected function get_all_columns(): array {

        $templatealias = $this->get_table_alias('local_template');

        // Full Name column.
        $columns[] = (new column(
            'fullname',
            new lang_string('fullnamecreated', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$templatealias}.fullname")
            ->set_is_sortable(true);

        // Short Name column.
        $columns[] = (new column(
            'shortname',
            new lang_string('shortnamecreated', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$templatealias}.shortname")
            ->set_is_sortable(true);

        // ID number column.
        $columns[] = (new column(
            'idnumber',
            new lang_string('idcreated', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$templatealias}.idnumber")
            ->set_is_sortable(true);

        // Time created column.
        $columns[] = (new column(
            'timecreated',
            new lang_string('timecreated', 'core_reportbuilder'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$templatealias}.timecreated")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'userdate']);

        // Time modified column.
        $columns[] = (new column(
            'timemodified',
            new lang_string('timemodified', 'core_reportbuilder'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$templatealias}.timemodified")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'userdate']);

        return $columns;

    }
    /**
     * Return list of all available filters
     *
     * @return array
     */
    protected function get_all_filters(): array {

        $templatealias = $this->get_table_alias('local_template');

        // Created course fullname filter.
        $filters[] = (new filter(
            text::class,
            'fullnameselect',
            new lang_string('fullnamecreated', 'local_template'),
            $this->get_entity_name(),
            "{$templatealias}.fullname"
        ))
            ->add_joins($this->get_joins());

        // Created course shortname filter.
        $filters[] = (new filter(
            text::class,
            'shortnameselect',
            new lang_string('shortnamecreated', 'local_template'),
            $this->get_entity_name(),
            "{$templatealias}.shortname"
        ))
            ->add_joins($this->get_joins());

        // Time created filter.
        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('timecreated', 'core_reportbuilder'),
            $this->get_entity_name(),
            "{$templatealias}.timecreated"
        ))
            ->add_joins($this->get_joins());

        // Time modified filter.
        $filters[] = (new filter(
            date::class,
            'timemodified',
            new lang_string('timemodified', 'core_reportbuilder'),
            $this->get_entity_name(),
            "{$templatealias}.timemodified"
        ))
            ->add_joins($this->get_joins());

        return $filters;

    }

    /**
     * Return list of all available conditions - not used
     *
     * @return array
     */
    protected function get_all_conditions(): array {

        $conditions = [];

        return $conditions;

    }

}
