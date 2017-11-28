
function updateRelated() {
    let columns;
    let columngroups;

    grid.jqxGrid({
        width: '98%',
        editable: true,
        editmode: 'selectedcell',
        selectionmode: 'singlecell',
        localization: localize()
    });
    $.ajax({
        dataType: 'json',
        url: 'consolidation/getstruct/' + picked_table,
        method: "GET",
        success: function (data, status, xhr) {
            grid.jqxGrid('beginupdate');
            let gridsource = {
                datatype: "json",
                datafields: data.datafields,
                //autoBind: false,
                id: 'id',
                url: 'consolidation/getrules/' + picked_table,
                root: null
            };
            let adapter = new $.jqx.dataAdapter(gridsource);
            current_row_name_datafield = data.columns[1].dataField;
            current_row_number_datafield = data.columns[2].dataField;
            //gridsource.datafields = datafields;
            //gridsource.url = cellsfetch_url + current_table;
            grid.jqxGrid( { columns: data.columns } );
            grid.jqxGrid( { columngroups: data.columngroups } );
            grid.jqxGrid({ source: adapter });
            //grid.jqxGrid('updatebounddata');
            grid.jqxGrid('endupdate');
        },
        error: function (xhr, status, errorThrown) {
            raiseError("Ошибка загрузки структуры таблицы");
        }
    });
}

let gridEventsInit = function () {
    grid.on('cellselect', function (event)
    {
        let args = event.args;
        let rowindex = event.args.rowindex;
        selected.column_id = args.datafield;
        selected.row_id = grid.jqxGrid('getrowid', rowindex);
        let row_code = grid.jqxGrid('getcellvaluebyid', selected.row_id, current_row_number_datafield);
        let row_name = grid.jqxGrid('getcellvaluebyid', selected.row_id, current_row_name_datafield);
        let colindex = grid.jqxGrid('getcolumnproperty', selected.column_id, 'text');
        $("#row").html(row_code + '. ' + row_name);
        $("#column").html(colindex);
    });
};