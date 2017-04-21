<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.com                                            //
//                                                                       //
// Copyright (C) 1999 onwards  Martin Dougiamas  http://moodle.com       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2016112100;
$plugin->cron     = 0;
$plugin->requires = 2010112400; // Moodle 2.0+
$plugin->release  = '2.x (Build: 2016112100)';
$plugin->maturity = MATURITY_STABLE;
$plugin->component = 'gradeexport_checklist';
$plugin->dependencies = array('mod_checklist' => 2010041800); // Must have checklist activity module installed.
