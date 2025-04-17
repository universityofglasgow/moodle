@local @local_xp @javascript
Feature: Points can be awarded using drops
  In order to award points from varied locations
  As a teacher
  I can use drops

  Background:
    Given the following "courses" exist:
      | fullname  | shortname | enablecompletion |
      | Course 1  | c1        | 1 |
    And the following "users" exist:
      | username | firstname | lastname  | email           |
      | s1       | Student   | One       | s1@example.com  |
      | s2       | Student   | Two       | s2@example.com  |
      | t1       | Teacher   | One       | t1@example.com  |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | s1       | c1     | student        |
      | s2       | c1     | student        |
      | t1       | c1     | editingteacher |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |
    And the following "activities" exist:
      | activity | course | name   | intro              | content          |
      | page     | c1     | Page 1 | Page 1 description | Page 1 content   |
      | page     | c1     | Page 2 | Page 2 description | Page 2 content   |
    And I am on the "c1" "block_xp > rules" page logged in as "t1"
    And I delete all XP event rules

  Scenario: Students can earn points when visiting a page containing one
    Given I am on the "c1" "block_xp > drops" page logged in as "t1"
    And I follow "Add a drop"
    And I set the field "Name" in the "Add a drop" "dialogue" to "Drop 1"
    And I set the field "Points" in the "Add a drop" "dialogue" to "66"
    And I press "Save changes"
    And I should see "[xpdrop" in the "Drop 1" "dialogue"
    And I click on "Close" "button" in the "Drop 1" "dialogue"
    And I am on the "Page 1" "activity editing" page logged in as "t1"
    And I set the field "Page content" to the XP drop snippet "Drop 1"
    And I press "Save and display"
    And I should see "Drop: Drop 1"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |
    When I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I should not see "Drop: Drop 1"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 66    |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I reload the page

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 66    |
