@mod @mod_checklist
Feature: Student checklist can track completion of other activities

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
      | student1 | Student   | 1        | student1@asd.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "admin"
    And I set the following administration settings values:
      | enablecompletion | 1 |
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And completion tracking is "Enabled" in current course
    And I turn editing mode on
    And I add a "Checklist" to section "1" and I fill the form with:
      | Checklist                        | Test checklist       |
      | Introduction                     | This is a checklist  |
      | Updates by                       | Student only         |
      | Show course modules in checklist | Current section      |
      | Check-off when modules complete  | Yes, cannot override |
    And I add a "Page" to section "1" and I fill the form with:
      | Name                | Test page 1                                       |
      | Description         | Test page 1                                       |
      | Page content        | This page 1 should be complete when I view it     |
      | Completion tracking | Show activity as complete when conditions are met |
      | Require view        | 1                                                 |
    And I add a "Page" to section "1" and I fill the form with:
      | Name                | Test page 2                                       |
      | Description         | Test page 2                                       |
      | Page content        | This page 2 should be complete when I view it     |
      | Completion tracking | Show activity as complete when conditions are met |
      | Require view        | 1                                                 |
    And I log out

  Scenario: The checklist should always display the current items from the section, keeping up to date when they change.
    Given I log in as "teacher1"
    And I follow "Course 1"
    When I follow "Test checklist"
    Then "Topic 1" "text" should appear before "Test page 1" "text"
    And "Test page 1" "text" should appear before "Test page 2" "text"
    # Check that changes to the course are tracked.
    When I follow "Course 1"
    And I follow "Test page 2"
    And I follow "Edit settings"
    And I set the field "Name" to "Updated name to page 5"
    And I press "Save and return to course"
    And I follow "Test checklist"
    Then "Topic 1" "text" should appear before "Test page 1" "text"
    And "Test page 1" "text" should appear before "Updated name to page 5" "text"

  @javascript
  Scenario: The checklist state should update to reflect the completion of imported activities.
    Given I log in as "student1"
    And I follow "Course 1"
    And I follow "Test checklist"
    And the following fields match these values:
      | Test page 1 | 0 |
      | Test page 2 | 0 |
    When I click on "Link to this module" "link" in the "Test page 1" "list_item"
    And I should see "This page 1 should be complete when I view it"
    And make checklist cron run first time
    And I trigger cron
    And I wait until "Cron completed at" "text" exists
    And I am on homepage
    And I follow "Course 1"
    And I follow "Test checklist"
    Then the following fields match these values:
      | Test page 1 | 1 |
      | Test page 2 | 0 |

  @javascript
  Scenario: The checklist state should update based on logs, if completion is disabled.
    Given I log in as "teacher1"
    And I follow "Course 1"
    And completion tracking is "Disabled" in current course
    And I log out
    Given I log in as "student1"
    And I follow "Course 1"
    And I follow "Test checklist"
    And the following fields match these values:
      | Test page 1 | 0 |
      | Test page 2 | 0 |
    When I click on "Link to this module" "link" in the "Test page 1" "list_item"
    And I should see "This page 1 should be complete when I view it"
    And make checklist cron run first time
    And I trigger cron
    And I wait until "Cron completed at" "text" exists
    And I am on homepage
    And I follow "Course 1"
    And I follow "Test checklist"
    Then the following fields match these values:
      | Test page 1 | 1 |
      | Test page 2 | 0 |
