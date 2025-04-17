@local @local_xp @javascript
Feature: Completion rules can be set in different scopes
  In order to create completion rules in different contexts
  As a manager
  I can change scope in the completion rules

  Background:
    Given the following "courses" exist:
      | fullname  | shortname | enablecompletion |
      | Course 1  | c1        | 1 |
      | Course 2  | c2        | 1 |
    And the following config values are set as admin:
      | name              | value |
      | block_xp_context  | 10    |

  Scenario: Scope selector supports switching scope
    And I am on the "sys" "block_xp > completionrules" page logged in as "admin"
    And "Change to course" "button" should exist
    And I should see "Sitewide"
    And I press "Change to course"
    And I set the field "Search and select a course" to "Course 1"
    When I click on "Select" "button" in the "Select course" "dialogue"
    Then I should see "Course \"c1\""
    And "Back to sitewide" "link" should exist
    And I press "Change course"
    And I set the field "Search and select a course" to "Course 2"
    And I click on "Select" "button" in the "Select course" "dialogue"
    And I should see "Course \"c2\""
    And I should not see "Course \"c1\""
    And I follow "Back to sitewide"
    And I should see "Sitewide"
    And I should not see "Course \"c1\""
    And I should not see "Course \"c2\""

  Scenario: Activity conditions can be scoped
    And I am on the "sys" "block_xp > completionrules" page logged in as "admin"
    And I press "Add"
    And I click on "Any activity" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "10"
    And I click on "Save" "button" in the "Add a condition" "dialogue"
    And I should see "+10"
    And I press "Change to course"
    And I set the field "Search and select a course" to "Course 1"
    And I click on "Select" "button" in the "Select course" "dialogue"
    And I should not see "+10"
    And I press "Add"
    And I click on "Any activity" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "20"
    And I press tab
    When I click on "Save" "button" in the "Add a condition" "dialogue"
    Then I should see "+20"
    And I follow "Back to sitewide"
    And I should see "+10"
    And I should not see "+20"
    And I press "Change to course"
    And I set the field "Search and select a course" to "Course 2"
    And I click on "Select" "button" in the "Select course" "dialogue"
    And I should not see "+10"
    And I should not see "+20"
