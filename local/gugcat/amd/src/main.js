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
    };

    const calculatePercentagePoints = (target, is_percentage = true) => {
        var grade_max = document.getElementsByName('grademax')[0].value;
        if (is_percentage){
            var input_point = document.getElementById(target.id.slice(0,10) + "pt_" + target.id.slice(10));
            input_point.value = target.value ? rounder((target.value / 100) * grade_max) : '';
        } else {
            var input_percentage = document.getElementById(target.id.slice(0,10) + target.id.slice(13));
            input_percentage.value = target.value ? rounder((target.value / grade_max) * 100) : '';
        }
    };

    const rounder = (number) => {
        var multiplier = parseInt("1" + "0".repeat(2));
        number = number * multiplier;
        return Math.round(number) / multiplier;
    };

    const clearConversionTable = () => {
        var input_percentage_points = document.querySelectorAll('.input-prc,.input-pt');
        input_percentage_points.forEach(element => {
            var input = element.lastElementChild.firstElementChild;
            var is_input_H = input.id == 'id_schedA_pt_1' || input.id == 'id_schedA_1'
                || input.id == 'id_schedB_1' || input.id == 'id_schedB_pt_1';
            if (!is_input_H){
                input.value = "";
            }
        });
    };

    const update_reason_inputs = (val) => {
        var list = document.querySelectorAll('.input-reason');
        list.forEach(input => {input.value = val;});
    };

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
                    if(is_blindmarking){
                        element.classList.add('hide-names');
                    }else{
                        element.classList.remove('hide-names');
                    }
                });
            });

        }
    };

    const toggle_child_activities = () =>{
        var btns = document.querySelectorAll('.btn-colexp');
        if(btns.length > 0){
            btns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    var selected = e.target.getAttribute('data-categoryid');
                    var classes = document.querySelectorAll(`[data-category="${selected}"]`);
                    var icon = btn.querySelector('.i-colexp');
                    classes.forEach(element => {
                       element.classList.toggle('hidden');
                       if(element.classList.contains('hidden')){
                           icon.classList.add('fa-plus');
                           icon.classList.remove('fa-minus');
                       }else{
                           icon.classList.remove('fa-plus');
                           icon.classList.add('fa-minus');
                       }
                   });
                    var colgroups = document.querySelectorAll(`.catid-${selected}`);
                    colgroups.forEach(element => element.classList.toggle('hidden'));
                });
            });
        }
    };

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
    };

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
    };

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
    };

    const toggleScaleTable = () => {
        var schedA = document.getElementById('table-schedulea');
        var schedB = document.getElementById('table-scheduleb');
        schedA.classList.toggle('hidden');
        schedB.classList.toggle('hidden');
    };

    const onChangeListeners = (event) =>{
        var urlParams = new URLSearchParams(window.location.search);
        var categories = document.getElementById('select-category');
        var activities = document.getElementById('select-activity');
        var childactivities = document.getElementById('select-child-act');
        var grade_reason = document.querySelector('.multi-select-reason > select');
        var select_scale = document.getElementById('select-scale');
        var select_template = document.getElementById('select-template');
        var select_alt_grade = document.getElementById('select-alt-grade');
        var mform_grade_reason = document.getElementById('id_reasons');
        switch (event.target) {
            case categories:
                urlParams.delete("activityid");
                urlParams.delete("childactivityid");
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
                urlParams.delete("childactivityid");
                urlParams.delete("page");
                window.location.search = urlParams;
                break;
            case childactivities:
                urlParams.set("childactivityid", childactivities.value);
                urlParams.delete("page");
                window.location.search = urlParams;
                break;
            case grade_reason:
                var selected = event.target.value;
                var input = document.querySelector('.togglemultigrd > input');
                update_reason_inputs(selected);
                if(selected === 'Other'){
                    update_reason_inputs('');
                    input.style.display = 'block';
                }else{
                    input.style.display = 'none';
                }
                break;
            case mform_grade_reason:
                var selectedOption = event.target.value;
                var mformReason = document.getElementById('id_otherreason');
                if(selectedOption == '9'){
                    mformReason.value = '';
                    mformReason.focus();
                }
                break;
            case select_scale:
                toggleScaleTable();
                break;
            case select_template:
                if(select_template.value != 0){
                    var templateid = select_template.value;
                    var requests = Ajax.call([{
                        methodname: 'local_gugcat_get_converter_template',
                        args: { templateid: templateid},
                    }]);
                    requests[0].done(function(data) {
                        var template = data.result;
                        if(template){
                            template = JSON.parse(template);
                            // Empty all input fields first
                            clearConversionTable();

                            // Toggle table scale based on the template scale
                            if(select_scale.value != template.scaletype){
                                toggleScaleTable();
                            }

                            // Assign template scale on the select scale field
                            select_scale.value = template.scaletype;

                            // Name identifier for the input percentages and input points

                            var nameidprc = template.scaletype != 2 ? "schedA[*]" : "schedB[*]";
                            var nameidpt = template.scaletype != 2 ? "schedA_pt[*]" : "schedB_pt[*]";

                            // Get maximum grade
                            var maxgrade = document.getElementsByName('grademax')[0].value;

                            // Start assigning lowerboundary from template on the input pt fields
                            var conversions = template.conversion;
                            for (let key in conversions) {
                                let conv = conversions[key];
                                let nameprc = nameidprc.replace("*", conv.grade);
                                let namept = nameidpt.replace("*", conv.grade);
                                var inputprc = document.querySelector(`.input-prc > div > input[name="${nameprc}"]`);
                                var inputpt = document.querySelector(`.input-pt > div > input[name="${namept}"]`);
                                let lowerboundary = parseFloat(conv.lowerboundary);
                                inputprc.value =  lowerboundary;
                                inputpt.value = rounder((lowerboundary / 100) * maxgrade); //convert percentage to points
                            }
                        }
                    }).fail(function(){
                        // console.log(e);
                    });
                }
                break;
            case select_alt_grade:
                var all = document.querySelectorAll('.merit-lbl, .gpa-lbl');
                if(select_alt_grade.value != 0){
                    var merit = document.querySelectorAll('.merit-lbl');
                    var gpa = document.querySelectorAll('.gpa-lbl');
                    if(select_alt_grade.value == 1){
                        if(merit.length > 0 ){
                            merit.forEach(lbl => lbl.classList.remove('hidden'));
                        }
                        if(gpa.length > 0 ){
                            gpa.forEach(lbl => lbl.classList.add('hidden'));
                        }
                    }else if(select_alt_grade.value == 2){
                        if(merit.length > 0 ){
                            merit.forEach(lbl => lbl.classList.add('hidden'));
                        }
                        if(gpa.length > 0 ){
                            gpa.forEach(lbl => lbl.classList.remove('hidden'));
                        }
                    }
                }else{
                    if(all.length > 0 ){
                        all.forEach(lbl => lbl.classList.add('hidden'));
                    }
                }
                break;
            default:
                break;
        }
    };

    const onClickListeners = (event) =>{
        var btn_multiadd = document.getElementById('btn-saveadd');
        var btn_multisave = document.getElementById('btn-multisave');
        var btn_release = document.getElementById('btn-release');
        var btn_import = document.getElementById('btn-import');
        var btn_identities = document.getElementById('btn-identities');
        var btn_coursegradeform = document.getElementById('btn-coursegradeform');
        var btn_download = document.getElementById('btn-download');
        var btn_finalrelease = document.getElementById('btn-finalrelease');
        var btn_switch_display = document.getElementById('btn-switch-display');
        var btn_bulk_import = document.getElementById('btn-blkimport');
        var btn_convert = document.getElementById('id_convertbutton');
        var radio_ptprc = document.getElementsByName('percentpoints');
        var btn_altsave = document.querySelector('[name="savealtbutton"]');
        switch (event.target) {
            case btn_multiadd:
                $(".togglemultigrd").toggle();
                btn_multisave.classList.toggle('hidden');
                break;
            case btn_multisave:
                if(document.querySelectorAll('input.is-invalid').length == 0){
                    document.getElementById('multiadd-submit').click();
                }
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
                        if(invalid !== null){
                            div.querySelector('[data-fieldtype="text"]').classList.add('no-after');
                        }else{
                            div.querySelector('[data-fieldtype="text"]').classList.remove('no-after');
                        }
                    });
                }
                var notes = document.querySelector('textarea#id_notes');
                var errorNotes = document.getElementById('id_error_notes');
                if(notes.value == ''){
                    errorNotes.innerHTML = '- You must supply a value here.';
                    errorNotes.style.display = 'block';
                }else{
                    errorNotes.innerHTML = '';
                    errorNotes.style.display = 'none';
                    if(total != 100){
                        showModal('adjustweight', 'modaladjustweights', 'confirmchanges');
                    }else{
                        document.getElementById('coursegradeform-submit').click();
                    }
                }
                break;
            case btn_finalrelease:
                var isConvertSubcat = document.getElementById('isconvertsubcat');
                showModal('finalrelease', isConvertSubcat.value != 1 ?
                'modalreleasefinalgrades' : 'modalreleasefinalconvertedgrades', 'confirmfinalrelease');
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
            case btn_bulk_import:
                if(!$(".gradeitems").text().includes("Moodle Grade[Date]")){
                    showModal('bulkimportgrades', 'modalimportgrades', 'confirmimport');
                }else{
                    document.getElementById('bulk-submit').click();
                }
                break;
            case btn_convert:
                showModal('hiddensubmit', 'modalconvertgrades', 'continueconvertgrade');
                break;
            case radio_ptprc[0]:
            case radio_ptprc[1]:
                clearConversionTable();
                break;
            case btn_altsave:
                var alttype = document.getElementById('select-alt-grade');
                if(alttype.value == 1){
                    var inputarr = document.querySelectorAll('.input-percent');
                    var total = 0;
                    if(inputarr.length > 0){
                        inputarr.forEach(div => {
                            var input = div.querySelector('input');
                            total += parseInt(input.value);
                            let invalid = div.querySelector('input.is-invalid');
                            if(invalid !== null){
                                div.querySelector('input').classList.add('is-invalid');
                                div.querySelector('[data-fieldtype="text"]').classList.add('no-after');
                            }else{
                                div.querySelector('input').classList.remove('is-invalid');
                                div.querySelector('[data-fieldtype="text"]').classList.remove('no-after');
                            }
                        });
                    }
                    var invalid = document.querySelectorAll('input.is-invalid');
                    if(invalid.length == 0){
                        if(total != 100){
                            showModal('hiddensubmit', 'modalmeritweights', 'confirmchanges');
                        }else{

                            document.querySelector('[name="hiddensubmitform"]').click();
                        }
                    }
                }else{
                    document.querySelector('[name="hiddensubmitform"]').click();
                }
                break;
            default:
                break;
        }
    };

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
                toggle_child_activities();

                var input_reason = document.querySelector('.togglemultigrd > input');
                if(input_reason){
                    input_reason.addEventListener('input', (e) => {
                        update_reason_inputs(e.target.value);
                    });
                }

                var select_scale = document.getElementById('select-scale');
                if(select_scale){
                    if(select_scale.value != 1){
                        toggleScaleTable();
                    }
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

                var input_percentage_points = document.querySelectorAll('.input-prc,.input-pt');
                if(input_percentage_points.length > 0){
                    input_percentage_points.forEach(input => {
                        var field = input.querySelector('input');
                        if(field.value){
                            calculatePercentagePoints(field, input.classList.contains('input-prc'));
                        }
                        input.addEventListener('change', (e) =>{
                            calculatePercentagePoints(e.target, input.classList.contains('input-prc'));
                        });
                    });
                }

                var input_gradept = document.querySelectorAll('.input-gradept');
                if(input_gradept.length > 0){
                    input_gradept.forEach(input => {
                        input.addEventListener('input', (e) => {
                            var val = e.target.value;
                            var grademax = e.target.getAttribute('data-grademax');
                            if(val && !(/^([mM][vV]|[0-9]|[nN][sS])+$/).test(val)){
                                input.classList.add('is-invalid');
                            }else{
                                if(Number.isInteger(parseInt(val)) && parseInt(val) > parseInt(grademax)){
                                    input.classList.add('is-invalid');
                                }else{
                                    input.classList.remove('is-invalid');
                                }
                            }
                        });
                    });
                }

                var input_percentarr = document.querySelectorAll('.input-percent');
                var totalweight = document.querySelector('.total-weight')
                    ? document.querySelector('.total-weight')
                    : document.querySelector('#fitem_id_totalweight .form-inline');
                if(input_percentarr.length > 0 && totalweight){
                    var total = 0;
                    input_percentarr.forEach(div => {
                        var input = div.querySelector('input');
                        total += parseInt(input.value);
                        input.addEventListener('input', () => {
                            let total = 0;
                            input_percentarr.forEach(div => {total += parseInt(div.querySelector('input').value);});
                            totalweight.innerHTML = `${total}%`;
                        });
                        input.addEventListener('focus', (e) => {
                            var val = e.target.value;
                            if(val !== "" && val.match(/^[0-9]+$/) === null){
                                div.querySelector('[data-fieldtype="text"]').classList.add('no-after');
                                div.querySelector('input').classList.add('is-invalid');
                            }else{
                                div.querySelector('[data-fieldtype="text"]').classList.remove('no-after');
                                div.querySelector('input').classList.remove('is-invalid');
                            }
                        });
                        input.addEventListener('blur', (e) => {
                            var val = e.target.value;
                            if(val !== "" && val.match(/^[0-9]+$/) === null){
                                div.querySelector('[data-fieldtype="text"]').classList.add('no-after');
                                div.querySelector('input').classList.add('is-invalid');
                            }else{
                                div.querySelector('[data-fieldtype="text"]').classList.remove('no-after');
                                div.querySelector('input').classList.remove('is-invalid');
                            }
                        });
                    });
                    totalweight.innerHTML = `${total}%`;
                }

                var input_templatename = document.querySelector('.template-name input');
                if(input_templatename){
                    input_templatename.addEventListener('input', (e) => {
                        update_reason_inputs(e.target.value);
                    });
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

                //Reset to 0 if merit checkbox is unchecked
                var meritCheckboxes = document.querySelectorAll('input.checkbox-field');
                if (meritCheckboxes.length > 0) {
                meritCheckboxes.forEach(cb => {
                    cb.addEventListener('change', (e) => {
                        if(!e.currentTarget.checked){
                            var id =  e.target.getAttribute('data-itemid');
                            var input_percent = document.querySelector(`.input-percent input[name="weights[${id}]"]`);
                            input_percent.value = 0;
                        }
                    });
                });
                }

                // Hide elements different pages
                if(checkCurrentUrl("gugcat/overview")){
                    document.querySelector('#btn-overviewtab').classList.add('active');
                    document.querySelector('#btn-assessmenttab').classList.remove('active');
                }else if(checkCurrentUrl("gugcat/index")){
                    document.getElementById('btn-release').style.display =
                    !$(".gradeitems").text().includes("Moodle Grade[Date]") ? 'inline-block' : 'none';
                    var nodeArr = Array.from(document.querySelectorAll('.gradeitems'));
                    var isConverted = document.getElementById('isconverted');
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
                    if(isConverted.value != 0){
                        document.getElementById('btn-release').style.display = 'none';
                    }
                }
            }
        }
    };
});
