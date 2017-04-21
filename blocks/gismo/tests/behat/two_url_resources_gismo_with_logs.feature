@block @block_gismo
Feature: Using two url type resources is viewed in GISMO overviews
	In order to enrol one student in course composed by 
    two url type resources
	As a admin
	I need to have the right data on GISMO overviews 
	after use of two url type resources

	@javascript
	Scenario: Add two url type resources and access GISMO overviews
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
		And I add a "URL" to section "1" and I fill the form with:
			| Name | univ-lemans |
			| Description | Test URL description |
			| External URL | http://www.univ-lemans.fr |
		And I add a "URL" to section "1" and I fill the form with:
			| Name | openStreetMap |
			| Description | Test URL description |
			| External URL | http://www.openstreetmap.org |
		And I log out
		When I log in as "student1"
		And I am on homepage
		And I follow "Course 1"
		And I follow "univ-lemans"
		And I follow "openStreetMap"
		And I log out
		Then I log in as "admin"
		And I follow "Course 1"
		And I synchronize gismo data
		And I go to the "Students > Accesses by students" report
		And I go to the "Students > Accesses overview" report
		And I should see "3" on "Students > Accesses overview" report
		And I wait "10" seconds