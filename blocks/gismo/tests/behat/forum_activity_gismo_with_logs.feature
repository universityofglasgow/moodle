@block @block_gismo
Feature: Using a forum activity is viewed in GISMO overviews
	In order to enrol one student in course composed by 
    one forum activity
	As a admin
	I need to have the right data on GISMO overviews 
	after use of forum activity

	@javascript
	Scenario: Add one forum and access GISMO overviews
		Given the following "courses" exist:
			| fullname | shortname | category |
			| Course 1 | C1 | 0 |
		And the following "users" exist:
			| username | firstname | lastname | email |
			| student1 | Student | 1 | student1@asd.com |
		And the following "course enrolments" exist:
			| user | course | role |
			| student1 | C1 | student |
		And I log in as "admin"
		And I am on homepage
		And I follow "Course 1"
		And I turn editing mode on
		And I add the "Gismo" block
		And I add a "Forum" to section "1" and I fill the form with:
			| Forum name | Test forum name |
			| Forum type | Standard forum for general use |
			| Description | Test forum description |
		And I wait until the page is ready
		And I log out
		When I log in as "student1"
		And I am on homepage
		And I follow "Course 1"
		And I add a new discussion to "Test forum name" forum with:
		  | Subject | Post with attachment |
		  | Message | This is the body |
		And I am on homepage
		And I log out
		Then I log in as "admin"
		And I follow "Course 1"
		And I synchronize gismo data
		And I go to the "Activities > Forums" report
		And I should see "1" on "Activities > Forums over time" report
		And I wait "10" seconds