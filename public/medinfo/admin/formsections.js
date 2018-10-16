// функция для обновления связанных объектов после выбора таблицы
let updateRelated = function() {
    updateFunctionList();
    $("#form")[0].reset();
};

let initdatasources = function() {
    fsectionsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'form_code', map: 'form>form_code', type: 'string' },
            { name: 'form_id', map: 'form>id', type: 'int' },
            { name: 'section_index', type: 'int' },
            { name: 'section_name', type: 'string' },
            { name: 'created_at', type: 'string' },
            { name: 'updated_at', type: 'string' }
        ],
        id: 'id',
        url: fsectionfetch_url,
        root: 'f'
    };
    fsectionDataAdapter = new $.jqx.dataAdapter(fsectionsource);
};
// Таблица функций
let initFunctionList = function() {
    fsgrid.jqxGrid(
        {
            width: '100%',
            height: '60%',
            theme: theme,
            localization: localize(),
            source: fsectionDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '50px' },
                { text: 'Форма', datafield: 'form_code', width: '50px'  },
                { text: 'Раздел', datafield: 'section_name' , width: '530px'},
                { text: 'Создан', datafield: 'created_at', width: '130px' },
                { text: 'Обновлен', datafield: 'updated_at', width: '130px' }
            ]
        });
    fsgrid.on('rowselect', function (event) {
        let row = event.args.row;
        $("#section_name").val(row.section_name);
        //let lev = row.level.code;
        $("#form option[value=" + row.form_id + "]").prop('selected', 'selected');
        //$("#form").val(row.form_id);
    });
};
// Обновление списка строк при выборе таблицы
let updateFunctionList = function() {
    //functionsource.url = functionfetch_url + current_table;
    fsgrid.jqxGrid('clearselection');
    fsgrid.jqxGrid('updatebounddata');
};
// Операции с функциями контроля

let setquerystring = function() {
    let include = $("#include").prop("checked") ? '1' :  '0' ;
    return "&section=" + $("#section_name").val() + "&form=" + $("#form").val() + "&album=" + $("#album").val() + "&include=" + include;
};

let initFunctionActions = function() {
    $("#update").click(function () {
        let row = fsgrid.jqxGrid('getselectedrowindex');
        if (row === -1 && typeof row !== 'undefined') {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let id = fsgrid.jqxGrid('getrowid', row);
        let rowdata = fsgrid.jqxGrid('getrowdatabyid', id);
        let data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: url + '/' + id,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                let updated = {};
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    fsgrid.jqxGrid('updatebounddata', 'data');
                    fsgrid.on("bindingcomplete", function (event) {
                        let newindex = fgrid.jqxGrid('getrowboundindexbyid', id);
                        fsgrid.jqxGrid('selectrow', newindex);
                        fsgrid.jqxGrid('ensurerowvisible', newindex);
                    });
                }
            },
            error: xhrErrorNotificationHandler
        });
    });

    $("#store").click(function () {
        let data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                fsgrid.jqxGrid('updatebounddata', 'data');
                fsgrid.on("bindingcomplete", function (event) {
                    let newindex = fsgrid.jqxGrid('getrowboundindexbyid', data.id);
                    fsgrid.jqxGrid('selectrow', newindex);
                    fsgrid.jqxGrid('ensurerowvisible', newindex);

                });
            },
            error: xhrErrorNotificationHandler
        });
    });

    $("#delete").click(function () {
        let row = fsgrid.jqxGrid('getselectedrowindex');
        if (row === -1 && typeof row !== 'undefined') {
            raiseError("Не выбрана запись для удаления");
            return false;
        }
        let id = fsgrid.jqxGrid('getrowid', row);
        let confirm_text = 'Подтвердите удаление раздела Id ' + id + '.';
        if (!confirm(confirm_text)) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: url + '/' + id,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    $("#formsection")[0].reset();
                }
                fsgrid.jqxGrid('updatebounddata', 'data');
                fsgrid.jqxGrid('clearselection');
            },
            error: xhrErrorNotificationHandler
        });

    });

    $("#editSection").click(function () {
        let row = fsgrid.jqxGrid('getselectedrowindex');
        if (row === -1 && typeof row !== 'undefined') {
            raiseError("Выберите раздел для редактирования");
            return false;
        }
        let id = fsgrid.jqxGrid('getrowid', row);
        window.open(editsection_url + id)
    });
};
