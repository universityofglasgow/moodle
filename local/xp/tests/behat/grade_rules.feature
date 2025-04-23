@local @local_xp @javascript
Feature: Points can be awarded for receiving grades
  In order to award points from grades
  As a teacher
  I need to configure the grade rules

  Background:
    Given the following "courses" exist:
      | fullname  | shortname | enablecompletion |
      | Course 1  | c1        | 1 |
    And the following "users" exist:
      | username | firstname | lastname |
      | s1       | Student   | One      |
      | s2       | Student   | Two      |
      | t1       | Teacher   | One      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | s1       | c1     | student        |
      | s2       | c1     | student        |
      | t1       | c1     | editingteacher |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |
    And the following "activities" exist:
      | activity | course | section | name     | intro                |
      | assign   | c1     | 1       | Assign 1 | Assign 1 description |
      | assign   | c1     | 1       | Assign 2 | Assign 2 description |
    And the following "grade items" exist:
      | course | itemname | maxgrade |
      | c1     | Item 1   | 100      |

  Scenario: Points can be awarded for grade per type
    Given I am on the "c1" "block_xp > graderules" page logged in as "t1"
    And I follow "Add a condition"
    And I click on "Grade type" "button" in the "Pick a condition type" "dialogue"
    And I set the field "gradefilters[1][rule][rules][1][value]" to "Assignment"
    And I press "Save changes"
    And I follow "Report" in the XP nav
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |
    And I am on the "c1" "grades > Grader report > View" page logged in as "t1"
    And I turn editing mode on
    And I give the grade "10" to the user "Student One" for the grade item "Assign 1"
    And I give the grade "20" to the user "Student One" for the grade item "Item 1"
    When I press "Save changes"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 10    |

  Scenario: Points can be awarded for grade per item
    Given I am on the "c1" "block_xp > graderules" page logged in as "t1"
    And I follow "Add a condition"
    And I click on "Specific grade item" "button" in the "Pick a condition type" "dialogue"
    And I press "Click to select a grade item"
    And I click on "//tr[td/div[@class='resource-name' and normalize-space(text())='Assign 1']]//button" "xpath"
    And I press "Save changes"
    And I follow "Report" in the XP nav
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |
    And I am on the "c1" "grades > Grader report > View" page logged in as "t1"
    And I turn editing mode on
    And I give the grade "6" to the user "Student One" for the grade item "Assign 1"
    And I give the grade "24" to the user "Student One" for the grade item "Assign 2"
    When I press "Save changes"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 6     |

  Scenario: Points can only increase beyond best score
    Given I am on the "c1" "block_xp > graderules" page logged in as "t1"
    And I follow "Add a condition"
    And I click on "Grade type" "button" in the "Pick a condition type" "dialogue"
    And I press "Save changes"
    And I follow "Report" in the XP nav
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |
    And I am on the "c1" "grades > Grader report > View" page logged in as "t1"
    And I turn editing mode on
    And I give the grade "10" to the user "Student One" for the grade item "Assign 1"
    And I give the grade "50" to the user "Student One" for the grade item "Assign 2"
    And I give the grade "70" to the user "Student One" for the grade item "Item 1"
    And I press "Save changes"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 130   |
    And I am on the "c1" "grades > Grader report > View" page logged in as "t1"
    And I turn editing mode on
    When I give the grade "0" to the user "Student One" for the grade item "Assign 1"
    And I give the grade "5" to the user "Student One" for the grade item "Assign 2"
    And I give the grade "20" to the user "Student One" for the grade item "Item 1"
    And I press "Save changes"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 130   |
    And I am on the "c1" "grades > Grader report > View" page logged in as "t1"
    And I turn editing mode on
    And I give the grade "8" to the user "Student One" for the grade item "Assign 1"
    And I give the grade "48" to the user "Student One" for the grade item "Assign 2"
    And I give the grade "68" to the user "Student One" for the grade item "Item 1"
    And I press "Save changes"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 130   |
    And I am on the "c1" "grades > Grader report > View" page logged in as "t1"
    And I turn editing mode on
    And I give the grade "11" to the user "Student One" for the grade item "Assign 1"
    And I give the grade "52" to the user "Student One" for the grade item "Assign 2"
    And I give the grade "73" to the user "Student One" for the grade item "Item 1"
    And I press "Save changes"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 136   |
    And I am on the "c1" "grades > Grader report > View" page logged in as "t1"
    And I turn editing mode on
    And I give the grade "100" to the user "Student One" for the grade item "Assign 1"
    And I give the grade "100" to the user "Student One" for the grade item "Assign 2"
    And I give the grade "100" to the user "Student One" for the grade item "Item 1"
    And I press "Save changes"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 300   |
