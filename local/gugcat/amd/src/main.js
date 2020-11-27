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
 * AMD module for local_gugcat.
 * 
 * @package    local_gugcat
 * @copyright  2020
 * @author     Accenture
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str' ], function($, Str) {

    //Returns boolean on check of the current url and match it to the path params
    var checkCurrentUrl = function(path) {
        var url = window.location.pathname;
        return url.match(path);
    }

    return {
        init: function() {
            $("#select-activity").on("change", function () { 
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set("activityid", $("#select-activity").val());
                window.location.search = urlParams;
            });
    
            $("#btn-saveadd").click(function(e) {
                e.preventDefault();
                var _this = $("#btn-saveadd");
                _this.toggleClass("togglebtn");
                _this.hide();
                var keys = [
                    {
                        key: 'addallnewgrade',
                        component: 'local_gugcat'
                    },
                    {
                        key: 'saveallnewgrade',
                        component: 'local_gugcat'
                    }
                ];
                Str.get_strings(keys).then(function(langStrings) {
                    _this.show();
                    if(_this.hasClass("togglebtn")){
                        _this.text(langStrings[1]);   
                    } else {
                        _this.text(langStrings[0]); 
                    }
                })
                $(".togglemultigrd").toggle();
            });

            $("#btn-assessmenttab").click(function(){
                if(checkCurrentUrl('gugcat/add')){
                    history.back();
                }
            });

            // Hide elements on add grade form page 
            if(checkCurrentUrl('gugcat/add')){
                $("#btn-approve").hide();
            }
        }
    };
});
