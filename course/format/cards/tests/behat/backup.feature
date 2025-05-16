@format @format_cards @format_cards-backup
Feature: Supports Moodle backup and restoring
  To manage my courses
  As a teacher
  I can back up and restore section breaks and card images

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format  | numsections |
      | Course 1 | C1        | cards   | 1           |
    And the following config values are set as admin:
      | enableasyncbackup | 0 |

  @javascript
  Scenario: Section breaks are backed up and restored
    Given the following "format_cards > section break" exists:
      | course  | C1                 |
      | section | 1                  |
      | name    | Test section break |
    And I am logged in as "admin"
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    When I restore "test_backup.mbz" backup into a new course using this options:
    And I am on "Course 1 copy 1" course homepage with editing mode off
    Then I should see "Test section break" in the "li.section-break" "css_element"

  @javascript @_file_upload
  Scenario: Card images are backed up and restored
    Given I am logged in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I edit the section "1"
    And I upload "course/format/cards/tests/fixtures/test_image.png" file to "Image" filemanager
    And I press "Save changes"
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    When I restore "test_backup.mbz" backup into a new course using this options:
    And I am on "Course 1 copy 1" course homepage with editing mode off
    Then "//div[contains(@style, 'test_image.png')]" "xpath_element" should exist in the "1" "format_cards > card"
