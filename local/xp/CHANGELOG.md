Changelog
=========

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
