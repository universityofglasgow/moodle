![GitHub Release](https://img.shields.io/github/v/release/PhilippImhof/moodle-quiz_essaydownload)
[![Automated code checks](https://github.com/PhilippImhof/moodle-quiz_essaydownload/actions/workflows/checks.yml/badge.svg)](https://github.com/PhilippImhof/moodle-quiz_essaydownload/actions/workflows/checks.yml) [![Automated testing](https://github.com/PhilippImhof/moodle-quiz_essaydownload/actions/workflows/testing.yml/badge.svg)](https://github.com/PhilippImhof/moodle-quiz_essaydownload/actions/workflows/testing.yml)

moodle-quiz_essaydownload
-------------------------

This is a quiz report plugin that allows bulk downloading of text answers and attachment files submitted in response to essay questions in a quiz.

It has been inspired by the [quiz_downloadsubmissions](https://github.com/IITBombayWeb/moodle-quiz_downloadsubmissions) plugin which offers similar functionality.


#### Installation

Install the plugin to the folder `$MOODLE_ROOT/mod/quiz/report/essaydownload`.

For more information, please see the [Moodle docs](https://docs.moodle.org/en/Installing_plugins).


#### Usage

1. Go to a quiz.
2. Click on "Results" in order to access the results tab.
3. From the dropdown menu, choose "Download essay responses".
4. Set the available options according to your needs.
5. Click "Download".

The plugin will then generate a ZIP archive containing the requested data and initiate
the download in your browser.

Note: No confirmation will be shown. Once you get your ZIP file, the work is done.


#### Grouping by attempt or question?

You can group the data in two different ways. If you group by attempt, your ZIP file will
have the following structure:

* StudentX/Question1
* StudentX/Question2
* StudentY/Question1
* StudentY/Question2

If you group by question, the structure will be as follows:

* Question1/StudentX
* Question1/StudentY
* Question2/StudentX
* Question2/StudentY

