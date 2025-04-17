@local @local_xp @javascript
Feature: Completion rules can be edited
  In order to tailor my students experience
  As a teacher
  I can change rules after the fact

  Background:
    Given the following "courses" exist:
      | fullname  | shortname | enablecompletion |
      | Course 1  | c1        | 1 |
    And the following "users" exist:
      | username | firstname | lastname |
      | t1       | Teacher   | One      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | t1       | c1     | editingteacher |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |
    And the following "activities" exist:
      | activity | course | section | name   | intro              | content          | completion | completionview |
      | page     | c1     | 1       | Page 1 | Page 1 description | Page 1 content   | 1          | 1              |

  Scenario: Points can be edited after the fact
    And I am on the "c1" "block_xp > completionrules" page logged in as "t1"
    And I press "Add"
    And I click on "Any activity" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "20"
    And I press tab
    And I press "Save"
    And I follow "Report" in the XP nav
    And I follow "Points" in the XP nav
    And I follow "Completion" in the XP secondary nav
    And I should see "+20"
    And I click on "options" "button" in the "region-main" "region"
    And I click on "Edit" "link" in the "#region-main .dropdown .dropdown-menu" "css_element"
    When I set the field "Points to award" to "30"
    And I press "Save changes"
    Then I should see "+30"
    And I reload the page
    And I should see "+30"

  Scenario: Rules can be deleted
    And I am on the "c1" "block_xp > completionrules" page logged in as "t1"
    And I press "Add"
    And I click on "Any activity" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "20"
    And I press tab
    And I press "Save"
    And I follow "Report" in the XP nav
    And I follow "Points" in the XP nav
    And I follow "Completion" in the XP secondary nav
    And I should see "+20"
    And I click on "options" "button" in the "region-main" "region"
    And I click on "Delete" "link" in the "#region-main .dropdown .dropdown-menu" "css_element"
    When I click on "Delete" "button" in the "Delete condition" "dialogue"
    Then I should not see "+20"
    And I reload the page
    And I should not see "+20"
