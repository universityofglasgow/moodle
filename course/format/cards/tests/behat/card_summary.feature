@format @format_cards @format_cards-card_summary
Feature: Cards may have visible summaries
  To manage my course
  As a teacher
  I can configure summary visibility in cards

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format  | numsections |
      | Course 1 | C1        | cards   | 1           |
    And the following "user preferences" exist:
      | user  | preference  | value     |
      | admin | htmleditor  | textarea  |

  @javascript
  Scenario Outline: Section summary visibility can be configured
    Given the following config values are set as admin:
      | showsummary | <adminvalue> | format_cards |
    And I am on the "Course 1" "course editing" page logged in as "admin"
    And I expand all fieldsets
    And I set the field "Section summary" to "<coursevalue>"
    And I click on "Save and display" "button"
    And I am on "Course 1" course homepage with editing mode on
    And I edit the section "1"
    And I set the field "Section summary" to "<sectionvalue>"
    And I set the section description to "This section has a summary!"
    And I click on "Save changes" "button"
    When I am on "Course 1" course homepage with editing mode off
    Then I <shouldornot> see "This section has a summary!" in the "1" "format_cards > card"

    Examples:
      | adminvalue | coursevalue      | sectionvalue     | shouldornot |
      | 1          | Default (Shown)  | Default (Shown)  | should      |
      | 2          | Default (Hidden) | Default (Hidden) | should not  |
      | 2          | Shown            | Default (Shown)  | should      |
      | 1          | Hidden           | Default (Hidden) | should not  |
      | 2          | Hidden           | Shown            | should      |
      | 1          | Shown            | Hidden           | should not  |
