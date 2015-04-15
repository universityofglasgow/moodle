@block @block_gismo @_file_upload
Feature: Using two file type resources is viewed in GISMO overviews
	In order to enrol one student in course composed by 
    two file type resources
	As a admin
	I need to have the right data on GISMO overviews 
	after use of two file type resources

	@javascript
	Scenario: Add two file type resources and access GISMO overviews
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
		And I add a "File" to section "1"
		And I set the following fields to these values:
			| Name | test_file_1 |
			| Description | test_file_1 |
		And I upload "lib/tests/fixtures/empty.txt" file to "Select files" filemanager
		And I wait until the page is ready
		And I press "Save and return to course"
		And I wait until the page is ready
		And I add a "File" to section "1"
		And I set the following fields to these values:
			| Name | test_file_2 |
			| Description | test_file_2 |
		And I upload "lib/tests/fixtures/empty.txt" file to "Select files" filemanager
		And I wait until the page is ready
		And I press "Save and return to course"
		And I wait until the page is ready		
		And I log out
		When I log in as "student1"
		And I am on homepage
		And I follow "Course 1"
		And I follow "test_file_1"
		And I move backward one page
		And I follow "test_file_2"
		And I move backward one page
		And I log out
		Then I log in as "admin"
		And I follow "Course 1"
		And I synchronize gismo data
		And I go to the "Students > Accesses by students" report
		And I go to the "Students > Accesses overview" report
		And I should see "5" on "Students > Accesses overview" report
		And I go to the "Resources > Students overview" report
		And I wait "10" seconds