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
                    { size: '60%', min: '10%', collapsible: false },
                    { size: '40%', min: '10%', collapsible: false }
                ]
        }
    );
};
initFilterDatasources = function() {
    var formssource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'form_code' }
        ],
        id: 'id',
        localdata: forms
    };
    formsDataAdapter = new $.jqx.dataAdapter(formssource);
    tablesource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'table_code' },
            { name: 'table_name' }
        ],
        id: 'id',
        url: tablefetch_url + current_form
    };
    tablesDataAdapter = new $.jqx.dataAdapter(tablesource);
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
        loadError: function(jqXHR, status, error) {
            if (jqXHR.status == 401) {
                raiseError('Пользователь не авторизован.', jqXHR );
            }
            if (jqXHR.status == 404) {
                $("#tableProperties").html('<div>Выберите форму и таблицу для редактирования</div>');
            }
        }
    });

};

initdropdowns = function() {
    var conditionsource =
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
};

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
            clipboard: true,
            columnsresize: true,
            filterable: false,
            columns: columns,
            columngroups: columngroups
        });
    //$('#tableGrid').on('cellselect', function (event) {

        //console.log(cells.length);
    //});

    $('#tableGrid').on('cellselect',  function() {
        var cells = $('#tableGrid').jqxGrid('getselectedcells');
        if (cellbeginedit) {
            clearTimeout(cellbeginedit);
        }
        cellbeginedit = setTimeout(function () {
            fetchcellcondition();
        }, 500);
    });

    $('#tableGrid').on('cellvaluechanged', function (event) {
        var rowBoundIndex = args.rowindex;
        var rowid = $('#tableGrid').jqxGrid('getrowid', rowBoundIndex);
        var condition = $("#condition").val() !== ''  ? $("#condition").val() : 0 ;
        //console.log(condition);
        var colid = event.args.datafield;
        var newstate = args.newvalue ? 1 : 0;
        //var oldvalue = args.oldvalue;
        $.ajax({
            dataType: 'json',
            url: changecellstate_url + rowid + '_' + colid + '/' + newstate + '/' + condition,
            method: "PATCH",
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
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

fetchcellcondition = function() {
    $("#conditionInfo").html('');
    var range = [];
    var cells = $('#tableGrid').jqxGrid('getselectedcells');
    var selected_count = cells.length;
    var rowid;
    var cell;
    for (i = 0; i < selected_count; i++) {
        rowid = $('#tableGrid').jqxGrid('getrowid', cells[i].rowindex);
        cell = $('#tableGrid').jqxGrid('getcell', cells[i].rowindex, cells[i].datafield);
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
                var message;
                //console.log(data.length);
                if (data.length == 1) {
                    message = data[0] == null ? "Для выделенного диапазона условия не определены" : 'Для выделенного диапазона определено условие: <strong>"' + data[0] + '"</strong>';
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
};
// Обновление списка таблиц при выборе формы
updateTableDropdownList = function(form) {
    tablesource.url = tablefetch_url + current_form;
    $("#tableListContainer").jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите таблицу из формы ' + form.label + '</div>');
    $("#tableList").jqxDataTable('updateBoundData');
};
// Обновление списка строк при выборе таблицы
updateTableGrid = function() {
    $.ajax({
        dataType: 'json',
        url: gridfetch_url + current_table,
        method: "GET",
        success: function (data, status, xhr) {
            columns = data.columns;
            $.each(columns, function(column, properties) {
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
            });
            columngroups = data.columngroups;
            $.each(columngroups, function(group, properties) {
                if (typeof properties.rendered !== 'undefined')
                    properties.rendered = tooltiprenderer;
            });
            datafields = data.datafields;
            gridsource.datafields = datafields;
            gridsource.url = cellsfetch_url + current_table;
            $("#tableGrid").jqxGrid( { columns: columns } );
            $("#tableGrid").jqxGrid( { columngroups: columngroups } );
            $('#tableGrid').jqxGrid('updatebounddata');
            $("#tableGrid").jqxGrid('endupdate');
        },
        error: function (xhr, status, errorThrown) {
            console.log("Ошибка загрузки данных по структуре таблиц");
        }
    });

    //console.log(datafields);
};
// Инициализация списков-фильтров форма -> таблица
initFormTableFilter = function() {
    $("#formList").jqxDropDownList({
        theme: theme,
        source: formsDataAdapter,
        displayMember: "form_code",
        valueMember: "id",
        placeHolder: "Выберите форму:",
        //selectedIndex: 2,
        width: 200,
        height: 32
    });
    $('#formList').on('select', function (event) {
        var args = event.args;
        current_form = args.item.value;
        updateTableDropdownList(args.item);
    });
    $("#tableListContainer").jqxDropDownButton({ width: 250, height: 32, theme: theme });
    $("#tableList").jqxDataTable({
        theme: theme,
        source: tablesDataAdapter,
        width: 420,
        height: 400,
        columns: [{
            text: 'Код',
            dataField: 'table_code',
            width: 100
            },
            {
                text: 'Наименование',
                dataField: 'table_name',
                width: 300
            }
        ]
    });
    $('#tableList').on('rowSelect', function (event) {
        $("#tableListContainer").jqxDropDownButton('close');
        var args = event.args;
        var r = args.row;
        current_table = args.key;
        $("#tableProperties").html('Таблица: (' + r.table_code + ') ' + r.table_name);
        updateTableGrid();
    });
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
    var newstate = noedit ? 1 : 0;
    var condition = $("#condition").val() !== ''  ? $("#condition").val() : 0 ;
    var range = [];
    var cells = $('#tableGrid').jqxGrid('getselectedcells');
    var selected_count = cells.length;
    var rowid;
    for (i = 0; i < selected_count; i++) {
        //console.log(cells[i]);
        rowid = $('#tableGrid').jqxGrid('getrowid', cells[i].rowindex);
        range.push(rowid + '_' + cells[i].datafield);

    }
    var data = range + '/' + newstate + '/' + condition;
    $.ajax({
        dataType: 'json',
        url: changerangestate_url + data,
        method: "PATCH",
        data: data,
        success: function (data, status, xhr) {
            if (typeof data.error != 'undefined') {
                raiseError(data.message);
            } else {
                raiseInfo(data.message);
            }
            $("#tableGrid").jqxGrid('clearselection');
            $("#tableGrid").jqxGrid('updatebounddata', 'data');
        },
        error: function (xhr, status, errorThrown) {
            $.each(xhr.responseJSON, function(field, errorText) {
                raiseError(errorText);
            });
        }
    });
};

cellbeginedit = function (row, datafield, columntype, value) {

};
defaultEditor = function (row, cellvalue, editor, celltext, pressedChar) {

};
cellclass = function (row, columnfield, value, rowdata) {
    var not_editable = '';
    if (value == true) {
        not_editable = 'jqx-grid-cell-pinned jqx-grid-cell-pinned-bootstrap';
    }
    return not_editable;
};
validation = function(cell, value) {
    return true;
};
tooltiprenderer = function (element) {

};