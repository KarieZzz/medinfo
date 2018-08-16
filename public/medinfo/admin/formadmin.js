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
    let formsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'form_index', type: 'int' },
            { name: 'form_name', type: 'string' },
            { name: 'form_code', type: 'string' },
            { name: 'medstat_code', type: 'string' },
            { name: 'short_ms_code', type: 'string' },
        ],
        id: 'id',
        url: 'fetchforms',
        root: null
    };
    formDataAdapter = new $.jqx.dataAdapter(formsource);
};
initperiodlist = function() {
    fl.jqxGrid(
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
                { text: 'Код Медстат', datafield: 'medstat_code', width: '100px' },
                { text: 'Сокр. код Медстат', datafield: 'short_ms_code', width: '100px' },
            ]
        });
    fl.on('rowselect', function (event) {
        let row = event.args.row;
        $("#form_name").val(row.form_name);
        $("#form_index").val(row.form_index);
        $("#form_code").val(row.form_code);
        $("#medstat_code").val(row.medstat_code);
        $("#short_ms_code").val(row.short_ms_code);
    });
};
initformactions = function() {
    $("#insert").click(function () {
        let data = "&form_name=" + $("#form_name").val() + "&form_index=" + $("#form_index").val() +
            "&form_code=" + $("#form_code").val()  +  "&medstat_code=" + $("#medstat_code").val() + "&short_ms_code=" + $("#short_ms_code").val();
        $.ajax({
            dataType: 'json',
            url: '/admin/forms/create',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                }
                fl.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        let row = fl.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let rowid = fl.jqxGrid('getrowid', row);
        let data = "id=" + rowid + "&form_name=" + $("#form_name").val() + "&form_index=" + $("#form_index").val() +
            "&form_code=" + $("#form_code").val() +  "&medstat_code=" + $("#medstat_code").val() + "&short_ms_code=" + $("#short_ms_code").val();
        $.ajax({
            dataType: 'json',
            url: '/admin/forms/update',
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
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
        let row = fl.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        if (!confirm('Если форма не содержит отчетных документов и данных, то она будет удалена вместе с входящими в нее таблицами, строками, графами')) {
            return false;
        }
        let rowid = fl.jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: '/admin/forms/delete/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    $("#form")[0].reset();
                }
                fl.jqxGrid('updatebounddata');
                fl.jqxGrid('clearselection');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка удаления отчетного периода', xhr);
            }
        });
    });
};