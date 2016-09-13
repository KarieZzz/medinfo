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
initdatasources = function() {
    var formsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'group_id', type: 'int' },
            { name: 'form_index', type: 'int' },
            { name: 'form_name', type: 'string' },
            { name: 'form_code', type: 'string' },
            { name: 'file_name', type: 'string' },
            { name: 'medstat_code', type: 'string' },
            { name: 'medinfo_id', type: 'int' }
        ],
        id: 'id',
        url: 'fetchforms',
        root: null
    };
    formDataAdapter = new $.jqx.dataAdapter(formsource);
};
initperiodlist = function() {
    $("#formList").jqxGrid(
        {
            width: '98%',
            height: '90%',
            theme: theme,
            localization: localize(),
            source: formDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '30px' },
                { text: '№ п/п', datafield: 'form_index', width: '70px' },
                { text: 'Код формы', datafield: 'form_code', width: '100px'  },
                { text: 'Имя', datafield: 'form_name' , width: '400px'},
                { text: 'Группа', datafield: 'group_id', width: '70px' },
                { text: 'Файл', datafield: 'file_name', width: '70px'  },
                { text: 'Медстат Id', datafield: 'medstat_code', width: '100px' },
                { text: 'Мединфо Id', datafield: 'medinfo_id', width: '70px' }
            ]
        });
    $('#formList').on('rowselect', function (event) {
        var row = event.args.row;
        $("#form_name").val(row.form_name);
        $("#group_id").val(row.group_id);
        $("#form_index").val(row.form_index);
        $("#form_code").val(row.form_code);
        $("#file_name").val(row.file_name);
        $("#medstat_code").val(row.medstat_code);
        $("#medinfo_id").val(row.medinfo_id);
    });
};
initformactions = function() {
    $("#insert").click(function () {
        var data = "&form_name=" + $("#form_name").val() + "&group_id=" + $("#group_id").val() + "&form_index=" + $("#form_index").val() +
            "&form_code=" + $("#form_code").val() + "&file_name=" + $("#file_name").val() +  "&medstat_code=" + $("#medstat_code").val() +
            "&medinfo_id=" + $("#medinfo_id").val();
        $.ajax({
            dataType: 'json',
            url: '/admin/forms/create',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                }
                $("#formList").jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        var row = $('#formList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = $("#formList").jqxGrid('getrowid', row);
        var data = "id=" + rowid + "&form_name=" + $("#form_name").val() + "&group_id=" + $("#group_id").val() + "&form_index=" + $("#form_index").val() +
            "&form_code=" + $("#form_code").val() + "&file_name=" + $("#file_name").val() +  "&medstat_code=" + $("#medstat_code").val() +
            "&medinfo_id=" + $("#medinfo_id").val();
        $.ajax({
            dataType: 'json',
            url: '/admin/forms/update',
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                $("#formList").jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#delete").click(function () {
        var row = $('#formList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = $("#formList").jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: '/admin/forms/delete/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    $("#form")[0].reset();
                }
                $("#formList").jqxGrid('updatebounddata');
                $("#formList").jqxGrid('clearselection');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка удаления отчетного периода', xhr);
            }
        });
    });
};