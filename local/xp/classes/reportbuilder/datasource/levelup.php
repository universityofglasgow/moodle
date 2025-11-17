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

namespace local_xp\reportbuilder\datasource;

use block_xp\di;
use core\reportbuilder\local\entities\context;
use core_cohort\reportbuilder\local\entities\cohort;
use core_cohort\reportbuilder\local\entities\cohort_member;
use core_group\reportbuilder\local\entities\group;
use core_group\reportbuilder\local\entities\group_member;
use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\entities\user;
use core_reportbuilder\local\helpers\database;
use local_xp\local\reportbuilder\entities\xp;

/**
 * Data source.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class levelup extends datasource {

    public function get_default_columns(): array {
        return [
            'user:fullnamewithpicturelink',
            'xp:pointswithsymbol',
            'xp:levelbadge',
            'course:courseshortnamewithlink',
        ];
    }

    public function get_default_filters(): array {
        return [];
    }

    public function get_default_conditions(): array {
        return [];
    }

    public function get_default_column_sorting(): array {
        return [
            'xp:pointswithsymbol' => SORT_DESC,
            'user:fullnamewithpicturelink' => SORT_ASC,
        ];
    }

    public static function get_name(): string {
        return get_string('xpparticipants', 'local_xp');
    }

    protected function initialise(): void {
        $incourse = di::get('config')->get('context') == CONTEXT_COURSE;

        $xpentity = new xp();
        $xptable = $xpentity->get_table_alias('block_xp');

        $this->set_main_table('block_xp', $xptable);
        $this->add_entity($xpentity);

        // Default filtering based on the sitewide config.
        $paramcourseid = database::generate_param_name();
        if (!$incourse) {
            $this->add_base_condition_sql("{$xptable}.courseid = :{$paramcourseid}", [$paramcourseid => SITEID]);
        } else {
            $this->add_base_condition_sql("{$xptable}.courseid != :{$paramcourseid}", [$paramcourseid => SITEID]);
        }

        $courseentity = new course();
        $course = $courseentity->get_table_alias('course');
        $this->add_entity($courseentity
            ->add_join("JOIN {course} {$course}
                ON {$course}.id = {$xptable}.courseid"));

        // Context entity was added in 4.3.
        if (class_exists(context::class)) {
            $contextentity = (new context())->set_table_alias('context', $courseentity->get_table_alias('context'));
            $this->add_entity($contextentity
                ->add_joins($courseentity->get_joins())
                ->add_join($courseentity->get_context_join()));
        }

        $userentity = new user();
        $user = $userentity->get_table_alias('user');
        $this->add_entity($userentity
            ->add_join("JOIN {user} {$user}
                ON {$user}.id = {$xptable}.userid"));

        $entitygroupmember = new group_member();
        $groupmember = $entitygroupmember->get_table_alias('groups_members');
        $this->add_entity($entitygroupmember
            ->add_joins($courseentity->get_joins())
            ->add_joins($entitygroupmember->get_joins())
            ->add_join("LEFT JOIN {groups_members} {$groupmember}
                ON {$xptable}.userid = {$groupmember}.userid
               AND {$groupmember}.groupid IN (SELECT grp.id
                                                FROM {groups} grp
                                                JOIN {course} crs ON crs.id = grp.courseid
                                               WHERE crs.id = {$course}.id)"));

        $entitygroup = (new group())->set_table_alias('context', $courseentity->get_table_alias('context'));
        $group = $entitygroup->get_table_alias('groups');
        $this->add_entity($entitygroup
            ->add_joins($courseentity->get_joins())
            ->add_join($courseentity->get_context_join())
            ->add_joins($entitygroupmember->get_joins())
            ->add_join("LEFT JOIN {groups} {$group} ON {$group}.id = {$groupmember}.groupid"));

        $entitycohortmember = new cohort_member();
        $cohortmember = $entitycohortmember->get_table_alias('cohort_members');
        $this->add_entity($entitycohortmember
            ->add_joins($entitycohortmember->get_joins())
            ->add_join("LEFT JOIN {cohort_members} {$cohortmember}
                ON {$xptable}.userid = {$cohortmember}.userid"));

        // Generate alias for compatibility with Moodle <4.4.
        $entitycohort = (new cohort())
            ->set_table_alias('cohort', database::generate_alias());
        $cohort = $entitycohort->get_table_alias('cohort');
        $this->add_entity($entitycohort
            ->add_joins($entitycohort->get_joins())
            ->add_joins($entitycohortmember->get_joins())
            ->add_join("LEFT JOIN {cohort} {$cohort}
                ON {$cohort}.id = {$cohortmember}.cohortid"));

        $this->add_all_from_entities();
    }

    public static function is_available(): bool {
        return di::get('addon')->is_activated()
            && di::get('addon')->supports_report_builder();
    }

}
