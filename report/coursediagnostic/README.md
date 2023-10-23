# moodle-course_diagnostic
MOOD-113 - course diagnostic report
___

# Purpose
___
This plugin is intended to display diagnostic information regarding problems regularly seen in course setup's.
A scheduled job exists also, which will check for issues in the background and notify users with alert warnings on the course home page should any issues be found.

# Requirements
___
* Your default caching server. However, for development purposes, the plugin had made use of...
* Redis server - the latest version is always a good bet...
* A getting started guide at redis.io (https://redis.io/docs/getting-started/)
* install and configure on centos7: https://www.linode.com/docs/databases/redis/install-and-configure-redis-on-centos-7
* install and configure on Ubuntu 18.04: https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-redis-on-ubuntu-18-04
* Install Redis 5 (https://dl.iuscommunity.org/pub/ius/stable/CentOS/7/x86_64/repoview/redis5.html) via IUS CentOS 7 repository
* Windows port (https://github.com/microsoftarchive/redis/releases) (the MSI file installs a service)
* Redis driver
* For CentOS, you can use either Remi (https://rpms.remirepo.net/) or IUS (https://ius.io/) repositories, and install the php71u-pecl-redis (https://dl.iuscommunity.org/pub/ius/stable/CentOS/7/x86_64/repoview/php71u-pecl-redis.html) driver.
* Redis php-fpm 7 driver on Ubuntu 14.04 (https://gist.github.com/hollodotme/418e9b7c6ebc358e7fda)
* Windows PHP extensions (https://pecl.php.net/package/redis) including DLLs for Windows. Check your PHP version, CPU (64bit or x86) and thread-safe value (see Site Admin > Server > PHP Info) to get the right version; add DLL file to ext directory, add 'extension=php_redis.dll' entry to php.ini and restart your web server.

# Installation
___
* Either clone or checkout the files to /your/moodle/course/report/coursediagnostic
* Visit Site admin => Notifications, follow the upgrade instructions which will install the files in the usual Moodle way.

# Use
___
* To use, begin by visiting the settings page at Site Administration -> Courses -> Course diagnostic settings.
* Use the checkboxes to enable the diagnostic tool, and select which test(s) to perform. Save any changes.
* Simply visit a course page, which will, if enabled run the diagnostic tests, if they haven't been run previously.
* Data will be stored and pulled from the Redis (or system) cache - which has been set to expire data every 30 minutes - this is to prevent running the tests each time you enter a course page.
* If any errors are found, a notification will appear with a link to the report page.
* The report page will display a list of tests run, highlighting those that have passed and/or failed.
* Any changes made at the course settings page, will invalidate the cache for that course.
* Any changes made at the enrolment settings page, will invalidate the cache for that course.
* Any changes made at the enrolment methods settings page, will invalidate the cache for that course.
* Any changes made at Site Administration ... course diagnostic settings will invalidate the ^whole^ cache. Be careful with this one, for (hopefully) obvious reasons.

# External references
___
* Icon courtesy of <a href="https://www.freepik.com/free-vector/set-health-care-medicine-icons-flat-style-pharmacy-symbol-sign-syringe-tablets_10700810.htm#query=diagnostic%20icon&position=8&from_view=keyword&track=ais">Image by macrovector</a> on Freepik
