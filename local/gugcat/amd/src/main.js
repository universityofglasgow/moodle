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

            $("#select-category").on("change", function () { 
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.delete("activityid");
                if($("#select-category").val() === 'null'){
                    urlParams.delete("categoryid");
                }else{
                    urlParams.set("categoryid", $("#select-category").val());
                }
                if(checkCurrentUrl("gugcat/add")){
                    urlParams.delete("studentid");
                    var url = window.location.pathname;
                    url = url.replace("gugcat/add", "gugcat")+"?"+urlParams;
                    window.location.replace(url);
                    return;
                }
                
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
                        $("#multigradesform").submit();
                        _this.text(langStrings[0]); 
                    }
                })
                $(".togglemultigrd").toggle();
            });

            $("#select-grade-reason").on("change", function () { 
                var $selected = $(this).find("option:selected");
                $(".input-reason").val($selected.val());
                if($selected.val() === 'Other'){
                    $(".input-reason").val("");
                    $("#input-reason").show();
                }else{
                    $("#input-reason").hide();
                }
            });

            $("#input-reason").on("input", function(e){
                $(".input-reason").val($(this).val());
            });

            $("#btn-release").click(function(e) {
                e.preventDefault();
                $("#release-submit").click();
            });

            //Show 'grade discrepancy' when grade discrepancy exist
            if($("td > .grade-discrepancy").length > 0){
                $("#btn-grddisc").show();
            }

            // Hide elements on add grade form page 
            if(checkCurrentUrl("gugcat/add")){
                $("#btn-release").hide();
            }else if(checkCurrentUrl("gugcat/overview")){
                $("#btn-release").hide();
                $("#btn-overviewtab").addClass("active");
                $("#btn-assessmenttab").removeClass("active");
            }

            if(!($(".gradeitems").text().includes("Moodle Grade[Date]"))){
                $("#btn-saveadd").show();
                $(".addnewgrade").show();
            }

            $("#btn-import").click(function(e) {
                e.preventDefault();
                if(!$(".gradeitems").text().includes("Moodle Grade[Date]")){
                    var confirmation = confirm("Please note that grades have already been imported from Moodle. If you continue you will overwrite all grades. Do you wish to continue?");
                    if(confirmation == true) {
                    $("#importgrades-submit").click();
                    }
                }
                else{
                    $("#importgrades-submit").click();
                }
            });
        }
    };
});
