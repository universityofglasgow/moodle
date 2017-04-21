Version 2016xxxx00:
Added behat for paste straight
Specified checked for multiple instances (Regression from 2016030100)

Version 2016031100:
Added another option for keyboard command
Improved behat tests

Version 2016030100:
Improved language strings
Added "Paste from Moodle"
Added keyboard shortcut for pasting
Added help button
Allowed width and height to be set as admin
Added function to check potential source of pasted content
Altered JS to allow for multiple instances of PS

Version 2016021100:
Further improved UI
Tables can now be pasted
Behat added to allow this
Further improved unformatted to handle no tags
Added German Language file
Added option to not clean text (for tables and such)

Version 2016010600:
Updated unformatted to better handle inline styling
Added optional handling for keyboard pasting
Improved UI to display a responsive view of content
Added behat
MOST IMPORTANTLY:
Started logging my changes (Sorry about before this folks)

Previous versions:
Everything up until 2016010600.

atto_pastespecial
=================

This is a plugin for Atto editor in Moodle that allows
the user to paste content from several different rich
formatting areas and either import the styling or
import as unformatted text. To install, unpack to
lib/editor/atto/plugins/pastespecial and visit the
admin notifications page to install. Then add the
button on that Atto toolbar settings page. The
pastespecial settings page can be used to configure
the allowed styles when importing formatting.

To build the YUI files from the yui/src/button/js/button.js
file, simple run the following command from yui/src/button:
PATH=$PATH:~/local/nodejs/node-v0.10.33-linux-x64/bin shifter

All original files are copyright Joseph Inhofer 2016
jinhofer@umn.edu and are licensed under GPL v3 or later
