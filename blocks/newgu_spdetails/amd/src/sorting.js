/**
 * Function to sort a table by column name and in which direction to sort items.
 * Inspiration provided by https://www.w3schools.com/howto/howto_js_sort_table.asp
 *
 * @param {int} n - the column number to sort the rows by
 * @param {string} sortName
 * @param {string} tableName
 */
function sortTable(n, sortName, tableName) {
    let table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    table = document.getElementById(tableName);
    switching = true;
    // Begin by assuming the column is of type string
    let compareFunction = compareString;
    // We're now including a 'raw' value for dates and weights to be sorted by.
    if (sortName.includes('date') == true || sortName.includes('weight') == true) {
        compareFunction = compareNumber;
    }
    //Set the sorting direction to ascending:
    dir = "asc";
    // Moving this here as not sure repeatedly calling this inside the loop is efficient.
    rows = table.rows;
    /*Make a loop that will continue until
    no switching has been done:*/
    while (switching) {
        //start by saying: no switching is done:
        switching = false;
        /*Loop through all table rows (except the
        first, which contains table headers):*/
        for (i = 1; i < (rows.length - 1); i++) {
            //start by saying there should be no switching:
            shouldSwitch = false;
            /*Get the two elements you want to compare,
            one from current row and one from the next:*/
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            /*check if the two rows should switch place,
            based on the direction, asc or desc:*/
            if (dir == "asc") {
                if (compareFunction(x, y, 'asc') == true) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch= true;
                    break;
                }
            } else if (dir == "desc") {
                if (compareFunction(x, y, 'desc') == true) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch= true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            /*If a switch has been marked, make the switch
            and mark that a switch has been done:*/
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            //Each time a switch is done, increase this count by 1:
            switchcount ++;
        } else {
            /*If no switching has been done AND the direction is "asc",
            set the direction to "desc" and run the while loop again.*/
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
    sortingStatus(sortName, dir);
}

/**
 * Function to compare two strings. This was previously using innerHTML but proved too slow.
 * The sort order is passed in also.
 *
 * @param {string} x
 * @param {string} y
 * @param {string} direction
 * @returns
 */
let compareString = function (x, y, direction) {
    if (direction == 'asc') {
        if (x.innerText.toLowerCase() > y.innerText.toLowerCase()) {
            return true;
        }
        return false;
    } else if (direction == 'desc') {
        if (x.innerText.toLowerCase() < y.innerText.toLowerCase()) {
            return true;
        }
        return false;
    }
};

/**
 * Function to compare two numbers. This was previously just dates but now extended
 * to include weight values.
 * The sort order (direction) is used to determine in which direction we are comparing.
 *
 * @param {string} x
 * @param {string} y
 * @param {string} direction
 * @returns
 */
let compareNumber = function (x, y, direction) {
    let attXName = x.getAttributeNames().filter((attName) => { return attName.includes('data');});
    let attYName = y.getAttributeNames().filter((attName) => { return attName.includes('data');});
    if (direction == 'asc') {
        if (Number(x.getAttribute(attXName[0])) > Number(y.getAttribute(attYName[0]))) {
            return true;
        }
        return false;
    } else if (direction == 'desc') {
        if (Number(x.getAttribute(attXName[0])) < Number(y.getAttribute(attYName[0]))) {
            return true;
        }
        return false;
    }
};

/**
 * Function to make UI changes to show which direction things are being sorted in.
 *
 * @param {string} sortby
 * @param {string} sortorder
 */
function sortingStatus(sortby, sortorder) {
    let sortElement = document.querySelector('#sortby_' + sortby);
    let excludeElement = '';
    if (sortElement) {
        excludeElement = sortElement;
        if (sortorder == 'asc') {
            sortElement.classList.add('th-sort-desc');
            sortElement.classList.remove('th-sort-asc');
            sortElement.setAttribute('data-value', 'desc');
        } else {
            sortElement.classList.add('th-sort-asc');
            sortElement.classList.remove('th-sort-desc');
            sortElement.setAttribute('data-value', 'asc');
        }
    }

    // Find everything that is not the thing we've just clicked and reset its position.
    if (excludeElement != '') {
        let elId = excludeElement.id;
        let els = document.querySelectorAll(".th-sortable:not(#" + elId + ")");
        els.forEach((el) => {
            let classes = el.className;
            let tmp = classes.match(new RegExp(/th-sort-.+/, 'g'));
            el.classList.remove(tmp);
            el.removeAttribute('data-value');
        });
    }
}

export default sortTable;