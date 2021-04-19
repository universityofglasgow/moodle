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
define(['jquery', 'core/str', 'core/modal_factory', 'local_gugcat/modal_gcat', 'core/sessionstorage', 'core/ajax'], 
function($, Str, ModalFactory, ModalGcat, Storage, Ajax) {

    const BLIND_MARKING_KEY = 'blind-marking';
    //Returns boolean on check of the current url and match it to the path params
    const checkCurrentUrl = (path) => {
        var url = window.location.pathname;
        return url.match(path);
    }

    const update_reason_inputs = (val) => {
        var list = document.querySelectorAll('.input-reason');
        list.forEach(input => input.value = val);
    }

    const check_blind_marking = () =>{
        var btn_identities = document.getElementById('btn-identities');
        var classes = document.querySelectorAll('.blind-marking');
        if(btn_identities && classes.length > 0){
            btn_identities.textContent = '...';
            classes.forEach(element => element.classList.add('hide-names'));
            var is_blindmarking = (Storage.get(BLIND_MARKING_KEY) == 'true');
            var strings = [
                {
                    key: 'showidentities',
                    component: 'local_gugcat'
                },
                {
                    key: 'hideidentities',
                    component: 'local_gugcat'
                }
            ];
            Str.get_strings(strings).then(function(langStrings){
                btn_identities.textContent = is_blindmarking
                    ?langStrings[0]//show
                    :langStrings[1];//hide
                classes.forEach(element => {
                    is_blindmarking 
                    ? element.classList.add('hide-names')
                    : element.classList.remove('hide-names');
                });
            });
           
        }
    }

    const toggle_display_assessments = () =>{
        var btn_switch_display = document.getElementById('btn-switch-display');
        var urlParams = new URLSearchParams(window.location.search);
        var courseid = urlParams.get('id');
        if(btn_switch_display){
            btn_switch_display.textContent = '...';
            var requests = Ajax.call([{
                methodname: 'local_gugcat_display_assessments',
                args: { courseid: courseid},
            }]);
            requests[0].done(function(data) {
                var switchOn = data.result;
                var strings = [
                    {
                        key: 'switchoffdisplay',
                        component: 'local_gugcat'
                    },
                    {
                        key: 'switchondisplay',
                        component: 'local_gugcat'
                    }
                ];
                Str.get_strings(strings).then(function(langStrings){
                    btn_switch_display.textContent = switchOn
                        ?langStrings[0]//off
                        :langStrings[1];//on
                });
                }).fail(function(){
                    btn_switch_display.style.display = 'none';
                });
        }
    }
        
    const sortTable = (n) => {
        var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
        table = document.getElementById("gcat-table");
        switching = true;
        // Set the sorting direction to ascending:
        dir = "asc";
        /* Make a loop that will continue until
        no switching has been done: */
        while (switching) {
          // Start by saying: no switching is done:
          switching = false;
          rows = table.rows;
          /* Loop through all table rows (except the
          first, which contains table headers): */
          for (i = 1; i < (rows.length - 1); i++) {
            // Start by saying there should be no switching:
            shouldSwitch = false;
            /* Get the two elements you want to compare,
            one from current row and one from the next: */
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            /* Check if the two rows should switch place,
            based on the direction, asc or desc: */
            if (dir == "asc") {
              if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                // If so, mark as a switch and break the loop:
                shouldSwitch = true;
                break;
              }
            } else if (dir == "desc") {
              if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                // If so, mark as a switch and break the loop:
                shouldSwitch = true;
                break;
              }
            }
          }
          if (shouldSwitch) {
            /* If a switch has been marked, make the switch
            and mark that a switch has been done: */
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            // Each time a switch is done, increase this count by 1:
            switchcount ++;
          } else {
            /* If no switching has been done AND the direction is "asc",
            set the direction to "desc" and run the while loop again. */
            if (switchcount == 0 && dir == "asc") {
              dir = "desc";
              switching = true;
            }
          }
        }
    }

    const showModal = (dataAction, messageId, confirmId, cancelId = 'cancelmodal') => {
        var strings = [
            {
                key: messageId,
                component: 'local_gugcat'
            },
            {
                key: cancelId,
                component: 'local_gugcat'
            },
            {
                key: confirmId,
                component: 'local_gugcat'
            }
        ];
        Str.get_strings(strings).then(function(langStrings){
            var templateContext = {
                bodycontent: langStrings[0],
                strcancel: langStrings[1], 
                strconfirm: langStrings[2], 
                dataaction: dataAction
            };
            ModalFactory.create({
                type:ModalGcat.TYPE,
                templateContext: templateContext
            }).done(modal => modal.show());
        });
    }

    const onChangeListeners = (event) =>{
        var urlParams = new URLSearchParams(window.location.search);
        var categories = document.getElementById('select-category');
        var activities = document.getElementById('select-activity');
        var grade_reason = document.getElementById('select-grade-reason');
        var input_reason = document.getElementById('input-reason');
        var mform_grade_reason = document.getElementById('id_reasons');
        switch (event.target) {
            case categories:
                urlParams.delete("activityid");
                urlParams.delete("page");
                if(categories.value === 'null'){
                    urlParams.delete("categoryid");
                }else{
                    urlParams.set("categoryid", categories.value);
                }
                if(checkCurrentUrl("gugcat/add")){
                    urlParams.delete("studentid");
                    var url = window.location.pathname;
                    url = url.replace("gugcat/add", "gugcat")+"?"+urlParams;
                    window.location.replace(url);
                    return;
                }
                window.location.search = urlParams;
                break;
            case activities:
                urlParams.set("activityid", activities.value);
                urlParams.delete("page");
                window.location.search = urlParams;
                break;
            case grade_reason:
                var selected = event.target.value;    
                update_reason_inputs(selected);
                if(selected === 'Other'){
                    update_reason_inputs('');
                    input_reason.style.display = 'block';
                }else{
                    input_reason.style.display = 'none';
                }
                break;
            case mform_grade_reason:
                var selectedOption = event.target.value;
                var mformReason = document.getElementById('id_otherreason');
                mformReason.value = selectedOption;
                if(selectedOption = '8'){
                    mformReason.value = '';
                    mformReason.required = true;
                }
                else
                    mformReason.required = false;
                mformReason.focus();
                break;
            default:
                break;
        }
    }

    const onClickListeners = (event) =>{
        var btn_saveadd = document.getElementById('btn-saveadd');
        var btn_release = document.getElementById('btn-release');
        var btn_import = document.getElementById('btn-import');
        var btn_identities = document.getElementById('btn-identities');
        var btn_coursegradeform = document.getElementById('btn-coursegradeform');
        var btn_download = document.getElementById('btn-download');
        var btn_finalrelease = document.getElementById('btn-finalrelease');
        var btn_switch_display = document.getElementById('btn-switch-display');
        switch (event.target) {
            case btn_saveadd:
                btn_saveadd.classList.toggle('togglebtn');
                btn_saveadd.style.display = 'none';
                Str.get_string('saveallnewgrade', 'local_gugcat').then(function(langString) {
                    btn_saveadd.style.display = 'inline-block';
                    if(btn_saveadd.classList.contains('togglebtn')){
                        btn_saveadd.textContent = langString;
                    } else {
                        document.getElementById('multiadd-submit').click();
                    }
                });
                $(".togglemultigrd").show();
                break;
            case btn_release:   
                showModal('release', 'modalreleaseprovisionalgrade', 'confirmreleaseprovisionalgrade');
                break;
            case btn_import:
                if(!$(".gradeitems").text().includes("Moodle Grade[Date]")){
                    showModal('importgrades', 'modalimportgrades', 'confirmimport');
                }else{
                    document.getElementById('importgrades-submit').click();
                }
                break;
            case btn_coursegradeform:
                var inputarr = document.querySelectorAll('.input-percent');
                var total = 0;
                if(inputarr.length > 0){
                    inputarr.forEach(div => {
                        var input = div.querySelector('input');
                        total += parseInt(input.value);
                        var invalid = div.querySelector('input.is-invalid');
                        if(invalid != null){
                            div.querySelector('div.felement').classList.add('no-after');
                        }else{
                            div.querySelector('div.felement').classList.remove('no-after');
                        }
                    });
                }
                if(total != 100){
                    showModal('adjustweight', 'modaladjustweights', 'confirmchanges');
                }else{
                    document.getElementById('coursegradeform-submit').click();
                }
                break;
            case btn_finalrelease:
                showModal('finalrelease', 'modalreleasefinalgrades', 'confirmfinalrelease');
                break;
            case btn_download:
                document.getElementById('downloadcsv-submit').click();
                break;
            case btn_identities:
                var is_blindmarking = (Storage.get(BLIND_MARKING_KEY) == 'true');
                Storage.set(BLIND_MARKING_KEY, !is_blindmarking);
                check_blind_marking();
                break;
            case btn_switch_display:
                toggle_display_assessments();
                break;
            default:
                break;
        }
    }

    return {
        init: function() {
            const GCAT = document.querySelector('.gcat-container');
            if(GCAT){
                GCAT.addEventListener('change', onChangeListeners);
                GCAT.addEventListener('click', onClickListeners);
    
                //if hide identities button is visible, add key in storage
                if(document.getElementById('btn-identities') && !Storage.get(BLIND_MARKING_KEY)){
                    Storage.set(BLIND_MARKING_KEY, false);
                }
                check_blind_marking();

                var input_reason = document.getElementById('input-reason');
                if(input_reason){
                    input_reason.addEventListener('input', (e) => {
                        update_reason_inputs(e.target.value);
                    });
                }

                var input_search = document.querySelectorAll('.input-search');
                if(input_search.length > 0){
                    input_search.forEach(input => {
                        input.addEventListener('keydown', (e) => {
                            var key = e.keyCode ? e.keyCode : e.which;
                            if(key === 13){
                                document.getElementById('search-submit').click();
                            }                        
                        });
                    });
                }

                var input_percentarr = document.querySelectorAll('.input-percent');
                if(input_percentarr.length > 0){
                    var total = 0;
                    var totalweight = document.querySelector('#fitem_id_totalweight .form-inline');
                    input_percentarr.forEach(div => {
                        var input = div.querySelector('input');
                        total += parseInt(input.value);
                        input.addEventListener('input', (e) => {
                            let total = 0;
                            input_percentarr.forEach(div => total += parseInt(div.querySelector('input').value));
                            totalweight.innerHTML = `${total}%`;
                        });
                        input.addEventListener('focus', (e) => {
                            var val = e.target.value;
                            if(val !== "" && val.match(/^[0-9]+$/) === null){
                                div.querySelector('div.felement').classList.add('no-after');
                            }else{
                                div.querySelector('div.felement').classList.remove('no-after');
                            }
                        });
                        input.addEventListener('blur', (e) => {
                            var val = e.target.value;
                            if(val !== "" && val.match(/^[0-9]+$/) === null){
                                div.querySelector('div.felement').classList.add('no-after');
                            }else{
                                div.querySelector('div.felement').classList.remove('no-after');
                            }
                        });
                    });
                    totalweight.innerHTML = `${total}%`;
                }

                var columns = document.querySelectorAll('.sortable');
                if(columns.length > 0){
                    for (const [id, column] of columns.entries()) {
                        column.addEventListener('click', () => sortTable(id));
                    }
                }

                var searchIcons = document.querySelectorAll('.fa-search');
                if(searchIcons.length > 0){
                    searchIcons.forEach(icon => {
                        icon.addEventListener('click', (e) => {
                            e.currentTarget.blur();
                            var input = e.target.nextSibling;
                            input.classList.toggle('visible');
                            if(input.classList.contains('visible')){
                                input.style.display = 'block';
                            } else {
                                input.style.display = 'none';
                            }
                        });
                    });
                }

                //Show 'grade discrepancy' when grade discrepancy exist
                var grdDiscExist = document.querySelectorAll('td .grade-discrepancy');
                if (grdDiscExist.length > 0) {
                    document.getElementById('btn-grddisc').style.display = 'inline-block';
                }

                // Hide elements different pages
                if(checkCurrentUrl("gugcat/overview")){
                    document.querySelector('#btn-overviewtab').classList.add('active');
                    document.querySelector('#btn-assessmenttab').classList.remove('active');
                    var mformNotes = document.getElementById('id_notes');
                    if(mformNotes){
                        (async () => {
                            mformNotes.placeholder = await Str.get_string('specifyreason', 'local_gugcat');
                            mformNotes.required = true;
                        })();
                    }
                }else if(checkCurrentUrl("gugcat/index")){
                    document.getElementById('btn-release').style.display = 
                    !$(".gradeitems").text().includes("Moodle Grade[Date]") ? 'inline-block' : 'none';
                    var nodeArr = Array.from(document.querySelectorAll('.gradeitems'));
                    if(nodeArr.find(node => node.innerHTML !== 'Moodle Grade<br>[Date]')){
                        document.getElementById('btn-saveadd').style.display = 'inline-block';
                        var btnnewgrade = document.querySelectorAll('.addnewgrade');
                        btnnewgrade.forEach(e => {
                            e.style.display = 'block';
                        });

                        var hideshowgrade = document.querySelectorAll('.hide-show-grade');
                        hideshowgrade.forEach(element => {
                            element.style.display = 'block';
                        });
                    }
                }else if(checkCurrentUrl("gugcat/add") || checkCurrentUrl("gugcat/edit")){
                    //Add placeholder
                    var mformReason = document.getElementById('id_otherreason');
                    var mformNotes = document.getElementById('id_notes');
                    (async () => {
                        mformReason.placeholder = await Str.get_string('pleasespecify', 'local_gugcat');
                        mformNotes.placeholder = await Str.get_string('specifyreason', 'local_gugcat');
                        mformNotes.required = true;
                    })();
                }
            }
        }
    };
});
