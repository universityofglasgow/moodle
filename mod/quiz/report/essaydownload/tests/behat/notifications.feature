@quiz @quiz_essaydownload @javascript
Feature: Output of notifications in case of errors

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
      | Course 2 | C2        | 0        |
    And the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | T1        | Teacher1 |
      | student1 | S1        | Student1 |
      | student2 | S2        | Student2 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher1 | C2     | editingteacher |
      | student1 | C2     | student        |
      | student2 | C2     | student        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C2        | Test questions |
    And the following "activities" exist:
      | activity | name   | intro              | course |
      | quiz     | Quiz 1 | Quiz 1 description | C1     |
      | quiz     | Quiz 2 | Quiz 2 description | C2     |
      | quiz     | Quiz 3 | Quiz 3 description | C2     |
      | quiz     | Quiz 4 | Quiz 3 description | C2     |
    And the following "questions" exist:
      | questioncategory | qtype       | name | questiontext   |
      | Test questions   | essay       | Q1   | First question |
      | Test questions   | shortanswer | Q2   | Foo            |
    And quiz "Quiz 1" contains the following questions:
      | question | page | maxmark |
      | Q1       | 1    | 1.0     |
    And quiz "Quiz 2" contains the following questions:
      | question | page | maxmark |
      | Q1       | 1    | 1.0     |
    And quiz "Quiz 3" contains the following questions:
      | question | page | maxmark |
      | Q2       | 1    | 1.0     |
    And quiz "Quiz 4" contains the following questions:
      | question | page | maxmark |
      | Q1       | 1    | 1.0     |
    And user "student1" has started an attempt at quiz "Quiz 4"
    And user "student1" has checked answers in their attempt at quiz "Quiz 4":
      | slot | response |
      | 1    | Foo Bar  |

  Scenario: If no students are enrolled, the teacher should see a notification.
    When I am on the "Quiz 1" "quiz_essaydownload > essaydownload report" page logged in as "teacher1"
    Then I should see "Attempts: 0"
    And I should see "Nothing to download"
    And "Download" "button" should not exist

  Scenario: If there are no attempts, the teacher should see a notification.
    When I am on the "Quiz 2" "quiz_essaydownload > essaydownload report" page logged in as "teacher1"
    Then I should see "Attempts: 0"
    And I should see "Nothing to download"
    And "Download" "button" should not exist

  Scenario: If a quiz does not contain any essay or random questions, the teacher should see a notification.
    When I am on the "Quiz 3" "quiz_essaydownload > essaydownload report" page logged in as "teacher1"
    Then I should see "This quiz does not contain any essay questions."
    And "Download" "button" should not exist

  Scenario: If a quiz only has unifinished attempts, the teacher should see a notification.
    When I am on the "Quiz 4" "quiz_essaydownload > essaydownload report" page logged in as "teacher1"
    Then I should see "Attempts: 1"
    And I should see "Nothing to download"
    And "Download" "button" should not exist
