@local @local_xp @javascript
Feature: Custom reports can be used to get XP data

  Background:
    Given the following "courses" exist:
      | fullname  | shortname |
      | Course 1  | c1        |
    And the following "users" exist:
      | username | firstname | lastname |
      | t1       | Teacher   | One      |
      | s1       | Student   | One      |
      | s2       | Student   | Two      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | t1       | c1     | editingteacher |
      | s1       | c1     | student        |
      | s2       | c1     | student        |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |
    And the following "block_xp > xp" exist:
      | worldcontext | user | xp  |
      | c1           | s1   | 10  |
      | c1           | s2   | 5   |

  Scenario: The default custom report is created as expected
    Given I am logged in as "admin"
    And I navigate to "Reports > Report builder > Custom reports" in site administration
    And I press "New report"
    And I set the field "Name" in the "New report" "dialogue" to "XP report"
    And I set the field "Report source" in the "New report" "dialogue" to "XP participants"
    When I click on "Save" "button" in the "New report" "dialogue"
    Then I should see "Student One"
