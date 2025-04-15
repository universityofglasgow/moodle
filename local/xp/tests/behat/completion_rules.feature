@local @local_xp @javascript
Feature: Points can be awarded for completing activities, sections and courses
  In order to award points for completing activities, sections and courses
  As a teacher
  I need to configure the rules

  Background:
    Given the following "courses" exist:
      | fullname  | shortname | enablecompletion |
      | Course 1  | c1        | 1 |
    And the following "users" exist:
      | username | firstname | lastname |
      | s1       | Student   | One      |
      | s2       | Student   | Two      |
      | t1       | Teacher   | One      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | s1       | c1     | student        |
      | s2       | c1     | student        |
      | t1       | c1     | editingteacher |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |
    And the following "activities" exist:
      | activity | course | section | name   | intro              | content          | completion | completionview |
      | page     | c1     | 1       | Page 1 | Page 1 description | Page 1 content   | 1          | 1              |
      | page     | c1     | 2       | Page 2 | Page 2 description | Page 2 content   | 1          | 1              |
      | page     | c1     | 2       | Page 3 | Page 3 description | Page 3 content   | 1          | 1              |
    And I am on the "c1" "block_xp > rules" page logged in as "t1"
    And I delete all XP event rules

  Scenario: Points can be awarded for completing any activity
    And I am on the "c1" "block_xp > completionrules" page logged in as "t1"
    And I press "Add"
    And I click on "Any activity" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "2"
    And I press tab
    And I press "Save"
    And I follow "Report" in the XP nav
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |

    When I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 2     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 2     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 2" "link" in the "region-main" "region"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 4     |

  Scenario: Points can be awarded for completing specific activities
    Given I am on the "c1" "block_xp > completionrules" page logged in as "t1"
    And I press "Add"
    And I click on "Specific" "button" in the "Add a condition" "dialogue"
    And I click on "Page 2" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "3"
    And I press tab
    And I press "Save"
    And I follow "Report" in the XP nav
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |

    And I am on the "c1" "course" page logged in as "s1"
    When I click on "Page 2" "link" in the "region-main" "region"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 3     |

  Scenario: Points can be awarded for completing with specific names
    Given I am on the "c1" "block_xp > completionrules" page logged in as "t1"
    And I press "Add"
    And I click on "Activity name" "button" in the "Add a condition" "dialogue"
    And I set the field "Comparison method" to "contains"
    And I set the field "Activity name" to "Page"
    And I set the field "Points to award" to "1"
    And I press tab
    And I press "Save"
    And I press "Add"
    And I click on "Activity name" "button" in the "Add a condition" "dialogue"
    And I set the field "Comparison method" to "is equal to"
    And I set the field "Activity name" to "Page 2"
    And I set the field "Points to award" to "10"
    And I press tab
    And I press "Save"

    And I follow "Report" in the XP nav
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I follow the breadcrumb "Course 1"
    And I click on "Page 2" "link" in the "region-main" "region"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 11     |

  Scenario: Points can be awarded for completing any sections
    Given I am on the "c1" "block_xp > completionrules" page logged in as "t1"
    And I press "Section"
    And I press "Add"
    And I click on "Any section" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "6"
    And I press tab
    And I press "Save"

    And I follow "Report" in the XP nav
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 2" "link" in the "region-main" "region"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 3" "link" in the "region-main" "region"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 6     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 12    |

  Scenario: Points can be awarded for completing specific sections
    Given I am on the "c1" "course" page logged in as "t1"
    And I turn editing mode on
    And I edit the section "1"
    And I click on "Custom" "checkbox" if it exists
    And I set the field "name" to "Topic 1"
    And I press "Save changes"
    And I am on "c1" course homepage
    And I edit the section "2"
    And I click on "Custom" "checkbox" if it exists
    And I set the field "name" to "Topic 2"
    And I press "Save changes"
    And I am on the "c1" "block_xp > completionrules" page logged in as "t1"
    And I press "Section"
    And I press "Add"
    And I click on "Specific section" "button" in the "Add a condition" "dialogue"
    And I press "Topic 2"
    And I set the field "Points to award" to "8"
    And I press tab
    And I press "Save"

    And I follow "Report" in the XP nav
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 2" "link" in the "region-main" "region"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 3" "link" in the "region-main" "region"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 8     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 8     |

  Scenario: Points can be awarded for completing the course
    Given I am on the "c1" "course" page logged in as "t1"
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the following fields to these values:
      | Page 1 | 1 |
    And I press "Save changes"
    And I am on the "c1" "block_xp > completionrules" page logged in as "t1"
    And I click on "Course" "button" in the "region-main" "region"
    And I press "Add"
    And I click on "This course" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "123"
    And I press tab
    And I press "Save"

    And I follow "Report" in the XP nav
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 2" "link" in the "region-main" "region"
    And I trigger cron
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | -     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I trigger cron
    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 123   |
