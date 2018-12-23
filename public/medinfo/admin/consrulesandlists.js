
function updateRelated() {
    ruleinput.val('');
    listinput.val('');
    selectionlog.html('');
    grid.jqxGrid({
        width: '100%',
        height: 700,
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
        if (cellbeginedit) {
            clearTimeout(cellbeginedit);
        }
        cellbeginedit = setTimeout(function () {
            fetchcells();
        }, 500);
    });
};

function fetchcells() {
    ruleinput.val('загрузка ...');
    listinput.val('загрузка ...');
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
    //console.log(selected);
    if (selected_count === 1) {
        selectionlog.html('<h5 class="text-info small">Выделена ячейка: строка ' + upper_left.rowcode + '. графа ' + upper_left.colindex + '</h5>');
    } else if (selected_count > 1) {
        down_right.value = grid.jqxGrid('getcellvaluebyid', selected[selected_count - 1].rowid, selected[selected_count - 1].colid);
        down_right.rowcode = grid.jqxGrid('getcellvaluebyid', selected[selected_count - 1].rowid, current_row_number_datafield);
        down_right.colindex = grid.jqxGrid('getcolumnproperty', selected[selected_count - 1].colid, 'text');
        selectionlog.html('<h5 class="text-info small">Выделен диапазон: С' + upper_left.rowcode + 'Г' + upper_left.colindex + ':С' + down_right.rowcode + 'Г' + down_right.colindex + '</h5>');
    }

    $.get(getscripts_url + '/' + selected[0].rowid + '/' + selected[0].colid, function (data) {
        ruleinput.val(data.rule);
        listinput.val(data.list);
    });
}

function setquerystring(cell_diapazon) {
    return "&rule=" + encodeURIComponent(ruleinput.val()) +
        "&list=" + encodeURIComponent(listinput.val()) +
        "&cells=" + cell_diapazon;
}

function setcelldiapazon() {
    let cell_diapazon = [];
    let selected_count = selected.length;
    for (i = 0; i < selected_count; i++) {
        cell_diapazon.push(selected[i].rowid + '_' + selected[i].colid)
    }
    return cell_diapazon;
}

let initactions = function() {
    $("#applyrule").click(function () {
        let cell_diapazon = setcelldiapazon();
        if(ruleinput.val() === '') {
            raiseError('Правило не заполнено');
            return false;
        }
        if(cell_diapazon.length === 0) {
            raiseError('Не выделены ячейки для применения правила/списка МО');
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: applyrule_url,
            method: "PATCH",
            data: setquerystring(cell_diapazon),
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo("Правило расчета сохранено. Затронуто ячеек " + data.affected_cells);
                }
                grid.jqxGrid('updatebounddata', 'data');
                //grid.on("bindingcomplete", function (event) { });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#applylist").click(function () {
        let cell_diapazon = setcelldiapazon();
        if(listinput.val() === '') {
            raiseError('Список МО пуст');
            return false;
        }
        if(cell_diapazon.length === 0) {
            raiseError('Не выделены ячейки для применения правила/списка МО');
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: applylist_url,
            method: "PATCH",
            data: setquerystring(cell_diapazon),
            success: function (data, status, xhr) {
                let m = '';
                if (typeof data.error !== 'undefined') {
                    raiseError(data.error);
                } else {
                    m = 'Список субъектов отчетности сохранен. Затронуто ячеек: ' + data.affected_cells;
                    raiseInfo(m);
                }
                grid.jqxGrid('updatebounddata', 'data');
                //grid.on("bindingcomplete", function (event) { });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#clearrule").click(function () {
        let cell_diapazon = setcelldiapazon();
        if(cell_diapazon.length === 0) {
            raiseError('Не выделены ячейки для удаления правил/списков МО');
            return false;
        }
        let confirm_text = 'Подтвердите удаление правил из выделенного диапазона';
        if (!confirm(confirm_text)) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: applyrule_url,
            data: setquerystring(cell_diapazon),
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
    $("#clearlist").click(function () {
        let cell_diapazon = setcelldiapazon();
        if(cell_diapazon.length === 0) {
            raiseError('Не выделены ячейки для удаления правил/списков МО');
            return false;
        }
        let confirm_text = 'Подтвердите удаление списков МО из выделенного диапазона';
        if (!confirm(confirm_text)) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: applylist_url,
            data: setquerystring(cell_diapazon),
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

    let unitlistsource = {
        datatype: "json",
        //datafields: [
        //    { name: 'slug' },
        //    { name: 'name' }
        //],
        url: fetchlists_url
    };
    let lists = [];
    let unitlistsdataAdapter = new $.jqx.dataAdapter(unitlistsource, { autoBind: true, loadComplete: function (data)
        {
            lists = data;
/*            console.log(data);
            for (let i = 0; i < data.length; i++) {
                lists.push(data[i]);
            }*/
        }

    });
    listinput.jqxInput({
        source: function (query, response) {
            let item = query.split(/,\s*/).pop();
            listinput.jqxInput({ query: item });
            response(lists);
        },
        renderer: function (itemValue, inputValue) {
            let terms = inputValue.split(/,\s*/);
            // remove the current input
            terms.pop();
            terms.push(itemValue);
            terms.push("");
            return terms.join(", ");
        }
    });
};