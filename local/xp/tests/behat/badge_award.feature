@local @local_xp @javascript @_file_upload
Feature: Badges can be awarded for reaching a level
  In order to award a badge when a student reaches a level
  As a teacher
  I can configure the badge on the levels page

  Background:
    Given the following "courses" exist:
      | fullname  | shortname |
      | Course 1  | c1        |
    And the following "users" exist:
      | username | firstname | lastname |
      | s1       | Student   | One      |
      | s2       | Student   | Two      |
      | t1       | Teacher   | One      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | s1       | c1     | student        |
      | s2       | c1     | student        |
      | t1       | c1     | editingteacher |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |
    And the following "block_xp > config" exist:
      | worldcontext | name               | value |
      | c1           | enablelevelupnotif | 0     |
    And the following "core_badges > Badge" exists:
      | name           | Badge for Level 2            |
      | status         | active                       |
      | course         | c1                           |
      | type           | 2                            |
      | description    | Test badge 2 description     |
      | image          | badges/tests/behat/badge.png |
      | imagecaption   | Test caption image           |
    And the following "core_badges > Badge" exists:
      | name           | Badge for Level 5            |
      | status         | active                       |
      | course         | c1                           |
      | type           | 2                            |
      | description    | Test badge 5 description     |
      | image          | badges/tests/behat/badge.png |
      | imagecaption   | Test caption image           |
    And the following "core_badges > Badge" exists:
      | name           | Badge from system            |
      | status         | active                       |
      | type           | 1                            |
      | description    | Test from sys  description   |
      | image          | badges/tests/behat/badge.png |
      | imagecaption   | Test caption image           |
    And the following "core_badges > Criteria" exists:
      | badge          | Badge for Level 2            |
      | role           | editingteacher               |
    And the following "core_badges > Criteria" exists:
      | badge          | Badge for Level 5            |
      | role           | editingteacher               |
    And the following "core_badges > Criteria" exists:
      | badge          | Badge from system            |
      | role           | manager                      |

  Scenario: Award course badge for reaching a level
    Given I am on the "c1" "block_xp > levels" page logged in as "t1"
    And I click on "Expand" "link" in the "Level #2" "fieldset"
    And I set the field "Name" in the "Level #2" "fieldset" to "Test 1"
    And I set the field "Badge to award" in the "Level #2" "fieldset" to "Badge for Level 2"
    And I click on "Expand" "link" in the "Level #5" "fieldset"
    And I set the field "Name" in the "Level #5" "fieldset" to "Test 2"
    And I set the field "Badge to award" in the "Level #5" "fieldset" to "Badge for Level 5"
    And I press "Save changes"
    And I follow "Report" in the XP nav
    And I follow "Award points" for "Student One" in XP report
    And I set the field "Increase by" to "200"
    And I press "Confirm"
    And I follow "Award points" for "Student Two" in XP report
    And I set the field "Increase by" to "10000"
    And I press "Confirm"

    When I am on the "s1" "user > profile" page logged in as "s1"
    And I click on "Course 1" "link" in the "region-main" "region"
    Then I should see "Badge for Level 2"
    And I should not see "Badge for Level 5"

    And I am on the "s2" "user > profile" page logged in as "s2"
    And I click on "Course 1" "link" in the "region-main" "region"
    And I should see "Badge for Level 2"
    And I should see "Badge for Level 5"

  Scenario: Award system badge for reaching a level
    Given I am on the "c1" "block_xp > levels" page logged in as "admin"
    And I click on "Expand" "link" in the "Level #2" "fieldset"
    And I set the field "Name" in the "Level #2" "fieldset" to "Test 1"
    And I set the field "Badge to award" in the "Level #2" "fieldset" to "Badge from system"
    And I press "Save changes"
    And I follow "Report" in the XP nav
    And I follow "Award points" for "Student One" in XP report
    And I set the field "Increase by" to "200"
    And I press "Confirm"
    When I am on the "s1" "user > profile" page logged in as "s1"
    Then I should see "Badge from system"
    And I should not see "Badge for Level 5"
