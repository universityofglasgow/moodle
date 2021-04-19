@qtype @qtype_mtf @qtype_mtf_step_7
Feature: Step 7

  Background:
    Given the following "users" exist:
      | username             | firstname      | lastname         | email               |
      | teacher1             | T1             | Teacher1         | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname             | shortname      | category         |
      | Course 1             | c1             | 0                |
    And the following "course enrolments" exist:
      | user                 | course         | role             |
      | teacher1             | c1             | editingteacher   |
    And the following "question categories" exist:
      | contextlevel         | reference      | name             |
      | Course               | c1             | Default for c1   |
    And the following "questions" exist:
      | questioncategory     | qtype          | name             | template            |
      | Default for c1       | mtf            | MTF-Question-001 | question_one        |
    Given I log in as "teacher1"

  @javascript @_switch_window @_file_upload
  Scenario: TESTCASE 7.
  # When creating a MTF question add "Image"
  # (and other html-editor possibilities) in the
  # stem and in the options.
  # Images and so on are displayed and work.

    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I choose "Edit question" action for "MTF-Question-001" in the question bank

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
    And I press "Save changes and continue editing"

  # Add image to feedback
    And I click on "Insert or edit image" "button" in the ".feedbacktext" "css_element"
    And I press "Browse repositories..."
    And I click on "URL downloader" "link" in the ".fp-repo-area" "css_element"
    And I set the field "fileurl" to "http://localhost/moodle-3-8-1+/question/type/mtf/tests/fixtures/testimage3.png"
    And I press "Download"
    And I click on "testimage3.png" "link"
    And I press "Select this file"
    And I set the field "Describe this image for someone who cannot see it" to "testimage3AltDescription"
    And I click on "Save image" "button"
    And I press "id_submitbutton"

  # Preview
    When I choose "Preview" action for "MTF-Question-001" in the question bank
    And I switch to "questionpreview" window
    And "[alt='testimage2AltDescription']" "css_element" should exist
    And I should not see "testimage2AltDescription"

    When I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=2]" "css_element"
    And I press "Check"
    Then "[alt='testimage3AltDescription']" "css_element" should exist
    And I should not see "testimage3AltDescription"