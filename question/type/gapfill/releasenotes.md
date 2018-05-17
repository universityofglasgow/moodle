### Version 1.972 of the Moodle Gapfill question type May 2018
Fixed a bug where correct answers were shown even thought it was turned off in the quiz settings checkbox.
Thanks to Matthias Giger and contributors to the Moodle German language for reporting this. Fixed
a bug introduced in the last release whereby the value of optionaftertext was not being saved. Thanks
to Elton LaClare for reporting that. Additional PHPDocs, code standards compliance and confirmation that 
it works with Moodle 3.5.

Implemented privacy API for GDPR compliance, see discussion here
https://moodle.org/mod/forum/discuss.php?d=365857

### Version 1.971 of the Moodle Gapfill question type Feb 2018
Bug fix for issue where dragdrop did not work on iOS. Improvements in code standards compliance.

Replaced various hard coded strings with get_string calls to allow for translation. Thanks to Dinis Medeiros for reporting this. 

The text in the letter hint prompt that appears in the Multiple tries section can be customised via the language pack. See instructions
https://docs.moodle.org/en/Language_customisation#Changing_words_or_phrases

### Version 1.97 of the Moodle Gapfill question type Feb 2018
Letter hints, new feature which only works when Interactive with multiple tries behaviour is used. A new checkbox in the question 
creation form toggles letterhints mode. This takes effect when an interactive question behaviour is selected. There is a global
checkbox setting for letterhints. If this is on, when a new question is created hints will be inserted into the first and second 
boxes under multiple tries block. If it is toggled on with an existing question the hints will have to be added by hand in the 
multiple tries section. Then when a student gives an incorrect response they will be given incrementing letters from the correct
 answer when they press try again. Thanks to Elton LaClare for the idea for this and to his employer Sojo University Japan 
 http://www.sojo-u.ac.jp/en for funding.

Bug fix that broke the display of the quiz menu when optionsaftertext was selected but gapfill mode was selected. Thanks to 
Lizardo Flores for reporting this.

### Version 1.961 of the Moodle Gapfill question type Dec 2017
Mainly a bugfix where MS SQL server installations would not create the gapfill settings table.
My thanks to marisol castro for reporting this. Improvements to phpdoc comments

### Version 1.96 of the Moodle Gapfill question type Oct 2017
Per gap feedback. This is a significant new feature and allows the creation of feedback that is
displayed dependent on if the student gave a correct or incorrect response on a per-gap basis The feedback is
entered by clicking a new button Gap settings which is shown under the question text field. This
toggles the screen to a grey colour and makes the text uneditable. Clicking a "gap" pops up a dialog
with fields for correct or incorrect response. Most HTML is stripped when the feedback is saved. Bold, 
Italic, Underscore and hyperlinks are retained. The feedback area does not support images. It has been tested
with the contents of a 10K file (though that would not be a sensible use of the feature).
Substantial improvements to amount of phpdoc comments, which is only of benefit to developers

### Version 1.95 of the Moodle Gapfill question type June 2017
New setting optionsaftertext can be to show the draggable options after the text. Thanks to Elton LaClare for the inspiration to do this.Fixed a bug where if there were multiple questions on a single page the draggables would become disabled after the first submit. Added behat featurefile add_quiz to test in 
quiz rather than just in preview mode. Added dragging of selections (previously it was
only type in). Configured up .travis.yml so testing is run every time there is a 
git commit. Made code tweaks to comply with results (e.g. csslint)

### Version 1.94 of the Moodle Gapfill question type February 2017
This is a minor release with a css fix and improvements to the mobile app code.
Thanks to Chris Kenniburg for the CSS fix to remove the comma before focus. Added
fix to renderer.php so select element list shows down arrows on android mobile.

In the mobile app answer option selection is more obvious. For dragdrop
questions there is now a prompt that says "Tap to select then tap to drop" as with
the core question types. Thanks to Elton LaClare for the mobile app feedback.

### Version 1.93 of the Moodle Gapfill question type February 2017
This release was made possible through the support of Sojo University Japan. 
http://www.sojo-u.ac.jp/en/ . Many thanks to Elton LaClare and Rob Hirschel.

Added remote addon support for the Moodle mobile app. CSS to give indication of onfocus in text imput boxes, subtle change in 
background color on hover over draggables. Other CSS tweaks to size of input and draggables. Fixed #25 on github

### Version 1.92 of the Moodle Gapfill question type contributed by Marcus Green
CSS to improve dropdowns on chrome mobile, discard gaps in wrong answers which improves display in feedback for dropdowns.
Removed setting of height in em in styles.css which was breaking the display on iOS. 

### Version 1.91 of the Moodle Gapfill question type contributed by Marcus Green
[.+] will make any text a valid answer and if left empty will not show the .+ as aftergap feedbak

### Version 1.9 of the Moodle Gapfill question type contributed by Marcus Green

In the admin interface there is now a link for importing the sample questions into a course.
This is a convenience way of doing a standard XML file question import.

Fixed issue where extended characters were not handled correctly. Have tested with 
accented French and Spanish words, Cyrillic and Hindi. Thanks for the feedback to Eduardo Montesinos, 
Mariapaola Cirelli, Ellen Spertus and others

Fixed issue where in interactive mode an incorrect answer would show empty braces (typically [])
where the answer in braces would have been shown in other modes.

