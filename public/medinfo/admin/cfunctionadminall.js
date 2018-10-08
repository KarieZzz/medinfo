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
            { name: 'form_code', map: 'table>form>form_code', type: 'string' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'levelcode', map: 'level>code', type: 'int' },
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
        url: functionfetch_url,
        root: 'f'
    };
    functionsDataAdapter = new $.jqx.dataAdapter(functionsource);
};
// Таблица функций
let initFunctionList = function() {
    fgrid.jqxGrid(
        {
            width: '100%',
            height: '90%',
            theme: theme,
            localization: localize(),
            source: functionsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '50px' },
                { text: 'Форма', datafield: 'form_code', width: '50px'  },
                { text: 'Таблица', datafield: 'table_code', width: '60px'  },
                { text: 'Уровень', datafield: 'levelname', width: '120px'  },
                { text: 'Тип', datafield: 'typename', width: '120px'  },
                { text: 'Функция контроля', datafield: 'script' , width: '40%'},
                { text: 'Комментарий', datafield: 'comment', width: '400px' },
                { text: 'Откл', datafield: 'blocked', columntype: 'checkbox', width: '40px' },
                { text: 'Создана', datafield: 'created_at', width: '130px' },
                { text: 'Обновлена', datafield: 'updated_at', width: '130px' }
            ]
        });
    fgrid.on('rowselect', function (event) {
        let row = event.args.row;
        $("#level").val(row.levelcode);
        //let lev = row.level.code;
        //$("#level option[value=" + lev + "]").prop('selected', 'selected');
        $("#script").val(row.script);
        $("#comment").val(row.comment);
        $("#blocked").prop('checked', row.blocked);
    });
};
// Обновление списка строк при выборе таблицы
let updateFunctionList = function() {
    functionsource.url = functionfetch_url + current_table;
    fgrid.jqxGrid('clearselection');
    fgrid.jqxGrid('updatebounddata');
};
// Операции с функциями контроля

let setquerystring = function() {
    return "&level=" + $("#level").val() +
        "&script=" + encodeURIComponent($("#script").val()) +
        "&comment=" + $("#comment").val() +
        "&blocked=" + ($("#blocked").prop('checked') ? 1 :0);
};

let initFunctionActions = function() {
    $("#save").click(function () {
        let row = fgrid.jqxGrid('getselectedrowindex');
        if (row === -1 && typeof row !== 'undefined') {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let id = fgrid.jqxGrid('getrowid', row);
        let rowdata = fgrid.jqxGrid('getrowdatabyid', id);
        let data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: '/admin/cfunctions/update/' + id,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                let updated = {};
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    updated.form_code = rowdata.form_code;
                    updated.table_code = rowdata.table_code;
                    updated.levelname = data.levelname;
                    updated.levelcode = data.levelcode;
                    updated.typename = data.typename;
                    updated.script = data.script;
                    updated.comment = data.comment;
                    updated.blocked = data.blocked;
                    updated.created_at = data.created_at;
                    updated.updated_at = data.updated_at;
                    fgrid.jqxGrid('updaterow', id,  updated);
/*                    fgrid.jqxGrid('updatebounddata', 'data');
                    fgrid.on("bindingcomplete", function (event) {
                        let newindex = fgrid.jqxGrid('getrowboundindexbyid', id);
                        fgrid.jqxGrid('selectrow', newindex);
                        fgrid.jqxGrid('ensurerowvisible', newindex);
                    });*/
                }
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
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
