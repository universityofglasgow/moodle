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
 * Iomad facade.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\iomad;

defined('MOODLE_INTERNAL') || die();

use company;
use context_system;
use iomad;
use moodle_database;

/**
 * Iomad facade class.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class facade {

    /** @var moodle_database The database. */
    protected $db = false;
    /** @var bool Whether we initialised the facade. */
    protected $initialised = false;

    /**
     * Constructor.
     *
     * @param moodle_database $db The database.
     */
    public function __construct(moodle_database $db) {
        $this->db = $db;
    }

    /**
     * Init the things.
     */
    public function init() {
    }

    /**
     * Return whether IOMAD exists.
     *
     * @return bool
     */
    public function exists() {
        return false;
    }

    /**
     * Get a company's name.
     *
     * @param int $id The ID.
     * @return string
     */
    public function get_company_name($id) {
        return '?';
    }

    /**
     * Get a departments's name.
     *
     * @param int $id The ID.
     * @return string
     */
    public function get_department_name($id) {
        return '?';
    }

    /**
     * Get a user's company IDs.
     *
     * In theory users should only belong to one company, but we return an array anyway.
     * Also there doesn't seem be a reliable way to extract this from IOMAD, so we wrote our own.
     *
     * @param object $user The user.
     * @return array
     */
    public function get_user_company_ids($user) {
        return [];
    }

    /**
     * Get a user's department IDs.
     *
     * In theory users should only belong to one department, but we return an array anyway.
     * Also there doesn't seem be a reliable way to extract this from IOMAD, so we wrote our own.
     *
     * @param object $user The user.
     * @return array
     */
    public function get_user_department_ids($user) {
        return [];
    }

    /**
     * Get the company ID being viewed.
     *
     * @return int
     */
    public function get_viewing_companyid() {
        return 0;
    }

    /**
     * Get the department ID being viewed.
     *
     * @return int
     */
    public function get_viewing_departmentid() {
        return 0;
    }

    /**
     * Redirect to pick a company if needed.
     *
     * @return void
     */
    public function redirect_for_company_if_needed() {
    }

}
