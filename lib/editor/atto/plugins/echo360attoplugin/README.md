# Echo360-Atto-Plugin

This is the <a href="https://docs.moodle.org/dev/Atto">Atto</a> plugin for moodle which will display an echo360 button which when pressed would display video options from the user's library. 

<h2>Dependencies</h2>

- For Mac, install the <a href="https://www.mamp.info/en/">MAMP</a> server
- For Linux, install the LAMP server, (steps vary depending on your distribution)
- Install <a href="https://docs.moodle.org/35/en/Installing_Moodle">Moodle</a>
- Install <a href="https://docs.moodle.org/dev/YUI/Shifter#Installing_Shifter">Shifter</a> globally on your local dev environment
- Install <a href="https://phpunit.de/manual/6.5/en/installation.html">PHPUnit</a> to run the tests

<h2>Deployment</h2>

- Give `archive.sh` execute permissions: ```chmod a+x archive.sh```
- Execute `archive.sh` with the current version number, ex. ```./archive.sh <major>.<minor>.<patch>```
- Upload the resulting archive to the admin downloads page using the upload form at "<Echo360 Base URL>/echoAdmin/uploadMoodlePlugin"

<h2>Local Development</h2>

- Clone this repo
- Ensure the top level dir is named "echo360attoplugin"
- The js source for the button is at yui/src/button/js/button.js. Changes to button.js won't do anything until you have run `shifter` on them. Go to `yui/src/button` and type `shifter`
- Copy "echo360attoplugin" to ```($MAMP_HOME|$LAMP_HOME)/htdocs/moodle<version number>/lib/editor/atto/plugins```
- Visit Settings > Site Administration > Notifications, and let Moodle guide you through the install (you may have to restart the MAMP/LAMP server for the notification to appear).
- Add the Public / Private Keys as well as the Host URL from your Echo360 LTI configuration on the next page
- Go to Site Administration > Plugins > Text Editors > Atto Toolbar Settings and you should find that this plugin has been added to the list of installed modules.
- Add the tool name to the "Other" button group next to HTML editor see <a href="https://docs.moodle.org/27/en/Text_editor">https://docs.moodle.org/27/en/Text_editor</a> for help
