@qtype @qtype_mtf @qtype_mtf_1
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
    And the following "questions" exist:
      | questioncategory | qtype | name           | template     |
      | Test questions   | mtf   | MTF Question 2 | question_one |
    And I log in as "admin"

  @javascript
  Scenario: Testcase 34

  # Check if the shuffleanswers option is checked per default and
  # check if it is set in the plugin administration it also should be checked in newly created questions
    When I navigate to "Plugins > Question types > Multiple True False (ETH)" in site administration
    And I should see "Default values for Multiple True/False questions."
    And the following fields match these values:
      | id_s_qtype_mtf_shuffleanswers | checked |
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Multiple True/False question"
    And the following fields match these values:
      | id_shuffleanswers | checked |

  # Check if the shuffleanswers option is NOT checked in the plugin administration
  # it also should NOT be checked in newly created questions
    When I navigate to "Plugins > Question types > Multiple True False (ETH)" in site administration
    And I should see "Default values for Multiple True/False questions."
    And I set the following fields to these values:
      | id_s_qtype_mtf_shuffleanswers | |
    And I press "Save changes"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Multiple True/False question"
    And the following fields match these values:
      | id_shuffleanswers | |
    And I log out

  @javascript
  Scenario: Testcase 1,3,4

  # Create question and check if all values are on default state
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Multiple True/False question"
    And the following fields match these values:
      | id_name                  ||
      | id_questiontext          | Enter the stem or question prompt here. |
      | id_generalfeedback       ||
      | id_option_0              ||
      | id_feedback_0            ||
      | id_option_1              ||
      | id_feedback_1            ||
      | id_option_2              ||
      | id_feedback_2            ||
      | id_option_3              ||
      | id_feedback_3            ||
      | id_weightbutton_0_1      | checked |
      | id_weightbutton_1_2      | checked |
      | id_weightbutton_2_2      | checked |
      | id_weightbutton_3_2      | checked |
    And "Blanks for 3 more choices" "button" should exist
    And "Multiple tries" "link" should exist

  @javascript
  Scenario: Testcase 7

  # Create a question filling out all forms
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Multiple True/False question"
    When I set the following fields to these values:
      | id_name                  | MTF Question              |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_0              | 1st optiontext            |
      | id_feedback_0            | 1st feedbacktext          |
      | id_option_1              | 2nd optiontext            |
      | id_feedback_1            | 2nd feedbacktext          |
      | id_option_2              | 3rd optiontext            |
      | id_feedback_2            | 3rd feedbacktext          |
      | id_option_3              | 4th optiontext            |
      | id_feedback_3            | 4th feedbacktext          |
      | id_weightbutton_0_1      | checked                   |
      | id_weightbutton_1_1      | checked                   |
      | id_weightbutton_2_2      | checked                   |
      | id_weightbutton_3_2      | checked                   |
    And I click on "Blanks for 3 more choices" "button"
    And I wait "5" seconds
    And I set the following fields to these values:
      | id_option_4              | 5th optiontext            |
      | id_feedback_4            | 5th feedbacktext          |
      | id_option_5              | 6th optiontext            |
      | id_feedback_5            | 6th feedbacktext          |
      | id_option_6              | 7th optiontext            |
      | id_feedback_6            | 7th feedbacktext          |
      | id_weightbutton_4_1      | checked                   |
      | id_weightbutton_5_1      | checked                   |
      | id_weightbutton_6_1      | checked                   |
    And I click on "Blanks for 3 more choices" "button"
    And I wait "5" seconds
    And I set the following fields to these values:
      | id_option_7              | 8th optiontext            |
      | id_feedback_7            | 8th feedbacktext          |
      | id_option_8              | 9th optiontext            |
      | id_feedback_8            | 9th feedbacktext          |
      | id_option_9              | 10th optiontext           |
      | id_feedback_9            | 10th feedbacktext         |
      | id_weightbutton_7_1      | checked                   |
      | id_weightbutton_8_1      | checked                   |
      | id_weightbutton_9_1      | checked                   |
    And I click on "Blanks for 3 more choices" "button"
    And I wait "5" seconds
    And I set the following fields to these values:
      | id_option_10             | 11th optiontext           |
      | id_feedback_10           | 11th feedbacktext         |
      | id_option_11             | 12th optiontext           |
      | id_feedback_11           | 12th feedbacktext         |
      | id_option_12             | 13th optiontext           |
      | id_feedback_12           | 13th feedbacktext         |
      | id_weightbutton_10_1     | checked                   |
      | id_weightbutton_11_1     | checked                   |
      | id_weightbutton_12_1     | checked                   |
    And I click on "Blanks for 3 more choices" "button"
    And I wait "5" seconds
    And I set the following fields to these values:
      | id_option_13             | 14th optiontext           |
      | id_feedback_13           | 14th feedbacktext         |
      | id_option_14             | 15th optiontext           |
      | id_feedback_14           | 15th feedbacktext         |
      | id_option_15             | 16th optiontext           |
      | id_feedback_15           | 16th feedbacktext         |
      | id_weightbutton_13_1     | checked                   |
      | id_weightbutton_14_1     | checked                   |
      | id_weightbutton_15_1     | checked                   |
    And I press "id_submitbutton"
    Then I should see "MTF Question"

  # Open the saved question and check if everything has been saved
    When I choose "Edit question" action for "MTF Question" in the question bank
    And I wait "5" seconds
    Then the following fields match these values:
      | id_name                  | MTF Question              |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_0              | 1st optiontext            |
      | id_feedback_0            | 1st feedbacktext          |
      | id_option_1              | 2nd optiontext            |
      | id_feedback_1            | 2nd feedbacktext          |
      | id_option_2              | 3rd optiontext            |
      | id_feedback_2            | 3rd feedbacktext          |
      | id_option_3              | 4th optiontext            |
      | id_feedback_3            | 4th feedbacktext          |
      | id_option_4              | 5th optiontext            |
      | id_feedback_4            | 5th feedbacktext          |
      | id_option_5              | 6th optiontext            |
      | id_feedback_5            | 6th feedbacktext          |
      | id_option_6              | 7th optiontext            |
      | id_feedback_6            | 7th feedbacktext          |
      | id_option_7              | 8th optiontext            |
      | id_feedback_7            | 8th feedbacktext          |
      | id_option_8              | 9th optiontext            |
      | id_feedback_8            | 9th feedbacktext          |
      | id_option_9              | 10th optiontext           |
      | id_feedback_9            | 10th feedbacktext         |
      | id_option_10             | 11th optiontext           |
      | id_feedback_10           | 11th feedbacktext         |
      | id_option_11             | 12th optiontext           |
      | id_feedback_11           | 12th feedbacktext         |
      | id_option_12             | 13th optiontext           |
      | id_feedback_12           | 13th feedbacktext         |
      | id_option_13             | 14th optiontext           |
      | id_feedback_13           | 14th feedbacktext         |
      | id_option_14             | 15th optiontext           |
      | id_feedback_14           | 15th feedbacktext         |
      | id_option_15             | 16th optiontext           |
      | id_feedback_15           | 16th feedbacktext         |
      | id_weightbutton_0_1      | checked                   |
      | id_weightbutton_1_1      | checked                   |
      | id_weightbutton_2_2      | checked                   |
      | id_weightbutton_3_2      | checked                   |
      | id_weightbutton_4_1      | checked                   |
      | id_weightbutton_5_1      | checked                   |
      | id_weightbutton_6_1      | checked                   |
      | id_weightbutton_7_1      | checked                   |
      | id_weightbutton_8_1      | checked                   |
      | id_weightbutton_9_1      | checked                   |
      | id_weightbutton_10_1     | checked                   |
      | id_weightbutton_11_1     | checked                   |
      | id_weightbutton_12_1     | checked                   |
      | id_weightbutton_13_1     | checked                   |
      | id_weightbutton_14_1     | checked                   |
      | id_weightbutton_15_1     | checked                   |

  # Delete some options
    When I set the following fields to these values:
      | id_option_13             | |
      | id_feedback_13           | |
      | id_option_14             | |
      | id_feedback_14           | |
      | id_option_15             | |
      | id_feedback_15           | |
    And I press "id_updatebutton"
    And I wait "5" seconds
    Then I should see "Option 1"
    And I should see "Option 2"
    And I should see "Option 3"
    And I should see "Option 4"
    And I should see "Option 5"
    And I should see "Option 6"
    And I should see "Option 7"
    And I should see "Option 8"
    And I should see "Option 9"
    And I should see "Option 10"
    And I should see "Option 11"
    And I should see "Option 12"
    And I should see "Option 13"
    And I should not see "Option 14"
    And I should not see "14th optiontext"
    And I should not see "14th feedbacktext"
    And I should not see "Option 15"
    And I should not see "15th optiontext"
    And I should not see "15th feedbacktext"
    And I should not see "Option 16"
    And I should not see "16th optiontext"
    And I should not see "16th feedbacktext"

  # Change some options
    When I set the following fields to these values:
      | id_option_10             | 11th optiontext edit      |
      | id_feedback_10           | 11th feedbacktext edit    |
      | id_option_11             | 12th optiontext edit      |
      | id_feedback_11           | 12th feedbacktext edit    |
      | id_option_12             | 13th optiontext edit      |
      | id_feedback_12           | 13th feedbacktext edit    |
      | id_weightbutton_10_2     | checked                   |
      | id_weightbutton_11_2     | checked                   |
      | id_weightbutton_12_2     | checked                   |

    And I press "id_updatebutton"
    And I wait "5" seconds
    Then the following fields match these values:
      | id_option_10             | 11th optiontext edit      |
      | id_feedback_10           | 11th feedbacktext edit    |
      | id_option_11             | 12th optiontext edit      |
      | id_feedback_11           | 12th feedbacktext edit    |
      | id_option_12             | 13th optiontext edit      |
      | id_feedback_12           | 13th feedbacktext edit    |
      | id_weightbutton_10_2     | checked                   |
      | id_weightbutton_11_2     | checked                   |
      | id_weightbutton_12_2     | checked                   |

  @javascript
  Scenario: Testcase 8

  # Create a question and check if question title is required
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Multiple True/False question"
    When I set the following fields to these values:
      | id_name     |                |
      | id_option_0 | 1st optiontext |
    And I press "id_submitbutton"
    Then "#id_name.is-invalid" "css_element" should exist

  # Enter question title and check if options are required
    When I set the following fields to these values:
      | id_name     | MTF Question |
      | id_option_0 |              |
    And I press "id_submitbutton"
    Then "#id_name.is-invalid" "css_element" should not exist
    And I should see "This type of question requires at least 1 option"

  # Check if defaultmark is required
    When I set the following fields to these values:
      | id_option_0    | 1st optiontext |
      | id_defaultmark |                |
    And I press "id_submitbutton"
    And "#id_defaultmark.is-invalid" "css_element" should exist

  # Check if judgment options are required
    When I set the following fields to these values:
      | id_defaultmark    | 1 |
      | id_responsetext_1 |   |
    And I press "id_submitbutton"
    Then "#id_defaultmark.is-invalid" "css_element" should not exist
    And "#id_responsetext_1.is-invalid" "css_element" should exist
    And "#id_responsetext_2.is-invalid" "css_element" should not exist
    When I set the following fields to these values:
      | id_responsetext_1 | True |
      | id_responsetext_2 |      |
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
      | id_responsetext_1 | True  |
      | id_responsetext_2 | False |
    And I press "id_submitbutton"
    Then I should see "Question bank"
    And I should see "MTF Question"
    And "#id_responsetext_1.is-invalid" "css_element" should not exist
    And "#id_responsetext_2.is-invalid" "css_element" should not exist

  @javascript
  Scenario: Testcase 1

  # Create a question and check if scoringmethod is default
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Multiple True/False question"
    When I click on "Scoring method" "link"
    Then "#id_scoringmethod_subpoints[checked]" "css_element" should exist

  # Change default scoringmethod in Plugin administration
    When I navigate to "Plugins > Question types > Multiple True False (ETH)" in site administration
    And I should see "Default values for Multiple True/False questions."
    And I select "MTF1/0" from the "id_s_qtype_mtf_scoringmethod" singleselect
    And I press "Save changes"

  # Create a question and check if default scoringmethod has changed
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    And I should see "Adding a Multiple True/False question"
    And I click on "Scoring method" "link"
    Then "#id_scoringmethod_mtfonezero[checked]" "css_element" should exist

  @javascript
  Scenario: Testcase 13

  # Install the german language pack
    When I navigate to "Language > Language packs" in site administration
    And I set the field "Available language packs" to "de"
    And I press "Install selected language pack(s)"
    Then the "Installed language packs" select box should contain "de"

  # Create a question and check english language strings
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then "#id_responsetext_1[value='True']" "css_element" should exist
    And "#id_responsetext_2[value='False']" "css_element" should exist

  # Change language
    And I press "id_cancel"
    And I click on "English ‎(en)‎" "link"
    And I click on "Deutsch ‎(de)" "link"

  # Create a question and check german language strings
    When I press "Neue Frage erstellen..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then "#id_responsetext_1[value='Wahr']" "css_element" should exist
    And "#id_responsetext_2[value='Falsch']" "css_element" should exist

  @javascript
  Scenario: Testcase 5, 6

    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I add a "Multiple True False (ETH)" question filling the form with:
      | id_name                  | MTF Question              |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_0              | 1st optiontext            |
      | id_feedback_0            | 1st feedbacktext          |
      | id_option_1              | 2nd optiontext            |
      | id_feedback_1            | 2nd feedbacktext          |
      | id_option_2              | 3rd optiontext            |
      | id_feedback_2            | 3rd feedbacktext          |
      | id_option_3              | 4th optiontext            |
      | id_feedback_3            | 4th feedbacktext          |
      | id_weightbutton_0_1      | checked                   |
      | id_weightbutton_1_1      | checked                   |
      | id_weightbutton_2_2      | checked                   |
      | id_weightbutton_3_2      | checked                   |
    Then I should see "Question bank"
    And I should see "MTF Question"

  # Include hints in question which will be duplicated
    When I choose "Edit question" action for "MTF Question" in the question bank
    And I click on "Multiple tries" "link"
    And I set the following fields to these values:
      | id_hint_0 | 1th hinttext |
      | id_hint_1 | 2nd hinttext |
    And I press "submitbutton"
    Then I should see "Question bank"
    And I should see "MTF Question"

  # Duplicate the question
    When I choose "Duplicate" action for "MTF Question" in the question bank
    And I press "id_submitbutton"
    Then I should see "MTF Question"
    And I should see "MTF Question (copy)"

  # Check if hints have been copied to the duplicated question
    When I choose "Edit question" action for "MTF Question (copy)" in the question bank
    And I click on "Multiple tries" "link"
    Then the following fields match these values:
      | id_hint_0 | 1th hinttext |
      | id_hint_1 | 2nd hinttext |
    And I press "submitbutton"
    Then I should see "Question bank"
    And I should see "MTF Question"

  # Move the question to another category
    When I click on "MTF Question" "checkbox" in the "MTF Question" "table_row"
    And I set the field "Question category" to "AnotherCat"
    And I press "Move to >>"
    Then I should see "Question bank"
    And I should see "AnotherCat"
    And I should see "MTF Question"

  # Delete the question
    When I choose "Delete" action for "MTF Question" in the question bank
    And I press "Delete"
    Then I should not see "MTF Question"

  @javascript
  Scenario: Testcase 8

  # Create a question filling out all forms
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I add a "Multiple True False (ETH)" question filling the form with:
      | id_name                  | MTF Question              |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_0              | 1st optiontext            |
      | id_feedback_0            | 1st feedbacktext          |
      | id_weightbutton_0_1      | checked                   |
    Then I should see "Question bank"
    And I should see "MTF Question"

  # Open the saved question and check if everything has been saved.
  # Empty option rows should not be saved.
    When I choose "Edit question" action for "MTF Question" in the question bank
    Then the following fields match these values:
      | id_name                  | MTF Question              |
      | id_questiontext          | This is a questiontext.   |
      | id_generalfeedback       | This feedback is general. |
      | id_option_0              | 1st optiontext            |
      | id_feedback_0            | 1st feedbacktext          |
      | id_weightbutton_0_1      | checked                   |
    And I should not see "Option 2"
    And I should not see "Option 3"
    And I should not see "Option 4"

  @javascript
  Scenario: Testcase 12
  # Edit true and false fields.
  # They sould be editable and the values
  # get updated in the answer options

    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I choose "Edit question" action for "MTF Question 2" in the question bank
    And I set the field "id_responsetext_1" to "Red Answer"
    And I set the field "id_responsetext_2" to "Blue Answer"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then I should see "Red Answer"
    And I should see "Blue Answer"
