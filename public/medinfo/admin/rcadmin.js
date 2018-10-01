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
                    { size: '50%', min: '10%', collapsible: false },
                    { size: '50%', min: '10%', collapsible: false }
                ]
        }
    );
};

initdatasources = function() {
    let columnTypessource =
        {
            datatype: "json",
            datafields: [
                { name: 'code' },
                { name: 'name' }
            ],
            id: 'code',
            localdata: columnTypes
        };

    rowsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            //{ name: 'excluded', map: 'excluded>0>album_id', type: 'bool' },
            { name: 'excluded', map: 'excluded>0>album_id', type: 'int' },
            { name: 'row_index', type: 'int' },
            { name: 'row_code', type: 'string' },
            { name: 'row_name', type: 'string' },
            { name: 'medstat_code', type: 'string' },
            { name: 'medstatnsk_id', type: 'int' }
        ],
        id: 'id',
        url: rowfetch_url + current_table,
        root: 'row'
    };
    columnsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'excluded', map: 'excluded>0>album_id', type: 'int' },
            { name: 'column_index', type: 'int' },
            { name: 'column_name', type: 'string' },
            { name: 'column_code', type: 'string' },
            { name: 'content_type', type: 'int' },
            { name: 'size', type: 'int' },
            { name: 'decimal_count', type: 'int' },
            { name: 'medstat_code', type: 'string' },
            { name: 'medstatnsk_id', type: 'int' }
        ],
        id: 'id',
        url: columnfetch_url + current_table,
        root: 'column'
    };
    rowsDataAdapter = new $.jqx.dataAdapter(rowsource);
    columnsDataAdapter = new $.jqx.dataAdapter(columnsource);
    columnTypesDataAdapter = new $.jqx.dataAdapter(columnTypessource);

};
// Таблица строк
initRowList = function() {
    rlist.jqxGrid(
        {
            width: '98%',
            height: '300px',
            theme: theme,
            localization: localize(),
            source: rowsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '50px' },
                { text: '№ п/п', datafield: 'row_index', width: '50px' },
                //{ text: 'Исключена из альбома', datafield: 'excluded', columntype: 'checkbox', width: '90px'  },
                { text: 'Исключена из альбома', datafield: 'excluded', width: '90px'  },
                //{ text: 'Код таблицы', datafield: 'table_code', width: '70px'  },
                { text: 'Код', datafield: 'row_code', width: '70px'  },
                { text: 'Имя', datafield: 'row_name' , width: '480px'},
                { text: 'Код МС(мск)', datafield: 'medstat_code', width: '80px' },
                { text: 'Код МС(нск)', datafield: 'medstatnsk_id', width: '70px' }
            ]
        });
    rlist.on('rowselect', function (event) {
        let row = event.args.row;
        $("#row_index").val(row.row_index);
        $("#row_name").val(row.row_name);
        $("#row_code").val(row.row_code);
        $("#row_medstat_code").val(row.medstat_code);
        $("#row_medstatnsk_id").val(row.medstatnsk_id);
        //$("#excludedRow").val(row.excluded);
        row.excluded > 0 ? $("#excludedRow").val(true) : $("#excludedRow").val(false);
    });
};
//Таблица граф
initColumnList = function() {
    clist.jqxGrid(
        {
            width: '98%',
            height: '300px',
            theme: theme,
            localization: localize(),
            source: columnsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '50px' },
                { text: '№ п/п', datafield: 'column_index', width: '50px' },
                //{ text: 'Исключена из альбома', datafield: 'excluded', columntype: 'checkbox', width: '90px'  },
                { text: 'Исключена из альбома', datafield: 'excluded', width: '90px'  },
                //{ text: 'Код таблицы', datafield: 'table_code', width: '70px'  },
                { text: 'Имя', datafield: 'column_name' , width: '300px'},
                { text: 'Код', datafield: 'column_code' , width: '50px'},
                { text: 'Тип', datafield: 'content_type', width: '50px' },
                { text: 'Размер', datafield: 'size', width: '70px' },
                { text: 'Десятичные', datafield: 'decimal_count', width: '90px' },
                { text: 'Код МС(мск)', datafield: 'medstat_code', width: '70px' },
                { text: 'Код МС(нск)', datafield: 'medstatnsk_id', width: '70px' }
            ]
        });
    clist.on('rowselect', function (event) {
        let row = event.args.row;
        $("#column_index").val(row.column_index);
        $("#column_name").val(row.column_name);
        $("#column_code").val(row.column_code);
        $("#column_type").val(row.content_type);
        $("#field_size").val(row.size);
        $("#decimal_count").val(row.decimal_count);
        $("#column_medstat_code").val(row.medstat_code);
        $("#column_medstatnsk_id").val(row.medstatnsk_id);
        row.excluded > 0 ? $("#excludedColumn").val(true) : $("#excludedColumn").val(false);

    });
};
// функция для обновления связанных объектов после выбора таблицы
updateRelated = function() {
    updateRowList();
    updateColumnList();
    $("#rowform")[0].reset();
    $("#columnform")[0].reset();
};

