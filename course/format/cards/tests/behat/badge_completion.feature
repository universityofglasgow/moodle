@format @format_cards @format_cards-badge_completion
Feature: Cards have a completion progress badge
  To view my completion
  As a user
  I can see my completion progress per section

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format  | numsections | enablecompletion |
      | Course 1 | C1        | cards   | 4           | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | course | section | idnumber | name   | intro       | completion | completionview |
      | page     | C1     | 1       | page-1   | Page 1 | First page  | 2          | 1              |
      | page     | C1     | 1       | page-2   | Page 2 | Second page | 2          | 1              |
      | page     | C1     | 1       | page-3   | Page 3 | Third page  | 2          | 1              |
      | page     | C1     | 2       | page-4   | Page 4 | Fourth page | 2          | 1              |
      | page     | C1     | 3       | page-5   | Page 5 | Fifth page  | 2          | 1              |

  Scenario Outline: Section progress visibility can be toggled on or off at the admin and course level
    Given the following config values are set as admin:
      | showprogress | <showadmin> | format_cards |
    And I am on the "Course 1" "course editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I set the field "Section progress" to "<showcourse>"
    And I click on "Save and display" "button"
    When I am on the "Course 1" "course" page logged in as "student1"
    Then "[data-sectionid=1] .section-completion" "css_element" <shouldornot> exist

    Examples:
      | showadmin | showcourse       | shouldornot |
      | 1         | Default (Shown)  | should      |
      | 1         | Shown            | should      |
      | 1         | Hidden           | should not  |
      | 2         | Default (Hidden) | should not  |
      | 2         | Shown            | should      |
      | 2         | Hidden           | should not  |

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
    When I am on the "Course 1" "course" page
    Then I should see "<section1>" in the "[data-sectionid=1] .section-completion" "css_element"
    And I should see "Completed" in the "[data-sectionid=2] .section-completion" "css_element"
    And "[data-sectionid=2] .section-completion .fa.fa-check" "css_element" should exist
    And I should see "<section3>" in the "[data-sectionid=3] .section-completion" "css_element"

    Examples:
      | admindisplay | coursedisplay              | section1 | section3 |
      | 1            | Default (A count of items) | 1/3      | 0/1      |
      | 1            | A percentage               | 33%      | 0%       |
      | 1            | A count of items           | 1/3      | 0/1      |
      | 2            | Default (A percentage)     | 33%      | 0%       |
      | 2            | A percentage               | 33%      | 0%       |
      | 2            | A count of items           | 1/3      | 0/1      |
