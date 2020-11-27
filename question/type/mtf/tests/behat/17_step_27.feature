@qtype @qtype_mtf @qtype_mtf_step_27
Feature: Step 27

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype | name             | template     |
      | Default for c1   | mtf   | MTF-Question-001 | question_one |
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And quiz "Quiz 1" contains the following questions:
      | MTF-Question-001 | 1 |

  @javascript
  Scenario: TESTCASE 27.
  # Backup Course (including quiz with MTF questions) and restore
  # Backup and restore works (Images etc. are also backuped and restored)

  # Upload images
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I choose "Edit question" action for "MTF-Question-001" in the question bank

  # Add image to question stem
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

  # Add image to optiontext
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

  # Backup MTF Question
    And I am on "Course 1" course homepage
    And I navigate to "Backup" in current page administration
    And I press "Next"
    And I press "Next"
    And I set the field "Filename" to "test_backup.mbz"
    And I press "Perform backup"
    Then I should see "The backup file was successfully created."
    And I press "Continue"

  # Restore
    When I click on "Restore" "link"
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    And I navigate to "Question bank" in current page administration
    And I choose "Edit question" action for "MTF-Question-001" in the question bank

  # Check
    Then the following fields match these values:
      | id_name              | MTF-Question-001            |
      | id_defaultmark       | 1                           |
      | id_option_1          | option text 2               |
      | id_feedback_0        | feedback to option 1        |
      | id_feedback_1        | feedback to option 2        |
    And "#id_weightbutton_0_1[checked]" "css_element" should exist
    And "#id_weightbutton_1_2[checked]" "css_element" should exist
    And I should see "Questiontext for Question 1"
    And I should see "This feedback is general"
    And I should see "option text 1"
    And "[alt='testimage1AltDescription']" "css_element" should exist
    And I should not see "testimage1AltDescription"
    And "[alt='testimage2AltDescription']" "css_element" should exist
    And I should not see "testimage2AltDescription"