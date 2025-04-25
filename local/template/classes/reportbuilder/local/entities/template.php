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
use core_reportbuilder\local\filters\boolean_select;

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
            'course',
            'course_categories',
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

        $templcoursejoin = "LEFT JOIN {course} templcoursealias ON templcoursealias.id = {$templatealias}.templatecourseid";
        $templcoursecatjoin = "LEFT JOIN {course_categories} templcoursecatalias
                                ON templcoursecatalias.id = templcoursealias.category";

        $impcoursejoin = "LEFT JOIN {course} impcoursealias ON impcoursealias.id = {$templatealias}.importcourseid";
        $impcoursecatjoin = "LEFT JOIN {course_categories} impcoursecatalias ON impcoursecatalias.id = impcoursealias.category";

        $joins = [
            $templcoursejoin,
            $templcoursecatjoin,
            $impcoursejoin,
            $impcoursecatjoin,
        ];
        $this->add_joins($joins);

        // Full Name when created column.
        $columns[] = (new column(
            'fullnameorigin',
            new lang_string('fullnameorigin', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$templatealias}.fullname")
            ->set_is_sortable(true);

        // Full Name of used template column.
        $columns[] = (new column(
            'fullnametemplate',
            new lang_string('fullnametemplate', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("templcoursealias.fullname")
            ->set_is_sortable(true);

        // Full Name of imported course column.
        $columns[] = (new column(
            'fullnameimported',
            new lang_string('fullnameimported', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("impcoursealias.fullname")
            ->set_is_sortable(true);

        // Short Name column.
        $columns[] = (new column(
            'shortnameorigin',
            new lang_string('shortnameorigin', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$templatealias}.shortname")
            ->set_is_sortable(true);

        // Short Name of used template column.
        $columns[] = (new column(
            'shortnametemplate',
            new lang_string('shortnametemplate', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("templcoursealias.shortname")
            ->set_is_sortable(true);

        // Short Name of imported course column.
        $columns[] = (new column(
            'shortnameimported',
            new lang_string('shortnameimported', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("impcoursealias.shortname")
            ->set_is_sortable(true);

        // ID number column.
        $columns[] = (new column(
            'idorigin',
            new lang_string('idorigin', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$templatealias}.idnumber")
            ->set_is_sortable(true);

        // ID number of used template column.
        $columns[] = (new column(
            'idtemplate',
            new lang_string('idtemplate', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("templcoursealias.idnumber")
            ->set_is_sortable(true);

        // ID number of imported course column.
        $columns[] = (new column(
            'idimported',
            new lang_string('idimported', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("impcoursealias.idnumber")
            ->set_is_sortable(true);

        // Used template category column.
        $columns[] = (new column(
            'categorytemplate',
            new lang_string('categorytemplate', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("templcoursecatalias.name")
            ->set_is_sortable(true);

        // Original course category column.
        $columns[] = (new column(
            'categoryorigin',
            new lang_string('categoryorigin', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$templatealias}.category")
            ->set_is_sortable(true);

        // Add auto enrolment column.
        $columns[] = (new column(
            'gudbenrolment',
            new lang_string('gudbenrolment', 'local_template'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN)
            ->add_fields("{$templatealias}.gudbenrolment")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'boolean_as_text']);

        // Enable existing enrolments column.
        $columns[] = (new column(
            'gudbstatus',
            new lang_string('status', 'enrol_gudatabase'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN)
            ->add_fields("{$templatealias}.gudbstatus")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'boolean_as_text']);

        // Enable codes in course settings column.
        $columns[] = (new column(
            'gudbsettingscodes',
            new lang_string('settingscodes', 'enrol_gudatabase'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN)
            ->add_fields("{$templatealias}.gudbsettingscodes")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'boolean_as_text']);

        // Allow hidden course column.
        $columns[] = (new column(
            'gudballowhidden',
            new lang_string('allowhidden', 'enrol_gudatabase'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN)
            ->add_fields("{$templatealias}.gudballowhidden")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'boolean_as_text']);

        // More codes (one per line) column.
        $columns[] = (new column(
            'gudbcodelist',
            new lang_string('codesettings', 'enrol_gudatabase'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$templatealias}.gudbcodelist")
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
            ->add_callback(function($value) {
                return self::format_date($value);
            });

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
            ->add_callback(function($value) {
                return self::format_date($value);
            });

        return $columns;

    }

    /**
     * Format the date for better CSV download.
     *
     * @param int $value
     * @return string
     */
    private static function format_date($value) {

        $screen = userdate($value, '');
        $downloadcsv = userdate($value, '%Y-%m-%d %H:%M');
        $downloadexcel = userdate($value, get_string('strftimedatetimeshort', 'core_langconfig'));

        if (!isset($_GET['download'])) {
            return $screen;
        } else {
            if ($_GET['download'] == 'csv') {
                return $downloadcsv;
            } else {
                return $downloadexcel;
            }
        }

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
            'fullnameorigin',
            new lang_string('fullnameorigin', 'local_template'),
            $this->get_entity_name(),
            "{$templatealias}.fullname"
        ))
            ->add_joins($this->get_joins());

        // Used template fullname filter.
        $filters[] = (new filter(
            text::class,
            'fullnametemplate',
            new lang_string('fullnametemplate', 'local_template'),
            $this->get_entity_name(),
            "templcoursealias.fullname"
        ))
            ->add_joins($this->get_joins());

        // Imported course fullname filter.
        $filters[] = (new filter(
            text::class,
            'fullnameimported',
            new lang_string('fullnameimported', 'local_template'),
            $this->get_entity_name(),
            "impcoursealias.fullname"
        ))
            ->add_joins($this->get_joins());

        // Created course shortname filter.
        $filters[] = (new filter(
            text::class,
            'shortnameorigin',
            new lang_string('shortnameorigin', 'local_template'),
            $this->get_entity_name(),
            "{$templatealias}.shortname"
        ))
            ->add_joins($this->get_joins());

        // Used template shortname filter.
        $filters[] = (new filter(
            text::class,
            'shortnametemplate',
            new lang_string('shortnametemplate', 'local_template'),
            $this->get_entity_name(),
            "templcoursealias.shortname"
        ))
            ->add_joins($this->get_joins());

            // Imported course shortname filter.
        $filters[] = (new filter(
            text::class,
            'shortnameimported',
            new lang_string('shortnameimported', 'local_template'),
            $this->get_entity_name(),
            "impcoursealias.shortname"
        ))
            ->add_joins($this->get_joins());

        // Created course ID number filter.
        $filters[] = (new filter(
            text::class,
            'idorigin',
            new lang_string('idorigin', 'local_template'),
            $this->get_entity_name(),
            "{$templatealias}.idnumber"
        ))
            ->add_joins($this->get_joins());

        // Used template ID number filter.
        $filters[] = (new filter(
            text::class,
            'idtemplate',
            new lang_string('idtemplate', 'local_template'),
            $this->get_entity_name(),
            "templcoursealias.idnumber"
        ))
            ->add_joins($this->get_joins());

        // Imported course ID number filter.
        $filters[] = (new filter(
            text::class,
            'idimported',
            new lang_string('idimported', 'local_template'),
            $this->get_entity_name(),
            "impcoursealias.idnumber"
        ))
            ->add_joins($this->get_joins());

        // Used template category filter.
        $filters[] = (new filter(
            text::class,
            'categorytemplate',
            new lang_string('categorytemplate', 'local_template'),
            $this->get_entity_name(),
            "templcoursecatalias.name"
        ))
            ->add_joins($this->get_joins());

        // Original course category filter.
        $filters[] = (new filter(
            text::class,
            'categoryorigin',
            new lang_string('categoryorigin', 'local_template'),
            $this->get_entity_name(),
            "{$templatealias}.category"
        ))
            ->add_joins($this->get_joins());

        // Auto enrolment filter.
        $filters[] = (new filter(
            boolean_select::class,
            'gudbenrolment',
            new lang_string('gudbenrolment', 'local_template'),
            $this->get_entity_name(),
            "{$templatealias}.gudbenrolment"
        ))
            ->add_joins($this->get_joins());

        // Enable existing enrolments filter.
        $filters[] = (new filter(
            boolean_select::class,
            'gudbstatus',
            new lang_string('status', 'enrol_gudatabase'),
            $this->get_entity_name(),
            "{$templatealias}.gudbstatus"
        ))
            ->add_joins($this->get_joins());

        // Enable codes in course settings filter.
        $filters[] = (new filter(
            boolean_select::class,
            'gudbsettingscodes',
            new lang_string('settingscodes', 'enrol_gudatabase'),
            $this->get_entity_name(),
            "{$templatealias}.gudbsettingscodes"
        ))
            ->add_joins($this->get_joins());

        // Allow hidden course filter.
        $filters[] = (new filter(
            boolean_select::class,
            'gudballowhidden',
            new lang_string('allowhidden', 'enrol_gudatabase'),
            $this->get_entity_name(),
            "{$templatealias}.gudballowhidden"
        ))
            ->add_joins($this->get_joins());

        // More codes (one per line) filter.
        $filters[] = (new filter(
            text::class,
            'gudbcodelist',
            new lang_string('codesettings', 'enrol_gudatabase'),
            $this->get_entity_name(),
            "{$templatealias}.gudbcodelist"
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
