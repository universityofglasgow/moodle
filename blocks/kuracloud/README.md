# kuraCloud Moodle integration

A block that allows a kuraCloud course to be mapped to a Moodle course. Student accounts are synchronised from Moodle to kuraCloud and grades are synchronised back from kuraCloud to Moodle.

## Requirements
* Moodle 3.5 or higher.
* A kuraCloud API key and API URL.

## Setup
* Extract file to the Moodle blocks directory.
* Access the moodle notifications page to start the upgrade process or run `php admin/cli/upgrade.php` from the command line.
* Go to 'Site administration' -> 'Plugins' -> 'Blocks' -> 'kuraCloud' -> 'Add a new connection'.
* Enter the kuraCloud LMS integration token and the URL to the API.


## Setting up a course mapping with kuraCloud
* From a Moodle course add an instance of the kuraCloud block (this will only be available to administrators and if the kuraCloud API has been correctly configured).
* From the newly added kuraCloud block click 'kuraCloud course mapping'.
* Select a course from the dropdown/search box, this will be a list of kuraCloud courses that have not already been mapped to another Moodle course, and click 'Save changes'.


## Syncing users
* From the kuraCloud block click 'Sync user enrolments', if there are difference in the student enrolments between Moodle and kuraCloud the differences will be displayed.
* Click continue to 'sync' the users.

## Syncing grades
* From the kuraCloud block click 'Sync grades from kuraCloud'.
* Click 'Continue' to start the grade sync process. This may take some time of there are a lot of students enrolled in the course.
