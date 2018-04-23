@block @block_course_overview
Feature: View the course overview block on the dashboard and test it's functionality
  In order to view the course overview block on the dashboard
  As an admin
  I can configure the course overview block

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber |
      | student1 | Student | 1 | student1@example.com | S1 |
      | teacher1 | Teacher | 1 | teacher1@example.com | T1 |
    And the following "categories" exist:
      | name        | category | idnumber |
      | Category 1  | 0        | CAT1     |
      | Category 2  | CAT1     | CAT2     |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
      | Course 2 | C2        | CAT1     |
      | Course 3 | C3        | CAT2     |
    And I log in as "student1"
      When I press "Customise this page"
      And I add the "Course overview (legacy)" block
      And I configure the "Course overview (legacy)" block
      And I set the field "Region" to "content"
      And I press "Save changes"
      And I log out

  Scenario: View the block by a user with several enrolments
    Given the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student1 | C2 | student |
    When I log in as "student1"
    Then I should see "Course 1" in the "Course overview (legacy)" "block"
    And I should see "Course 2" in the "Course overview (legacy)" "block"

  @javascript
  Scenario: View the block by a user with the parent categories displayed.
    Given the following config values are set as admin:
      | showcategories | Parent category only | block_course_overview |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student1 | C2 | student |
      | student1 | C3 | student |
    When I log in as "student1"
    Then I should see "Miscellaneous" in the "Course overview (legacy)" "block"
    And I should see "Category 1" in the "Course overview (legacy)" "block"
    And I should see "Category 2" in the "Course overview (legacy)" "block"
    And I should not see "Category 1 / Category 1" in the "Course overview (legacy)" "block"

  Scenario: View the block by a user with the full categories displayed.
    Given the following config values are set as admin:
      | showcategories | 2 | block_course_overview |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student1 | C2 | student |
      | student1 | C3 | student |
    When I log in as "student1"
    Then I should see "Miscellaneous" in the "Course overview (legacy)" "block"
    And I should see "Category 1 / Category 2" in the "Course overview (legacy)" "block"