// Обновление списка строк при выборе таблицы
updateRowList = function() {
    rowsource.url = rowfetch_url + current_table;
    rlist.jqxGrid('clearselection');
    rlist.jqxGrid('updatebounddata');
};
// Обновление списка граф при выборе таблицы
updateColumnList = function() {
    columnsource.url = columnfetch_url + current_table;
    clist.jqxGrid('clearselection');
    clist.jqxGrid('updatebounddata');
};

setrowquery = function() {
    return "&table_id=" + current_table +
        "&row_index=" + $("#row_index").val() +
        "&row_code=" + $("#row_code").val() +
        "&row_name=" + $("#row_name").val() +
        "&medstat_code=" + $("#row_medstat_code").val() +
        "&medstatnsk_id=" + $("#row_medstatnsk_id").val() +
        "&excluded=" + ($("#excludedRow").val() ? 1 : 0);
};

setcolumnquery = function() {
    return "&table_id=" + current_table +
        "&column_index=" + $("#column_index").val() +
        "&column_name=" + $("#column_name").val() +
        "&column_code=" + $("#column_code").val() +
        "&content_type=" + $("#column_type").val() +
        "&field_size=" + $("#field_size").val() +
        "&decimal_count=" + $("#decimal_count").val() +
        "&medstat_code=" + $("#column_medstat_code").val() +
        "&medstatnsk_id=" + $("#column_medstatnsk_id").val() +
        "&excluded=" + ($("#excludedColumn").val() ? 1 : 0);
};

