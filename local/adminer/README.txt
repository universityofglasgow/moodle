Moodle Adminer is based on the great tool adminer (www.adminer.org).
The main advantage of this plugin is, it can handle different types of database.
So it works with MySQL, PostgreSQL, Oracle and MSSQL.

Moodle Adminer is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Moodle Adminer is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You can receive a copy of the GNU General Public License
at <http:www.gnu.org/licenses/>.

Installation:
To install Moodle Adminer just copy the folder "adminer" into your moodle/local/adminer.
After that you have to go to http://your-moodle/admin (Site administration -> Notifications) to trigger the installation process.

Using:
To use Moodle Adminer go to "Site administration" -> "Server" -> "Moodle Adminer".

changes in 2012060301
- it is based on adminer-3.4.0-dev
- now it works correctly again in google chrome
- the query textarea can syntax highlighting

changes in 2012060301
- added missing lib/adminer.css

changes in 2012091801
- added support for MSSQL 2008 R2

changes in 2013031601
- it is based on adminer-3.6.4-dev
- it uses the context_system class since moodle 2.2

changes in 2013061301
- it is based on adminer-3.7.1-dev
    look here: https://github.com/vrana/adminer/blob/master/changes.txt

changes in 2014011601
- it is based on adminer-4.0.2
    now it doesn't use deprecated ereg functions anymore
    for more infos see here: https://github.com/vrana/adminer/blob/master/changes.txt

changes in 2014011602
- ports other than default port are supported now - thanks to Ian Tasker

changes in 2014111800
- it is based on adminer-4.1.0
    for more infos see here: https://github.com/vrana/adminer/blob/master/changes.txt

changes in 2015052700
- it is based on adminer-4.2.1
    for more infos see here: https://github.com/vrana/adminer/blob/master/changes.txt

changes in 2016061000
- compatible with moodle_30 and moodle_31
- compatible with php7
- it is based on adminer-4.2.4
    for more infos see here: https://github.com/vrana/adminer/blob/master/changes.txt

changes in 2016102000
- CONTRIB-6566 local_adminer: Hardening the Adminer for Moodle

changes in 2016122600
- compatible with moodle_30, moodle_31 and moodle_32
- it is based on adminer-4.2.5
    for more infos see here: https://github.com/vrana/adminer/blob/master/changes.txt

changes in 2017052800
- compatible with moodle_31, moodle_32 and moodle_33
- it is based on adminer-4.3.1
    for more infos see here: https://github.com/vrana/adminer/blob/master/changes.txt
- export feature in postgresql is working again
- higher z-index value for overlay window
- changed design to price/adminer.css

changes in 2018040700
- compatible with moodle_31, moodle_33 and moodle_34
- it is based on adminer-4.6.2
    for more infos see here: https://github.com/vrana/adminer/blob/master/changes.txt
- changed design back to default

changes in 2018040700
- compatible with moodle_35
- replaced grey box with bootstrap modal dialog
- add a legacy css to work on themes not based on boost
