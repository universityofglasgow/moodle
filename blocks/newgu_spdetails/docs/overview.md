# Overview

This document describes the blocks_newgu_spdetails plugin, the second generation of the GCAT project, now formally known as MyGrades. This is the student facing application, formally known as Student MyGrades.

## General description

The code is currently organised as a Moodle block plugin - however, this may move to become a local plugin as further development takes place. A link to the plugin gets added to the "Custom menu items" in Site Administration -> Appearance -> Advanced theme settings - which creates an additional top navigation item, this is how the student accesses Student MyGrades.

The user interface is a combination of Mustache templates alongwith plain old JavaScript to manage updates and server requests.

The user arrives at index.php which contains general Moodle boilerplate - header, footer, login and capability checks, etc. It then makes a call to load in the javascript file main.js which in turn loads in further dependant js files, which in turn make the web service requests to the various PHP scripts, which fetch, process and return data which is then handled in the return methods of the various JavaScript files - displaying the charts and displaying the courses available.  

## Activity support classes

The classes/activities directory contains classes that support different activity types. When the user views a grade category within a course - for each activity in that category a factory method loads the correct class for the activity to be processed. If a specific class for the activity type exists (e.g. 'assign_activity') then that is instantiated and used to process the data that has been returned. If no relevant activity can be found by the factory, a 'default_activity' will be used. This provides only basic functionality. 

## AMD module

A number of AMD files have been created to facilitate connecting the user interface with the back end. The file main.js kicks things off by instantiating the Assessments Overview charts, the Beta notification and the main tabs which present current and past courses for end users to explore. The file coursetabs.js loads up the course information depending on which tab has been selected. It also uses sorting.js to allow table sorting of the returned data. The files all make use of Moodle's web service one way or another, 

## References

* [Moodle AJAX docs](https://moodledev.io/docs/guides/javascript/ajax)
* [Moodle JS modules](https://moodledev.io/docs/guides/javascript/modules)