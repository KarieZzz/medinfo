// функция для обновления связанных объектов после выбора таблицы
let updateRelated = function() {
    updateFunctionList();
    $("#form")[0].reset();
};

let initdatasources = function() {
    functionsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'level', type: 'int' },
            { name: 'levelname', map: 'level>name', type: 'string' },
            { name: 'type', type: 'int' },
            { name: 'typename', map: 'type>name', type: 'string' },
            { name: 'script', type: 'string' },
            { name: 'comment', type: 'string' },
            { name: 'blocked', type: 'bool' },
            { name: 'created_at', type: 'string' },
            { name: 'updated_at', type: 'string' }
        ],
        id: 'id',
        url: functionfetch_url + current_table,
        root: 'f'
    };
    functionsDataAdapter = new $.jqx.dataAdapter(functionsource);
};
// Таблица функций
let initFunctionList = function() {
    fgrid.jqxGrid(
        {
            width: '98%',
            height: '300px',
            theme: theme,
            localization: localize(),
            source: functionsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '50px' },
                //{ text: 'Код таблицы', datafield: 'table_code', width: '70px'  },
                { text: 'Уровень', datafield: 'levelname', width: '120px'  },
                { text: 'Тип', datafield: 'typename', width: '120px'  },
                { text: 'Функция контроля', datafield: 'script' , width: '45%'},
                { text: 'Комментарий', datafield: 'comment', width: '400px' },
                { text: 'Отключена', datafield: 'blocked', columntype: 'checkbox', width: '70px' },
                { text: 'Создана', datafield: 'created_at', width: '130px' },
                { text: 'Обновлена', datafield: 'updated_at', width: '130px' }
            ]
        });
    fgrid.on('rowselect', function (event) {
        let row = event.args.row;
        $("#level").val(row.level.code);
        $("#script").val(row.script);
        $("#comment").val(row.comment);
        $("#blocked").val(row.blocked);
    });
};
// Обновление списка строк при выборе таблицы
let updateFunctionList = function() {
    functionsource.url = functionfetch_url + current_table;
    fgrid.jqxGrid('clearselection');
    fgrid.jqxGrid('updatebounddata');
};
// Операции с функциями контроля

let initButtons = function() {
    $('#blocked').jqxSwitchButton({
        height: 31,
        width: 120,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    let errorlevelsource =
    {
        datatype: "json",
        datafields: [
            { name: 'code' },
            { name: 'name' }
        ],
        id: 'code',
        localdata: errorLevels
    };
    errorLevelsDataAdapter = new $.jqx.dataAdapter(errorlevelsource);
    $("#level").jqxDropDownList({
        theme: theme,
        source: errorLevelsDataAdapter,
        displayMember: "name",
        valueMember: "code",
        placeHolder: "Выберите уровень ошибки:",
        width: 300,
        height: 34
    });
};

let setquerystring = function() {
    return "&level=" + $("#level").val() +
        "&script=" + encodeURIComponent($("#script").val()) +
        "&comment=" + $("#comment").val() +
        "&blocked=" + ($("#blocked").val() ? 1 :0);
};

let initFunctionActions = function() {
    $("#insert").click(function () {
        if (current_table === 0 ) {
            raiseError('Выберите форму и таблицу для которых будет применяться функция');
            return;
        }
        let data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: '/admin/cfunctions/create/' + current_table ,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                fgrid.jqxGrid('updatebounddata', 'data');
                fgrid.on("bindingcomplete", function (event) {
                    let newindex = fgrid.jqxGrid('getrowboundindexbyid', data.id);
                    fgrid.jqxGrid('selectrow', newindex);
                    fgrid.jqxGrid('ensurerowvisible', newindex);
                });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        let row = fgrid.jqxGrid('getselectedrowindex');
        if (row === -1 && typeof row !== 'undefined') {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let id = fgrid.jqxGrid('getrowid', row);
        let data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: '/admin/cfunctions/update/' + id,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    fgrid.jqxGrid('updatebounddata', 'data');
                    fgrid.on("bindingcomplete", function (event) {
                        let newindex = fgrid.jqxGrid('getrowboundindexbyid', id);
                        fgrid.jqxGrid('selectrow', newindex);
                        fgrid.jqxGrid('ensurerowvisible', newindex);
                    });
                }
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#delete").click(function () {
        let row = fgrid.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        current_function = fgrid.jqxGrid('getrowid', row);
        raiseConfirm("Удалить функцию контроля № " + current_function + "?" )
    });
    $("#selectedcheck").click(function () {
        let row = fgrid.jqxGrid('getselectedrowindex');
        if (row === -1 && typeof row !== 'undefined') {
            raiseError("Выберите запись для выполнения выборочного контроля данных");
            return false;
        }
        let id = fgrid.jqxGrid('getrowid', row);
        window.open('/admin/dcheck/selected/' + id);
        return true;
    });
    $("#recompileTable").click(function () {
        if (current_table === 0) {
            raiseError("Не выбрана таблица в которой будут перекомпилированы функции");
            return false;
        }
        window.open(recompileTable_url + current_table);
    });
    $("#recompileForm").click(function () {
        if (current_form === 0) {
            raiseError("Не выбрана форма в которой будут перекомпилированы функции");
            return false;
        }
        window.open(recompileForm_url + current_form);
    });
    $("#excelExport").click(function () {
        if (current_form === 0) {
            raiseError("Не выбрана форма из которой экспортируются функции");
            return false;
        }
        window.open(excelExport_url + current_form);
    });
};

let performAction = function() {
    $.ajax({
        dataType: 'json',
        url: '/admin/cfunctions/delete/' + current_function,
        method: "DELETE",
        success: function (data, status, xhr) {
            if (typeof data.error !== 'undefined') {
                raiseError(data.message);
            } else {
                raiseInfo(data.message);
                $("#form")[0].reset();
            }
            fgrid.jqxGrid('updatebounddata', 'data');
            fgrid.jqxGrid('clearselection');
        },
        error: function (xhr, status, errorThrown) {
            raiseError('Ошибка удаления записи', xhr);
        }
    });
};
