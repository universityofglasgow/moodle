@local @local_xp @javascript @_file_upload
Feature: Points can be imported by CSV
  In order to award points from CSV
  As a teacher
  I can import a CSV file

  Background:
    Given the following "courses" exist:
      | fullname  | shortname | enablecompletion |
      | Course 1  | c1        | 1 |
    And the following "users" exist:
      | username | firstname | lastname  | email           |
      | s1       | Student   | One       | s1@example.com  |
      | s2       | Student   | Two       | s2@example.com  |
      | s3       | Student   | Three     | s3@example.com  |
      | s4       | Student   | Four      | s4@example.com  |
      | s5       | Student   | Five      | s5@example.com  |
      | s6       | Student   | Six       | s6@example.com  |
      | s7       | Student   | Seven     | s7@example.com  |
      | s8       | Student   | Eight     | s8@example.com  |
      | s9       | Student   | Nine      | s9@example.com  |
      | s10      | Student   | Ten       | s10@example.com |
      | s11      | Student   | Eleven    | s11@example.com |
      | s12      | Student   | Twelve    | s12@example.com |
      | s13      | Student   | Thirteen  | s13@example.com |
      | s14      | Student   | Fourteen  | s14@example.com |
      | s15      | Student   | Fifteen   | s15@example.com |
      | s16      | Student   | Sixteen   | s16@example.com |
      | s17      | Student   | Seventeen | s17@example.com |
      | s18      | Student   | Eighteen  | s18@example.com |
      | s19      | Student   | Nineteen  | s19@example.com |
      | s20      | Student   | Twenty    | s20@example.com |
      | s21      | Student   | TwentyOne | s21@example.com |
      | t1       | Teacher   | One       | t1@example.com  |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | s1       | c1     | student        |
      | s2       | c1     | student        |
      | s3       | c1     | student        |
      | s4       | c1     | student        |
      | s5       | c1     | student        |
      | s6       | c1     | student        |
      | s7       | c1     | student        |
      | s8       | c1     | student        |
      | s9       | c1     | student        |
      | s10      | c1     | student        |
      | s11      | c1     | student        |
      | s12      | c1     | student        |
      | s13      | c1     | student        |
      | s14      | c1     | student        |
      | s15      | c1     | student        |
      | s16      | c1     | student        |
      | s17      | c1     | student        |
      | s18      | c1     | student        |
      | s19      | c1     | student        |
      | s20      | c1     | student        |
      | s21      | c1     | student        |
      | t1       | c1     | editingteacher |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |

  Scenario: Points can be set to from a CSV
    Given I am on the "c1" "block_xp > report" page logged in as "t1"
    And I follow "Award points" for "Student Two" in the XP report
    And I set the field "Increase by" to "100"
    And I press "Confirm"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student Two | 100   |
    And I am on the "c1" "block_xp > import" page logged in as "t1"
    And I upload "local/xp/tests/fixtures/points-import.csv" file to "CSV file" filemanager
    And I set the field "Points import action" to "Set as total"
    And I press "Preview"
    And I press "Preview more"
    And I press "Preview more"
    And the following should exist in the "table" table:
      | Line | Full name               |
      | 2    | Student One             |
      | 22   | Student TwentyOne       |
      | 23   | Unable to identify user |
    When I press "Confirm"
    And I should see "Successfully imported 21 entries out of a total of 23"
    And I press "Continue"
    Then the following should exist in the "table" table:
      | First name        | Total |
      | Student TwentyOne | 21    |
      | Student Twenty    | 20    |
      | Student Nineteen  | 19    |
      | Student Eighteen  | 18    |
      | Student Seventeen | 17    |
      | Student Sixteen   | 16    |
      | Student Fifteen   | 15    |
      | Student Fourteen  | 14    |
      | Student Thirteen  | 13    |
      | Student Twelve    | 12    |
      | Student Eleven    | 11    |
      | Student Ten       | 10    |
      | Student Nine      | 9     |
      | Student Eight     | 8     |
      | Student Seven     | 7     |
      | Student Six       | 6     |
      | Student Five      | 5     |
      | Student Four      | 4     |
      | Student Three     | 3     |
      | Student Two       | 2     |
    And I follow "Next page"
    And the following should exist in the "table" table:
      | First name        | Total |
      | Student One       | 1     |

  Scenario: Points can be increased from a CSV
    Given I am on the "c1" "block_xp > report" page logged in as "t1"
    And I follow "Award points" for "Student One" in the XP report
    And I set the field "Increase by" to "100"
    And I press "Confirm"
    And I follow "Award points" for "Student Two" in the XP report
    And I set the field "Increase by" to "100"
    And I press "Confirm"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 100   |
      | Student Two | 100   |
    And I am on the "c1" "block_xp > import" page logged in as "t1"
    And I upload "local/xp/tests/fixtures/points-import.csv" file to "CSV file" filemanager
    And I set the field "Points import action" to "Increase"
    And I press "Preview"
    When I press "Confirm"
    And I press "Continue"
    Then the following should exist in the "table" table:
      | First name        | Total |
      | Student One       | 101   |
      | Student Two       | 102   |
      | Student TwentyOne | 21    |
      | Student Four      | 4     |

  Scenario: Points can be increased with a message
    Given I am on the "c1" "block_xp > import" page logged in as "t1"
    And I upload "local/xp/tests/fixtures/points-import.csv" file to "CSV file" filemanager
    And I set the field "Points import action" to "Increase"
    And I set the field "Send award notification" to "1"
    And I press "Preview"
    And I press "Confirm"
    When I log in as "s1"
    And I open the notification popover
    And I follow "You were awarded 1 points"
    Then I should see "Message to s1"
