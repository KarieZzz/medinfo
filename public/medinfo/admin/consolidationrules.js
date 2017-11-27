
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