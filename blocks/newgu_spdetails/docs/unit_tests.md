# UNIT TESTS

Unit tests are provided for testing the PHP side of the plugin. This primarily means
testing the web services exported by the plugin, but may include the actual class methods.

## Configuring Unit Tests

Please see document https://moodledev.io/general/development/tools/phpunit

Currently tests can be run individually, using (for example)

    vendor/bin/phpunit blocks/newgu_spdetails/tests/external/get_weight_test.php

...or the complete set for the plugin can be executed using

    vendor/bin/phpunit --testsuite blocks_newgu_spdetails_testsuite

## Test configuration

Web service tests, extend the class *newgu_spdetails_base_testcase*. This creates some basic structure for
the tests to use. Including...

* A MyGrades enabled course
* The 22-point scale - Schedule A
* A teacher
* Some students
* Grade categories
* Some activities
* Some grades for the activities

## Test descriptions

# admin_grade_test

* Test general admnin grades
* Test MV and MV0 as specific admin grades - see MGU-1202.
* Test NS and NS0 as specific admin grades - see MGU-1202.

# assessment_overview_test

* Test the assessment overview chart returns data for the 4 parameters.
* Test the chart returns submitted data.
* Test the chart returns activities to be submitted.
* Test the chart returns activities that are overdue.
* Test the chart returns gradedbook graded activities.
* Test the chart doesn't return MyGrades actvities that have been graded but unreleased.
* Test the chart returns MyGrades actvities that have been graded and unreleased.
* Test the chart returns data by type, e.g. Assessments to be submitted

# assessment_type_test

* Test the assessment type of the activity.

# assessments_due_soon_test

* Test assessments due in the next 24 hours, 7 days and 1 month.
* Test assessments due by type, e.g. 7 days

# block_newgu_spdetails_test

* Test the config method.
* Test the applicable formats method.
* Test the get content method.

# get_course_structure_test

* Test the structure of the course, i.e. contains a name, categories, items etc.

# get_grade_status_and_feedback_test

* Test that a returned grade item contains the relevant grade, status and feedback values.

# get_weight_test

* Test that the weight of the grade item, as a % is returned.

# gradable_activities_test

* Test that for a given course, gradable items are returned.

# newgu_spdetails_advanced_testcase

Extends newgu_spdetails_base_testcase.
Sets up some grade categories from which extending classes can add activities to.

# newgu_spdetails_base_testcase
Sets up an initial MyGrades type course, enrols some teachers and students.