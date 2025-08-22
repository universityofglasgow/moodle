@quiz @quiz_essaydownload @javascript
Feature: Show notification, if ZIP archive will be empty

  Background:
    Given the following "course" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | T1        | Teacher1 |
      | student1 | S1        | Student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "activities" exist:
      | activity | name   | intro              | course |
      | quiz     | Quiz 1 | Quiz 1 description | C1     |
    And the following "questions" exist:
      | questioncategory | qtype       | name                    | questiontext   |
      | Test questions   | truefalse   | Q1                      | First question |
      | Test questions   | shortanswer | Q2                      | Foo            |
      | Test questions   | random      | Random (Test questions) | 0              |
    And quiz "Quiz 1" contains the following questions:
      | question                | page | maxmark |
      | Random (Test questions) | 1    | 1.0     |
    And user "student1" has attempted "Quiz 1" with responses:
      | slot | response |
      | 1    | True     |

  Scenario: If the generated archive is empty, a notification should be shown.
    When I am on the "Quiz 1" "quiz_essaydownload > essaydownload report" page logged in as "teacher1"
    Then I should see "Attempts: 1"
    When I press "Download"
    Then I should see "Nothing to download"
