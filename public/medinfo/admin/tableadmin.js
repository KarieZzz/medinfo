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
};
initdatasources = function() {
    var tablesource =
    {
        datatype: "json",
        datafields: [
            { name: 'form_code', map: 'form>form_code', type: 'string' },
            { name: 'id', type: 'int' },
            { name: 'table_index', type: 'int' },
            { name: 'form_id', type: 'int' },
            { name: 'table_name', type: 'string' },
            { name: 'table_code', type: 'string' },
            { name: 'transposed', type: 'imt' },
            { name: 'medstat_code', type: 'string' },
            { name: 'medinfo_id', type: 'int' }
        ],
        id: 'id',
        url: 'fetchtables',
        root: 'table'
    };
    tableDataAdapter = new $.jqx.dataAdapter(tablesource);
};
inittablelist = function() {
    $("#tableList").jqxGrid(
        {
            width: '98%',
            height: '90%',
            theme: theme,
            localization: localize(),
            source: tableDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '30px' },
                { text: '№ п/п', datafield: 'table_index', width: '50px' },
                { text: 'Форма (Id)', datafield: 'form_id', width: '70px'  },
                { text: 'Код формы', datafield: 'form_code', width: '100px'  },
                { text: 'Код таблицы', datafield: 'table_code', width: '100px'  },
                { text: 'Имя', datafield: 'table_name' , width: '400px'},
                { text: 'Транспнирование', datafield: 'transposed', width: '70px' },
                { text: 'Код Медстат', datafield: 'medstat_code', width: '100px' },
                { text: 'Мединфо Id', datafield: 'medinfo_id', width: '70px' }
            ]
        });
    $('#tableList').on('rowselect', function (event) {
        var row = event.args.row;
        $("#table_index").val(row.table_index);
        $("#table_name").val(row.table_name);
        $("#form_id").val(row.form_id);
        $("#table_code").val(row.table_code);
        $("#transposed").val(row.transposed);
        $("#medstat_code").val(row.medstat_code);
        $("#medinfo_id").val(row.medinfo_id);
    });
};
initformactions = function() {
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
    $("#insert").click(function () {
        var data = "&form_id=" + $("#form_id").val() +
            "&table_index=" + $("#table_index").val() +
            "&table_code=" + $("#table_code").val() +
            "&table_name=" + $("#table_name").val() +
            "&medstat_code=" + $("#medstat_code").val() +
            "&medinfo_id=" + $("#medinfo_id").val() +
            "&transposed=" + $("#transposed").val();
;
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
            "&transposed=" + $("#transposed").val();
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