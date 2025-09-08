@gradereport @gradereport_uofguser  @javascript
Feature: Basic tests for UofG User Report to see it on different pages

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |

  Scenario: Plugin gradereport_uofguser appears in the list of installed additional plugins
    Given I log in as "admin"
    When I navigate to "Plugins > Plugins overview" in site administration
    And I follow "Additional plugins"
    Then I should see "UofG User Report"
    And I should see "gradereport_uofguser"

  Scenario: Report appears on the Grades report setting page
    Given I log in as "admin"
    When I navigate to "Grades > Report settings" in site administration
    Then I should see "UofG User Report"
    Then I should see "grade_report_uofguser_showsource"

  Scenario: the report is available in the course as one of the views and within course settings
    Given I am on the "Course 1" "grades > UofG User Report > View" page logged in as "admin"
    Then I should see "UofG User Report"
    When I navigate to "Setup > Course grade settings" in the course gradebook
    Then I should see "UofG User Report"

  Scenario: the report should be an option as a default user profile report
    Given I log in as "admin"
    When I navigate to "Grades > General settings" in site administration
    Then the "s__grade_profilereport" select box should contain "UofG User Report"
