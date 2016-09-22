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
                    { size: '50%', min: '10%'},
                    { size: '50%', min: '10%'}
                ]
        }
    );
};
initfilterdatasources = function() {
    var unittypessource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'form_code' }
        ],
        id: 'id',
        localdata: unitTypes
    };
    unittypesDataAdapter = new $.jqx.dataAdapter(unittypessource);
};
initdatasources = function() {
    var unitsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'parent', map: 'parent>unit_code', type: 'string' },
            { name: 'unit_code', type: 'string' },
            { name: 'inn', type: 'string' },
            { name: 'unit_name', type: 'string' },
            { name: 'node_type', type: 'int' },
            { name: 'report', type: 'int' },
            { name: 'aggregate', type: 'int' },
            { name: 'medinfo_id', type: 'int' }
        ],
        id: 'id',
        url: unitfetch_url,
        root: 'unit'
    };
    unitDataAdapter = new $.jqx.dataAdapter(unitsource);
};
inittablelist = function() {
    $("#unitList").jqxGrid(
        {
            width: '98%',
            height: '90%',
            theme: theme,
            localization: localize(),
            source: unitDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '30px' },
                { text: 'Входит в', datafield: 'parent', width: '50px' },
                { text: 'Код', datafield: 'unit_code', width: '100px'  },
                { text: 'ИНН', datafield: 'inn', width: '100px'  },
                { text: 'Имя', datafield: 'unit_name' , width: '400px'},
                { text: 'Первичный', datafield: 'report' , width: '40px'},
                { text: 'Сводный', datafield: 'aggregate' , width: '40px'},
                { text: 'Мединфо Id', datafield: 'medinfo_id', width: '70px' }
            ]
        });
    $('#unitList').on('rowselect', function (event) {
        var row = event.args.row;
        $("#unit_name").val(row.unit_name);
        $("#parent_id").val(row.parent_id);
        $("#unit_code").val(row.unit_code);
        $("#inn").val(row.inn);
        $("#report").val( row.report == 1 );
        $("#aggregate").val(row.aggregate == 1);
        $("#medinfo_id").val(row.medinfo_id);
    });
};
initunitactions = function() {
    $("#node_type").jqxDropDownList({
        theme: theme,
        source: unittypesDataAdapter,
        displayMember: "code",
        valueMember: "id",
        placeHolder: "Выберите тип ОЕ:",
        //selectedIndex: 2,
        width: 200,
        height: 34
    });
    $('#report').jqxSwitchButton({
        height: 31,
        width: 81,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    $('#aggregate').jqxSwitchButton({
        height: 31,
        width: 81,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    $("#insert").click(function () {
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
    $("#save").click(function () {
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
    $("#delete").click(function () {
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