# AMD module / Glue logic

And AMD module - interface.js - has been created to establish links between Vue and Moodle. 

## Requirement...

Vue needs to be able to access Moodle core web service functions. Moodle's core ajax libraries allow the client to access specified web services with all the security issues taken care of in the background. The alternative would be to use a standard Node.js library (e.g. Axios) but a lot of additional setup would be required to get it to work. However, as Vue cannot access Moodle's AMD modules this is difficult.

## Solution

...a dreadful bodge.

interface.js creates a 'global' object called 'GU'. This is attached to the browser's 'window' object. The classes for required Moodle functionality is then attached to GU. In particular, this provides the string access functions and the Moodle ajax functions. 

The course id is also added to GU.

Vue code can then simply access 'window.GU' and obtain the required data. This doesn't feel terribly elegant but it does work. 

There is a potential timing issue between running the various bits of Javascript. To this end, Vue's 'main.js' contains a bit of code that delays deploying the Vue application until GU exists. 

## Building module

There is nothing unusual - use the standard 'grunt' tooling and load the js as documented, providing the courseid as a parameter.

## References

* [Moodle AJAX docs](https://moodledev.io/docs/guides/javascript/ajax)
* [Moodle JS modules](https://moodledev.io/docs/guides/javascript/modules)