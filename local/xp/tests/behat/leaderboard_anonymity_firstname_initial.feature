@local @local_xp
Feature: Participants can be anonymised by first name and initial.

  Background:
    Given the following "courses" exist:
      | fullname  | shortname |
      | Course 1  | c1        |
    And the following "users" exist:
      | username | firstname | lastname |
      | t1       | Teacher   | One      |
      | s1       | James     | Wilson   |
      | s2       | Sophia    | Smith    |
      | s3       | Emily     | Johnson  |
      | s4       | Michael   | Brown    |
      | s5       | Sarah     | Davis    |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | t1       | c1     | editingteacher |
      | s1       | c1     | student        |
      | s2       | c1     | student        |
      | s3       | c1     | student        |
      | s4       | c1     | student        |
      | s5       | c1     | student        |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |
    And the following "block_xp > config" exist:
      | worldcontext | name         | value |
      | c1           | identitymode | 2     |
    And the following "block_xp > xp" exist:
      | worldcontext | user | xp  |
      | c1           | s1   | 10  |
      | c1           | s2   | 5   |
      | c1           | s3   | 15  |
      | c1           | s4   | 20  |
      | c1           | s5   | 8   |

  Scenario: Names are partially anonymised
    Given I am on the "c1" "block_xp > ladder" page logged in as "s1"
    Then the following should exist in the "table" table:
      | Participant   |
      | James Wilson  |
      | Sophia S.     |
      | Emily J.      |
      | Michael B.    |
      | Sarah D.      |
