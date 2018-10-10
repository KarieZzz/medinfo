/**
 * Created by shameev on 13.09.2016.
 */
initsplitter = function() {
    $("#mainSplitter").jqxSplitter(
        {
            width: '100%',
            height: '100%',
            theme: theme,
            panels:
                [
                    { size: '80%', min: '10%', collapsible: false },
                    { size: '20%', min: '10%', collapsible: true }
                ]
        }
    );
};

initdatasources = function() {
    gridsource = {
        datatype: "json",
        datafields: datafields,
        autoBind: false,
        id: 'id',
        url: cellsfetch_url + current_table,
        root: null
    };
    dataAdapter = new $.jqx.dataAdapter(gridsource, {
/*        beforeSend: function(jqXHR, settings) {
            console.log('вызов');
            if (current_table == 0) {
                return false;
            }
        },*/
        loadError: xhrErrorNotificationHandler
    });
};

/*initdropdowns = function() {
    let conditionsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'group_id' },
            { name: 'condition_name' }
        ],
        id: 'code',
        localdata: conditions
    };
    conditionsDataAdapter = new $.jqx.dataAdapter(conditionsource);
    $("#condition").jqxDropDownList({
        theme: theme,
        source: conditionsDataAdapter,
        displayMember: "condition_name",
        valueMember: "id",
        placeHolder: "Выберите условие:",
        width: 300,
        height: 34
    });
};*/

// Форма ввода данных по таблице
initTableGrid = function() {
    $("#tableGrid").jqxGrid(
        {
            width: '98%',
            height: '90%',
            source: dataAdapter,
            localization: localize(),
            selectionmode: 'multiplecellsadvanced',
            theme: theme,
            editable: true,
            editmode: 'selectedcell',
            columnsresize: true,
            filterable: false,
            columns: columns,
            columngroups: columngroups
        });

/*    grid.on('cellselect',  function() {
        //let cells = $('#tableGrid').jqxGrid('getselectedcells');
        if (cellbeginedit) {
            clearTimeout(cellbeginedit);
        }
        cellbeginedit = setTimeout(function () {
            fetchcellcondition();
        }, 500);
    });*/

    grid.on('cellvaluechanged', function (event) {
        let rowBoundIndex = args.rowindex;
        let rowid = $('#tableGrid').jqxGrid('getrowid', rowBoundIndex);
        //let condition = $("#condition").val() !== ''  ? $("#condition").val() : 0 ;
        let condition = 0 ;
        let colid = event.args.datafield;
        let newstate = args.newvalue ? 1 : 0;
        //var oldvalue = args.oldvalue;
        $.ajax({
            dataType: 'json',
            url: changecellstate_url + rowid + '_' + colid + '/' + newstate + '/' + condition,
            method: "PATCH",
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                }
/*                else {
                    raiseInfo(data.message);
                }*/
                $("#tableGrid").jqxGrid('updatebounddata', 'data');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
};

/*fetchcellcondition = function() {
    $("#conditionInfo").html('Выделено ячеек: 0');
    let range = [];
    let cells = grid.jqxGrid('getselectedcells');
    let selected_count = cells.length;
    let rowid;
    let cell;
    for (i = 0; i < selected_count; i++) {
        rowid = grid.jqxGrid('getrowid', cells[i].rowindex);
        cell = grid.jqxGrid('getcell', cells[i].rowindex, cells[i].datafield);
        if (cell.value === true) {
            range.push(rowid + '_' + cells[i].datafield);
        }
    }
    if (range.length > 0) {
        $.ajax({
            dataType: 'json',
            url: fetchcellcondition_url + range,
            method: "GET",
            success: function (data, status, xhr) {
                let message;
                //console.log(data.length);
                if (data.length === 1) {
                    message = data[0] == null ? "Для выделенного диапазона условия не определены (для всех учреждений)" : 'Для выделенного диапазона определено условие: <strong>"' + data[0] + '"</strong>';
                    $("#conditionInfo").html(message);
                }
                if (data.length > 1) {
                    $("#conditionInfo").html("Для выделенного диапазона определено несколько условий");
                }
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    } else {
        $("#conditionInfo").html("В выделенном диапазоне только редактируемые ячейки");
    }
    $("#selectedInfo").html("Выделено ячеек: " + cells.length);
};*/

updateRelated = function() {
    updateTableGrid();
};

// Обновление списка строк при выборе таблицы
updateTableGrid = function() {
    $.ajax({
        dataType: 'json',
        url: gridfetch_url + current_table,
        method: "GET",
        success: function (data, status, xhr) {
            grid.jqxGrid('beginupdate');
            columns = data.columns;
/*            $.each(columns, function(column, properties) {
                if (typeof properties.cellclassname !== 'undefined') {
                    //properties.cellclassname = cellclass;
                    properties.cellclassname = eval(properties.cellclassname);
                }
                if (typeof properties.initeditor !== 'undefined') {
                    properties.initeditor = defaultEditor;
                }
                if (typeof properties.validation !== 'undefined') {
                    //properties.validation = validation;
                    properties.validation = eval(properties.validation);
                }
                if (typeof properties.cellbeginedit !== 'undefined') {
                    properties.cellbeginedit = cellbeginedit;
                }
            });*/
            columngroups = data.columngroups;
/*            $.each(columngroups, function(group, properties) {
                if (typeof properties.rendered !== 'undefined')
                    properties.rendered = tooltiprenderer;
            });*/
            datafields = data.datafields;
            gridsource.datafields = datafields;
            gridsource.url = cellsfetch_url + current_table;
            grid.jqxGrid( { columns: columns } );
            grid.jqxGrid( { columngroups: columngroups } );
            grid.jqxGrid('updatebounddata');
            grid.jqxGrid('endupdate');
        },
        error: function (xhr, status, errorThrown) {
            raiseError("Ошибка загрузки данных по структуре таблиц");
        }
    });

    //console.log(datafields);
};

// Операции с ячейками
initCellActions = function() {
    $("#noteditable").click(function () {
        setcellrange(true);
    });
    $("#editable").click(function () {
        setcellrange(false);
    });
};

setcellrange = function(noedit) {
    let newstate = noedit ? 1 : 0;
    //let condition = $("#condition").val() !== ''  ? $("#condition").val() : 0 ;
    let condition = 0;
    let range = [];
    let cells = grid.jqxGrid('getselectedcells');
    let selected_count = cells.length;
    let rowid;
    for (i = 0; i < selected_count; i++) {
        //console.log(cells[i]);
        rowid = $('#tableGrid').jqxGrid('getrowid', cells[i].rowindex);
        range.push(rowid + '_' + cells[i].datafield);
    }
    let data = range + '/' + newstate + '/' + condition;
    $.ajax({
        dataType: 'json',
        url: changerangestate_url + data,
        method: "PATCH",
        data: data,
        success: function (data, status, xhr) {
            if (typeof data.error !== 'undefined') {
                raiseError(data.message);
            } else {
                raiseInfo(data.message);
            }
            grid.jqxGrid('clearselection');
            grid.jqxGrid('updatebounddata', 'data');
            $("#selectedInfo").html("Выделено ячеек: 0");
            $("#conditionInfo").html("Условия не определены");
        },
        error: function (xhr, status, errorThrown) {
            $.each(xhr.responseJSON, function(field, errorText) {
                raiseError(errorText);
            });
        }
    });
};

cellclass = function (row, columnfield, value, rowdata) {
    let not_editable = '';
    if (value === true) {
        not_editable = 'jqx-grid-cell-pinned jqx-grid-cell-pinned-bootstrap';
    }
    return not_editable;
};