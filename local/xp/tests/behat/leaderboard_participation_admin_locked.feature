@local @local_xp @javascript
Feature: Admins can lock the leaderboard participation setting

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

  Scenario: Managers cannot change the participation mode when locked
    Given the following config values are set as admin:
      | ladderparticipation        | 0 | local_xp |
      | ladderparticipation_locked | 1 | local_xp |
    When I am on the "c1" "block_xp > leaderboard" page logged in as "s1"
    Then the table row "Student One" should not contain ".action-menu" "css_element"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And I open the action menu for "Student One" in the XP report
    And the table row "Student One" should not contain "Leaderboard" "link"
    And I follow "Leaderboard" in the XP nav
    And I follow "Page settings" in the XP page menu
    And "Participation" "field" should not exist
    And the following config values are set as admin:
      | ladderparticipation_locked | 0 | local_xp |
    And I reload the page
    And I follow "Page settings" in the XP page menu
    And "Participation" "field" should exist

  Scenario: Managers can reset the state of everyone in opt-out mode
    Given the following config values are set as admin:
      | ladderparticipation        | 1 | local_xp |
      | ladderparticipation_locked | 1 | local_xp |
    And I am on the "c1" "block_xp > leaderboard" page logged in as "s1"
    And I follow "Leave" for "Student One" in the XP leaderboard
    And I click on "Leave" "button" in the "Leave leaderboard" "dialogue"
    And I am on the "c1" "block_xp > leaderboard" page logged in as "s2"
    And I follow "Leave" for "Student Two" in the XP leaderboard
    And I click on "Leave" "button" in the "Leave leaderboard" "dialogue"
    And I am on the "c1" "block_xp > leaderboard" page logged in as "t1"
    And I should not see "Student One"
    And I should not see "Student Two"
    And I follow "Page settings" in the XP page menu
    When I click on "ladderparticipationreset" "checkbox"
    And I press "Save changes"
    Then I should see "Student One"
    And I should see "Student Two"

  Scenario: Managers can reset the state of everyone in opt-in mode
    Given the following config values are set as admin:
      | ladderparticipation        | 2 | local_xp |
      | ladderparticipation_locked | 1 | local_xp |
    And I am on the "c1" "block_xp > leaderboard" page logged in as "s1"
    And I click on "Join" "button"
    And I click on "Join" "button" in the "Join leaderboard" "dialogue"
    And I am on the "c1" "block_xp > leaderboard" page logged in as "s2"
    And I click on "Join" "button"
    And I click on "Join" "button" in the "Join leaderboard" "dialogue"
    And I am on the "c1" "block_xp > leaderboard" page logged in as "t1"
    And I should see "Student One"
    And I should see "Student Two"
    And I follow "Page settings" in the XP page menu
    When I click on "ladderparticipationreset" "checkbox"
    And I press "Save changes"
    Then I should not see "Student One"
    And I should not see "Student Two"

  Scenario: Managers can change the state in opt-out locked mode
    Given the following config values are set as admin:
      | ladderparticipation        | 1 | local_xp |
      | ladderparticipation_locked | 1 | local_xp |
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the table row "Student One" should not contain "Not ranked" "text"
    When I follow "Leaderboard" for "Student One" in the XP report
    And I set the field "Leaderboard participation" to "Not participating"
    And I press "Save changes"
    Then the table row "Student One" should contain "Not ranked" "text"

  Scenario: Managers can change the state in opt-in locked mode
    Given the following config values are set as admin:
      | ladderparticipation        | 2 | local_xp |
      | ladderparticipation_locked | 1 | local_xp |
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the table row "Student One" should not contain "Ranked" "text"
    When I follow "Leaderboard" for "Student One" in the XP report
    And I set the field "Leaderboard participation" to "Participating"
    And I press "Save changes"
    Then the table row "Student One" should contain "Ranked" "text"

  Scenario: Managers cannot change the lock time in opt-out locked mode
    Given the following config values are set as admin:
      | ladderparticipation        | 1 | local_xp |
      | ladderparticipation_locked | 1 | local_xp |
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    When I follow "Leaderboard" for "Student One" in the XP report
    Then I should not see "Lock participation until"

  Scenario: Managers cannot change the lock time in opt-in locked mode
    Given the following config values are set as admin:
      | ladderparticipation        | 2 | local_xp |
      | ladderparticipation_locked | 1 | local_xp |
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    When I follow "Leaderboard" for "Student One" in the XP report
    Then I should not see "Lock participation until"

  Scenario: Managers can reset the lock time in opt-out locked mode
    Given the following config values are set as admin:
      | ladderparticipation        | 1 | local_xp |
      | ladderparticipation_locked | 1 | local_xp |
    And I am on the "c1" "block_xp > leaderboard" page logged in as "s1"
    And I follow "Leave" for "Student One" in the XP leaderboard
    And I click on "Leave" "button" in the "Leave leaderboard" "dialogue"
    And I click on "Join" "button"
    And I click on "Join" "button" in the "Join leaderboard" "dialogue"
    And I follow "Leave" for "Student One" in the XP leaderboard
    And I should see "You cannot leave the leaderboard until"
    And the "Leave" "button" should be disabled
    When I am on the "c1" "block_xp > report" page logged in as "t1"
    And I follow "Leaderboard" for "Student One" in the XP report
    And I should not see "Lock participation until"
    And the field "ladderparticipation" matches value "Participating"
    And I press "Save changes"
    Then I am on the "c1" "block_xp > leaderboard" page logged in as "s1"
    And I follow "Leave" for "Student One" in the XP leaderboard
    And I should not see "You cannot leave the leaderboard until"
    And I click on "Leave" "button" in the "Leave leaderboard" "dialogue"

  Scenario: Managers can reset the lock time in opt-in locked mode
    Given the following config values are set as admin:
      | ladderparticipation        | 2 | local_xp |
      | ladderparticipation_locked | 1 | local_xp |
    And I am on the "c1" "block_xp > leaderboard" page logged in as "s1"
    And I click on "Join" "button"
    And I click on "Join" "button" in the "Join leaderboard" "dialogue"
    And I follow "Leave" for "Student One" in the XP leaderboard
    And I should see "You cannot leave the leaderboard until"
    And the "Leave" "button" should be disabled
    When I am on the "c1" "block_xp > report" page logged in as "t1"
    And I follow "Leaderboard" for "Student One" in the XP report
    And I should not see "Lock participation until"
    And the field "ladderparticipation" matches value "Participating"
    And I press "Save changes"
    Then I am on the "c1" "block_xp > leaderboard" page logged in as "s1"
    And I follow "Leave" for "Student One" in the XP leaderboard
    And I should not see "You cannot leave the leaderboard until"
    And I click on "Leave" "button" in the "Leave leaderboard" "dialogue"

