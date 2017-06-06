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
    var periodsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'name', type: 'string' },
            { name: 'begin_date', type: 'string' },
            { name: 'end_date', type: 'string' },
            { name: 'pattern_id', type: 'int' },
            { name: 'pattern', map: 'periodpattern>name', type: 'string' },
            { name: 'medinfo_id', type: 'string' }
        ],
        id: 'id',
        url: 'fetchperiods',
        root: null
    };
    periodDataAdapter = new $.jqx.dataAdapter(periodsource);

    var patternsource =
        {
            datatype: "json",
            datafields: [
                { name: 'id' },
                { name: 'name' }
            ],
            id: 'id',
            localdata: patterns
        };
    patternsDataAdapter = new $.jqx.dataAdapter(patternsource);
};

initFormElements = function () {
    $("#pattern_id").jqxDropDownList({
        theme: theme,
        source: patternsDataAdapter,
        displayMember: "name",
        valueMember: "id",
        placeHolder: "Выберите шаблон периода:",
        width: 405,
        height: 34
    });
};

initCreatePeriodWindow = function () {
    var container = $('#mainSplitter');
    var offset = container.offset();
    $('#createPeriodForm').jqxWindow({
        position: { x: offset.left + 100, y: offset.top + 100} ,
        autoOpen: false,
        height: 455, width: 770,
        resizable: false,
        isModal: true,
        modalOpacity: 0.3,
        okButton: $('#ok'),
        cancelButton: $('#cancel'),
        initContent: function () {
            $('#ok').focus();

        }
    });
};

initCreateActions = function () {
    $('#ok').click(function () {
        $.ajax({
            dataType: 'json',
            url: '/admin/periods/store',
            method: "POST",
            data: setCreateQuery(),
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                plist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
};

setCreateQuery = function () {
    return "&year=" + $("#year").val() + "&pattern_id=" + $("#pattern_id").val();
};

initperiodlist = function() {
    plist.jqxGrid(
        {
            width: '98%',
            height: '98%',
            theme: theme,
            localization: localize(),
            source: periodDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '30px' },
                { text: 'Имя', datafield: 'name' , width: '250px'},
                { text: 'Начало', datafield: 'begin_date', width: '100px' },
                { text: 'Окончание', datafield: 'end_date', width: '100px' },
                { text: 'Шаблон', datafield: 'pattern', width: '250px'  },
                { text: 'Id Мединфо', datafield: 'medinfo_id', width: '100px' }
            ]
        });
    plist.on('rowselect', function (event) {
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
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                }
                plist.jqxGrid('updatebounddata');
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
                if (typeof data.error !== 'undefined') {
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
        var row = plist.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = plist.jqxGrid('getrowid', row);
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
                plist.jqxGrid('updatebounddata');
                plist.jqxGrid('clearselection');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка удаления отчетного периода', xhr);
            }
        });
    });

    $("#create").click(function () {
        $("#createPeriodForm").jqxWindow('open');
    });
};