@format @format_cards @format_cards-section_navigation
Feature: Configurable section navigation
  To customise my course
  As a teacher
  I can choose whether to display section navigation buttons

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format  | numsections |
      | Course 1 | C1        | cards   | 4           |

  Scenario Outline: Configuring section navigation at the admin and course level

    Given the following config values are set as admin:
      | sectionnavigation | <adminvalue> | format_cards |
    And I am on the "Course 1" "course editing" page logged in as "admin"
    And I expand all fieldsets
    And I set the field "Section navigation" to "<coursevalue>"
    And I click on "Save and display" "button"
    When I am on the "Course 1 > Section 1" "format_cards > section" page
    Then "sectionnavigation-top" "region" <shouldornottop> exist
    Then "sectionnavigation-bottom" "region" <shouldornotbottom> exist

    Examples:
      | adminvalue | coursevalue           | shouldornottop | shouldornotbottom |
      | 1          | Default (None)        | should not     | should not        |
      | 2          | Default (Top only)    | should         | should not        |
      | 3          | Default (Bottom only) | should not     | should            |
      | 4          | Default (Both)        | should         | should            |
      | 4          | None                  | should not     | should not        |
      | 3          | Top only              | should         | should not        |
      | 2          | Bottom only           | should not     | should            |
      | 1          | Both                  | should         | should            |

  Scenario Outline: Configuring home button visibility at admin and course level
    Given the following config values are set as admin:
      | sectionnavigation     | 2            | format_cards |
      | sectionnavigationhome | <adminvalue> | format_cards |
    And I am on the "Course 1" "course editing" page logged in as "admin"
    And I expand all fieldsets
    And I set the field "Home link" to "<coursevalue>"
    And I click on "Save and display" "button"
    When I am on the "Course 1 > Section 2" "format_cards > section" page
    Then "Main course page" "icon" <shouldornot> exist

    Examples:
      | adminvalue | coursevalue      | shouldornot |
      | 1          | Default (Hidden) | should not  |
      | 2          | Default (Shown)  | should      |
      | 1          | Shown            | should      |
      | 2          | Hidden           | should not  |
