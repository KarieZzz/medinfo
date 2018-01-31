
function updateRelated() {
    let columns;
    let columngroups;

    grid.jqxGrid({
        width: '98%',
        height: 780,
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
        let rowindex = args.rowindex;
        selected.column_id = args.datafield;
        selected.row_id = grid.jqxGrid('getrowid', rowindex);
        selected.cell_value = grid.jqxGrid('getcellvaluebyid', selected.row_id, selected.column_id);
        let row_code = grid.jqxGrid('getcellvaluebyid', selected.row_id, current_row_number_datafield);
        let row_name = grid.jqxGrid('getcellvaluebyid', selected.row_id, current_row_name_datafield);
        let colindex = grid.jqxGrid('getcolumnproperty', selected.column_id, 'text');
        $("#row").html(row_code + '. ' + row_name);
        $("#column").html(colindex);
        $("#rule").val(selected.cell_value);
    });
};

function setquerystring() {
    return "&rule=" + encodeURIComponent($("#rule").val()) +
        "&comment=" + $("#comment").val() +
        "&row=" + selected.row_id +
        "&column=" + selected.column_id;
}

let initactions = function() {
    $("#save").click(function () {
        if($("#rule").val() === '') {
            console.log('Правило пустое');
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: rule_url,
            method: "POST",
            data: setquerystring(),
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                grid.jqxGrid('updatebounddata', 'data');
                grid.on("bindingcomplete", function (event) {
                    let newindex = grid.jqxGrid('getrowboundindexbyid', rowid);
                    grid.jqxGrid('selectrow', newindex);
                });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#delete").click(function () {
        let confirm_text = 'Подтвердите удаление правила ' +  $("#rule").val();
        if (!confirm(confirm_text)) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: rule_url + '/' + selected.row_id + '/' + selected.column_id,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    grid.jqxGrid('clearselection');
                    grid.jqxGrid('updatebounddata')
                    raiseInfo(data.message);
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
            }
        });
    });
};