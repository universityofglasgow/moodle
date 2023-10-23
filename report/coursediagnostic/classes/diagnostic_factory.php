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
 * This file uses the factory pattern as part of the course diagnostic tool.
 *
 * @package    report_coursediagnositc
 * @copyright  2022 Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursediagnostic;

defined('MOODLE_INTERNAL') || die;
class diagnostic_factory {

    /** The cache stores have been disabled */
    const STATE_STORES_DISABLED = 11;

    /**
     * The current state of the cache API.
     * @var int
     */
    protected $state = 0;

    /**
     * An instance of the diagnostic_factory class created upon the first request.
     * @var diagnostic_factory
     */
    protected static $instance;

    /**
     * Protected constructor, please use the static instance method.
     */
    protected function __construct() {
        // Nothing to do here.
    }

    /**
     * Returns an instance of the diagnostic_factory class.
     *
     * @return diagnostic_factory
     */
    public static function instance(): diagnostic_factory {

        if (self::$instance === null) {
            // Initialise a new factory to facilitate our needs.
            if (!empty($CFG->alternative_cache_factory_class)) {
                $factoryclass = $CFG->alternative_cache_factory_class;
                self::$instance = new $factoryclass();
            } else {
                // We're using the regular factory.
                self::$instance = new diagnostic_factory();
                if (defined('CACHE_DISABLE_STORES') && CACHE_DISABLE_STORES !== false) {
                    // The cache stores have been disabled.
                    self::$instance->set_state(self::STATE_STORES_DISABLED);
                }
            }
        }
        return self::$instance;
    }

    /**
     * Updates the state of the cache API.
     *
     * @param int $state
     * @return bool
     */
    public function set_state($state) {

        if ($state <= $this->state) {
            return false;
        }

        $this->state = $state;

        return true;
    }

    /**
     * @param $name - the test being performed
     * @param $course - the course object
     * @return mixed
     */
    public function create_diagnostic_test_from_config($name, $course) {

        $class = 'course_' . $name . '_test';

        require_once('interfaces.php');
        require_once($class . '.php');

        $fqclassname = '\\report_coursediagnostic\\course_' . $name . '_test';

        $testclass = new $fqclassname($name, $course);

        $testclass->runtest();

        return $testclass;
    }
}
