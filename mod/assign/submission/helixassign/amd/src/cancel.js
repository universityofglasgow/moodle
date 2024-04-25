// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    assignsubmission_helixassign
 * @copyright  2021 Tim Williams Streaming LTD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    var module = {};

    module.helixCancelClick = function() {
        var xmlDoc=null;
        if (typeof window.ActiveXObject != 'undefined' ) {
            xmlDoc = new ActiveXObject('Microsoft.XMLHTTP');
        } else {
            xmlDoc = new XMLHttpRequest();
            var params='resource_link_id='+module.resID+'&user_id='+module.userID;
            xmlDoc.open('POST', module.statusURL , false);
            xmlDoc.setRequestHeader('Content-type','application/x-www-form-urlencoded');
            xmlDoc.send(params);
        }
    }

    module.bind = function() {
        var cbtn=document.getElementById('id_cancel');
        if (cbtn!=null) {
            cbtn.addEventListener('click', module.helixCancelClick);
        }
    }
    
    module.init = function(resID, userID, statusURL) {
        module.resID = resID;
        module.userID = userID;
        module.statusURL = statusURL;
        module.bind();
    }

    return module;
});
