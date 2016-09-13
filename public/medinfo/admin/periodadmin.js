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
                    { size: '40%', min: '10%'},
                    { size: '40%', min: '10%'}
                ]
        }
    );
};
initdatasources = function() {
    var periodsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'name', type: 'string' },
            { name: 'begin_date', type: 'string' },
            { name: 'end_date', type: 'string' },
            { name: 'pattern_id', type: 'int' },
            { name: 'medinfo_id', type: 'string' }
        ],
        id: 'id',
        url: 'fetchperiods',
        root: null
    };
    perioddataAdapter = new $.jqx.dataAdapter(periodsource);
};
initperiodlist = function() {
    $("#periodList").jqxGrid(
        {
            width: '98%',
            height: '98%',
            theme: theme,
            localization: localize(),
            source: perioddataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '30px' },
                { text: 'Имя', datafield: 'name' , width: '200px'},
                { text: 'Начало', datafield: 'begin_date', width: '100px' },
                { text: 'Окончание', datafield: 'end_date', width: '100px' },
                { text: 'Паттерн', datafield: 'pattern_id', width: '100px'  },
                { text: 'Id Мединфо', datafield: 'medinfo_id', width: '100px' }
            ]
        });
    $('#periodList').on('rowselect', function (event) {
        var row = event.args.row;
        $("#name").val(row.name);
        $("#begin_date").val(row.begin_date);
        $("#end_date").val(row.end_date);
        $("#pattern_id").val(row.pattern_id);
        $("#medinfo_id").val(row.medinfo_id);
    });
};
initformactions = function() {
    $("#insert").click(function () {
        var data = "&name=" + $("#name").val() + "&begin_date=" + $("#begin_date").val() + "&end_date=" + $("#end_date").val() +
            "&pattern_id=" + $("#pattern_id").val() + "&medinfo_id=" + $("#medinfo_id").val();
        $.ajax({
            dataType: 'json',
            url: '/admin/periods/create',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                }
                $("#periodList").jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        var row = $('#periodList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = $("#periodList").jqxGrid('getrowid', row);
        var data = "id=" + rowid + "&name=" + $("#name").val() + "&begin_date=" + $("#begin_date").val() + "&end_date=" + $("#end_date").val() +
            "&pattern_id=" + $("#pattern_id").val() + "&medinfo_id=" + $("#medinfo_id").val();
        $.ajax({
            dataType: 'json',
            url: '/admin/periods/update',
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                $("#periodList").jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#delete").click(function () {
        var row = $('#periodList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = $("#periodList").jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: '/admin/periods/delete/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    $("#period")[0].reset();
                }
                $("#periodList").jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка удаления отчетного периода', xhr);
            }
        });
    });
};