Changelog
=========

v1.14.1
-------

Bug fixes

- Address compatibility issues with Moodle 3.4

v1.14.0
-------

Quality of life

- Adjusted styles and rules for better flow and readability

Bug fixes

- Restoring backup into existing course honours delete contents option
- The restore process could be interupted while restoring drops
- Prevent extraneous grade rules from being restored

Technical changes

- Raised minimum required version to Moodle 3.4
- Compatibility with Moodle 4.2

v1.13.2
-------

Quality of life

- Added German translations
- Updated French translations

Technical changes

- Compatibility with 4.0 message provider constants
- Escape output of location in log table
- Other inconsequential minor changes

Acknowledgements

- We would like to thank [Ruhr-Universität Bochum](https://moodle.ruhr-uni-bochum.de) and [Hochschule München](https://moodle.hm.edu) for translating and sharing the plugin in German.

v1.13.1
-------

Bug fixes

- Some JS files were not loaded in development mode

v1.13.0
-------

New features

- Rule to award points for completing a section
- Added level visuals showcasing a seed growing into a tree
- Added level visuals showcasing a house constructed to completion
- Added alternative point symbols: brick, droplet 💧, leaf 🍃, lightbulb 💡, puzzle 🧩 and star ⭐

Quality of life

- Additional identity columns included in downloadable report
- Additional identity columns included in downloadable logs
- Team names included in downloadable logs
- Renamed "Team ladder" to "Team leaderboard"
- The team leaderboards is now found under the "Leaderboard" tab

Technical changes

- Compatibility with Moodle 4.1

v1.12.1
-------

New features

- Drops can be deleted (Pro, Multi)

v1.12.0
-------

New features

- Drops: award points by placing code snippets anywhere (Pro, Multi)
- Anonymise leaderboard by first name and initial (Pro, Multi)
- User leaderboard can be downloaded to CSV, XLS, etc. (Pro, Multi)
- Team leaderboard can be downloaded to CSV, XLS, etc. (Pro, Multi)

Quality of life

- Team names are displayed in the report (Pro, Multi)
- Updated Chinese language strings

Bug fixes

- Minor bug fixes and improvements

Technical changes

- Raised minimum required version to Moodle 3.3
- Compatibility with Moodle 4.0
- Compatibility with PHP 8
- Support for optional activation of Level Up XP+ for shared hosting (beta)

Additional notes

- Level Up Plus is renamed Level Up XP+.

v1.11.2
-------

Bug fixes

- Adapt mobile styling for compatibility with latest Moodle app
- Group selector was not always usable in Moodle app
- Fix hardcoded language string in Moodle app

v1.11.1
-------

Quality of life

- Updated French language strings

Bug fixes

- Included missing language string for course rule
- Converted hardcoded string to translatable string
- Log could cause fatal errors when invalid reasons were used

v1.11.0
-------

New features

- New rule to target an activity by its name
- The logs can be downloaded to CSV, XLS, etc.

Quality of life

- Updated French language strings

Bug fixes

- Points based on a grade could be 1 point off due to rounding errors
- Restoring backups with missing grade items resulted in a fatal error
- The filters on the log table could not be removed when empty

Technical changes

- Compatibility with Moodle 3.11

v1.10.4
-------

Bug fixes

- Team leaderboard was inaccessible in Moodle 3.1

v1.10.3
-------

Bug fixes

- Log retention setting incorrectly used default value of 3 months

v1.10.2
-------

Quality of life

- Additional options for log retention duration
- Added Chinese language strings
- Updated French language strings

Bug fixes

- Prevent rules from displaying debug notices when instantiated

v1.10.1
-------

Bug fixes

- Team leaderboard using cohorts raised exceptions for Moodle 3.4 and older

v1.10.0
-------

New features

- The points of teams with less members can be compensated in the leaderboard
- Administrators can enforce the anonymity of the leaderboards
- Add support for the new capability to control access to the logs

Bug fixes

- Fixed an error when sending an award notification using Moodle 3.2 and older

v1.9.1
------

Bug fixes

- Import was not available with Moodle 3.3 and older (Pro, Multi)
- Corrected invalid reference to a language string

v1.9.0
------

New features

- Points can be awarded manually to individual students
- Added import points from CSV file (Pro, Multi)
- Support additional Privacy API requirement (core_userlist_provider)

Quality of life

- Log page display a nicer notice when empty

Bug fixes

- Relax cheat guard to always count course/activity completion

v1.8.0
------

New features

- Grade-based rewards are now possible
- Default grade rules can be created by admins
- Added shortcode `xpteamladder` to display the team leaderboard
- The log page displays where the points originated from

v1.7.3
------

- Update Moodle Classic add-on to v1.1.1
- Fix issue with team leaderboard on Moodle Classic

v1.7.2
------

- Handle removal of httpswwwroot in Moodle 3.8
- Fix group picture related issue for Moodle 3.3 and older

v1.7.1
------

- Limit the team leaderboard to the groups from the course's default grouping

v1.7.0
------

- _Group_ leaderboards were renamed _Team_ leaderboards
- Team leaderboards now support course groups, cohorts and IOMAD companies and departments
- Team leaderboards can be anonymised
- The visibility of teams' points and progress in the leaderboard can be set
- The ordering of the team leaderboard can be set to points or progress
- Minor bug fixes and improvements

Some of these changes were sponsored by Xi'an Jiaotong-Liverpool University.

v1.6.1
------

- Display instructions on the mobile app info page
- Default badges did not always display properly on mobile
- Link in mobile app could be missing when no student had earned points
- Slight performance improvement when fetching mobile settings
- Include progress bar setting in backups

v1.6.0
------

- The progress bar can be set to display the overall progress

This change was sponsored by Xi'an Jiaotong-Liverpool University.

v1.5.1
------

- Fixed issue with course selector when theme designer is off

v1.5.0
------

- New rule for selecting a specific course
- Increase minimum required version to Moodle 3.1

v1.4.5
------

- Remove usage of function deprecated in Moodle 3.6
- Added Turkish translation
- Added CLI tools to import/export language strings

v1.4.4
------

- Fixed missing database column after installation

v1.4.3
------

- Workaround backup issue caused by Moodle's limitations (MDL-45441)

v1.4.2
------

- Support backup and restore of the group ladder setting

v1.4.1
------

- Fix upgrade path leading to missing database column

v1.4.0
------

- Support for group leaderboards

v1.3.3
------

- Support for excluding other company's users from ladder in IOMAD

v1.3.2
------

- Fixed broken leaderboard on mobile when alternate ranking is used

v1.3.1
------

- Fixed bug incorrectly hiding level on user's profile
- Hide others' levels on profile when anonymity is enabled

v1.3.0
------

- Display a person's level when viewing their profile

v1.2.3
------

- Fixed bug causing some activity completions to be ignored

v1.2.2
------

- French translation added

v1.2.1
------

- Support for Moodle Mobile 3.5
- Fixed loose check when verifying whether user could access group

v1.2.0
------

- GDPR compliance
- Prevent very rare exception during event collection

v1.1.1
------

- Mobile support is compatible with local_mobile

v1.1.0
------

- Moodle Mobile app support
- Fixed invalid link to profile in log
- Badge theme setting was not backed up
- Badges resolution increased
- Minor bug fixes and improvements
