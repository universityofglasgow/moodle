@qtype @qtype_kprime @qtype_kprime_step_1
Feature: Add a Kprime question

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  Scenario: Add a Kprime question

    When I add a "Kprime" question filling the form with:
      | id_name                  | Added-Kprime-Question-1   |
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
    Then I should see "Added-Kprime-Question-1"