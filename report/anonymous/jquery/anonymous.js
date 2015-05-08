$(function(){
    $("#anonymous_table").tablesorter({
        sortList: [
            [0, 0],
        ],
        headers: {
            '.anonymous-date': {sorter: "shortDate", dateFormat: "ddmmyyyy"},
        },
        theme: "bootstrap",
        widgets: ["uitheme", "pager"],
        headerTemplate: '{content} {icon}',

        pager_output: '{startRow:input} to {endRow} of {totalRows} rows',
    });

    $("#anonymous_table").tablesorterPager({
        container: $(".anonymous_pager"),
        output: '{startRow} - {endRow} ({totalRows})',
    });

});
