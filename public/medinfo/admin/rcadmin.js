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
    rowsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'excluded', map: 'excluded>0>album_id', type: 'bool' },
            { name: 'row_index', type: 'int' },
            { name: 'row_code', type: 'string' },
            { name: 'row_name', type: 'string' },
            { name: 'medstat_code', type: 'string' },
            { name: 'medinfo_id', type: 'int' }
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
            { name: 'column_index', type: 'int' },
            { name: 'column_name', type: 'string' },
            { name: 'content_type', type: 'int' },
            { name: 'size', type: 'int' },
            { name: 'decimal_count', type: 'int' },
            { name: 'medstat_code', type: 'string' },
            { name: 'medinfo_id', type: 'int' }
        ],
        id: 'id',
        url: columnfetch_url + current_table,
        root: 'column'
    };
    rowsDataAdapter = new $.jqx.dataAdapter(rowsource);
    columnsDataAdapter = new $.jqx.dataAdapter(columnsource);
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
                { text: 'Исключена из альбома', datafield: 'excluded', columntype: 'checkbox', width: '70px'  },
                { text: 'Код таблицы', datafield: 'table_code', width: '70px'  },
                { text: 'Код строки', datafield: 'row_code', width: '70px'  },
                { text: 'Имя', datafield: 'row_name' , width: '480px'},
                { text: 'Код Медстат', datafield: 'medstat_code', width: '80px' },
                { text: 'Мединфо Id', datafield: 'medinfo_id', width: '70px' }
            ]
        });
    rlist.on('rowselect', function (event) {
        var row = event.args.row;
        $("#row_index").val(row.row_index);
        $("#row_name").val(row.row_name);
        $("#row_code").val(row.row_code);
        $("#row_medstat_code").val(row.medstat_code);
        $("#row_medinfo_id").val(row.medinfo_id);
        $("#excludedRow").val(row.excluded != null);
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
                { text: 'Код таблицы', datafield: 'table_code', width: '70px'  },
                { text: 'Имя', datafield: 'column_name' , width: '300px'},
                { text: 'Тип', datafield: 'content_type', width: '100px' },
                { text: 'Размер', datafield: 'size', width: '100px' },
                { text: 'Десятичные', datafield: 'decimal_count', width: '100px' },
                { text: 'Код Медстат', datafield: 'medstat_code', width: '70px' },
                { text: 'Мединфо Id', datafield: 'medinfo_id', width: '70px' }
            ]
        });
    clist.on('rowselect', function (event) {
        var row = event.args.row;
        $("#column_index").val(row.column_index);
        $("#column_name").val(row.column_name);
        $("#content_type").val(row.content_type);
        $("#size").val(row.size);
        $("#decimal_count").val(row.decimal_count);
        $("#column_medstat_code").val(row.medstat_code);
        $("#column_medinfo_id").val(row.medinfo_id);
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
        "&medinfo_id=" + $("#row_medinfo_id").val();
};

setcolumnquery = function() {
    return "&table_id=" + current_table +
        "&column_index=" + $("#column_index").val() +
        "&column_name=" + $("#column_name").val() +
        "&content_type=" + $("#content_type").val() +
        "&size=" + $("#size").val() +
        "&decimal_count=" + $("#decimal_count").val() +
        "&medstat_code=" + $("#column_medstat_code").val() +
        "&medinfo_id=" + $("#column_medinfo_id").val();
};

initButtons = function() {
    $('#excludedRow').jqxSwitchButton({
        height: 31,
        width: 81,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });

};

// Операции со строками
initRowActions = function() {
    $("#insertrow").click(function () {
        var data = setrowquery();
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/rowcreate',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                rlist.jqxGrid('updatebounddata', 'data');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#saverow").click(function () {
        var row = rlist.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = rlist.jqxGrid('getrowid', row);
        var data = setrowquery();
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/rowupdate/' + rowid,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                rlist.jqxGrid('updatebounddata', 'data');
                rlist.on("bindingcomplete", function (event) {
                    var newindex = rlist.jqxGrid('getrowboundindexbyid', rowid);
                    rlist.jqxGrid('selectrow', newindex);

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
        var row = rlist.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = rlist.jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/rowdelete/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
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
        var data = setcolumnquery();
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/columncreate',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                clist.jqxGrid('updatebounddata', 'data');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#savecolumn").click(function () {
        var row = clist.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = clist.jqxGrid('getrowid', row);
        var data = setcolumnquery();
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/columnupdate/' + rowid,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                clist.jqxGrid('updatebounddata', 'data');
                clist.on("bindingcomplete", function (event) {
                    var newindex = clist.jqxGrid('getrowboundindexbyid', rowid);
                    clist.jqxGrid('selectrow', newindex);

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
        var row = clist.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = clist.jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/columndelete/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
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