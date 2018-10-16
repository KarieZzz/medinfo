let initdatasources = function() {
    tablesource =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'int' },
                { name: 'table_code', type: 'string' },
                { name: 'table_name', type: 'string' }
            ],
            id: 'id',
            localdata: tables
        };
    tablesDataAdapter = new $.jqx.dataAdapter(tablesource);
};

let initTableList = function() {
    tgrid.on("bindingcomplete", function (event) {
        for (i = 0; i < included_tables.length; i++ ) {
            let index = tgrid.jqxGrid('getrowboundindexbyid', included_tables[i].table_id);
            tgrid.jqxGrid('selectrow', index);
        }
    });
    tgrid.on('rowselect', function (event)
    {
        let rowindexes = tgrid.jqxGrid('getselectedrowindexes');
        $("#count_of_included").html(rowindexes.length);
    });
    tgrid.on('rowunselect', function (event)
    {
        let rowindexes = tgrid.jqxGrid('getselectedrowindexes');
        $("#count_of_included").html(rowindexes.length);
    });
    tgrid.jqxGrid(
        {
            width: '650px',
            height: '80%',
            theme: theme,
            localization: localize(),
            source: tablesDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            selectionmode: 'checkbox',
            columns: [
                { text: 'Код', datafield: 'table_code', width: '50px'  },
                { text: 'Таблица', datafield: 'table_name' , width: '530px'}
            ]
        });
};

let initActions = function() {
    $("#update").click(function () {
        let row_ids = noselected_error("Не выбрано ни одного документа для удаления");
        if (!row_ids) {
            return false;
        }
        let data = "tables=" + row_ids;
        $.ajax({
            dataType: 'json',
            url: editsection_url,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                let updated = {};
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
            },
            error: xhrErrorNotificationHandler
        });
    });
};

noselected_error = function(message) {
    let row_ids = getselectedtables();
    if (row_ids.length === 0) {
        raiseError(message);
        return false;
    }
    return row_ids;
};

getselectedtables = function () {
    let rowindexes = tgrid.jqxGrid('getselectedrowindexes');
    let indexes_length =  rowindexes.length;
    let row_ids = [];
    for (i = 0; i < indexes_length; i++) {
        row_ids.push(tgrid.jqxGrid('getrowid', rowindexes[i]));
    }
    return row_ids;
};
