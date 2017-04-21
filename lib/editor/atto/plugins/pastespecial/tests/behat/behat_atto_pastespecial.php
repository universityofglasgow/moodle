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
 * Commenting system steps definitions.
 *
 * @package    atto_pastespecial
 * @copyright  2016 Joseph Inhofer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Steps definitions to deal with the commenting system
 *
 * @package    atto_pastespecial
 * @copyright  2016 Joseph Inhofer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_atto_pastespecial extends behat_base {

    /**
     * Sets the innerhtml for the specified selector
     *
     * @Given /^I set the innerhtml of "(?P<element_string>(?:[^"]|\\")*)" to "(?P<field_value_string>(?:[^"]|\\")*)"$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $element The locator of the specified selector
     * @param string $value
     * @return void
     */
    public function i_set_the_selector_innerhtml_to($element, $value) {
        $session = $this->getSession();
        $session->evaluateScript("
                document.getElementsByClassName('".$element."')[0].innerHTML = '".$value."';
                ");
    }
}
