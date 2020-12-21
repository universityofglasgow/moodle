courseIsAscending = true;
dateIsAscending = true;

function sortByDate(header){
    sortTable(4, dateIsAscending);
    dateIsAscending = !dateIsAscending;

    headerIcon = header.lastElementChild;
    if (dateIsAscending) {
        headerIcon.classList.add("rotateimg180")
    } else {
        headerIcon.classList.remove("rotateimg180")
    }
}

function sortByCourse(header){
    sortTable(0, courseIsAscending);
    courseIsAscending = !courseIsAscending;

    headerIcon = header.lastElementChild;
    if (courseIsAscending) {
        headerIcon.classList.add("rotateimg180")
    } else {
        headerIcon.classList.remove("rotateimg180")
    }
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