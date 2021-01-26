courseIsAscending = true;
dateIsAscending = true;
pastCourseIsAscending = true;
pastStartDateIsAscending = true;
pastEndDateIsAscending = true;

function sortByDate(header){
    changeSelectSortIndex(1)
    sortTable(4, dateIsAscending);
    saveSorts("date", dateIsAscending);
    dateIsAscending = !dateIsAscending;

    headerIcon = header.lastElementChild;
    if (headerIcon.classList.contains("rotateimg180")){
        headerIcon.classList.remove("rotateimg180");
    } else {
        headerIcon.classList.add("rotateimg180");
    }
}

function sortByPastCourseDate(header, isStartDate){
    sortIndex = isStartDate ? 4 : 5;
    sortName = isStartDate ? "startdate" : "enddate";
    isAscending = isStartDate ? pastStartDateIsAscending : pastEndDateIsAscending;

    sortTable(sortIndex, isAscending);
    saveSorts(sortName, isAscending, true);

    if (isPast){
        pastStartDateIsAscending = !pastStartDateIsAscending;
    } else {
        pastEndDateIsAscending = !pastEndDateIsAscending;
    }

    headerIcon = header.lastElementChild;
    if (headerIcon.classList.contains("rotateimg180")){
        headerIcon.classList.remove("rotateimg180");
    } else {
        headerIcon.classList.add("rotateimg180");
    }
}

function sortByCourse(header, suffix = ""){
    console.log(header, suffix)
    isPast = suffix != "";
    isAscending = (suffix == "") ? courseIsAscending : pastCourseIsAscending;
    changeSelectSortIndex(0)
    sortTable(0, isAscending, suffix);
    saveSorts("course", isAscending, isPast);
    
    if (isPast) {
        pastCourseIsAscending = !pastCourseIsAscending;
    } else {
        courseIsAscending = !courseIsAscending;
    }

    headerIcon = header.lastElementChild;
    if (headerIcon.classList.contains("rotateimg180")){
        headerIcon.classList.remove("rotateimg180");
    } else {
        headerIcon.classList.add("rotateimg180");
    }
}

function saveSorts(sortedBy, isAscending, isPast = false){

    if (isPast){
        localStorage["pastSortedBy"] = sortedBy
        localStorage["pastIsAscending"] = sortedBy
    } else {
        localStorage["sortedBy"] = sortedBy;
        localStorage["isAscending"] = isAscending;
    }
}

function sortTable(index, isAscending = true, suffix = "") {
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("block_spdetails_table" + suffix);
    switching = true;
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[index];
            y = rows[i + 1].getElementsByTagName("TD")[index];
            if (isAscending && x.innerText.toLowerCase() > y.innerText.toLowerCase()) {
                shouldSwitch = true;
                break;
            } else if (!isAscending && x.innerText.toLowerCase() < y.innerText.toLowerCase()){
                shouldSwitch = true;
                break;
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
        }
    }
}

function changeSelectSortIndex(index){
    selectSort = document.getElementById("gu_spdetails_select_sort");
    selectSort.selectedIndex  = index;
}

function toggleTableShow(id){
    switch (id) {
        case "block_spdetails_table" :
            document.getElementById("gu_spdetails_current_tab").classList.add("selected-element");
            document.getElementById("gu_spdetails_past_tab").classList.remove("selected-element");
            document.getElementById(id).classList.remove("hidden-element");
            document.getElementById("block_spdetails_table_past_history").classList.add("hidden-element");
            break;
        case "block_spdetails_table_past_history" :
            document.getElementById("gu_spdetails_past_tab").classList.add("selected-element");
            document.getElementById("gu_spdetails_current_tab").classList.remove("selected-element");
            document.getElementById(id).classList.remove("hidden-element");
            document.getElementById("block_spdetails_table").classList.add("hidden-element");
    }
}

window.addEventListener("DOMContentLoaded", ()=>{
    sortedBy = localStorage["sortedBy"];
    isAscending = localStorage["isAscending"] == "true";

    switch (sortedBy) {
        case "date" :
            dateIsAscending = isAscending;
            sortByDate(document.getElementById("gu_spdetails_tableheader_duedate"));
            break;
        case "course" :
            courseIsAscending = isAscending;
            sortByCourse(document.getElementById("gu_spdetails_tableheader_course"));
            break;
        default :
            courseIsAscending = true;
            sortByCourse(document.getElementById("gu_spdetails_tableheader_course"));
            break;
    }

    sortedBy = localStorage["pastSortedBy"];
    isAscending = localStorage["pastIsAscending"] == "true";

    switch (sortedBy) {
        case "startdate" :
            dateIsAscending = isAscending;
            sortByDate(document.getElementById("gu_spdetails_tableheader_startdate_past_history"));
            break;
        case "enddate" :
            dateIsAscending = isAscending;
            sortByDate(document.getElementById("gu_spdetails_tableheader_enddate_past_history"));
            break;
        case "course" :
            courseIsAscending = isAscending;
            sortByCourse(document.getElementById("gu_spdetails_tableheader_course_past_history"));
            break;
        default :
            courseIsAscending = true;
            sortByCourse(document.getElementById("gu_spdetails_tableheader_course_past_history"));
            break;
    }
});