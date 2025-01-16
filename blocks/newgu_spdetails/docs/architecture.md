# Introduction

## General description

# Organisation of code

### Linking to moodle

The plugin uses items added to the  "Custom menu items" in Site Administration -> Appearance -> Advanced theme settings - this creates the additional top navigation link.

## "Backend" implementation

The backend / business logic is written as PHP classes and is primarily exposed through normal Moodle external APIs (web services). These are supported by static service classes for commonly/repeatedly used functions.

Each API function is accessible in (mostly) two places. The parameters etc. are the same. They can all be accessed either as a static method in classes/api.php or by a Moodle web service. Note that Moodle web services are self-documenting (see developer docs).

The web services are defined in db/services.php and as separate classes in classes/external. Each is self-documenting in the normal Moodle manner.

## Activity classes

In order to interface with different activies (and grade) types, a set of classes have been created. These are implemented such that if a class exists for a specific activity (e.g. Assignment/Quiz) then that class will be instantiated. If no class exists then a 'default' class is instantiated giving "lowest common demoninator" functionality.

Current implemented classes are as follows

| Class name              | Description
|-------------------------|--------------------------------------|
| [assign_activity](../classes/activities/assign_activity.php) | Assign activity class |
| [attendance_activity](../classes/activities/attendance_activity.php) | Attendance Activity class |
| [base](../classes/activities/base.php) | Abstract base class providing generic functionality which all extending classes inherit |
| [checklist_activity](../classes/activities/checklist_activity.php) | Checklist activity class |
| [data_activity](../classes/activities/checklist_activity.php) | Database activity class |
| [default_activity](../classes/activities/default_activity.php) | Used if no other class exists |
| [forum_activity](../classes/activities/forum_activity.php) | Forum activity class |
| [game_activity](../classes/activities/game_activity.php) | Game activity class |
| [glossary_activity](../classes/activities/glossary_activity.php) | Glossary activity class |
| [h5pactivity_activity](../classes/activities/h5pactivity_activity.php) | H5P activity class |
| [hsuforum_activity](../classes/activities/hsuforum_activity.php) | HSU Forum activity class |
| [hvp_activity](../classes/activities/hvp_activity.php) | HVP activity class |
| [kalvidassign_activity](../classes/activities/kalvidassign_activity.php) | Kaltura Video Assignment activity class |
| [lesson_activity](../classes/activities/lesson_activity.php) | Lesson activity class |
| [lti_activity](../classes/activities/lti_activity.php) | LTI activity class |
| [oublog_activity](../classes/activities/oublog_activity.php) | OU Blog activity class |
| [peerwork_activity](../classes/activities/peerwork_activity.php) | Peerwork activity class |
| [questionnaire_activity](../classes/activities/questionnaire_activity.php) | Questionnaire activity class |
| [quiz_activity](../classes/activities/quiz_activity.php) | Quiz activity class |
| [scheduler_activity](../classes/activities/scheduler_activity.php) | Scheduler activity class |
| [scorm_activity](../classes/activities/scorm_activity.php) | SCORM activity class |
| [workshop_activity](../classes/activities/workshop_activity.php) | Workshop activity class |

## User interface

This is currently made up from Mustache templates, which have a number placeholders for Mustache variables to be replaced with their interpolated values.