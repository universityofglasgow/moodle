@local @local_xp
Feature: Participants can opt-out from participating in the leaderboard

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
    And the following "block_xp > config" exist:
      | worldcontext | name                | value |
      | c1           | ladderparticipation | 1     |
    And the following "block_xp > xp" exist:
      | worldcontext | user | xp  |
      | c1           | s1   | 10  |
      | c1           | s2   | 5   |

  Scenario: User is participating by default
    Given I am on the "c1" "course" page logged in as "s1"
    And I should see "Ranking" in the "Level up!" "block"
    When I click on "Leaderboard" "link" in the "Level up!" "block"
    Then I should not see "Not participating"

  @javascript
  Scenario: User can leave the leaderboard
    Given I am on the "c1" "course" page logged in as "s1"
    And I should see "Ranking" in the "Level up!" "block"
    And I click on "Leaderboard" "link" in the "Level up!" "block"
    When I follow "Leave" for "Student One" in the XP leaderboard
    And I click on "Leave" "button" in the "Leave leaderboard" "dialogue"
    Then I should see "Not participating"
    And I follow "Course"
    And I should not see "Ranking" in the "Level up!" "block"

  @javascript
  Scenario: User cannot immediately join again
    Given I am on the "c1" "block_xp > ladder" page logged in as "s1"
    And I follow "Leave" for "Student One" in the XP leaderboard
    When I click on "Leave" "button" in the "Leave leaderboard" "dialogue"
    Then I should see "Not participating"
    And I click on "Join" "button"
    And I click on "Join" "button" in the "Join leaderboard" "dialogue"
    And I follow "Leave" for "Student One" in the XP leaderboard
    And I should see "You cannot leave the leaderboard until"
    And the "Leave" "button" should be disabled

  @javascript
  Scenario: User can leave again after manager resets the state
    Given I am on the "c1" "block_xp > ladder" page logged in as "s1"
    And I follow "Leave" for "Student One" in the XP leaderboard
    And I click on "Leave" "button" in the "Leave leaderboard" "dialogue"
    And I click on "Join" "button"
    And I click on "Join" "button" in the "Join leaderboard" "dialogue"
    And I follow "Leave" for "Student One" in the XP leaderboard
    And I should see "You cannot leave the leaderboard until"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And I follow "Leaderboard" for "Student One" in the XP report
    And I click on "ladderparticipationlocked[enabled]" "checkbox"
    And "[name='ladderparticipationlocked[enabled]']:checked" "css_element" should exist
    When I press "Save changes"
    And I am on the "c1" "block_xp > ladder" page logged in as "s1"
    And I follow "Leave" for "Student One" in the XP leaderboard
    Then I should not see "You cannot join the leaderboard until"
    And I click on "Leave" "button" in the "Leave leaderboard" "dialogue"
    And I should see "Not participating"

  @javascript
  Scenario: Manager can opt users out and lock their state
    Given I am on the "c1" "block_xp > report" page logged in as "t1"
    And the table row "Student One" should not contain "Not ranked" "text"
    And the table row "Student Two" should not contain "Not ranked" "text"
    And I follow "Leaderboard" for "Student One" in the XP report
    And I set the field "Leaderboard participation" to "Not participating"
    And I click on "ladderparticipationlocked[enabled]" "checkbox"
    And I set the field "ladderparticipationlocked[year]" to "2050"
    And I press "Save changes"
    And I follow "Leaderboard" for "Student Two" in the XP report
    And I set the field "Leaderboard participation" to "Not participating"
    And I press "Save changes"
    And the table row "Student One" should contain "Not ranked" "text"
    And the table row "Student Two" should contain "Not ranked" "text"
    When I am on the "c1" "block_xp > ladder" page logged in as "s1"
    Then I should see "Not participating"
    And "Join" "button" should not exist
    And I am on the "c1" "block_xp > ladder" page logged in as "s2"
    And I should see "Not participating"
    And I click on "Join" "button"
    And I click on "Join" "button" in the "Join leaderboard" "dialogue"
    And the following should exist in the "table" table:
     | Participant        |
     | Student Two        |

  @javascript
  Scenario: Manager can reset everyone's states
    Given the following "users" exist:
      | username | firstname | lastname |
      | s3       | Student   | S3       |
      | s4       | Student   | S4       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | s3       | c1     | student        |
      | s4       | c1     | student        |
    And the following "block_xp > xp" exist:
     | worldcontext | user | xp |
     | c1           | s1   | 1  |
     | c1           | s2   | 2  |
     | c1           | s3   | 3  |
     | c1           | s4   | 4  |
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And I follow "Leaderboard" for "Student One" in the XP report
    And I set the field "Leaderboard participation" to "Not participating"
    And I press "Save changes"
    And I follow "Leaderboard" for "Student Two" in the XP report
    And I set the field "Leaderboard participation" to "Participating"
    And I press "Save changes"
    And I follow "Leaderboard" for "Student S3" in the XP report
    And I set the field "Leaderboard participation" to "Not participating"
    And I click on "ladderparticipationlocked[enabled]" "checkbox"
    And I set the field "ladderparticipationlocked[year]" to "2050"
    And I press "Save changes"
    And I follow "Leaderboard" for "Student S4" in the XP report
    And I set the field "Leaderboard participation" to "Participating"
    And I click on "ladderparticipationlocked[enabled]" "checkbox"
    And I set the field "ladderparticipationlocked[year]" to "2050"
    And I press "Save changes"
    And the table row "Student One" should contain "Not ranked" "text"
    And the table row "Student S3" should contain "Not ranked" "text"

    And I am on the "c1" "block_xp > ladder" page logged in as "s1"
    And I should see "Not participating"
    And "Join" "button" should exist

    And I am on the "c1" "block_xp > ladder" page logged in as "s2"
    And the following should exist in the "table" table:
     | Participant        |
     | Student Two        |
     | Student S4         |
    And the table row "Student Two" should contain ".action-menu" "css_element"

    And I am on the "c1" "block_xp > ladder" page logged in as "s3"
    And I should see "Not participating"
    And "Join" "button" should not exist

    And I am on the "c1" "block_xp > ladder" page logged in as "s4"
    And the following should exist in the "table" table:
     | Participant        |
     | Student Two        |
     | Student S4         |
    And the table row "Student S4" should not contain ".action-menu" "css_element"

    And I am on the "c1" "block_xp > ladder" page logged in as "t1"
    And I follow "Page settings" in the XP page menu
    And I press "Save changes"
    And the following should exist in the "table" table:
     | Participant        |
     | Student Two        |
     | Student S4         |
    And I follow "Page settings" in the XP page menu
    And I click on "ladderparticipationreset" "checkbox"
    When I press "Save changes"
    Then the following should exist in the "table" table:
     | Participant        |
     | Student One        |
     | Student Two        |
     | Student S3         |
     | Student S4         |

    And I follow "Report" in the XP nav
    And the table row "Student One" should not contain "Not ranked" "text"
    And the table row "Student Two" should not contain "Not ranked" "text"
    And the table row "Student S3" should not contain "Not ranked" "text"
    And the table row "Student S4" should not contain "Not ranked" "text"

    And I am on the "c1" "block_xp > ladder" page logged in as "s1"
    And the following should exist in the "table" table:
     | Participant        |
     | Student One        |
     | Student Two        |
     | Student S3         |
     | Student S4         |
    And I am on the "c1" "block_xp > ladder" page logged in as "s2"
    And the following should exist in the "table" table:
     | Participant        |
     | Student One        |
     | Student Two        |
     | Student S3         |
     | Student S4         |
    And I am on the "c1" "block_xp > ladder" page logged in as "s3"
    And the following should exist in the "table" table:
     | Participant        |
     | Student One        |
     | Student Two        |
     | Student S3         |
     | Student S4         |
    And I am on the "c1" "block_xp > ladder" page logged in as "s4"
    And the following should exist in the "table" table:
     | Participant        |
     | Student One        |
     | Student Two        |
     | Student S3         |
     | Student S4         |
