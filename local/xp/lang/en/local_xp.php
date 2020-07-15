<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language file.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activitycompleted'] = 'Activity completed';
$string['afterimport'] = 'After import';
$string['anonymousgroup'] = 'Another team';
$string['anonymousiomadcompany'] = 'Another company';
$string['anonymousiomaddepartment'] = 'Another department';
$string['badgetheme'] = 'Level badges theme';
$string['badgetheme_help'] = 'A badge theme defines the default appearance of the badges.';
$string['categoryn'] = 'Category: {$a}';
$string['clicktoselectcourse'] = 'Click to select a course';
$string['clicktoselectgradeitem'] = 'Click to select a grade item';
$string['courseselector'] = 'Course selector';
$string['csvisempty'] = 'The CSV file is empty.';
$string['csvline'] = 'Line';
$string['csvfile'] = 'CSV file';
$string['csvfile_help'] = 'The CSV file must contain the columns __user__ and __points__. The column __message__ is optional and can be used when notifications are enabled. Note that the __user__ column understands user IDs, email addresses and usernames.';
$string['csvmissingcolumns'] = 'The CSV is missing the following column(s): {$a}.';
$string['currentpoints'] = 'Current points';
$string['currencysign'] = 'Points symbol';
$string['currencysign_help'] = 'With this setting you can change the meaning of the points. It will be displayed next to the amount of points each user has as a substitute for the reference to _experience points_.

For instance you could upload the image of a carrot for the users to be rewarded with carrots for their actions.';
$string['currencysignformhelp'] = 'The image uploaded here will be displayed next to the points as a substitute for the reference to experience points. The recommended image height is 18 pixels.';
$string['displaygroupidentity'] = 'Display teams identity';
$string['enablecheatguard'] = 'Enable cheat guard';
$string['enablecheatguard_help'] = 'The cheat guard prevents students from being rewarded once they reach certain limits.';
$string['errorunknowncourse'] = 'Error: unknown course';
$string['errorunknowngradeitem'] = 'Error: unknown grade item';
$string['filtergradeitems'] = 'Filter grade items';
$string['for2weeks'] = 'For 2 weeks';
$string['for3months'] = 'For 3 months';
$string['keeplogsdesc'] = 'The logs are playing an important role in the plugin. They are used for
the cheat guard, for finding the recent rewards, and for some other things. Reducing the time for
which the logs are kept can affect how points are distributed over time and should be dealt with carefully.';
$string['gradeitemselector'] = 'Grade item selector';
$string['gradeitemtypeis'] = 'The grade is a {$a} grade';
$string['gradereceived'] = 'Grade received';
$string['gradesrules'] = 'Grades rules';
$string['gradesrules_help'] = '
The rules below determine when students earn points for the grades they receive.

Student will earn as many points as their grade.
A grade of 5/10, and a grade of 5/100 will both award the student 5 points
When a student\'s grade changes multiple times, they will earn points equal to the maximum grade they have received.
Points are never taken away from students, and negative grades are ignored.

Example: Alice submits an assignment, and receives the mark of 40/100. In _Level up!_, Alice receives 40 points for her grade.
Alice reattempts her assignment, but this time her grade is lowered to 25/100. Alice\'s points in _Level up!_ do not change.
For her final attempt, Alice scores 60/100, she earns 20 additional points in _Level up!_, her total of points earned is 60.

