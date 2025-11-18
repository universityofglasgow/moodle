<?php
// This file is part of Level Up XP+.
//
// Level Up XP+ is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Level Up XP+ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
//
// https://levelup.plus

namespace local_xp\local\reportbuilder\entities;

use block_xp\di;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;

/**
 * Entity.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class xp extends base {
    use pre_404_compat_trait;

    protected function get_default_tables(): array {
        return ['block_xp'];
    }

    protected function get_default_entity_title(): lang_string {
        return new lang_string('pluginname', 'block_xp');
    }

    public function initialise(): base {
        $worldfactory = di::get('course_world_factory');
        $coursecurrencyfactory = di::get('course_currency_factory');
        $tablealias = $this->get_table_alias('block_xp');

        $column = (new column('points', new lang_string('points', 'local_xp'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->add_field("{$tablealias}.xp")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);
        $this->add_column($column);

        $column = (new column('pointswithsymbol', new lang_string('pointswithsymbol', 'local_xp'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->add_field("{$tablealias}.xp")
            ->add_field("{$tablealias}.courseid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_callback(static function ($value, $row) use ($coursecurrencyfactory) {
                $currency = $coursecurrencyfactory->get_currency($row->courseid ?? SITEID);
                return di::get('renderer')->xp($value, $currency);
            });
        $this->add_column($column);

        $column = (new column('level', new lang_string('level', 'block_xp'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->add_field("{$tablealias}.xp")
            ->add_field("{$tablealias}.courseid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true, ['xp'])
            ->set_disabled_aggregation_all()
            ->add_callback(static function ($value, $row) use ($worldfactory) {
                if (!$row->courseid) {
                    return null;
                }
                $world = $worldfactory->get_world($row->courseid);
                $level = $world->get_levels_info()->get_level_from_xp((int) $value);
                return $level->get_level();
            });
        $this->add_column($column);

        $column = (new column('levelbadge', new lang_string('levelbadge', 'block_xp'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->add_field("{$tablealias}.xp")
            ->add_field("{$tablealias}.courseid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true, ['xp'])
            ->set_disabled_aggregation_all()
            ->add_callback(static function ($value, $row) use ($worldfactory) {
                if (!$row->courseid) {
                    return null;
                }
                $world = $worldfactory->get_world($row->courseid);
                $level = $world->get_levels_info()->get_level_from_xp((int) $value);
                return \html_writer::div(di::get('renderer')->small_level_badge($level), 'd-inline-block');
            });
        $this->add_column($column);

        $filter = (new filter(number::class, 'points', new lang_string('points', 'local_xp'), $this->get_entity_name()))
            ->set_field_sql("{$tablealias}.xp")
            ->add_joins($this->get_joins())
            ->set_limited_operators([
                number::ANY_VALUE,
                number::LESS_THAN,
                number::GREATER_THAN,
                number::EQUAL_TO,
                number::EQUAL_OR_LESS_THAN,
                number::EQUAL_OR_GREATER_THAN,
                number::RANGE,
            ]);

        $this->add_filter($filter);
        $this->add_condition($filter);

        return $this;
    }

}
