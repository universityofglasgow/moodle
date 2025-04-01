@qtype @qtype_kprime @qtype_kprime_1
Feature: Step 1

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
      | Course       | C1        | AnotherCat     |
    And I log in as "admin"

  @javascript
  Scenario: Testcase 5

  # Create question and check if all values are on default state
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_kprime" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Kprime question"
    And the following fields match these values:
      | id_name                  ||
      | id_questiontext          | Enter the stem, a question or a part of a sentence, here. |
      | id_generalfeedback       ||
      | id_option_1              ||
      | id_feedback_1            ||
      | id_option_2              ||
      | id_feedback_2            ||
      | id_option_3              ||
      | id_feedback_3            ||
      | id_option_4              ||
      | id_feedback_4            ||
      | id_weightbutton_1_1      | checked |
      | id_weightbutton_2_1      | checked |
      | id_weightbutton_3_1      | checked |
      | id_weightbutton_4_1      | checked |

  @javascript
  Scenario: (new0)

  # Create a question filling out all forms
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I add a "Kprime" question filling the form with:
      | id_name                  | Kprime Question           |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_1              | 1st optiontext            |
      | id_feedback_1            | 1st feedbacktext          |
      | id_option_2              | 2nd optiontext            |
      | id_feedback_2            | 2nd feedbacktext          |
      | id_option_3              | 3rd optiontext            |
      | id_feedback_3            | 3rd feedbacktext          |
      | id_option_4              | 4th optiontext            |
      | id_feedback_4            | 4th feedbacktext          |
      | id_weightbutton_1_1      | checked                   |
      | id_weightbutton_2_1      | checked                   |
      | id_weightbutton_3_2      | checked                   |
      | id_weightbutton_4_2      | checked                   |
    Then I should see "Question bank"
    And I should see "Kprime Question"

  # Open the saved question and check if everything has been saved
    When I choose "Edit question" action for "Kprime Question" in the question bank
    Then the following fields match these values:
      | id_name                  | Kprime Question           |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_1              | 1st optiontext            |
      | id_feedback_1            | 1st feedbacktext          |
      | id_option_2              | 2nd optiontext            |
      | id_feedback_2            | 2nd feedbacktext          |
      | id_option_3              | 3rd optiontext            |
      | id_feedback_3            | 3rd feedbacktext          |
      | id_option_4              | 4th optiontext            |
      | id_feedback_4            | 4th feedbacktext          |
      | id_weightbutton_1_1      | checked                   |
      | id_weightbutton_2_1      | checked                   |
      | id_weightbutton_3_2      | checked                   |
      | id_weightbutton_4_2      | checked                   |

  @javascript
  Scenario: Testcase 6

  # Create a question and check if question title is required
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_kprime" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Kprime question"
    When I set the following fields to these values:
      | id_name     |                |
      | id_option_1 | 1st optiontext |
    And I press "id_submitbutton"
    Then "#id_name.is-invalid" "css_element" should exist
    Then "#id_option_1editable.is-invalid" "css_element" should not exist

  # Check if options are required
    When I set the following fields to these values:
      | id_name     | Kprime Question |
      | id_option_1 |                 |
      | id_option_2 |                 |
      | id_option_3 |                 |
      | id_option_4 |                 |
    And I press "id_submitbutton"
    Then "#id_name.is-invalid" "css_element" should not exist
    And "#id_error_option_1.form-control-feedback.invalid-feedback" "css_element" should exist
    And "#id_error_option_2.form-control-feedback.invalid-feedback" "css_element" should exist
    And "#id_error_option_3.form-control-feedback.invalid-feedback" "css_element" should exist
    And "#id_error_option_4.form-control-feedback.invalid-feedback" "css_element" should exist

  # Check if defaultmark is required
    When I set the following fields to these values:
      | id_name        | Kprime Question |
      | id_option_1    | 1st optiontext  |
      | id_option_2    | 2nd optiontext  |
      | id_option_3    | 3rd optiontext  |
      | id_option_4    | 4th optiontext  |
      | id_defaultmark |                 |
    And I press "id_submitbutton"
    Then "#id_defaultmark.is-invalid" "css_element" should exist
    And "#id_option_1editable.is-invalid" "css_element" should not exist
    And "#id_option_2editable.is-invalid" "css_element" should not exist
    And "#id_option_3editable.is-invalid" "css_element" should not exist
    And "#id_option_4editable.is-invalid" "css_element" should not exist

  # Check if judgment options are required
    When I set the following fields to these values:
      | id_defaultmark    | 1 |
      | id_responsetext_1 |   |
    And I press "id_submitbutton"
    Then "#id_defaultmark.is-invalid" "css_element" should not exist
    And "#id_responsetext_1.is-invalid" "css_element" should exist
    And "#id_responsetext_2.is-invalid" "css_element" should not exist
    When I set the following fields to these values:
      | id_responsetext_1 | Richtig |
      | id_responsetext_2 |         |
    And I press "id_submitbutton"
    And "#id_responsetext_1.is-invalid" "css_element" should not exist
    And "#id_responsetext_2.is-invalid" "css_element" should exist
    When I set the following fields to these values:
      | id_responsetext_1 | |
      | id_responsetext_2 | |
    And I press "id_submitbutton"
    And "#id_responsetext_1.is-invalid" "css_element" should exist
    And "#id_responsetext_2.is-invalid" "css_element" should exist

  # Enter everything correctly
    When I set the following fields to these values:
      | id_responsetext_1 | Richtig |
      | id_responsetext_2 | Falsch  |
    And I press "id_submitbutton"
    Then I should see "Question bank"
    And I should see "Kprime Question"
    And "#id_responsetext_1.is-invalid" "css_element" should not exist
    And "#id_responsetext_2.is-invalid" "css_element" should not exist

  @javascript
  Scenario: Testcase 1

  # Create a question and check if scoringmethod is default
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_kprime" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Kprime question"
    When I click on "Scoring method" "link"
    Then "#id_scoringmethod_kprime[checked]" "css_element" should exist

  # Change default scoringmethod in Plugin administration
    When I navigate to "Plugins > Question types > Kprime (ETH)" in site administration
    And I should see "Default values for kprime questions."
    And I select "Kprime1/0" from the "s_qtype_kprime_scoringmethod" singleselect
    And I press "Save changes"

  # Create a question and check if default scoringmethod has changed
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_kprime" to "1"
    And I press "submitbutton"
    And I should see "Adding a Kprime question"
    And I click on "Scoring method" "link"
    Then "#id_scoringmethod_kprimeonezero[checked]" "css_element" should exist

  @javascript
  Scenario: Testcase 2

  # Install the german language pack
    Given the following "language packs" exist:
      | language |
      | de       |

  # Create a question and check english language strings
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_kprime" to "1"
    And I press "submitbutton"
    Then "#id_responsetext_1[value='True']" "css_element" should exist
    And "#id_responsetext_2[value='False']" "css_element" should exist

  # Change language
    And I press "id_cancel"
    And I follow "Language" in the user menu
    And I click on "//a[contains(@href, 'lang=de')]" "xpath"
    And I wait "3" seconds

  # Create a question and check german language strings
    When I press "Neue Frage erstellen..."
    And I set the field "item_qtype_kprime" to "1"
    And I press "submitbutton"
    Then "#id_responsetext_1[value='Richtig']" "css_element" should exist
    And "#id_responsetext_2[value='Falsch']" "css_element" should exist

  @javascript
  Scenario: Testcase 7

    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I add a "Kprime" question filling the form with:
      | id_name                  | Kprime Question           |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_1              | 1st optiontext            |
      | id_feedback_1            | 1st feedbacktext          |
      | id_option_2              | 2nd optiontext            |
      | id_feedback_2            | 2nd feedbacktext          |
      | id_option_3              | 3rd optiontext            |
      | id_feedback_3            | 3rd feedbacktext          |
      | id_option_4              | 4th optiontext            |
      | id_feedback_4            | 4th feedbacktext          |
      | id_weightbutton_1_1      | checked                   |
      | id_weightbutton_2_1      | checked                   |
      | id_weightbutton_3_2      | checked                   |
      | id_weightbutton_4_2      | checked                   |
    Then I should see "Question bank"
    And I should see "Kprime Question"

  # Duplicate the question
    When I choose "Duplicate" action for "Kprime Question" in the question bank
    And I press "id_submitbutton"
    Then I should see "Kprime Question"
    And I should see "Kprime Question (copy)"

  # Move the question to another category
    When I click on "Kprime Question" "checkbox" in the "Kprime Question" "table_row"
    And I press "With selected"
    And I click on question bulk action "move"
    And I set the field "Question category" to "AnotherCat"
    And I press "Move to"
    Then I should see "Question bank"
    And I should see "AnotherCat"
    And I should see "Kprime Question"

  # Delete the question
    When I choose "Delete" action for "Kprime Question" in the question bank
    And I press "Delete"
    Then I should not see "Kprime Question"
