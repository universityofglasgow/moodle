# Changelog

### 1.6.3 (2025-10-14)

- assure compatibility with Moodle 5.1
- internal: update CI for Moodle 5.1
- internal: avoid debugging notice in tests with Moodle 5.0

### 1.6.2 (2025-04-14)

- assure compatibility with Moodle 5.0

### 1.6.1 (2025-01-12)

- bugfix: fix export of question text with embedded images
- internal: add unit tests for Windows based servers

### 1.6.0 (2024-12-10)

- improvement: for quizzes that allow multiple attempts, add option to export only first/last/best attempt

### 1.5.0 (2024-12-03)

- improvement: store export settings in user preferences
- improvement: allow grouping all responses of an attempt in one PDF
- bugfix: fixed bad page breaking when footer is enabled
- bugfix: page format US Letter now working properly
- internal: update CI for upcoming Moodle version


### 1.4.0 (2024-11-03)

- improvement: add option for flatter folder hierarchy in archive

### 1.3.0 (2024-10-27)

- improvement: add option to convert relative font-size from rem to percent, working around MDL-67360
- improvement: add option to include footer with page number
- improvement: add option to include word and character count
- bugfix: linebreaks are no longer lost when creating PDF output from plain-text summary

### 1.2.0 (2024-10-07)

- assure compatibility with freshly released Moodle 4.5 LTS
- improvement: add possibility to export formatted responses to PDF
- internal: added tests
- internal: remove temporary CI change after bug in moodle-plugin-ci was fixed

### 1.1.0 (2024-08-23)

- improvement: add setting to choose between ordering by first/last or last/first name
- improvement: add setting to use shorter names and thus avoid problem with Windows' unzipper
- internal: temporary change CI to work around problem with moodle-plugin-ci

### 1.0.2 (2024-08-08)

- bugfix: avoid corruption of ZIP file in case of errors
- improvement: in case of an error, include detailed message in ZIP file
- internal: add test with empty response to essay question

### 1.0.1 (2024-04-26)

- bugfix: download button was not working properly
- testing: add behat test for download button
- internal: simplified CI

### 1.0.0 (2024-04-19)

Initial release, inspired by the moodle-quiz_downloadsubmissions plugin. Main features:

- compatible with all current versions of Moodle, i. e. 4.1 and higher
- support for course groups
- support for attachments
