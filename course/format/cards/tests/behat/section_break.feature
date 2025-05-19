@format @format_cards @format_cards-section_break
Feature: Can add or remove section breaks
  To customise my course
  As a teacher
  I can add, remove, and rename section breaks

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format  | numsections |
      | Course 1 | C1        | cards   | 1           |
    And "Course 1" section 1 is named "Section 1"

  @javascript
  Scenario: Creating a new section break
    Given I am logged in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    When I click on "Add section break here" "link" in the "Section 1" "section"
    Then I should see "(Section break)"
    When I am on "Course 1" course homepage with editing mode off
    Then "//li[contains(@class, 'section-break')][following::li[contains(@data-for, 'section')]]" "xpath_element" should exist

  @javascript
  Scenario: Deleting a section break
    Given the following "format_cards > section break" exists:
      | course  | C1 |
      | section | 1  |
    And I am logged in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    When I click on "Remove section break" "icon" in the "Section 1" "section"
    Then I should not see "(Section break)"
    When I am on "Course 1" course homepage with editing mode off
    Then "#section-0 + .section-break" "css_element" should not exist

  @javascript
  Scenario: Renaming a section break with an inplace editable
    Given the following "format_cards > section break" exists:
      | course  | C1 |
      | section | 1  |
    And I am logged in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    When I set the field "Edit section break" in the "Section 1" "section" to "Section break name"
    Then I should see "Section break name"
    When I am on "Course 1" course homepage with editing mode off
    Then I should see "Section break name"
