# Boomi Integration

The Boomi integration replaces the old SOAP web services to interact with CoreHR. All other functionality of this plugin remains the
same.

The same two functions of the CoreHR integration are supported.

* **(INT0100 - Moodle to PXD, GET Staff Data)** A range of staff data is read from CoreHR. This is done by an adhoc task which is queued when a staff user logs into Moodle. The
background process reads the data for an individual user and then writes all of it into a database table ('local_corehr_extract') and a
selected number of fields into user's profile records. As the data is not critical, errors are not handled in any way. It will
simply retry the next time they log in.
* **(INT0101 - Moodle to PXD, POST Staff Training Record)** Completion data for staff training courses is written to CoreHR. Certain courses (use for staff training) are tagged with a
unique code, allocated by HR. They are configured (in Moodle) to 'complete' when agreed criteria are met. The course completion
event is trapped by this plugin. This then queues the completion event in a table ('local_corehr_status'). A Moodle scheduled task
runs every few minutes and looks for any 'candidates' in the table. These are sent to CoreHR using the web service. As these are
critical, any failure is retried until either the service succeeds or an error is returned that indicates success will not be
possible (e.g. the personid does not relate to a member of staff).

The basic Boomi interaction is also logged in a new table - local_corehr_boomi_log

All new Boomi functionality has been added to a new class - classes/boomi.php

## Testing

A new unit test has been created to test aspects of the Boomi integration in file tests/boomi_tests.php.

### Testing INT0100 - Moodle to PXD, GET Staff Data

* The basic functionality of the web service is checked with the unit test
    * A valid user account is created in Moodle and the guid passed to Boomi GetPerson. It is checked that valid (looking) data
is returned. The log record for the interaction is checked to ensure no error logged etc.. User profile data is checked to make
sure the appropriate records have been populated.
    * An invalid GUID is sent to the Boomi GetPerson service. It is checked that an appropriate error is returned. The boomi log is
checked for the correct error log.
* The new service was deployed on the testing site and configured.
* Several members of staff in the team were asked to log in.
    * The Moodle task logs where found and checked. These showed that the GetPerson tasks had completed without error
    for each person.
    * The database was (manualy) checked for each person. The local_corehr_extract entry was correct for each user.
    * The user profiles were checked (manualy) for correct user profile fields.
    * The Boomi log table was checked for correct entries.

### Testing INT0101 - Moodle to PXD, POST Staff Training Record

* The basic functionality of the web service is checked with a unit test
    * The service is checked with an invalid URL. The response and Boomi log is confirmed to contain appropriate error messasges
    * The service is checked with valid data. It is confirmed that an 'OK' status is returned and that appropriate data is
    stored in the Boomi log table.
    * The service is checked again with the same data. It is confirmed that a 'RECORD_ALREADY_EXISTS' error is returned and that
    an appropriate error is stored in the Boomi log table.
    * The service is checked with valid course code, time but a student identifier. It is conformed that a 'PERSON_IS_STUDENT' error
    is returned and that an appropriate error is stored in the Boomi log table.
* The new service was deployed on the test server and configured
* A Moodle course was set up with a valid HR CourseCode and trivial completion criteria. Several test members of staff and students
were added to the course.
* For a sample of users they accessed the course and completed it.
    * It was confirmed that the completion was logged in the status table (existing functionality)
    * When the scheduled task ran, the task logs showed that it completed without error
    * The task returned 'OK' (for staff users) and this was written to the status table
    * For student users an error is returned that was written to the status table.
* Error handling and retry was not specifically tested but this is all existing functionality and not considered to be an issue.