#!/bin/sh
recess --compile --compress  less/moodle.less > style/moodle.css
sed -i 's/}/}\n/g' style/moodle.css
sed -i 's/\(@media[^{]*\){/\1{\n/g' style/moodle.css
sed -i 's/,.dir-rtl[^,]*,/,/g' style/moodle.css
sed -i 's/^.dir-rtl[^,]*,//g' style/moodle.css
sed -i 's/,.dir-rtl[^,]*{/{/g' style/moodle.css
sed -i '/^[^ ,]*dir-rtl/d' style/moodle.css
