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
var initfilterdatasources = function() {
    var forms_source =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'form_code' }
        ],
        id: 'id',
        localdata: forms
    };
    formsDataAdapter = new $.jqx.dataAdapter(forms_source);
    var table_source =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'table_code' }
        ],
        id: 'id',
        localdata: tables
    };
    tablesDataAdapter = new $.jqx.dataAdapter(table_source);
};
initdatasources = function() {
    rowsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'row_index', type: 'int' },
            { name: 'row_code', type: 'string' },
            { name: 'row_name', type: 'string' },
            { name: 'medstat_code', type: 'string' },
            { name: 'medinfo_id', type: 'int' }
        ],
        id: 'id',
        url: 'fetchrows/' + current_table,
        root: 'row'
    };
    rowsDataAdapter = new $.jqx.dataAdapter(rowsource);
};
initRowList = function() {
    $("#rowList").jqxGrid(
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
                { text: 'Id', datafield: 'id', width: '30px' },
                { text: '№ п/п', datafield: 'row_index', width: '50px' },
                { text: 'Код таблицы', datafield: 'table_code', width: '70px'  },
                { text: 'Код строки', datafield: 'row_code', width: '70px'  },
                { text: 'Имя', datafield: 'row_name' , width: '550px'},
                { text: 'Код Медстат', datafield: 'medstat_code', width: '100px' },
                { text: 'Мединфо Id', datafield: 'medinfo_id', width: '70px' }
            ]
        });
    $('#rowList').on('rowselect', function (event) {
        var row = event.args.row;
        $("#row_index").val(row.row_index);
        $("#row_name").val(row.row_name);
        $("#table_id").val(row.table_id);
        $("#row_code").val(row.row_code);
        $("#medstat_code").val(row.medstat_code);
        $("#medinfo_id").val(row.medinfo_id);
    });
};

updateRowList = function() {
    rowsource.url = rowfetch_url + current_table;
    $('#rowList').jqxGrid('clearselection');
    $('#rowList').jqxGrid('updatebounddata');
};


initFormTableFilter = function() {
    $("#form_id").jqxDropDownList({
        theme: theme,
        source: formsDataAdapter,
        displayMember: "form_code",
        valueMember: "id",
        placeHolder: "Выберите форму:",
        //selectedIndex: 2,
        width: 200,
        height: 34
    });
    $("#table_id").jqxDropDownList({
        theme: theme,
        source: tablesDataAdapter,
        displayMember: "table_code",
        valueMember: "id",
        placeHolder: "Выберите таблицу:",
        //selectedIndex: 2,
        width: 200,
        height: 34
    });
    $('#table_id').on('select', function (event)
    {
        var args = event.args;
        current_table = args.item.value;
        updateRowList();
    });
    
};

initrowactions = function() {
    $("#insertrow").click(function () {
        var data = "&form_id=" + $("#form_id").val() +
            "&table_index=" + $("#table_index").val() +
            "&table_code=" + $("#table_code").val() +
            "&table_name=" + $("#table_name").val() +
            "&medstat_code=" + $("#medstat_code").val() +
            "&medinfo_id=" + $("#medinfo_id").val() +
            "&transposed=" + ($("#transposed").val() ? 1 :0);
        $.ajax({
            dataType: 'json',
            url: '/admin/tables/create',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                }
                $("#tableList").jqxGrid('updatebounddata', 'data');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#saverow").click(function () {
        var row = $('#tableList').jqxGrid('getselectedrowindex');
        console.log(row);
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = $("#tableList").jqxGrid('getrowid', row);

        var data = "&id=" + rowid +
            "&form_id=" + $("#form_id").val() +
            "&table_index=" + $("#table_index").val() +
            "&table_code=" + $("#table_code").val() +
            "&table_name=" + $("#table_name").val() +
            "&medstat_code=" + $("#medstat_code").val() +
            "&medinfo_id=" + $("#medinfo_id").val() +
            "&transposed=" + ($("#transposed").val() ? 1 :0);
        $.ajax({
            dataType: 'json',
            url: '/admin/tables/update',
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                $("#tableList").jqxGrid('updatebounddata', 'data');
                $("#tableList").on("bindingcomplete", function (event) {
                    var newindex = $('#tableList').jqxGrid('getrowboundindexbyid', rowid);
                    console.log(newindex);
                    $("#tableList").jqxGrid('selectrow', newindex);

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
        var row = $('#tableList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = $("#tableList").jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: '/admin/tables/delete/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    $("#form")[0].reset();
                }
                $("#tableList").jqxGrid('updatebounddata', 'data');
                $("#tableList").jqxGrid('clearselection');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка удаления отчетного периода', xhr);
            }
        });
    });
};