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

namespace local_xp\local\userfilter;

use block_xp\local\userfilter\user_filter;

/**
 * User filter.
 *
 * @package    local_xp
 * @copyright  2025 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stack implements user_filter {

    /** @var user_filter[] */
    protected $filters;
    /** @var bool Whether we AND, the filters. */
    protected $and = true;

    /**
     * Constructor.
     *
     * @param user_filter[] $filters The filters.
     * @param bool $and Whether to AND or OR the filters.
     */
    public function __construct(array $filters, $and = true) {
        $this->filters = $filters;
        $this->and = $and;
    }

    /**
     * Get the SQL fragment to filter users.
     *
     * @return array Containing both SQL fragment, and params.
     */
    public function get_sql(string $useridalias): array {
        if (empty($this->filters)) {
            return ['1=1', []];
        }

        $params = [];
        $sqls = [];
        foreach ($this->filters as $filter) {
            [$insql, $inparams] = $filter->get_sql($useridalias);
            $sqls[] = $insql;
            $params += $inparams;
        }

        $operator = $this->and ? ' AND ' : ' OR ';
        return ['((' . implode(") $operator (", $sqls) . '))', $params];
    }

}
