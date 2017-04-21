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
 * Gismo steps definitions.
 *
 * @package    core_gismo
 * @category   test
 * @copyright  2014 Corbière Alain 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

// http://docs.behat.org/en/latest/guides/2.definitions.html
// http://davidwinter.me/articles/2012/01/13/using-behat-with-mink/ Présente la technique pour se protéger des futurs changements
// http://docs.behat.org/en/v2.5/guides/4.context.html \moodle271\lib\behat\features\bootstrap\behat_init_context.php

use Behat\Behat\Context\Step\Given as Given,
    Behat\Behat\Context\Step\Then as Then,
    Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Steps definitions to deal with the gismo component
 *
 * @package    core_gismo
 * @category   test
 * @copyright  2014 Corbière Alain <alain.corbiere@univ-lemans.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_gismo extends behat_base {

    /**
     * Synchronizes Gismo data.
     *
     * @Given /^I synchronize gismo data$/
     */
    public function i_synchronize_gismo_data() {
        $this->getSession()->visit($this->locate_path('/blocks/gismo/lib/gismo/server_side/export_data.php?password='));
        $this->getSession()->back();
    }

    /**
     *  Select a reporting.
     *
     * @Given /^I go to the "(?P<overview>(?:[^"]|\\")*)" report$/
     * @param string $overview The menu item
     */
    public function i_go_to_the_gismo_report($overview) {
        return array(
            new Given('I follow "' . get_string('gismo_report_launch', 'block_gismo') . '"'),
            new Given('I select a reporting on "' . $overview . '" with back')
        );
    }

    /**
     *  Select overview by gismo with back navigation.
     *
     * @Given /^I select a reporting on "(?P<parentnodes_string>(?:[^"]|\\")*)" with back$/
     * @param string $parentnodes_string The menu items
     */
    public function i_select_a_reporting_on_with_back($parentnodes) {
        if ($this->running_javascript()) {
            $parentnodes = array_map('trim', explode('>', $parentnodes));
            $javascript = $this->getSession()->getPage()->find("xpath", "//a[contains(text(),'" . $parentnodes[0] . "')]/../ul/li/a/div/nobr[contains(text(),'" . $parentnodes[1] . "')]/../..")->getAttribute("href");
            $this->getSession()->executeScript($javascript);
            $this->getSession()->wait(self::TIMEOUT * 1000, false);
            $this->getSession()->back();
        }
    }

    /**
     *  Select overview by gismo without back navigation.
     *
     * @Given /^I select a reporting on "(?P<parentnodes_string>(?:[^"]|\\")*)" without back$/
     * @param string $parentnodes_string The menu items
     */
    public function i_select_a_reporting_on_without_back($parentnodes) {
        if ($this->running_javascript()) {
            $parentnodes = array_map('trim', explode('>', $parentnodes));
            $javascript = $this->getSession()->getPage()->find("xpath", "//a[contains(text(),'" . $parentnodes[0] . "')]/../ul/li/a/div/nobr[contains(text(),'" . $parentnodes[1] . "')]/../..")->getAttribute("href");
            $this->getSession()->executeScript($javascript);
            $this->getSession()->wait(self::TIMEOUT * 1000, false);
        }
    }

    /**
     *  Compare number on overview by gismo.
     *
     * @Then /^I should see "(?P<element_string>(?:[^"]|\\")*)" on "(?P<parentnodes_string>(?:[^"]|\\")*)" report$/
     * @throws ExpectationException
     * @param string $element_string The show element
     * @param string $parentnodes_string The menu items
     */
    public function i_should_see_accesses_on_overview($element, $parentnodes) {
        return array(
            new Given('I follow "' . get_string('gismo_report_launch', 'block_gismo') . '"'),
            new Given('I select a reporting on "' . $parentnodes . '" without back'),
            new Then('I see "' . $element . '"')
        );
    }

    /**
     *  Compare number/text on accesses overview by gismo.
     *
     * @Then /^I see "(?P<element_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @param string $element_string The show element
     */
    public function i_see($element) {
        if ($this->running_javascript()) {
            // line 192 de gismo.js.php
            // accesses overview in resources: this.current_analysis.prepared_data["lines"] 
            // g.current_analysis.prepared_data[\"lines\"][0][0][2] = 1 and not 2 ([\"lines\"][0][1]) on "Activities > Forums over time" report
            $javascript = "if (g.current_analysis.prepared_data[\"lines\"] !== undefined && typeof g.current_analysis.prepared_data[\"lines\"] !== \"object\" ) " .
                    " return (g.current_analysis.prepared_data[\"lines\"]);";
            $elementReturn = $this->getSession()->evaluateScript($javascript);
            if (is_null($elementReturn)) {
                $javascript = "if (g.current_analysis.prepared_data[\"lines\"][0] !== undefined && typeof g.current_analysis.prepared_data[\"lines\"][0] !== \"object\" ) " .
                        " return (g.current_analysis.prepared_data[\"lines\"][0]);";
                $elementReturn = $this->getSession()->evaluateScript($javascript);
                if (is_null($elementReturn)) {
                    // accesses by students & accesses overview : this.current_analysis.prepared_data["lines"][0][1]
                    $javascript = "if (g.current_analysis.prepared_data[\"lines\"][0][1] !== undefined && typeof g.current_analysis.prepared_data[\"lines\"][0][1] !== \"object\" )  " .
                            " return (g.current_analysis.prepared_data[\"lines\"][0][1]);";
                    $elementReturn = $this->getSession()->evaluateScript($javascript);
                    if (is_null($elementReturn)) {
                        // students overview & accesses overview : this.current_analysis.prepared_data["lines"][0][0][2]
                        $javascript = "if (g.current_analysis.prepared_data[\"lines\"][0][0][2] !== undefined && typeof g.current_analysis.prepared_data[\"lines\"][0][0][2] !== \"object\" )  " .
                                " return (g.current_analysis.prepared_data[\"lines\"][0][0][2]);";
                        $elementReturn = $this->getSession()->evaluateScript($javascript);
                    }
                }
            }
            if (is_numeric($element)) {
                if ($element != $elementReturn)
                    throw new ExpectationException('The element is "' . $elementReturn . '" and not "' . $element . '"', $this->getSession());
            }
            else {
                if (strpos(strtolower($elementReturn), strtolower($element)) === false)
                    throw new ExpectationException('The element is "' . $elementReturn . '" and not "' . $element . '"', $this->getSession());
            }
            $this->getSession()->back();
        }
    }

    /**
     * @Given /^I move backward one page$/
     */
    public function iMoveBackwardOnePage() {
        $this->getSession()->back();
    }

}
