@format @format_flexsections @javascript
Feature: Use the activity chooser to insert activities anywhere in a section in format_flexsections
  In order to add activities to a course
  As a teacher
  I should be able to add an activity anywhere in a section.

  Background:
    Given the site is running Moodle version 4.2 or higher
    And the following "users" exist:
      | username  | firstname | lastname  | email               |
      | teacher   | Teacher   | 1         | teacher@example.com |
    And the following "courses" exist:
      | fullname  | shortname | format       |
      | Course    | C         | flexsections |
    And the following "course enrolments" exist:
      | user      | course  | role            |
      | teacher   | C       | editingteacher  |
    And the following "activities" exist:
      | activity  | course | idnumber | intro | name        | section  |
      | page      | C      | p1       | x     | Test Page   | 1        |
      | forum     | C      | f1       | x     | Test Forum  | 1        |
      | label     | C      | l1       | x     | Test Label  | 1        |
    And I log in as "teacher"
    And I am on "Course" course homepage with editing mode on

  Scenario: The activity chooser icon is hidden by default and be made visible on hover
    Given I hover ".navbar-brand" "css_element"
    And "[data-action='insert-before-Test Forum'] button" "css_element" should not be visible
    When I hover "Insert an activity or resource before 'Test Forum'" "button"
    Then "[data-action='insert-before-Test Forum'] button" "css_element" should be visible

  Scenario: The activity chooser can be used to insert modules before existing modules
    Given I hover "Insert an activity or resource before 'Test Forum'" "button"
    And I press "Insert an activity or resource before 'Test Forum'"
    And I should see "Add an activity or resource" in the ".modal-title" "css_element"
    When I click on "Add a new Assignment" "link" in the "Add an activity or resource" "dialogue"
    And I set the following fields to these values:
      | Assignment name | Test Assignment |
    And I press "Save and return to course"
    And I should see "Test Assignment" in the "Topic 1" "section"
    # Ensure the new assignment is in the middle of the two existing modules.
    Then "Test Page" "text" should appear before "Test Assignment" "text" in the "region-main" "region"
    And "Test Assignment" "text" should appear before "Test Forum" "text" in the "region-main" "region"
