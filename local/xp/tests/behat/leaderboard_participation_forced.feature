@local @local_xp
Feature: Participants can be forced to participate in the leaderboard

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
      | xp        | Course       | c1         |
    And the following "block_xp > config" exist:
      | worldcontext | name                | value |
      | c1           | ladderparticipation | 0     |
    And the following "block_xp > xp" exist:
      | worldcontext | user | xp  |
      | c1           | s1   | 10  |
      | c1           | s2   | 5   |

  Scenario: Student cannot see menu to opt-out
    Given I am on the "c1" "course" page logged in as "s1"
    And I should see "Ranking" in the "Level up!" "block"
    When I click on "Leaderboard" "link" in the "Level up!" "block"
    Then the following should exist in the "table" table:
     | Participant        | Rank |
     | Student One        | 1    |
     | Student Two        | 2    |
    And the table row "Student One" should not contain ".action-menu" "css_element"
    And the table row "Student Two" should not contain ".action-menu" "css_element"

  @javascript
  Scenario: Managers cannot see menu to change participation
    Given I am on the "c1" "block_xp > report" page logged in as "t1"
    When I open the action menu for "Student One" in the XP report
    Then the table row "Student One" should not contain "Leaderboard" "link"

