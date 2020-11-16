@qtype @qtype_mtf_step_28_29
Feature: Step 28 and Step 29

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | student1 | S1        | Student1 | student1@moodle.com |
      | student2 | S2        | Student2 | student2@moodle.com |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | c1     | student |
      | student2 | c1     | student |
      | teacher1 | c1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype | name             | template     |
      | Default for c1   | mtf   | MTF-Question-001 | question_one |
      | Default for c1   | mtf   | MTF-Question-002 | question_one |
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And quiz "Quiz 1" contains the following questions:
      | MTF-Question-001 | 1 |
      | MTF-Question-002 | 2 |

  @javascript
  Scenario: TESTCASE 28.
  # Backup exam including MTF questions.
  # The Backup should work and images should also be backuped

  # Preparing the questions
  # Upload images
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I choose "Edit question" action for "MTF-Question-001" in the question bank

  # Preparing the questions
  # Question 1: Add image to question stem
    And I click on "Insert or edit image" "button" in the "#id_generalheader" "css_element"
    And I press "Browse repositories..."
    And I click on "URL downloader" "link" in the ".fp-repo-area" "css_element"
    And I set the field "fileurl" to "http://localhost/moodle-3-8-1+/question/type/mtf/tests/fixtures/testimage1.png"
    And I press "Download"
    And I click on "testimage1.png" "link"
    And I press "Select this file"
    And I set the field "Describe this image for someone who cannot see it" to "testimage1AltDescription"
    And I click on "Save image" "button"
    And I press "Save changes and continue editing"

  # Preparing the questions
  # Question 1: Add image to optiontext
    And I click on "Insert or edit image" "button" in the ".optiontext" "css_element"
    And I press "Browse repositories..."
    And I click on "URL downloader" "link" in the ".fp-repo-area" "css_element"
    And I set the field "fileurl" to "http://localhost/moodle-3-8-1+/question/type/mtf/tests/fixtures/testimage2.png"
    And I press "Download"
    And I click on "testimage2.png" "link"
    And I press "Select this file"
    And I set the field "Describe this image for someone who cannot see it" to "testimage2AltDescription"
    And I click on "Save image" "button"
    And I press "id_submitbutton"
    And I choose "Edit question" action for "MTF-Question-002" in the question bank

  # Preparing the questions
  # Question 1: Add image to question stem
    And I click on "Insert or edit image" "button" in the "#id_generalheader" "css_element"
    And I press "Browse repositories..."
    And I click on "URL downloader" "link" in the ".fp-repo-area" "css_element"
    And I set the field "fileurl" to "http://localhost/moodle-3-8-1+/question/type/mtf/tests/fixtures/testimage1.png"
    And I press "Download"
    And I click on "testimage1.png" "link"
    And I press "Select this file"
    And I set the field "Describe this image for someone who cannot see it" to "testimage1AltDescription"
    And I click on "Save image" "button"
    And I press "Save changes and continue editing"

  # Preparing the questions
  # Question 1: Add image to optiontext
    And I click on "Insert or edit image" "button" in the ".optiontext" "css_element"
    And I press "Browse repositories..."
    And I click on "URL downloader" "link" in the ".fp-repo-area" "css_element"
    And I set the field "fileurl" to "http://localhost/moodle-3-8-1+/question/type/mtf/tests/fixtures/testimage2.png"
    And I press "Download"
    And I click on "testimage2.png" "link"
    And I press "Select this file"
    And I set the field "Describe this image for someone who cannot see it" to "testimage2AltDescription"
    And I click on "Save image" "button"
    And I press "id_submitbutton"
    And I log out

  # Solving the exam as students
  # Student 1 (100% correct)
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=2]" "css_element"
    And I press "Next page"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=2]" "css_element"
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
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I press "Next page"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Backup Exam as admin
    When I log in as "admin"
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

  # Set Scoring Method to MTF 1/0
    And I click on "nav a:contains('Quiz 1')" "css_element"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question MTF-Question-001" "link" in the "MTF-Question-001" "list_item"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_mtfonezero" "radio"
    And I press "id_submitbutton"

  # Regrade first exam
    And I click on "nav a:contains('Quiz 1')" "css_element"
    And I navigate to "Results" in current page administration
    And I click on "#mod-quiz-report-overview-report-selectall-attempts" "css_element"
    And I press "Regrade selected attempts"
    And I press "Continue"
    And I log out

  # Change first exam Question content
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question MTF-Question-001" "link" in the "MTF-Question-001" "list_item"
    And I set the following fields to these values:
      | id_questiontext | Edited MTF Questiontext |
    And I press "id_submitbutton"
    And I log out

  # Change quiz title
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit settings" in current page administration
    And I set the following fields to these values:
      | id_name | Quiz 0 |
    And I press "id_submitbutton"

  # Restore
    When I navigate to "Restore" in current page administration
    And I click on "tr:contains('test_backup.mbz') a:contains('Restore')" "css_element"
    And I press "Continue"
    And I click on "tr:contains('Course 1') input[type='radio']" "css_element"
    And I press "Continue"
    And I press "Next"
    And I press "Next"
    And I press "Perform restore"
    And I press "Continue"

  # Check if grades are different
    When I follow "Quiz 0"
    When I navigate to "Results" in current page administration
    Then "tr:contains('student1@moodle.com') .c8:contains('100.00')" "css_element" should exist
    And "tr:contains('student2@moodle.com') .c8:contains('25.00')" "css_element" should exist

    When I click on "nav a[title='Course 1']" "css_element"
    And I follow "Quiz 1"
    When I navigate to "Results" in current page administration
    Then "tr:contains('student1@moodle.com') .c8:contains('100.00')" "css_element" should exist
    And "tr:contains('student2@moodle.com') .c8:contains('50.00')" "css_element" should exist

  # Check if the altered MTF-Question-001 exists twice in the question bank
    When I click on "nav a[title='Course 1']" "css_element"
    And I follow "Quiz 0"
    When I navigate to "Question bank" in current page administration
    Then "tr:contains('MTF-Question-001') td[class='modifiername']:contains('Admin User')" "css_element" should exist
    And "tr:contains('MTF-Question-001') td[class='modifiername']:contains('T1 Teacher')" "css_element" should exist
    And "tr:contains('MTF-Question-002')" "css_element" should exist

  # Check for images
    When I click on "tr:contains('MTF-Question-001'):not(:contains('T1 Teacher1')) a[aria-label='Edit']" "css_element"
    And I click on "tr:contains('MTF-Question-001'):not(:contains('T1 Teacher1')) a[target='questionpreview']" "css_element"
    And I switch to "questionpreview" window
    Then "[alt='testimage1AltDescription']" "css_element" should exist
    And I should not see "testimage1AltDescription"
    And "[alt='testimage2AltDescription']" "css_element" should exist
    And I should not see "testimage2AltDescription"
    And I switch to the main window

    When I click on "tr:contains('MTF-Question-001'):contains('T1 Teacher1') a[aria-label='Edit']" "css_element"
    And I click on "tr:contains('MTF-Question-001'):contains('T1 Teacher1') a[target='questionpreview']" "css_element"
    And I switch to "questionpreview" window
    And "[alt='testimage2AltDescription']" "css_element" should exist
    And I should not see "testimage2AltDescription"
    And I switch to the main window

    When I choose "Preview" action for "MTF-Question-002" in the question bank
    And I switch to "questionpreview" window
    Then "[alt='testimage1AltDescription']" "css_element" should exist
    And I should not see "testimage1AltDescription"
    And "[alt='testimage2AltDescription']" "css_element" should exist
    And I switch to the main window