[More at _Level up!_ documentation](https://levelup.plus/docs/article/grade-based-rewards?ref=localxp_help)
';
$string['groupanonymity'] = 'Anonymity';
$string['groupanonymity_help'] = 'This setting controls whether participants can see the names of the teams they do not belong to.';
$string['groupladder'] = 'Team ladder';
$string['groupladdercols'] = 'Ladder columns';
$string['groupladdercols_help'] = 'This setting determines which columns are displayed aside from the teams ranks and names.

The __Points__ column displays the sum of the points earned by all team members.
You may want to remove the _Points_ column when teams are unbalanced.
This can be the case when some teams accumulate considerably more points than others, for example due to having more members.

The __Progress__ column displays the overall progression of the team towards all of its members reaching the ultimate level.
In other words, the progress can only attain 100% when all team members are at the maximum level. Note that the number of
points remaining, displayed next to the progress bar, may be confusing when teams are unbalanced as teams with more members
will have more points remaining than others, even though their progressions may be similar.

Press the CTRL or CMD key while clicking to select more than one column, or to unselect a selected column.';
$string['groupladdersource'] = 'Team up students using';
$string['groupladdersource_help'] = 'The team ladder displays a leaderboard of an aggregate of the students\' points.
The value you choose determines what _Level up!_ uses to group the students together.
When set to _Nothing_ the team ladder will not be available.

To limit the _Course groups_ that appear in the leaderboard, you may create a new grouping containing the relevant groups, and then set this grouping as the _Default grouping_ in the course settings.';
$string['groupname'] = 'Team name';
$string['grouporderby'] = 'Order by';
$string['grouporderby_help'] = 'Determines what is the basis for ranking the teams.

When set to __Points__, the teams are ranked based on the sum of the points of its members.

When set to __Progress__, the teams are ranked based on their overall progression towards all of its members reaching the ultimate level. You may want to use _Progress_ when the teams are unbalanced, for example when some teams have a lot more members than others.';
$string['grouppoints'] = 'Points';
$string['groupsourcecoursegroups'] = 'Course groups';
$string['groupsourcecohorts'] = 'Cohorts';
$string['groupsourceiomadcompanies'] = 'IOMAD companies';
$string['groupsourceiomaddepartments'] = 'IOMAD departments';
$string['groupsourcenone'] = 'Nothing, the ladder is disabled';
$string['hidegroupidentity'] = 'Hide teams identity';
$string['importcsvfile_help'] = '';
$string['importcsvintro'] = 'Use the form below to import points from a CSV file. The import may be used to _increase_ students\' points, or to override them with the provided value. Note that the import __does not__ use the same format as the exported report. The required format is described in the [documentation]({$a->docsurl}), additionally a sample file is available [here]({$a->sampleurl}).';
$string['importpreview'] = 'Import preview';
$string['importpreviewintro'] = 'Here is a preview showcasing the first {$a} records out of all of those to be imported. Please review and confirm when you are ready to import everything.';
$string['importpoints'] = 'Import points';
$string['importpointsaction'] = 'Points import action';
$string['importpointsaction_help'] = 'Determines what to do with the points found in the CSV file.

**Set as total**

The points will override the current points of the student, making it their new total. Users will not be notified, and there won\'t be any entries in the logs.

**Increase**

The points represent the amount of points to award the student. When enabled, a notification containing the optional _message_ from the CSV file will be sent to the recipients. A _Manual award_ entry will also be added to the logs.
';
$string['importresults'] = 'Import results';
$string['importresultsintro'] = 'Successfully **imported {$a->successful} entries** out of a total of **{$a->total}**. If some entries could not be imported, details will be displayed below.';
$string['importsettings'] = 'Import settings';
$string['increaseby'] = 'Increase by';
$string['increaseby_help'] = 'The amount of points to award the student.';
$string['increasemsg'] = 'Optional message';
$string['increasemsg_help'] = 'When a message is provided, it is added to the notification.';
$string['invalidpointscannotbenegative'] = 'Points cannot be negative.';
$string['levelbadges'] = 'Level badges override';
$string['levelbadges_help'] = 'Upload images to override the designs provided by the badge theme.';
$string['levelup'] = 'Level up!';
$string['manualawardsubject'] = 'You were awarded {$a->points} points!';
$string['manualawardnotification'] = 'You were awarded {$a->points} points by {$a->fullname}.';
$string['manualawardnotificationwithcourse'] = 'You were awarded {$a->points} points by {$a->fullname} in the course {$a->coursename}.';
$string['manuallyawarded'] = 'Manually awarded';
$string['maxn'] = 'Max: {$a}';
$string['maxpointspertime'] = 'Max. points in time frame';
$string['maxpointspertime_help'] = 'The maxmimum number of points that can be earned during the time frame given. When this value is empty, or equals to zero, it does not apply.';
$string['messageprovider:manualaward'] = 'Level up! points manually awarded';
$string['missingpermssionsmessage'] = 'You do not have the required permissions to access this content.';
$string['mylevel'] = 'My level';
$string['navgroupladder'] = 'Team ladder';
$string['pluginname'] = 'Level up! Plus';
$string['points'] = 'Points';
$string['previewmore'] = 'Preview more';
$string['privacy:metadata:log'] = 'Stores a log of events';
$string['privacy:metadata:log:points'] = 'The points awarded for the event';
$string['privacy:metadata:log:signature'] = 'Some event data';
$string['privacy:metadata:log:time'] = 'The date at which it happened';
$string['privacy:metadata:log:type'] = 'The event type';
$string['privacy:metadata:log:userid'] = 'The user who gained the points';
$string['progressbarmode'] = 'Display progress towards';
$string['progressbarmode_help'] = '
When set to _The next level_, the progress bar displays the progress of the user towards the next level.

When set to _The ultimate level_, the progress bar will indicate the percentage of progression towards the very last level that users can attain.

In either case, the progress bar will remain full when the last level is attained.';
$string['progressbarmodelevel'] = 'The next level';
$string['progressbarmodeoverall'] = 'The ultimate level';
$string['ruleactivitycompletion'] = 'Activity completion';
$string['ruleactivitycompletion_help'] = '
This condition is met when an activity was just marked as complete, so long as the completion was not marked as failed.

As per the standard Moodle activity completion settings, teachers have full control over the conditions
needed to _complete_ an activity. Those can be individually set for each activity in the course and
be based on a date, a grade, etc... It is also possible to allow students to manually mark the activities
as complete.

This condition will only reward the student once.';
$string['ruleactivitycompletion_link'] = 'Activity_completion';
$string['ruleactivitycompletiondesc'] = 'An activity or resource was successfully completed';
$string['rulecoursecompletion'] = 'Course completion';
$string['rulecoursecompletion_help'] = 'This rule is met when a course is completed by a student.

__Note:__ Students will not instantaneously receive their points, it takes a little while for Moodle to process course completions. In other words, this requires a _cron_ run.';
$string['rulecoursecompletion_link'] = 'Course_completion';
$string['rulecoursecompletiondesc'] = 'A course was completed';
$string['rulecoursecompletioncoursemodedesc'] = 'The course was completed';
$string['rulecourse'] = 'Course';
$string['rulecourse_help'] = 'This condition is met when the event occurs in the course specified.

It is only available when the plugin is used for the whole site. When the plugin is used per course, this condition becomes ineffective.';
$string['rulecoursedesc'] = 'The course is: {$a}';
$string['rulegradeitem'] = 'Specific grade item';
$string['rulegradeitem_help'] = 'This condition is met when a grade is given for the grade item specified.';
$string['rulegradeitemdesc'] = 'The grade item is \'{$a->gradeitemname}\'';
$string['rulegradeitemdescwithcourse'] = 'The grade item is: \'{$a->gradeitemname}\' in \'{$a->coursename}\'';
$string['rulegradeitemtype'] = 'Grade type';
$string['rulegradeitemtype_help'] = 'This condition is met when the grade item is of the required type. When an activity type is selected, any grade originating from this activity type would match.';
$string['rulegradeitemtypedesc'] = 'The grade is a \'{$a}\' grade';
$string['ruleusergraded'] = 'Grade received';
$string['ruleusergraded_help'] = 'This condition is met when:

* The grade was received in an activity
* The activity specified a passing grade
* The grade met the passing grade
* The grade is _not_ based on ratings (e.g. in forums)
* The grade is point-based, not scale-based

This condition will only reward the student once.';
$string['ruleusergradeddesc'] = 'The student received a passing grade';
$string['sendawardnotification'] = 'Send award notification';
$string['sendawardnotification_help'] = 'When enabled, the student will receive a notification that they were awarded points. The message will contain your name, the amount of points, and the name of the course if any.';
$string['shortcode:xpteamladder'] = 'Display a portion of the team ladder.';
$string['shortcode:xpteamladder_help'] = '
By default, a portion of the team ladder surrounding the current user will be displayed.

```
[xpteamladder]
```

To display the top 5 teams instead of the teams neighbouring those of the current user, set the parameter `top`. You can optionally set the number of teams to display by giving `top` a value, like so: `top=20`.

```
[xpteamladder top]
[xpteamladder top=15]
```

A link to the full ladder will automatically be displayed below the table if there are more results to be displayed, if you do not want to display such link, add the argument `hidelink`.

```
[xpteamladder hidelink]
```

By default, the table does not include the progress column which displays the progress bar. If such column has been selected in the additional colums in the ladder\'s settings, you can use the argument `withprogress` to display it.

```
[xpteamladder withprogress]
```

Note that when the current user belongs to multiple teams, the plugin will use the one with the best rank as reference.
';
$string['unabletoidentifyuser'] = 'Unable to identify user.';
$string['unknowngradeitemtype'] = 'Unknown type ({$a})';
$string['uptoleveln'] = 'Up to level {$a}';
$string['themestandard'] = 'Standard';
$string['theyleftthefollowingmessage'] = 'They left the following message:';
$string['timeformaxpoints'] = 'Time frame for max. points';
$string['timeformaxpoints_help'] = 'The time frame (in seconds) during which the user cannot receive more than a certain amount of points.';
$string['visualsintro'] = 'Customise the appearance of the levels, and the points.';

// Deprecated since v1.7.
$string['enablegroupladder'] = 'Enable group ladder';
$string['enablegroupladder_help'] = 'When enabled, students can view a leaderboard of the course groups. The group points are computed from the points accrued by the members of each group. This currently only applies when the plugin is used per course, and not for the whole site.';
