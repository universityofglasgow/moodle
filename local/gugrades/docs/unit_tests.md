# UNIT TESTS

Unit tests are provided for testing the PHP side of the plugin. This primarily means
testing the web services expored by the plugin.

Note that the tests ONLY test the web services. There are no tests for the Vue.js UI stuff. 

PLEASE MAKE SURE ALL TESTS PASS BEFORE CHECKING IN!!

## Configuring Unit Tests

Please see document https://moodledev.io/general/development/tools/phpunit

Currently tests can be run individually, using (for example)

    vendor/bin/phpunit local/gugrades/tests/external/get_add_grade_form_test.php

...or the complete set for the plugin can be executed using

    vendor/bin/phpunit --testsuite local_gugrades_testsuite

Additional useful flags are --stop-on-error and --stop-on-failue (as the complete suite can take some time to complete)

## Docker

If running PHP in docker, you're going to have to do something like...

Exec into the Docker container for PHP-FPM:

    docker exec -it moodle44-php-1 /bin/bash

CD to the base of the Moodle install:

    cd /app/public

Run the tests:

    vendor/bin/phpunit --testsuite local_gugrades_testsuite --stop-on-error --stop-on-failure

If you need to recreate the database, the above will tell you and supply the correct command. 

## Test configuration

Web service tests, extend the class *gugrades_base_testcase*, *gugrades_advanced_testcase* and *gugrades_advanced_testcase*. T
his creates some basic structure for tests to use. Including...

* A course
* The 22-point scale
* A teacher
* Some students
* Grade categories (confirming to MyGrades requirements)
* Assignments
* Some grades for the Assignments
* gugrades_aggregation_testcase has functionality to load schemas and data from pre-prepared json files

## Test descriptions

### aggregation_schema2_test

Tests aggregation functionality. Tests above or below 75% completion and the basic aggregation strategies with scales.

* Test no data in aggregation
* Test output where completion < 75%
* test output where completion > 75%
* Test simple weighted mean result
* Test mode result
* Test median result
* Test max result
* Test min result


# aggregation_schema3_test

Test exception when all weights are zero in aggregation

* Test all weights being zero for mean aggregation (not an error) and weighted mean (an error)


# aggregation_schema5_test

Test aggregation strategies with points

* Test weighted mean
* Test simple weighted mean
* Test mode
* Test median
* Test max
* Test min


# aggregation_schema6_test

Test grade category 'drop lowest' functionality with points grades

* Test weighted mean with drop lowest selected
* Test weighted mean with drop lowest exceeding number of items


# aggregation_schema7_test

Test drop lowest functionality with scale grades. Test combinations of admin grades

* Test weighted mean with drop lowest selected
* Test weighted mean with drop lowest exceeding number of items
* Test NS grades at 2nd level+
* Test MV/IS grades at 2nd level+

# aggregation_schema8_test

More combination of admin grades tests


# aggregation_schema9_test

Performance testing of aggregation in very large datasets.

* Ongoing

# aggregation_strategies_test

More tests for aggregation strategies

# csv_capture_test