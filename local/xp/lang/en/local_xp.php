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
$string['adddrop'] = 'Add a drop';
$string['afterimport'] = 'After import';
$string['anysection'] = 'Any section';
$string['anonymousgroup'] = 'Another team';
$string['anonymousiomadcompany'] = 'Another company';
$string['anonymousiomaddepartment'] = 'Another department';
$string['awardpoints'] = 'Award points';
$string['badgetheme'] = 'Level badges theme';
$string['badgetheme_help'] = 'A badge theme defines the default appearance of the badges.';
$string['categoryn'] = 'Category: {$a}';
$string['clicktoselectcourse'] = 'Click to select a course';
$string['clicktoselectgradeitem'] = 'Click to select a grade item';
$string['courseselector'] = 'Course selector';
$string['csvisempty'] = 'The CSV file is empty.';
$string['csvline'] = 'Line';
$string['csvfieldseparator'] = 'Field separator for CSV';
$string['csvfile'] = 'CSV file';
$string['csvfile_help'] = 'The CSV file must contain the columns __user__ and __points__. The column __message__ is optional and can be used when notifications are enabled. Note that the __user__ column understands user IDs, email addresses and usernames.';
$string['csvmissingcolumns'] = 'The CSV is missing the following column(s): {$a}.';
$string['currentpoints'] = 'Current points';
$string['currencysign'] = 'Points symbol';
$string['currencysign_help'] = 'With this setting you can change the meaning of the points. It will be displayed next to the amount of points each user has as a substitute for the reference to _experience points_.

Choose one of the provided symbols, or upload your own!';
$string['currencysignformhelp'] = 'The recommended image height is 18 pixels.';
$string['currencysignoverride'] = 'Points symbol override';
$string['currencysignxp'] = 'XP (Experience points)';
$string['custom'] = 'Custom';
$string['dropcollected'] = 'Drop collected';
$string['dropherea'] = 'Drop: {$a}';
$string['dropenabled'] = 'Enabled';
$string['dropenabled_help'] = 'A drop will not award any points unless it is enabled.';
$string['dropname'] = 'Name';
$string['dropname_help'] = 'The name of the drop for your reference. This is not displayed to users.';
$string['droppoints'] = 'Points';
$string['droppoints_help'] = 'The number of points to award when this drop is found.';
$string['drops'] = 'Drops';
$string['dropsintro'] = 'Drops are code snippets directly placed in content that award points when encountered by a user.';
$string['drops_help'] = '
In video games, some characters can _drop_ items or experience points on the ground for the player to pick up. These items and points are commonly referred to as drops.

In Level Up XP, drops are shortcodes (e.g. `[xpdrop abcdef]`) that an instructor can place in regular Moodle content. When encountered by a user, these drops will be _picked up_ and a certain amount of points will be awarded.

At present, drops are invible to the user and passively award points the first time they are encountered.

Drops can be use to cleverly award points when certain type of content is consumed by a student. Here are some ideas:

- Place a drop in the feedback of a quiz only visible for perfect scores
- Place a drop in deep content to reward their consumption
- Place a drop in an interesting forum discussion
- Place a drop in a hard-to-get-to page in a lesson module

