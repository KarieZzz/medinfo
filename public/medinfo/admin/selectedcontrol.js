// функция для обновления связанных объектов после выбора таблицы
updateRelated = function() {
    updateFunctionList();
    $("#form")[0].reset();
};

initdatasources = function() {
    functionsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'level', type: 'int' },
            { name: 'script', type: 'string' },
            { name: 'comment', type: 'string' },
            { name: 'blocked', type: 'bool' }
        ],
        id: 'id',
        url: functionfetch_url + current_table,
        root: 'f'
    };
    functionsDataAdapter = new $.jqx.dataAdapter(functionsource);
};
// Таблица функций
initFunctionList = function() {
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
            selectionmode: 'checkbox',
            columns: [
                { text: 'Id', datafield: 'id', width: '50px' },
                { text: 'Код таблицы', datafield: 'table_code', width: '70px'  },
                { text: 'Уровень', datafield: 'level', width: '70px'  },
                { text: 'Функция контроля', datafield: 'script' , width: '50%'},
                { text: 'Комментарий', datafield: 'comment', width: '30%' },
                { text: 'Отключена', datafield: 'blocked', columntype: 'checkbox', width: '70px' }
            ]
        });
};
// Обновление списка строк при выборе таблицы
updateFunctionList = function() {
    functionsource.url = functionfetch_url + current_table;
    fgrid.jqxGrid('clearselection');
    fgrid.jqxGrid('updatebounddata');
};
// Операции с функциями контроля

initButtons = function() {
    $('#blocked').jqxSwitchButton({
        height: 31,
        width: 81,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    var errorlevelsource =
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

setquerystring = function() {
    return "&level=" + $("#level").val() +
        "&script=" + encodeURIComponent($("#script").val()) +
        "&comment=" + $("#comment").val() +
        "&blocked=" + ($("#blocked").val() ? 1 :0);
};

initFunctionActions = function() {
    $("#performControl").click(function () {
        var functions = getselectedfunctions();
        if (functions.length == 0) {
            raiseError("Не выбраны функции для контроля данных");
            return false;
        }
        var id = fgrid.jqxGrid('getrowid', row);
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
                    fgrid.jqxGrid('updatebounddata', 'data');
                    fgrid.on("bindingcomplete", function (event) {
                        var newindex = fgrid.jqxGrid('getrowboundindexbyid', id);
                        fgrid.jqxGrid('selectrow', newindex);
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
};

var getselectedfunctions = function () {
    var rowindexes = fgrid.jqxGrid('getselectedrowindexes');
    var indexes_length =  rowindexes.length;
    var row_ids = [];
    for (i = 0; i < indexes_length; i++) {
        row_ids.push(fgrid.jqxGrid('getrowid', rowindexes[i]));
    }
    return row_ids;
};