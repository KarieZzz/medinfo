/**
 * Created by shameev on 13.09.2016.
 */

// функция для обновления связанных объектов после выбора таблицы
updateRelated = function() {
    updateFunctionList();
};

initdatasources = function() {
    controlsource = {
        datatype: "json",
        datafields: [
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'row_id', type: 'int' },
            { name: 'row_code', map: 'row>row_code', type: 'string' },
            { name: 'control_scope', type: 'int' },
            { name: 'relation', type: 'int' }
        ],
        id: 'id',
        url: fetchcontrolledrows_url + current_table + '/' + controlscope,
        root: 'controlled'
    };
    controlledRowsDataAdapter = new $.jqx.dataAdapter(controlsource);
    controlingsource = {
        datatype: "json",
        datafields: [
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'row_id', type: 'int' },
            { name: 'row_code', map: 'row>row_code', type: 'string' },
            { name: 'first_col', type: 'int' },
            { name: 'count_col', type: 'int' },
            { name: 'rec_id', type: 'int' },
            { name: 'relation', type: 'int' }
        ],
        id: 'id',
        url: fetchcontrollingrows_url + current_table + '/' + current_relation,
        root: 'controlling'
    };
    controllingRowsDataAdapter = new $.jqx.dataAdapter(controlingsource);
    columnsource = {
        datatype: "json",
        datafields: [
            { name: 'rec_id', type: 'int' },
            { name: 'controlled', type: 'int' },
            { name: 'controlling',type: 'int' },
            { name: 'boolean_sign', type: 'int' },
            { name: 'number_sign', type: 'int' }
        ],
        id: 'id',
        url: fetchcolumns_url + current_firstcol + '/' + current_countcol,
        root: 'column'
    };
    columnsDataAdapter = new $.jqx.dataAdapter(columnsource);
};
// Таблица функций
initControlList = function() {
    grid.jqxGrid(
        {
            width: '900px',
            height: '300px',
            theme: theme,
            localization: localize(),
            source: controlledRowsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Код таблицы', datafield: 'table_code', width: '150px'  },
                { text: 'Код строки', datafield: 'row_code' , width: '150px'},
                { text: 'Область контроля', datafield: 'control_scope', width: '150px' },
                { text: 'Ссылка на контролирующие строки', datafield: 'relation', width: '150px' }
            ]
        });
    grid.on('rowselect', function (event) {
        var row = event.args.row;
        current_relation = row.relation;
        updateControllingRowsList();
    });
    pgrid.jqxGrid(
        {
            width: '900px',
            height: '300px',
            theme: theme,
            localization: localize(),
            source: controllingRowsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Код таблицы', datafield: 'table_code', width: '150px'  },
                { text: 'Код строки', datafield: 'row_code' , width: '150px'},
                { text: 'Начальная графа', datafield: 'first_col', width: '150px' },
                { text: 'Кол-во граф', datafield: 'count_col', width: '100px'},
                { text: 'Номер записи', datafield: 'rec_id', width: '100px' },
                { text: 'Ссылка', datafield: 'relation', width: '100px' }
            ]
        });
    pgrid.on('rowselect', function (event) {
        var row = event.args.row;
        current_firstcol = row.first_col;
        current_countcol = row.count_col;
        updateColumnList();
    });
    cgrid.jqxGrid(
        {
            width: '900px',
            height: '300px',
            theme: theme,
            localization: localize(),
            source: columnsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Номер записи', datafield: 'rec_id', width: '150px'  },
                { text: 'Контролируемая графа', datafield: 'controlled' , width: '150px'},
                { text: 'Контролирующая графа', datafield: 'controlling', width: '150px' },
                { text: 'Знак сравнения', datafield: 'boolean_sign', width: '100px'},
                { text: 'Знак числа', datafield: 'number_sign', width: '100px' }
            ]
        });

};
// Обновление списка строк при выборе таблицы

updateFunctionList = function() {
    controlsource.url = fetchcontrolledrows_url + current_table + '/' + controlscope;
    grid.jqxGrid('clearselection');
    grid.jqxGrid('updatebounddata');
};

updateControllingRowsList = function() {
    controlingsource.url = fetchcontrollingrows_url + current_table + '/' + current_relation;
    pgrid.jqxGrid('clearselection');
    pgrid.jqxGrid('updatebounddata');
};

updateColumnList = function() {
    columnsource.url = fetchcolumns_url + current_firstcol + '/' + current_countcol;
    cgrid.jqxGrid('clearselection');
    cgrid.jqxGrid('updatebounddata');
};
// Операции с функциями контроля

initButtons = function() {
    $('#blocked').jqxSwitchButton({
        height: 31,
        width: 81,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
};

setquerystring = function() {
    return "&table_id=" + current_table +
        "&level=" + $("#level").val() +
        "&script=" + $("#script").val() +
        "&comment=" + $("#comment").val() +
        "&blocked=" + ($("#blocked").val() ? 1 :0);
};

initFunctionActions = function() {
    $("#insert").click(function () {
        if (current_table == 0 ) {
            raiseError('Выберите форму и таблицу для которых будет применяться функция');
            return;
        }
        var data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: '/admin/cfunctions/create',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                grid.jqxGrid('updatebounddata', 'data');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        var row = grid.jqxGrid('getselectedrowindex');
        if (row == -1 && typeof row !== 'undefined') {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var id = grid.jqxGrid('getrowid', row);

        var data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: '/admin/cfunctions/update/' + id,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                grid.jqxGrid('updatebounddata', 'data');
                grid.on("bindingcomplete", function (event) {
                    var newindex = $('#functionList').jqxGrid('getrowboundindexbyid', rowid);
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
        var row = grid.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        current_function = grid.jqxGrid('getrowid', row);
        raiseConfirm("Удалить функцию контроля № " + current_function + "?" )
    });
};

performAction = function() {
    $.ajax({
        dataType: 'json',
        url: '/admin/cfunctions/delete/' + current_function,
        method: "DELETE",
        success: function (data, status, xhr) {
            if (typeof data.error != 'undefined') {
                raiseError(data.message);
            } else {
                raiseInfo(data.message);
                $("#form")[0].reset();
            }
            grid.jqxGrid('updatebounddata', 'data');
            grid.jqxGrid('clearselection');
        },
        error: function (xhr, status, errorThrown) {
            raiseError('Ошибка удаления записи', xhr);
        }
    });
};
