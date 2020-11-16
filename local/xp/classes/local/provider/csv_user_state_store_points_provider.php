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
 * CSV state store points provider.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\provider;
defined('MOODLE_INTERNAL') || die();

use csv_import_reader;
use block_xp\local\iterator\csv_reader_iterator;
use block_xp\local\iterator\map_iterator;
use block_xp\local\reason\reason;

/**
 * CSV state store points provider.
 *
 * Provides state store points from a CSV.
 *
 * @package    local_xp
 * @copyright  2020 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csv_user_state_store_points_provider implements user_state_store_points_provider {

    /** @var csv_import_reader The CSV import reader. */
    protected $cir;
    /** @var int The default action. */
    protected $defaultaction;
    /** @var int The default reason. */
    protected $defaultreason;
    /** @var user_resolver The user resolver. */
    protected $userresolver;

    /**
     * Constructor.
     *
     * @param csv_import_reader $cir The CSV reader with content loaded.
     * @param user_resolver $userresolver The user resolver.
     * @param int $defaultaction A user_state_store_points::ACTION_ constant.
     * @param reason $defaultreason The default reason.
     */
    public function __construct(csv_import_reader $cir, user_resolver $userresolver,
            $defaultaction = user_state_store_points::ACTION_INCREASE, reason $defaultreason = null) {
        $this->cir = $cir;
        $this->userresolver = $userresolver;
        $this->defaultaction = $defaultaction;
        $this->defaultreason = $defaultreason;
    }

    /**
     * Get the iterator.
     *
     * @return \Iterator
     */
    public function getIterator() {
        return new map_iterator(
            new csv_reader_iterator($this->cir),
            function($line, $lineno) {
                return $this->process_line($line, $lineno);
            }
        );
    }

    /**
     * Processes a line.
     *
     * This returned structured information about the line, its data
     * and the errors that we may have encountered while processing it.
     *
     * @param array $line Raw line from CSV.
     * @param int $lineno Line number.
     * @return object
     */
    protected function process_line($line, $lineno) {
        $errors = [];
        $line = array_combine($this->cir->get_columns(), $line);

        $points = (int) $line['points'];
        if ($points < 0) {
            $errors['negativepoints'] = get_string('invalidpointscannotbenegative', 'local_xp');
        }

        $user = $this->userresolver->resolve($line['user']);
        if (!$user) {
            $errors['unknownuser'] = get_string('unabletoidentifyuser', 'local_xp');
        }

        $message = !empty($line['message']) ? $line['message'] : null;

        $object = null;
        if (empty($errors)) {
            $object = new user_state_store_points($user, $points, $this->defaultaction, $this->defaultreason, $message);
        }

        return new user_state_store_points_entry($lineno, $object, $errors);
    }

    /**
     * Validate the CSV.
     *
     * @return string[] Returns an array of errors, if any.
     */
    public function validate_csv() {
        $errors = [];
        $csvloaderror = $this->cir->get_error();

        if ($csvloaderror !== null) {
            $errors['csvloaderror'] = $csvloaderror;
            return $errors;
        }

        $columns = $this->cir->get_columns();
        $columns = empty($columns) ? [] : $columns;
        $requiredcols = ['user', 'points'];
        $diff = array_diff($requiredcols, $columns);
        if (!empty($diff)) {
            $errors['csvloaderror'] = get_string('csvmissingcolumns', 'local_xp', implode(', ', $diff));
            return $errors;
        }

        return [];
    }

}
