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
 * SQL join.
 *
 * The purpose of this file is to offer compatibility with multiple
 * Moodle versions where the class core\dml\sql_join may or may not
 * exist. Therefore we interface with this one.
 *
 * Ideally this should be placed in block_xp, but due to our intentions
 * to upgrade the minimum Moodle version requirements, and the urgency
 * to fix a bug, we'll leave this here for now.
 *
 * In the future, we may remove the local implementation of sql_join.
 *
 * @package    local_xp
 * @copyright  2021 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\sql;
defined('MOODLE_INTERNAL') || die();

// Whenever the class exists, prefer it.
if (class_exists('core\dml\sql_join')) {

    /**
     * SQL join.
     *
     * @package    local_xp
     * @copyright  2021 Frédéric Massart
     * @author     Frédéric Massart <fred@branchup.tech>
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    class join extends \core\dml\sql_join {
    }

} else {

    /**
     * SQL join.
     *
     * Original copied from core\dml\sql_join created by The Open University.
     *
     * @package    local_xp
     * @copyright  2016 The Open University
     * @author     Frédéric Massart <fred@branchup.tech>
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    class join {

        /**
         * @var string joins.
         */
        public $joins;

        /**
         * @var string wheres.
         */
        public $wheres;

        /**
         * @var array params.
         */
        public $params;

        /**
         * Create an object that contains sql join fragments.
         *
         * @param string $joins The join sql fragment.
         * @param string $wheres The where sql fragment.
         * @param array $params Any parameter values.
         */
        public function __construct($joins = '', $wheres = '', $params = []) {
            $this->joins = $joins;
            $this->wheres = $wheres;
            $this->params = $params;
        }
    }

}

