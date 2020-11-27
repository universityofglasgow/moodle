@qtype @qtype_mtf @qtype_mtf_step_1_2
Feature: Step 1 and 2

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  Scenario: TESTCASE 1 and TESTCASE 2.
    And I add a "Multiple True False (ETH)" question filling the form with:
      | id_name              | MTF-Question-001                                    |
      | id_defaultmark       | 1                                                   |
      | id_questiontext      | question_one                                        |
      | id_generalfeedback   | This feedback is general                            |
      | id_option_0          | q1                                                  |
      | id_option_1          | q2                                                  |
      | id_option_2          | q3                                                  |
      | id_option_3          | q4                                                  |
      | id_feedback_0        | f1                                                  |
      | id_feedback_1        | f2                                                  |
      | id_feedback_2        | f3                                                  |
      | id_feedback_3        | f4                                                  |
      | id_weightbutton_0_1  | checked                                             |
      | id_weightbutton_1_1  | checked                                             |
      | id_weightbutton_2_1  | checked                                             |
      | id_weightbutton_3_1  | checked                                             |
    Then I should see "MTF-Question-001"

    And I choose "Edit question" action for "MTF-Question-001" in the question bank
    And I press "Blanks for 3 more choices"
    And I set the following fields to these values:
      | id_option_4          | q5                                                  |
      | id_option_5          | q6                                                  |
      | id_option_6          | q7                                                  |
      | id_feedback_4        | f5                                                  |
      | id_feedback_5        | f6                                                  |
      | id_feedback_6        | f7                                                  |
      | id_weightbutton_4_2  | checked                                             |
      | id_weightbutton_5_2  | checked                                             |
      | id_weightbutton_6_2  | checked                                             |
    And I press "Blanks for 3 more choices"
    And I set the following fields to these values:
      | id_option_7          | q8                                                  |
      | id_option_8          | q9                                                  |
      | id_option_9          | q10                                                 |
      | id_feedback_7        | f8                                                  |
      | id_feedback_8        | f9                                                  |
      | id_feedback_9        | f10                                                 |
      | id_weightbutton_7_2  | checked                                             |
      | id_weightbutton_8_2  | checked                                             |
      | id_weightbutton_9_2  | checked                                             |
    And I press "Blanks for 3 more choices"
    And I set the following fields to these values:
      | id_option_10         | q11                                                 |
      | id_option_11         | q12                                                 |
      | id_option_12         | q13                                                 |
      | id_feedback_10       | f11                                                 |
      | id_feedback_11       | f12                                                 |
      | id_feedback_12       | f13                                                 |
      | id_weightbutton_10_2 | checked                                             |
      | id_weightbutton_11_2 | checked                                             |
      | id_weightbutton_12_2 | checked                                             |
    And I press "Blanks for 3 more choices"
    And I set the following fields to these values:
      | id_option_13         | q14                                                 |
      | id_option_14         | q15                                                 |
      | id_option_15         | q16                                                 |
      | id_feedback_13       | f14                                                 |
      | id_feedback_14       | f15                                                 |
      | id_feedback_15       | f16                                                 |
      | id_weightbutton_13_2 | checked                                             |
      | id_weightbutton_14_2 | checked                                             |
      | id_weightbutton_15_2 | checked                                             |
    And I press "id_submitbutton"
  Then I should see "MTF-Question-001"