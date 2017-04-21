This is the Helix Media Library Tiny MCE HTML editor plugin for Moodle 2.x.

This plugin Requires Moodle 2.5 or better.


Installation
------------

1 - Check that you have the Helix Media Library Activity module installed on your system. If it is not installed,
    please install before proceeding.
2 - Copy the contents of the zip file which contains this readme.txt into the lib/editor/tinymce/plugins/
    directory of your Moodle installation.
3 - Login to your Moodle system as an administrator.
4 - Click the "Notifications" link in the "Administration" block.
5 - Follow the on-screen instructions.
6 - After installation, navigate to Site Administration>Plugins>Text Editors>TinyMCE HTML editor>General settings
7 - In order to activate the Helix Media Library plugin in the HTML editor, you need to add 'helixmedia' to one of the
    entries in the Editor toolbar settings. eg: to add the Helix button after the table function on the third toolbar,
    change the following line from:

    bullist,numlist,outdent,indent,|,link,unlink,|,image,nonbreaking,charmap,table,|,code

    to:

    bullist,numlist,outdent,indent,|,link,unlink,|,image,nonbreaking,charmap,table,helixmedia,|,code



Module provided by Streaming LTD http://www.streaming.co.uk
