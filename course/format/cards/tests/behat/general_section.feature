@format @format_cards @format_cards-general_section
Feature: Configurable section 0 visibility
  To customise my course
  As a teacher
  I can choose where section 0 is displayed

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format  | numsections |
      | Course 1 | C1        | cards   | 4           |
    And the following "activities" exist:
      | activity | course | section | idnumber | name                  | intro       |
      | page     | C1     | 0       | page     | General activity page | First page  |

  Scenario Outline: Configuring section 0 at the admin and course level
    Given the following config values are set as admin:
      | section0 | <adminvalue> | format_cards |
    And I am on the "Course 1" "course editing" page logged in as "admin"
    And I expand all fieldsets
    And I set the field "General section" to "<coursevalue>"
    And I click on "Save and display" "button"
    When I am on the "Course 1 > Section 2" "format_cards > section" page
    Then I <shouldornot> see "General activity page" in the "page" "region"

    Examples:
      | adminvalue | coursevalue                                               | shouldornot |
      | 1          | Default (Only show on the main course page)               | should not  |
      | 1          | Only show on the main course page                         | should not  |
      | 1          | Show on all pages, including individual sections          | should      |
      | 2          | Default (Show on all pages, including individual sections | should      |
      | 2          | Only show on the main course page                         | should not  |
      | 2          | Show on all pages, including individual sections          | should      |