### Version 1.8 of the Moodle Gapfill question type contributed by Marcus Green
Fixed a bug by adding checking for initialisation of array values. Discussed here
https://moodle.org/mod/forum/discuss.php?d=314487#p1274939. Thanks to Ellen Spertus, 
Al Rachels and others for the feedback on this.

Added a value in settings so the default for case sensitive can be set
Updated the export of xml code so it adds information on the version of the Gapfill
plugin and the version of Moodle that ran the export. This data can be useful
for tracking down issues (it means I don't have to get back to people asking for
 versions which people may not know and might get wrong).

The | symbol will now be recognised as an or operator even
when regular expression processing is turned off. This is handy for programming language
and math questions that use characters treated as special such as \/?* etc.


### Version 1.7 of the Moodle Gapfill question type contributed by Marcus Green
This is maintenance version with no new features. The main purpose of this version is
to ensure the question type will work with Moodle 2.9. This is required because the
JQuery code in the previous version of Gapfill would not work with 2.9. The versions
of JQuery, JQuery UI and touchpunch (for mobile support) have been updated. This addresses
some issues with drag and drop when using MS IE.  The calls are taken from the way JQuery is 
used in the ordering question type. Credit to Gordon Bateson for this.

There is a fix to ensure proper handling of string comparison. Previously 
tolower was used which would not work correctly with text containing accents. 
This has been changed to use mb_lower. Another issue was that a gap like 
[cat|dog] would match bigcat and catty and adog and doggy. This is now fixed.

### Version 1.6 of the Moodle Gapfill question type contributed by Marcus Green
When fixed gapsize the width of a gap such as [cat|tiger] will be the width of tiger not cat|tiger, i.e. 5 not 9

When display right answer is selected in the quiz settings the correct answer will be displayed within the question delimiters.
If the correct answer is [cat] and you enter[dog] the answer will show dog [cat] (with dog in red followed by a tick). 
Thanks to Gordon McLeod of Glasgow University for inspiring this feature.

When using deferred feedback zero marks were given overall when any gaps were blank. This is now fixed

### Version 1.5 of the Moodle Gapfill question type contributed by Marcus Green
This version has two significant new feature is the double knot or !! and the fixedgapsize setting. 
The use of !! indicates that a gap can be left empty and considered a correct response.

This is of particular use with the | or operator where one or more answers or a blank will be considered correct e.g. [cat|dog|!!]. 

As part of this change the calculation of maximum score per question instance has been modified, so "The [cat] sat on the [!!]" 
each gap will be worth 1 mark. This is necessary to ensure that if a value is put in the [!!] space a mark will be lost.

The fixedgapsize settings makes all gaps the same size as the biggest. This stops size being a clue to the correct answer.

The upgrade.php file has been tweaked to use XMLDB to fix issues with databases other than MySQL.

### Version 1.4 of the Moodle Gapfill question type contributed by Marcus Green
This release has one bug fix and one new feature. The new feature is support for drag and drop
on touch enabled devices such as iphone, ipad and android. This is by adding in the JQuery touchpunch library into
the renderer.php file. Many thanks to Adam Wojtkiewicz who suggested and tested this solution. 

There was a bug in the db/install.xml file with some of the next previous values being incorrect and so preventing a fresh 
installation on Moodle 2.4.

The elevator pitch for this question type is as follows

"The Gapfill question type is so easy use, the instructions require one 7 word sentence. Put square braces around
the missing words."


### Version 1.3 of the Moodle Gapfill question type contributed by Marcus Green
The main new feature is disableregex which switches from regular expressions for 
matching the given answer with the stored answer to do a plain string comparison. This
can be useful for maths, HTML and programming questions. In this mode the characters that have a
special meaning in regular expressions are treated as plain strings. 
This feature appears as a checkbox in the More Options section of the question editing form. The default
for this option can be set in the admin interface so you could set this to be checked by default for every
new question.

I have included a file called sample_questions.xml in with the source code that can be imported 
to illustrate the features.

It is now possible to have drag and drop with distractors in "answers in any order" mode. This is where
each field contains the same set of strings separated by the | (or) operator. In dragdrop and dropdown mode
these will be broken into separate selectable answer options. This builds on the code in the previous
version that allowed this approach in plain gapfill mode and can discard duplicate correct answers. 

This version has been modified to work with Moodle 2.6. Previous versions of this quesiton type 
will throw an error when used with Moodle 2.6 which is linked to a rule on the question text editing box.

This version has been tested mainly in Moodle 2.5 and for about a month with early versions of Moodle 2.6.
It has been installed and briefly tested with 2.4 but it will not work at all with versions of Moodle prior 
to 2.1

It is now possible to have commas in the answer strings and to have commas in distractors by escaping
them with a backslash.

A bug has been fixed that was stopping distractor options being exported to xml. A bug has been fixed in 
the CSS which meant that there was no border to the gaps when viewed in chrome.

Thanks to Adam Wojtkiewicz testing and feedback. I have implemented his suggestion for a minor modification
to the Javascript to ensure it works along with Geogebra. He has made some suggestions to allow the dragdrop 
code work on more mobile platforms which I hope to look at closely in the near future.

Thanks for testing and feedback and comments from Joseph Rézeau, Frankie Kam and Nigel Robertson and 
Wayne Prescott.

The elevator pitch for this question type is as follows

"The Gapfill question type is so easy use, the instructions require one 7 word sentence. Put square braces around the missing words."
