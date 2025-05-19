@format @format_cards @format_cards-card_visibility
Feature: Cards can be hidden
  To manage my course
  As a teacher
  I can hide cards from students

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format  | numsections |
      | Course 1 | C1        | cards   | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

  @javascript
  Scenario: Hidden sections are not displayed to students
    Given I am logged in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I hide section "1"
    When I am on the "Course 1" "course" page logged in as "student1"
    Then "Section 1" "section" should not exist
