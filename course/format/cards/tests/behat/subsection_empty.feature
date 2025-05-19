@format @format_cards @format_cards_subsection
Feature: Empty subsection support
  In order to use courses using the cards format
  As a user
  Empty subsections don't emit a warning

  Background:
    Given I enable "subsection" "mod" plugin
    And the following config values are set as admin:
      | subsectionsascards | 1 | format_cards |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | category | numsections | initsections | enablecompletion |
      | Course 1 | C1        | cards  | 0        | 3           | 1            | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity   | name                | course | idnumber | section | visible |
      | subsection | Subsection1         | C1     | sub1     | 1       | 1       |

  @javascript @moodle_405_and_after
  Scenario: No warnings are emitted when viewing a course with an empty subsection
    Given I log in as "teacher1"
    When I am on "Course 1" course homepage
    Then I should not see "Warning" in the "region-main" "region"
