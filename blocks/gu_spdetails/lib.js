courseIsAscending = true;
dateIsAscending = true;

function sortByDate(header){
    changeSelectSortIndex(1)
    sortTable(4, dateIsAscending);
    saveSorts("date", dateIsAscending);
    dateIsAscending = !dateIsAscending;

    headerIcon = header.lastElementChild;
    if (dateIsAscending) {
        headerIcon.classList.add("rotateimg180");
    } else {
        headerIcon.classList.remove("rotateimg180");
    }
}

function sortByCourse(header){
    changeSelectSortIndex(0)
    sortTable(0, courseIsAscending);
    saveSorts("course", courseIsAscending);
    courseIsAscending = !courseIsAscending;

    headerIcon = header.lastElementChild;
    if (courseIsAscending) {
        headerIcon.classList.add("rotateimg180");
    } else {
        headerIcon.classList.remove("rotateimg180");
    }
}

function saveSorts(sortedBy, isAscending){
    localStorage["sortedBy"] = sortedBy;
    localStorage["isAscending"] = isAscending;
}

function sortTable(index, isAscending = true) {
    var table, rows, switching, i, x, y, shouldSwitch;
    table = document.getElementById("block_spdetails_table");
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
});