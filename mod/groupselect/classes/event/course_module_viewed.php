<?php

// This file is part of Moodle - http://moodle.org/
// //
// // Moodle is free software: you can redistribute it and/or modify
// // it under the terms of the GNU General Public License as published by
// // the Free Software Foundation, either version 3 of the License, or
// // (at your option) any later version.
// //
// // Moodle is distributed in the hope that it will be useful,
// // but WITHOUT ANY WARRANTY; without even the implied warranty of
// // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// // GNU General Public License for more details.
// //
// // You should have received a copy of the GNU General Public License
// // along with Moodle. If not, see <http://www.gnu.org/licenses/>.
// /**
// * Group self selection - course module viewed event
// *
// * @package mod
// * @subpackage groupselect
// * @copyright 2014 Tampere University of Technology, P. Pyykkönen (pirkka.pyykkonen ÄT tut.fi)
// * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
// */
//

namespace mod_groupselect\event;
defined('MOODLE_INTERNAL') || die();

class course_module_viewed extends \core\event\course_module_viewed 
{	
    protected function init() {	
	$this->data['objecttable'] = 'groupselect';
	parent::init();
    }	
}	
	