[More info](https://docs.levelup.plus/xp/docs/how-to/use-drops?ref=localxp_help)
';
$string['displaygroupidentity'] = 'Display teams identity';
$string['displayfirstnameinitiallastname'] = 'Display first name and initial (e.g. Sam H.)';
$string['editdrop'] = 'Edit drop';
$string['enablecheatguard'] = 'Enable cheat guard';
$string['enablecheatguard_help'] = 'The cheat guard prevents students from being rewarded once they reach certain limits.

[More info](https://docs.levelup.plus/xp/docs/getting-started/cheat-guard?ref=localxp_help)
';
$string['errorunknowncourse'] = 'Error: unknown course';
$string['errorunknowngradeitem'] = 'Error: unknown grade item';
$string['event_section_completed'] = 'Section completed';
$string['filtergradeitems'] = 'Filter grade items';
$string['filtershortcodesrequiredfordrops'] = 'The plugin [Shortcodes]({$a->url}) needs to be installed and enabled to use drops, it is freely available from [moodle.org]({$a->url}). This plugin will also unlock [Level Up XP\'s shortcodes]({$a->shortcodesdocsurl}).';
$string['keeplogsdesc'] = 'The logs are playing an important role in the plugin. They are used for
the cheat guard, for finding the recent rewards, and for some other things. Reducing the time for
which the logs are kept can affect how points are distributed over time and should be dealt with carefully.';
$string['gradeitemselector'] = 'Grade item selector';
$string['gradeitemtypeis'] = 'The grade is a {$a} grade';
$string['gradereceived'] = 'Grade received';
$string['gradesrules'] = 'Grades rules';
$string['gradesrules_help'] = '
The rules below determine when students earn points for the grades they receive.

Students will earn as many points as their grade.
A grade of 5/10, and a grade of 5/100 will both award the student 5 points.
When a student\'s grade changes multiple times, they will earn points equal to the maximum grade they have received.
Points are never taken away from students, and negative grades are ignored.

Example: Alice submits an assignment, and receives the mark of 40/100. In _Level Up XP_, Alice receives 40 points for her grade.
Alice reattempts her assignment, but this time her grade is lowered to 25/100. Alice\'s points in _Level Up XP_ do not change.
For her final attempt, Alice scores 60/100, she earns 20 additional points in _Level Up XP_, her total of points earned is 60.

[More at _Level Up XP_ documentation](https://docs.levelup.plus/xp/docs/how-to/grade-based-rewards?ref=localxp_help)
';
$string['groupanonymity'] = 'Anonymity';
$string['groupanonymity_help'] = 'This setting controls whether participants can see the names of the teams they do not belong to.';
$string['groupladder'] = 'Team leaderboard';
$string['groupladdercols'] = 'Columns';
$string['groupladdercols_help'] = 'This setting determines which columns are displayed aside from the teams ranks and names.

The __Points__ column displays the points of the team.
This value may have been compensated depending on the _Ranking strategy_ chosen.

The __Progress__ column displays the overall progression of the team towards all of its members reaching the ultimate level.
In other words, the progress can only attain 100% when all team members are at the maximum level. Note that the number of
points remaining, displayed next to the progress bar, may be confusing when teams are unbalanced and points not compensated
as teams with more members will have more points remaining than others, even though their progressions may be similar.

Press the CTRL or CMD key while clicking to select more than one column, or to unselect a selected column.';
$string['groupladdersource'] = 'Team up students using';
$string['groupladdersource_help'] = 'The team leaderboard displays a ranking of an aggregate of the students\' points.
The value you choose determines what _Level Up XP_ uses to group the students together.
When set to _Nothing_ the team leaderboard will not be available.

To limit the _Course groups_ that appear in the leaderboard, you may create a new grouping containing the relevant groups, and then set this grouping as the _Default grouping_ in the course settings.';
$string['groupname'] = 'Team name';
$string['grouporderby'] = 'Ranking strategy';
$string['grouporderby_help'] = 'Determines what is the basis for ranking the teams.

When set to __Points__, the teams are ranked based on the sum of the points of its members.

When set to __Points (with compensation)__, the points of teams with less members than others are compensated using their team\'s average per member. For example, if a team lacks 3 members, they receive points equal to three times their average per member. This creates a balanced ranking where all teams have equal chances.

When set to __Progress__, the teams are ranked based on their overall progression towards all of its members reaching the ultimate level, without compensating their points. You may want to use _Progress_ when the teams are unbalanced, for example when some teams have a lot more members than others.';
$string['grouppoints'] = 'Points';
$string['grouppointswithcompensation'] = 'Points (with compensation)';
$string['groupsourcecoursegroups'] = 'Course groups';
$string['groupsourcecohorts'] = 'Cohorts';
$string['groupsourceiomadcompanies'] = 'IOMAD companies';
$string['groupsourceiomaddepartments'] = 'IOMAD departments';
$string['groupsourcenone'] = 'Nothing, the leaderboard is disabled';
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
$string['levelup'] = 'Level up!'; // The action, not the brand!
$string['manualawardsubject'] = 'You were awarded {$a->points} points!';
$string['manualawardnotification'] = 'You were awarded {$a->points} points by {$a->fullname}.';
$string['manualawardnotificationwithcourse'] = 'You were awarded {$a->points} points by {$a->fullname} in the course {$a->coursename}.';
$string['manuallyawarded'] = 'Manually awarded';
$string['maxn'] = 'Max: {$a}';
$string['maxpointspertime'] = 'Max. points in time frame';
$string['maxpointspertime_help'] = 'The maxmimum number of points that can be earned during the time frame given. When this value is empty, or equals to zero, it does not apply.';
$string['messageprovider:manualaward'] = 'Level Up XP points manually awarded';
$string['missingpermssionsmessage'] = 'You do not have the required permissions to access this content.';
$string['mylevel'] = 'My level';
$string['navdrops'] = 'Drops';
$string['navgroupladder'] = 'Team leaderboard';
$string['pluginname'] = 'Level Up XP+';
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
$string['reallyedeletedrop'] = 'Are you sure that you want to delete this drop? This action is not reversible.';
$string['reason'] = 'Reason';
$string['reasonlocation'] = 'Location';
$string['reasonlocationurl'] = 'Location URL';
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
$string['ruleactivitycompletioninfo'] = 'This condition matches when a student completes an activity or resource.';
$string['rulecmname'] = 'Activity name';
$string['rulecmname_help'] = 'This condition is met when the event occurs in an activity that is named as specified.

Notes:

- The comparison is not case sensitive.
- An empty value will never match.
- Consider using **contains** when the activity name includes [multilang](https://docs.moodle.org/en/Multi-language_content_filter) tags.';
$string['rulecmnamedesc'] = 'The activity name {$a->compare} \'{$a->value}\'.';
$string['rulecmnameinfo'] = 'Specifies the name of the activities or resources in which the action must occur.';
$string['rulecoursecompletion'] = 'Course completion';
$string['rulecoursecompletion_help'] = 'This rule is met when a course is completed by a student.

__Note:__ Students will not instantaneously receive their points, it takes a little while for Moodle to process course completions. In other words, this requires a _cron_ run.';
$string['rulecoursecompletion_link'] = 'Course_completion';
$string['rulecoursecompletiondesc'] = 'A course was completed';
$string['rulecoursecompletioncoursemodedesc'] = 'The course was completed';
$string['rulecoursecompletioninfo'] = 'This condition matches when a student completes a course.';
$string['rulecourse'] = 'Course';
$string['rulecourse_help'] = 'This condition is met when the event occurs in the course specified.

It is only available when the plugin is used for the whole site. When the plugin is used per course, this condition becomes ineffective.';
$string['rulecoursedesc'] = 'The course is: {$a}';
$string['rulecourseinfo'] = 'This condition requires that the action takes place in a specific course.';
$string['rulegradeitem'] = 'Specific grade item';
$string['rulegradeitem_help'] = 'This condition is met when a grade is given for the grade item specified.';
$string['rulegradeitemdesc'] = 'The grade item is \'{$a->gradeitemname}\'';
$string['rulegradeitemdescwithcourse'] = 'The grade item is: \'{$a->gradeitemname}\' in \'{$a->coursename}\'';
$string['rulegradeiteminfo'] = 'This condition matches for grades received for a particular grade item.';
$string['rulegradeitemtype'] = 'Grade type';
$string['rulegradeitemtype_help'] = 'This condition is met when the grade item is of the required type. When an activity type is selected, any grade originating from this activity type would match.';
$string['rulegradeitemtypedesc'] = 'The grade is a \'{$a}\' grade';
$string['rulegradeitemtypeinfo'] = 'This condition matches when the grade item is of the required type.';
$string['rulesectioncompletion'] = 'Section completion';
$string['rulesectioncompletion_help'] = 'This condition is met an activity is completed and that activity is the last activity to be completed within the section.';
$string['rulesectioncompletioninfo'] = 'This condition matches when the student completes all activities in a section.';
$string['rulesectioncompletiondesc'] = 'The section to complete is \'{$a->sectionname}\'';
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
$string['shortcode:xpdrop'] = 'Include a drop in the content.';
$string['shortcode:xpteamladder'] = 'Display a portion of the team ladder.';
$string['shortcode:xpteamladder_help'] = '
By default, a portion of the team leaderboard surrounding the current user will be displayed.

```
[xpteamladder]
```

To display the top 5 teams instead of the teams neighbouring those of the current user, set the parameter `top`. You can optionally set the number of teams to display by giving `top` a value, like so: `top=20`.

```
[xpteamladder top]
[xpteamladder top=15]
```

A link to the full leaderboard will automatically be displayed below the table if there are more results to be displayed, if you do not want to display such link, add the argument `hidelink`.

```
[xpteamladder hidelink]
```

By default, the table does not include the progress column which displays the progress bar. If such column has been selected in the additional colums in the leaderboard\'s settings, you can use the argument `withprogress` to display it.

```
[xpteamladder withprogress]
```

Note that when the current user belongs to multiple teams, the plugin will use the one with the best rank as reference.
';
$string['sectioncompleted'] = 'Section completed';
$string['sectiontocompleteis'] = 'The section to complete is {$a}';
$string['studentsearnpointsforgradeswhen'] = 'Students earn points for grades when:';
$string['unabletoidentifyuser'] = 'Unable to identify user.';
$string['unknowngradeitemtype'] = 'Unknown type ({$a})';
$string['unknownsectiona'] = 'Unknown section ({$a})';
$string['uptoleveln'] = 'Up to level {$a}';
$string['team'] = 'Team';
$string['teams'] = 'Teams';
$string['themestandard'] = 'Standard';
$string['theyleftthefollowingmessage'] = 'They left the following message:';
$string['timeformaxpoints'] = 'Time frame for max. points';
$string['timeformaxpoints_help'] = 'The time frame (in seconds) during which the user cannot receive more than a certain amount of points.';
$string['visualsintro'] = 'Customise the appearance of the levels, and the points.';

// Deprecated since v1.7.
$string['enablegroupladder'] = 'Enable group ladder';
$string['enablegroupladder_help'] = 'When enabled, students can view a leaderboard of the course groups. The group points are computed from the points accrued by the members of each group. This currently only applies when the plugin is used per course, and not for the whole site.';

// Deprecated since v1.10.2.
$string['for2weeks'] = 'For 2 weeks';
$string['for3months'] = 'For 3 months';
