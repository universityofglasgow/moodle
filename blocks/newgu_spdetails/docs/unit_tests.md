# UNIT TESTS

Unit tests are provided for testing the PHP side of the plugin. This primarily means
testing the web services exported by the plugin.

## Configuring Unit Tests

Please see document https://moodledev.io/general/development/tools/phpunit

Currently tests can be run individually, using (for example)

    vendor/bin/phpunit blocks/newgu_spdetails/tests/external/get_grade_test.php

...or the complete set for the plugin can be executed using

    vendor/bin/phpunit --testsuite blocks_newgu_spdetails_testsuite

## Test configuration

Web service tests, extend the class *newgu_spdetails_advanced_testcase*. This creates some basic structure for
the tests to use. Including...

* Courses - MyGrades and regular Gradebook types
* The 22-point scale - both Schedule A and Schedule B
* A teacher
* Some students
* Grade categories
* Activities:
* Assignment
* Quiz
* Forum
* Workshop
* Some grades for the Assignments

## Test descriptions

<dl>
    <dt>[get_assessment_summary_test](../tests/external/get_assessment_summary_test.php)</dt>
    <dd>Tests the get_assessmentsummary web service.</dd>
</dl>