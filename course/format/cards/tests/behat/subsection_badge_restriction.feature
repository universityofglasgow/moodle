@format @format_cards @format_cards-badge_restriction
Feature: Subsection cards have a section restriction badge
  To navigate my courses
  As a student
  I can see a badge outlining any subsection restrictions

  Background:
    Given I enable "subsection" "mod" plugin
    And the following config values are set as admin:
      | subsectionsascards | 1 | format_cards |
    And the following "courses" exist:
      | fullname | shortname | format  | numsections |
      | Course 1 | C1        | cards   | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity   | course | section | idnumber | name        |
      | subsection | C1     | 1       | sub1     | Subsection1 |
      | subsection | C1     | 1       | sub2     | Subsection2 |

  @javascript @moodle_405_and_after
  Scenario: Restriction badge is visible
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    When I edit the section "2"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I set the field "day" to "##tomorrow##%-d##"
    And I click on "Save changes" "button"
    When I am on the "Course 1 > Section 1" "format_cards > section" page logged in as "student1"
    Then ".section-availability" "css_element" should exist in the "Subsection1" "format_cards > card"
    And ".section-availability" "css_element" should not exist in the "Subsection2" "format_cards > card"
