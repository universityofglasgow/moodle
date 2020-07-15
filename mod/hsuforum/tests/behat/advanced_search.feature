@mod @mod_hsuforum
Feature: The Open Forum search allows users to perform advanced searches for forum posts
  In order to perform an advanced search for a forum post
  As a teacher
  I can use the search feature

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber |
      | teacher1 | Teacher | ONE | teacher1@example.com | T1 |
      | teacher2 | Teacher | TWO | teacher2@example.com | T1 |
      | student1 | Student | 1 | student1@example.com | S1 |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | teacher2 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Open Forum" to section "1" and I fill the form with:
      | Forum name | Announcements |
      | Forum type | Standard forum for general use |
      | Description | Test forum description |
    And I add a new topic to "Announcements" Open Forum with:
      | Subject | My subject |
      | Message | My message |
    And I am on "Course 1" course homepage
    And I add a new topic to "Announcements" Open Forum with:
      | Subject | My subjective|
      | Message | My long message |
    And I log out

  Scenario: Perform an advanced search using any term
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Announcements"
    And I press "Search"
    And I should see "Advanced search"
    And I set the field "words" to "subject"
    When I press "Search"
    Then I should see "My subject"
    And I should see "My subjective"

  Scenario: Perform an advanced search avoiding words
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Announcements"
    And I press "Search"
    And I should see "Advanced search"
    And I set the field "words" to "My"
    And I set the field "notwords" to "subjective"
    When I press "Search"
    Then I should see "My subject"
    And I should not see "My subjective"

  Scenario: Perform an advanced search using whole words
    Given database family used is one of the following:
      | mysql |
      | postgres |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Announcements"
    And I press "Search"
    And I should see "Advanced search"
    And I set the field "fullwords" to "subject"
    When I press "Search"
    Then I should see "My subject"
    And I should not see "My subjective"

  Scenario: Perform an advanced search matching the subject
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Announcements"
    And I press "Search"
    And I should see "Advanced search"
    And I set the field "subject" to "subjective"
    When I press "Search"
    Then I should not see "My message"
    And I should see "My subjective"

  Scenario: Perform an advanced search matching the author
    Given I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I add a new topic to "Announcements" Open Forum with:
      | Subject | My Subjects |
      | Message | My message |
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Announcements"
    And I press "Search"
    And I should see "Advanced search"
    And I set the field "user" to "TWO"
    And I press "Search"
    Then I should see "Teacher TWO"
    And I should not see "Teacher ONE"

  Scenario: Perform an advanced search with multiple words
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Announcements"
    And I press "Search"
    And I should see "Advanced search"
    And I set the field "subject" to "my subjective"
    When I press "Search"
    Then I should not see "My message"
    And I should see "My subjective"