initButtons = function() {
    let typelist = $("#column_type");
    typelist.jqxDropDownList({
        theme: theme,
        source: columnTypesDataAdapter,
        displayMember: "name",
        valueMember: "code",
        placeHolder: "Выберите тип поля:",
        //selectedIndex: 2,
        width: 200,
        height: 32
    });
    typelist.on('change', function (event) {
        let args = event.args;
        if (args) {
            let index = args.index;
            let item = args.item;
            let label = item.label;
            let value = item.value;
            let type = args.type; // keyboard, mouse or null depending on how the item was selected.
            if (label === 'Вычисляемая графа') {
                $("#editFormula").show();
            } else {
                $("#editFormula").hide();
            }
        }
    });
    $('#excludedRow').jqxSwitchButton({
        height: 31,
        width: 120,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    $('#excludedColumn').jqxSwitchButton({
        height: 31,
        width: 120,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
};

// Операции со строками
initRowActions = function() {
    $("#insertrow").click(function () {
        if (current_table === 0) {
            raiseError('Не выбрана текущая таблица');
            return false;
        }
        let data = setrowquery();
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/rowcreate',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                rlist.jqxGrid('updatebounddata', 'data');
                rlist.on("bindingcomplete", function (event) {
                    let newindex = rlist.jqxGrid('getrowboundindexbyid', data.id);
                    rlist.jqxGrid('selectrow', newindex);
                    rlist.jqxGrid('ensurerowvisible', newindex);

                });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#saverow").click(function () {
        let row = rlist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let rowid = rlist.jqxGrid('getrowid', row);
        let data = setrowquery();
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/rowupdate/' + rowid,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                rlist.jqxGrid('updatebounddata', 'data');
                rlist.on("bindingcomplete", function (event) {
                    var newindex = rlist.jqxGrid('getrowboundindexbyid', rowid);
                    rlist.jqxGrid('selectrow', newindex);
                    rlist.jqxGrid('ensurerowvisible', newindex);

                });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#deleterow").click(function () {
        let row = rlist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        let rowid = rlist.jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/rowdelete/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    $("#rowform")[0].reset();
                }
                rlist.jqxGrid('updatebounddata', 'data');
                rlist.jqxGrid('clearselection');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка удаления записи', xhr);
            }
        });
    });
};
initColumnActions = function() {
    $("#insertcolumn").click(function () {
        if (current_table === 0) {
            raiseError('Не выбрана текущая таблица');
            return false;
        }
        let data = setcolumnquery();
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/columncreate',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                clist.jqxGrid('updatebounddata', 'data');
                clist.on("bindingcomplete", function (event) {
                    let newindex = clist.jqxGrid('getrowboundindexbyid', data.id);
                    clist.jqxGrid('selectrow', newindex);
                    clist.jqxGrid('ensurerowvisible', newindex);

                });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#savecolumn").click(function () {
        let row = clist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let rowid = clist.jqxGrid('getrowid', row);
        let data = setcolumnquery();
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/columnupdate/' + rowid,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                clist.jqxGrid('updatebounddata', 'data');
                clist.on("bindingcomplete", function (event) {
                    let newindex = clist.jqxGrid('getrowboundindexbyid', rowid);
                    clist.jqxGrid('selectrow', newindex);
                    clist.jqxGrid('ensurerowvisible', newindex);

                });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#deletecolumn").click(function () {
        let row = clist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        let rowid = clist.jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/columndelete/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    $("#columnform")[0].reset();
                }
                clist.jqxGrid('updatebounddata', 'data');
                clist.jqxGrid('clearselection');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка удаления записи', xhr);
            }
        });
    });
};

let initColumnFormulaWindow = function () {
    let savebutton = $("#saveFormula");
    let formulaWindow = $('#formulaWindow');
    let formula = $("#formula");
    let formulaexists = false;
    let columnid;
    let formulaid = null;
    $("#editFormula").click(function () {
        formula.attr("placeholder", "");;
        let colHeader = $("#columnNameId");
        colHeader.html("");
        let row = clist.jqxGrid('getselectedrowindex');
        columnid = clist.jqxGrid('getrowid', row);
        if (row === -1) {
            raiseError("Не выбрана графа для ввода/изменения формулы расчета");
            return false;
        }
        colHeader.html($("#column_name").val() + ' (Id:' + columnid + ')');
        $.get(showcolumnformula_url + columnid, function( data ) {
            if (data.formula) {
                formula.val(data.formula);
                formulaid = data.id;
                formulaexists = true;
            } else {
                formula.val('');
                formulaexists = false;
            }
            formula.attr("placeholder", data.placeholder);
        });
        formulaWindow.jqxWindow('open');
    });

    formulaWindow.jqxWindow({
        width: 600,
        height: 290,
        resizable: false,
        autoOpen: false,
        isModal: true,
        cancelButton: $('#cancelButton'),
        position: { x: 310, y: 125 }
    });
    savebutton.click(function() {
        let data = "&formula=" + encodeURIComponent(formula.val());
        let method;
        let url;
        if (formulaexists) {
            method = 'PATCH';
            url = updatecolumnformula_url + formulaid;
        } else {
            method = 'POST';
            url = storecolumnformula_url + columnid;
        }
        $.ajax({
            dataType: 'json',
            url: url,
            method: method,
            data: data,
            success: function (data, status, xhr) {
                if (data.saved) {
                    raiseInfo("Изменения сохранены");
                }
                else {
                    raiseError("Ошибка сохранения. " + data.message)
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