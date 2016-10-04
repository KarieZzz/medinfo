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
    var conditionsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'group_id', type: 'int' },
            { name: 'gname', map: 'group>group_name', type: 'string' },
            { name: 'condition_name', type: 'string' }
        ],
        id: 'id',
        url: 'fetchconditions',
        root: null
    };
    conditionDataAdapter = new $.jqx.dataAdapter(conditionsource);
};
initConditionList = function() {
    $("#conditionList").jqxGrid(
        {
            width: '98%',
            height: '98%',
            theme: theme,
            localization: localize(),
            source: conditionDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '70px' },
                { text: 'Id группы', datafield: 'group_id', width: '70px' },
                { text: 'Наименование группы', datafield: 'gname', width: '300px' },
                { text: 'Наименование', datafield: 'condition_name' , width: '500px'}
            ]
        });
    $('#conditionList').on('rowselect', function (event) {
        $("#group_id").jqxDropDownList('clearSelection');
        var row = event.args.row;
        $("#group_id").val(row.group_id);
        $("#condition_name").val(row.condition_name);
    });
};

initdropdowns = function() {
    var groupesource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'group_name' }
        ],
        id: 'id',
        localdata: groups
    };
    groupeDataAdapter = new $.jqx.dataAdapter(groupesource);
    $("#group_id").jqxDropDownList({
        theme: theme,
        source: groupeDataAdapter,
        filterable: true,
        filterPlaceHolder: "Поиск",
        displayMember: "group_name",
        valueMember: "id",
        placeHolder: "Выберите группу:",
        width: 300,
        height: 34
    });
};

initformactions = function() {
    $("#insert").click(function () {
        var data = "&condition_name=" + $("#condition_name").val() + "&group_id=" + $("#group_id").val();
        $.ajax({
            dataType: 'json',
            url:  '/admin/necells/conditioncreate',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                }
                $("#conditionList").jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        var row = $('#conditionList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = $("#conditionList").jqxGrid('getrowid', row);
        var data = "&condition_name=" + $("#condition_name").val() + "&group_id=" + $("#group_id").val();
        $.ajax({
            dataType: 'json',
            url: '/admin/necells/conditionsave/' + rowid,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                $("#conditionList").jqxGrid('updatebounddata', 'data');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#delete").click(function () {
        var row = $('#conditionList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = $("#conditionList").jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: '/admin/necells/conditiondelete/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    $("#period")[0].reset();
                }
                $("#conditionList").jqxGrid('updatebounddata');
                $("#conditionList").jqxGrid('clearselection');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка удаления отчетного периода', xhr);
            }
        });
    });
};