@local @local_xp @javascript
Feature: Popup notifications celebrate students for attaining a level
  In order to celebrate my students
  As a teacher
  I can configure the badge on the levels page

  Background:
    Given the following "courses" exist:
      | fullname  | shortname |
      | Course 1  | c1        |
    And the following "users" exist:
      | username | firstname | lastname |
      | s1       | Student   | One      |
      | t1       | Teacher   | One      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | s1       | c1     | student        |
      | t1       | c1     | editingteacher |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |

  Scenario: A student is celebrated for attaining a level
    Given I am on the "c1" "block_xp > levels" page logged in as "t1"
    And I click on "Expand" "link" in the "Level #2" "fieldset"
    And I set the field "Start" in the "Level #2" "fieldset" to "100"
    And I set the field "Name" in the "Level #2" "fieldset" to "Champion!"
    And I set the field "Popup notification message" in the "Level #2" "fieldset" to "Wow, you're amazing!"
    And I click on "Expand" "link" in the "Level #3" "fieldset"
    And I set the field "Start" in the "Level #3" "fieldset" to "200"
    And I set the field "Name" in the "Level #3" "fieldset" to "Godlike!"
    And I set the field "Popup notification message" in the "Level #3" "fieldset" to "G.G.G.Godlike!"
    And I press "Save changes"
    And I follow "Report" in the XP nav
    And I follow "Award points" for "Student One" in the XP report
    And I set the field "Increase by" to "200"
    And I press "Confirm"

    When I am on the "c1" "course" page logged in as "s1"
    And I wait until "Congratulations!" "dialogue" exists
    And I wait "4" seconds
    Then I should see "Champion!" in the "Congratulations!" "dialogue"
    And I should see "Wow, you're amazing!" in the "Congratulations!" "dialogue"
    And I press "Cool, thanks"
    And I wait until "Congratulations!" "dialogue" exists
    And I wait "4" seconds
    And I should see "Godlike!" in the "Congratulations!" "dialogue"
    And I should see "G.G.G.Godlike!" in the "Congratulations!" "dialogue"
