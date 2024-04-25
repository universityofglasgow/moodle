README file for the MUSIC THEORY question type

================================================================================

DESCRIPTION
-----------

This question type is meant for music theory instruction. It currently supports
the following exercises:

1) Note writing: The respondent is asked to enter a given note on the staff.
2) Note identification: The response is asked to identify a give note.
3) Keyboard Input: The respondent is asked to select a given key on the piano keyboard.
4) Key signature writing: The respondent is asked to enter a given key signature.
5) Key signature identification: The respondent is asked to identify a given major
or minor key signature.
6) Interval writing: The respondent is asked to enter a given interval above or
below a given note.
7) Interval identification: The respondent is asked to identify the quality and
size of a given interval.
8) Scale writing: The respondent is asked to enter a given scale.
9) Scale identification: The respondent is asked to identify a given scale.
10) Chord quality writing: Given a chord root and quality (e.g. 'G major', 'D minor'),
the respondent is asked to enter corresponding chord on the staff.
11) Chord quality identification: The respondent is asked to identify the quality
of a given chord (e.g. 'major', 'minor', etc.).
12) Harmonic function writing: The respondent is asked to enter a given harmonic
function in a given key (e.g. 'IV in D minor').
13) Harmonic function identification: The respondent is asked to identify the
harmonic function of a given chord in a given key.

When Javascript is enabled, answers are entered and displayed in an HTML5 graphical
user interface, using two separate Javascript components (Music Theory GUI and
Keyboard Input) packaged as YUI modules. All the exercises above are fully
functional without Javascript, except for 1) Note writing, 2) Note identification
and 3) Keyboard Input.

Each of the exercises above (except for #3, Keyboard Input) can also be configured
so that a specific question is randomly generated upon starting an activity. The
randomization can be based on a set of parameters configured when the question is
created or edited. For example, it is possible to configure an interval writing
question so that it asks for a perfect fifth above a randomly chosen given note.
This randomization process, configured during question creation, alleviates the
need to create very large question banks, and can be particularly useful for
interval and chord/harmonic function questions, given that the number of possible
questions for these exercises is very large.

I made a choice to create a framework where the correctness of a given question
is automatically computed, without having to enter individual answers during
question creation. A framework for adding various grading strategies for each
exercise is also in place, and is currently used for the scale writing exercise
(currently allowing either an "all-or-nothing" grading strategy, or a "partial
grade" approach where each correct note is given partial credit).

This plugin was developed with the hope of creating a music question type that
would support a wide range of music theory exercises, using a single graphical
user interface that is flexible enough to support such exercises. It is hoped
that it will facilitate further development and Moodle server maintenance.

Special thanks to Jay Huber for his very significant contributions to the existing
standalone Moodle music question types (Music Key Signature, Music Interval and
Music Scale), and for inspiring me to develop this new question type.

Many thanks to Ken Graetz, Scott Schradle and colleagues in Winona State
University's TLT and IT departments for their support of my Moodle development
efforts through the maintenance of our institution's Moodle server.

Maintainer: Eric Brisson

===========================================================================

INSTALLATION
------------

Requirements:

1) PHP 5: the plugin was coded with version 5.5.3. It hasn't been tested with
earlier versions of PHP.

2) Javascript is used for the graphical user interface, but the question type
is fully functional without it, with the exception of the note writing, note
identification and keyboard input exercises.

How to install:

1) Copy the 'musictheory' folder into the following folder:
[moodle]/question/type
2) Load the "Notifications" page on the Moodle home page - this will create
the database tables used by the question type.

================================================================================

LICENSE
-------

This plugin is released under the GNU GPL License, v3 or later.

It also includes three separate utilities:

1) Music Theory GUI (musictheory/yui/src/musictheoryui/js/MusThGUI): An HTML5
component for displaying or interacting with music theory examples and exercises.
It was created by Eric Brisson and its source code is available under the MIT
License.

1) Keyboard Input (musictheory/yui/src/musictheorykeyboardui/js/KeyboardInput):
An HTML5 component for displaying or interacting with a piano keyboard. It was
created by Eric Brisson and its source code is available under the MIT License.

3) Music Theory PHP Library (musictheory/lib/MusicTheory): A PHP library which
may be useful for any application dealing with music theoretical concepts. It
was created by Eric Brisson and its source code is available under the MIT
License.
