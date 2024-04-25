# Filedownloader

![](https://github.com/ethz-let/quiz-report_filedownloader/actions/workflows/moodle-ci.yml/badge.svg)

## What it is
The filedownloader plugin lets you download files attached to questions within a quiz.
The plugin lets you choose which questiontypes will be included in the download. 
Also it will create a textfile that cointains user and meta information for each downloaded file.

## Preferences

#### Included Questiontypes
Lets you choose which questiontypes will be included while using the plugin.

#### Anonymization
For each included user the plugin will generate an USERINFO.txt file that contains

 * Firstname* 
 * Lastname*
 * id-number
 * Database user id
 * E-mail*
 * Question name
 * Question id
 * Course name
 * Course id

(*) if anonymization is disabled.

With anonymization enabled, names and e-mail adresses will also be cut from the output folder names.

#### Choosable filestructure
This option enabled will allow users to switch between two file structures with each download.

1. Option: `Question / User / Attempt / file`
1. Option: `Question / User_Attempt_file`

## Installation
1. Extract the contents of the downloaded zip to `mod/quiz/report/`.
1. Rename the extracted folder to `filedownloader`.
1. Start the Moodle upgrade procedure.

## Contributors
ETH Zürich (Lead maintainer)
Thomas Korner (Service owner, thomas.korner@let.ethz.ch)