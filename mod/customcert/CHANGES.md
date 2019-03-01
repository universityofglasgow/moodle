# Changelog

All notable changes to this project will be documented in this file. 

Note - All hash comments refer to the issue number. Eg. #169 refers to https://github.com/markn86/moodle-mod_customcert/issues/169.

## [3.4.7] - 2018-12-31

### Changed

- Make it clear what element values are just an example when previewing the PDF (#144).

### Fixed

- Missing implementation for privacy provider (#260).
- Use course module context when calling format_string/text (#200).
- Exception being thrown when adding the 'teachername' element to site template (#261).

## [3.4.6] - 2018-12-20
### Added

- GDPR: Add support for removal of users from a context (see MDL-62560) (#252).
- Images can be made transparent (#186).
- Set default values of activity instance settings (#180).
- Allow element plugins to control if they can be added to a certificate (#225).
- Allow element plugins to have their own admin settings (#213).
- Added plaintext language variants for email bodies (#231).
- Added possibility to selectively disable activity instance settings (#179).

### Changed

- Allow verification of deleted users (#159).
- The 'element' field in the 'customcert_elements' table has been changed from a Text field to varchar(255) (#241).
- The 'Completion date' option in the 'date' element is only displayed when completion is enabled (#160).
- Instead of assuming 2 decimal points for percentages, we now make use of the decimal value setting, which the
  function `grade_format_gradevalue` does by default if no decimal value is passed.

### Fixed

- Issue with scales not displaying correctly (#242).
- The report now respects the setting 'Show user identity' (#224).
- Removed incorrect course reset logic (#223).
- Description strings referring to the wrong setting (#254).

## [3.4.5] - 2018-07-13
### Fixed

- Use custom fonts if present (#211).
- Fix broken SQL on Oracle in the email certificate task (#187).
- Fixed exception when clicking 'Add page' when template has not been saved (#154).
- Only email teachers who are enrolled within the course (#176).
- Only display teachers who are enrolled within the course in the dropdown (#171).

### Changed

- Multiple UX improvements to both the browser and mobile views (#207).
  - One big change here is combining the report and activity view page into one.
- Allow short dates with leading zeros (#210).

## [3.4.4] - 2018-06-26
### Fixed

- Respect filters in the 'My certificates' and 'Verify certificate' pages (#197).
- Fixed reference to 'mod/certificate' capability.
- Provided access to necessary web services for mobile functionality to the local_mobile plugin (#202).

### Changed

- Multiple UX improvements to both the browser and mobile views (#203).

## [3.4.3] - 2018-06-07
### Fixed

- Hotfix to prevent misalignment of 'text' elements after last release (#196).

## [3.4.2] - 2018-06-06
### Added
- Mobile app support (#70).
```
    This allows students to view the activity and download
    their certificate. It also allows teachers to view the
    list of issued certificates, with the ability to revoke
    any.
    
    This is for the soon-to-be released Moodle Mobile v3.5.0 
    (not to be confused with your Moodle site version) and
    will not work on Mobile versions earlier than this.
    
    If you are running a Moodle site on version 3.4 or below
    you will need to install the local_mobile plugin in order
    for this to work.
    
    If you are running a Moodle site on version 3.0 or below
    then you will need to upgrade.
```
- More font sizes (#148).
- Added new download icon.
```
    This was done because the core 'import' icon was mapped
    to the Font Awesome icon 'fa-level-up' which did not look
    appropriate. So, a new icon was added and that was mapped
    to the 'fa-download' icon.
```
### Fixed
- No longer display the 'action' column and user picture URL when downloading the user report (#192).
- Elements no longer ignore filters (#170).

## [3.4.1] - 2018-05-17
### Added
- GDPR Compliance (#189).

### Fixed
- Race condition on certificate issues in scheduled task (#173).
- Ensure we backup the 'verifyany' setting (#169).
- Fixed encoding content links used by restore (#166).

## [3.3.9] - 2017-11-13
### Added
- Added capability ```mod/customcert:verifyallcertificates``` that provides a user with the ability to verify any certificate
  on the site by simply visiting the ```mod/customcert/verify_certificate.php``` page, rather than having to go to the
  verification link for each certificate.
- Added site setting ```customcert/verifyallcertificates``` which when enabled allows any person (including users not logged in)
  to be able to verify any certificate on the site, rather than having to go to the verification link for each certificate.
  However, this only applies to certificates where ```Allow anyone to verify a certificate``` has been set to ```Yes``` in the
  certificate settings.
- You can now display the grade and date of all grade items, not just the course and course activities.
- Text has been added above the ```My certificates``` list to explain that it contains certificates that have been issued to
  avoid confusion as to why certificates may not be appearing.

### Changed
- The course full name is now used in emails.

### Fixed
- Added missing string used in course reset.

## [3.3.8] - 2017-09-04
### Added
- New digital signature element (uses existing functionality in the TCPDF library).
- Ability to duplicate site templates via the manage templates page.
- Ability to delete issued certificates for individual users on the course report page.

### Changed
- Removed usage of magic getter and abuse of ```$this->element```. The variable ```$this->element``` will still be
  accessible by any third-party element plugins, though this is discouraged and the appropriate ```get_xxx()```
  method should be used instead. Using ```$this->element``` in ```definition_after_data()``` will no longer work.
  Please explicitly set the value of any custom fields you have in the form.

### Fixed
- Added missing ```confirm_sesskey()``` checks.
- Minor bug fixes.

## [3.3.7] - 2017-08-11
### Added
- Added much needed Behat test coverage.

### Changed
- Minor language string changes.
- Made changes to the UI when editing a certificate.
  - Moved the 'Add element' submit button below the list of elements.
  - Added icon next to the 'Delete page' link.
  - Changed the 'Add page' button to a link, added an icon and moved it's location to the right.
  - Do not make all submit buttons primary. MDL-59740 needs to be applied to your Moodle install in order to notice the change.

### Fixed
- Issue where the date an activity was graded was not displaying at all.

## [3.3.6] - 2017-08-05
### Changed
- Renamed the column 'size' in the table 'customcert_elements' to 'fontsize' due to 'size' being a reserved word in Oracle.
