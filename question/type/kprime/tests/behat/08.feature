@qtype @qtype_kprime @qtype_kprime_8
Feature: Step 8

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | student1 | S1        | Student1 | student1@moodle.com |
      | student2 | S2        | Student2 | student2@moodle.com |
      | teacher  | T1        | teacher  | teacher@moodle.com  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | c1     | student |
      | student2 | c1     | student |
      | teacher  | c1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype  | name                | template     |
      | Default for c1   | kprime | Kprime Question 001 | question_one |
      | Default for c1   | kprime | Kprime Question 002 | question_one |
    And the following "activities" exist:
      | activity | name   | intro           | course | idnumber |
      | quiz     | Quiz 1 | This is a  quiz | c1     | quiz1    |
    And quiz "Quiz 1" contains the following questions:
      | Kprime Question 001 | 1 |
      | Kprime Question 002 | 2 |

  @javascript
  Scenario: Testcase 20

  # Solving the exam as students
  # Student 1 (100% correct)
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=2]" "css_element"
    And I press "Next page"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=2]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving the exam as students
  # Student 1 (50% correct)
    Given I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=1]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Next page"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=1]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Backup Exam as admin
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Backup" in current page administration
    And I click on "input[id='id_setting_root_grade_histories']" "css_element"
    And I press "Next"
    And I press "Next"
    And I set the field "Filename" to "test_backup.mbz"
    And I press "Perform backup"
    Then I should see "The backup file was successfully created."
    And I press "Continue"

  # Testcase 21
  # change correct answers
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question Kprime Question 001" "link" in the "Kprime Question 001" "list_item"
    And I set the following fields to these values:
      | id_weightbutton_1_1 | checked |
      | id_weightbutton_2_1 | checked |
      | id_weightbutton_3_1 | checked |
      | id_weightbutton_4_1 | checked |
    And I press "id_submitbutton"

  # Regrade first exam
    And I follow "Quiz 1"
    And I navigate to "Results" in current page administration
    And I click on "#mod-quiz-report-overview-report-selectall-attempts" "css_element"
    And I press "Regrade selected attempts"
    And I press "Continue"
    And I log out

  # Change first exam Question content
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question Kprime Question 001" "link" in the "Kprime Question 001" "list_item"
    And I set the following fields to these values:
      | id_questiontext | Edited Kprime Questiontext |
    And I press "id_submitbutton"
    And I log out

  # Change quiz title of original quiz
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit settings" in current page administration
    And I set the following fields to these values:
      | id_name | Quiz_original |
    And I press "id_submitbutton"

  # Testcase 22
  # 1st Restore
    When I follow "Quiz_original"
    And I navigate to "Restore" in current page administration
    And I restore "test_backup.mbz" backup into "Course 1" course using this options:
    Then I should see "Course 1"
    And I should see "Quiz_original"
    And I should see "Quiz 1"

  # Check if grades are different
    When I follow "Quiz_original"
    And I navigate to "Results" in current page administration
    Then "tr:contains('student1@moodle.com') .c8:contains('75.00')" "css_element" should exist
    And "tr:contains('student2@moodle.com') .c8:contains('75.00')" "css_element" should exist
    When I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Results" in current page administration
    Then "tr:contains('student1@moodle.com') .c8:contains('100.00')" "css_element" should exist
    And "tr:contains('student2@moodle.com') .c8:contains('50.00')" "css_element" should exist

  # Check if the altered Kprime Question 001 exists twice in the question bank
    When I am on "Course 1" course homepage
    And I follow "Quiz_original"
    And I navigate to "Question bank" in current page administration
    Then "tr:contains('Kprime Question 001') td[class='modifiername']:contains('Admin User')" "css_element" should exist
    And "tr:contains('Kprime Question 001') td[class='modifiername']:contains('T1 teacher')" "css_element" should exist
    And "tr:contains('Kprime Question 002')" "css_element" should exist

  # Change quiz title of restored quiz
    When I am on "Course 1" course homepage
    And I turn editing mode on
    And I open "Quiz 1" actions menu
    And I click on "Edit settings" "link" in the "Quiz 1" activity
    Then I should see "Updating: Quiz"
    And I set the following fields to these values:
      | id_name | Quiz_restored |
    And I press "id_submitbutton2"
    Then I should see "Quiz_restored"
    And I turn editing mode off

  # Testcase 23
  # 2nd Restore
    When I follow "Quiz_original"
    And I navigate to "Restore" in current page administration
    And I restore "test_backup.mbz" backup into "Course 1" course using this options:
    Then I should see "Course 1"
    And I should see "Quiz_original"
    And I should see "Quiz_restored"
    And I should see "Quiz 1"

  # Check if grades are different
    When I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Results" in current page administration
    Then "tr:contains('student1@moodle.com') .c8:contains('100.00')" "css_element" should exist
    And "tr:contains('student2@moodle.com') .c8:contains('50.00')" "css_element" should exist

  # Testcase 24:
    When I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question Kprime Question 001" "link" in the "Kprime Question 001" "list_item"
    And I set the following fields to these values:
      | id_questiontext | Edited Kprime Questiontext |
      | id_option_1     | questiontext 1 edited      |
      | id_option_2     | questiontext 2 edited      |
      | id_option_3     | questiontext 3 edited      |
      | id_option_4     | questiontext 4 edited      |
    And I press "id_submitbutton"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Results" in current page administration
    Then "tr:contains('student1@moodle.com') .c8:contains('100.00')" "css_element" should exist
    And "tr:contains('student2@moodle.com') .c8:contains('50.00')" "css_element" should exist
