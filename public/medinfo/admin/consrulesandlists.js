
function updateRelated() {
    ruleinput.val('');
    listinput.val('');
    selectionlog.html('');
    grid.jqxGrid({
        width: '98%',
        height: 780,
        editable: false,
        selectionmode: 'multiplecellsadvanced',
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
                id: 'id',
                url: 'consolidation/getrules/' + picked_table,
                root: null
            };
            let adapter = new $.jqx.dataAdapter(gridsource);
            current_row_name_datafield = data.columns[1].dataField;
            current_row_number_datafield = data.columns[2].dataField;
            grid.jqxGrid( { columns: data.columns } );
            grid.jqxGrid( { columngroups: data.columngroups } );
            grid.jqxGrid({ source: adapter });
            grid.jqxGrid('endupdate');
        },
        error: function (xhr, status, errorThrown) {
            raiseError("Ошибка загрузки структуры таблицы");
        }
    });
    grid.on('cellvaluechanged', function (event) {
        let rowBoundIndex = args.rowindex;
        let rowid = grid.jqxGrid('getrowid', rowBoundIndex);
        let colid = event.args.datafield;
        let rule = args.newvalue;
        if (rule === "" || rule === null) {
            $.ajax({
                dataType: 'json',
                url: rule_url + '/' + rowid + '/' + colid,
                method: "DELETE",
                success: function (data, status, xhr) {
                    if (typeof data.error !== 'undefined') {
                        raiseError(data.message);
                    } else {
                        grid.jqxGrid('clearselection');
                        grid.jqxGrid('updatebounddata');
                        raiseInfo(data.message);
                    }
                },
                error: function (xhr, status, errorThrown) {
                    raiseError('Ошибка сохранения данных на сервере', xhr);
                }
            });
        } else {
            let data = "row=" + rowid + "&column=" + colid + "&rule=" + encodeURIComponent(rule);
            $.ajax({
                dataType: 'json',
                url: rule_url,
                data: data,
                method: 'POST',
                success: function (data, status, xhr) {
                    if (typeof data.error !== 'undefined') {
                        raiseError(data.message);
                    } else {
                        raiseInfo(data.message);
                    }
                },
                error: function (xhr, status, errorThrown) {
                    raiseError("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.");
                }
            });
        }
    });
}

let gridEventsInit = function () {
    grid.on('cellselect', function (event)
    {
        ruleinput.val('');
        selectionlog.html('');
        let cells = grid.jqxGrid('getselectedcells');
        let selected_count = cells.length;
        let rowid;
        let cell;
        let upper_left = { rowcode : null, colindex: null, value: null };
        let down_right = { rowcode : null, colindex: null, value: null };
        selected = [];
        for (i = 0; i < selected_count; i++) {
            rowid = grid.jqxGrid('getrowid', cells[i].rowindex);
            cell = grid.jqxGrid('getcell', cells[i].rowindex, cells[i].datafield);
            selected.push({ rowid: parseInt(rowid), colid: cells[i].datafield } );
        }
        upper_left.value = grid.jqxGrid('getcellvaluebyid', selected[0].rowid, selected[0].colid);
        upper_left.rowcode = grid.jqxGrid('getcellvaluebyid', selected[0].rowid, current_row_number_datafield);
        upper_left.colindex = grid.jqxGrid('getcolumnproperty', selected[0].colid, 'text');
        ruleinput.val(upper_left.value);
        //console.log(selected);
        if (selected_count === 1) {
            selectionlog.html('Выделена ячейка: строка ' + upper_left.rowcode + '. графа ' + upper_left.colindex);
        } else if (selected_count > 1) {
            down_right.value = grid.jqxGrid('getcellvaluebyid', selected[selected_count - 1].rowid, selected[selected_count - 1].colid);
            down_right.rowcode = grid.jqxGrid('getcellvaluebyid', selected[selected_count - 1].rowid, current_row_number_datafield);
            down_right.colindex = grid.jqxGrid('getcolumnproperty', selected[selected_count - 1].colid, 'text');
            selectionlog.html('Выделен диапазон: С' + upper_left.rowcode + 'Г' + upper_left.colindex + ':С' + down_right.rowcode + 'Г' + down_right.colindex );
        }
    });
};

function setquerystring(cell_diapazon) {
    return "&rule=" + encodeURIComponent(ruleinput.val()) +
        "&list=" + encodeURIComponent(listinput.val()) +
        "&cells=" + cell_diapazon;
}

let initactions = function() {
    $("#applyrule").click(function () {
        if(ruleinput.val() === '') {
            raiseError('Правило пустое');
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
    $("#applylist").click(function () {
        let cell_diapazon = [];
        let selected_count = selected.length;
        if(listinput.val() === '') {
            raiseError('Список МО пуст');
            return false;
        }
        if(selected_count === 0) {
            raiseError('Не выделены ячейки для применения правила/списка МО');
            return false;
        }
        for (i = 0; i < selected_count; i++) {
            cell_diapazon.push(selected[i].rowid + '_' + selected[i].colid)
        }
        //console.log(setquerystring(cell_diapazon));
        $.ajax({
            dataType: 'json',
            url: applylist_url,
            method: "PATCH",
            data: setquerystring(cell_diapazon),
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                grid.jqxGrid('updatebounddata', 'data');
                grid.on("bindingcomplete", function (event) {
                    //let newindex = grid.jqxGrid('getrowboundindexbyid', rowid);
                    //grid.jqxGrid('selectrow', newindex);
                });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });

    });
    $("#clearrule").click(function () {
        let confirm_text = 'Подтвердите удаление правила ' +  ruleinput.val();
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
                    grid.jqxGrid('updatebounddata');
                    raiseInfo(data.message);
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
            }
        });
    });
};