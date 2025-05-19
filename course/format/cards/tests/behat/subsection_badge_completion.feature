@format @format_cards @format_cards-badge_completion
Feature: Subsection cards have a completion progress badge
  To view my completion
  As a user
  I can see my completion progress per subsection

  Background:
    Given I enable "subsection" "mod" plugin
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format  | numsections | enablecompletion |
      | Course 1 | C1        | cards   | 1           | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity   | course | section | idnumber | name        | intro            | completion | completionview |
      | subsection | C1     | 1       | sub1     | Subsection1 | Subsection one   | 0          | 0              |
      | subsection | C1     | 1       | sub2     | Subsection2 | Subsection two   | 0          | 0              |
      | subsection | C1     | 1       | sub3     | Subsection3 | Subsection three | 0          | 0              |
      | page       | C1     | 2       | page-1   | Page 1      | First page       | 2          | 1              |
      | page       | C1     | 2       | page-2   | Page 2      | Second page      | 2          | 1              |
      | page       | C1     | 2       | page-3   | Page 3      | Third page       | 2          | 1              |
      | page       | C1     | 3       | page-4   | Page 4      | Fourth page      | 2          | 1              |
      | page       | C1     | 4       | page-5   | Page 5      | Fifth page       | 2          | 1              |
    And the following config values are set as admin:
      | subsectionsascards | 1 | format_cards |

  @moodle_405_and_after
  Scenario Outline: Section progress visibility can be toggled on or off at the admin and course level
    Given the following config values are set as admin:
      | showprogress | <showadmin> | format_cards |
    And I am on the "Course 1" "course editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I set the field "Section progress" to "<showcourse>"
    And I click on "Save and display" "button"
    When I am on the "Course 1 > Section 1" "format_cards > section" page logged in as "student1"
    Then ".section-completion" "css_element" <should or not> exist in the "Subsection1" "format_cards > card"

    Examples:
      | showadmin | showcourse       | should or not |
      | 1         | Default (Shown)  | should        |
      | 1         | Shown            | should        |
      | 1         | Hidden           | should not    |
      | 2         | Default (Hidden) | should not    |
      | 2         | Shown            | should        |
      | 2         | Hidden           | should not    |

  @moodle_405_and_after
  Scenario Outline: I can view section progress configured as <admindisplay>/<coursedisplay>
    Given the following config values are set as admin:
      | showprogress   | 1              | format_cards |
      | progressformat | <admindisplay> | format_cards |
    And I am on the "Course 1" "course editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I set the field "Display progress as" to "<coursedisplay>"
    And I click on "Save and display" "button"
    When I am on the "Page 1" "page activity" page logged in as "student1"
    And I am on the "Page 4" "page activity" page logged in as "student1"
    When I am on the "Course 1 > Section 1" "format_cards > section" page
    Then I should see "<section1>" in the "Subsection1" "format_cards > card"
    And I should see "Completed" in the "Subsection2" "format_cards > card"
    And ".section-completion .fa.fa-check" "css_element" should exist in the "Subsection2" "format_cards > card"
    And I should see "<section3>" in the "Subsection3" "format_cards > card"

    Examples:
      | admindisplay | coursedisplay              | section1 | section3 |
      | 1            | Default (A count of items) | 1/3      | 0/1      |
      | 1            | A percentage               | 33%      | 0%       |
      | 1            | A count of items           | 1/3      | 0/1      |
      | 2            | Default (A percentage)     | 33%      | 0%       |
      | 2            | A percentage               | 33%      | 0%       |
      | 2            | A count of items           | 1/3      | 0/1      |

  @moodle_405_and_after
  Scenario: Section progress should include progress items from within subsections as well
    Given the following "activities" exist:
      | activity | course | section | idnumber | name        | intro      | completion | completionview |
      | page     | C1     | 1       | page-6   | Page 6      | Sixth page | 2          | 1              |
    And the following config values are set as admin:
      | showprogress   | 1 | format_cards |
      | progressformat | 1 | format_cards |
    When I am on the "Page 1" "page activity" page logged in as "student1"
    And I am on the "Course 1" "course" page logged in as "student1"
    Then I should see "1/6" in the "1" "format_cards > card"
