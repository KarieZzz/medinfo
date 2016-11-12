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
    var albumsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'album_name', type: 'string' },
            { name: 'default', type: 'bool' }
        ],
        id: 'id',
        url: album_url,
        root: 'album'
    };
    membersource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'group_id', type: 'int' },
            { name: 'uname', map: 'unit>unit_name', type: 'string' },
            { name: 'ou_id', type: 'int' }
        ],
        id: 'id',
        url: member_url + currentalbum,
        root: 'member'
    };
    AlbumDataAdapter = new $.jqx.dataAdapter(albumsource);
    memberDataAdapter = new $.jqx.dataAdapter(membersource);
};
inittablelist = function() {
    $("#AlbumList").jqxGrid(
        {
            width: '98%',
            height: '30%',
            theme: theme,
            localization: localize(),
            source: AlbumDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id Альбома', datafield: 'id', width: '70px' },
                { text: 'Наименование', datafield: 'album_name' , width: '400px'},
                { text: 'По умолчанию', datafield: 'default', columntype: 'checkbox', width: '70px' }
            ]
        });
    $('#AlbumList').on('rowselect', function (event) {
        var row = event.args.row;
        currentalbum = row.id;
        membersource.url = member_url + currentalbum;
        $("#memberList").jqxGrid('updatebounddata');
        $("#album_name").val(row.album_name);
        $("#default").val(row.default);
    });
    $("#memberList").jqxGrid(
        {
            width: '98%',
            height: '65%',
            theme: theme,
            localization: localize(),
            source: memberDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                {
                text: '№ п/п', sortable: false, filterable: false, editable: false,
                groupable: false, draggable: false, resizable: false,
                datafield: '', columntype: 'number', width: 50,
                cellsrenderer: function (row, column, value) {
                        return "<div style='margin:4px;'>" + (value + 1) + "</div>";
                    }
                },
                { text: 'МО', datafield: 'uname' , width: '580px'}
            ]
        });
};
setquerystring = function() {
    return "&group_name=" + $("#group_name").val() +
        "&slug=" + $("#slug").val() +
        "&parent_id=" + $("#parent_id").val();
};
initgroupactions = function() {
    $("#insert").click(function () {
        var data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: groupcreate_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                $("#unitGroupList").jqxGrid('updatebounddata', 'data');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        var row = $('#unitGroupList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = $("#unitGroupList").jqxGrid('getrowid', row);
        var data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: groupupdate_url + rowid,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                $("#unitGroupList").jqxGrid('updatebounddata', 'data');
                $("#unitGroupList").on("bindingcomplete", function (event) {
                    var newindex = $('#unitGroupList').jqxGrid('getrowboundindexbyid', rowid);
                    console.log(newindex);
                    $("#unitGroupList").jqxGrid('selectrow', newindex);

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
        var row = $('#unitGroupList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        var rowid = $("#unitGroupList").jqxGrid('getrowid', row);
        raiseConfirm("<strong>Внимание!</strong> Выбранная группа будет удалена вместе со всеми входящими в состав элементами и созданными документами.", event);
        $("#okButton").click(function () {
            hideConfirm();
            $.ajax({
                dataType: 'json',
                url: groupdelete_url + rowid,
                method: "DELETE",
                success: function (data, status, xhr) {
                    if (data.group_deleted) {
                        raiseInfo(data.message);
                        $("#form")[0].reset();
                        $("#unitGroupList").jqxGrid('updatebounddata', 'data');
                        $("#unitGroupList").jqxGrid('clearselection');

                    } else {
                        raiseError(data.message);
                    }
                },
                error: function (xhr, status, errorThrown) {
                    raiseError('Ошибка удаления группы', xhr);
                }
            });
        });
    });
};

getcheckedunits = function() {
    var ids = [];
    var checkedRows = $('#moTree').jqxTreeGrid('getCheckedRows');
    for (var i = 0; i < checkedRows.length; i++) {
        // get a row.
        ids.push(checkedRows[i].uid);
    }
    return ids;
};

initmemberactions = function() {
    $("#insertmembers").click(function() {
        var selectedunits = getcheckedunits();
        var data = "&units=" + selectedunits;
        $.ajax({
            dataType: 'json',
            url: addmembers_url + currentgroup,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.count_of_inserted > 0) {
                    raiseInfo("Добавлено учреждений в группу " + data.count_of_inserted);
                    $('#memberList').jqxGrid('clearselection');
                    $('#memberList').jqxGrid('updatebounddata');
                }
                else {
                    raiseError("Учреждения не добавлены");
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
            }
        });
    });
    $("#removemember").click(function() {
        var row = $('#memberList').jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для удаления из списка МО, входящих в текущую группу");
            return false;
        }
        var rowid = $("#memberList").jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: removemember_url + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (data.member_deleted) {
                    raiseInfo(data.message);
                    $('#memberList').jqxGrid('clearselection');
                    $('#memberList').jqxGrid('updatebounddata');
                }
                else {
                    raiseError("Учреждения из группы не удалены");
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
            }
        });
    });
}

initmotree = function() {
    var form_source =
    {
        dataType: "json",
        dataFields: [
            { name: 'id', type: 'int' },
            { name: 'form_code', type: 'string' },
            { name: 'form_name', type: 'string' }
        ],
        id: 'id',
        root: '',
        url: form_url
    };
    FormDataAdapter = new $.jqx.dataAdapter(form_source);
    $("#FormContainer").jqxPanel({width: '100%', height: '350px'});
    $("#Forms").jqxTreeGrid(
        {
            width: '98%',
            height: '99%',
            theme: theme,
            source: FormDataAdapter,
            selectionMode: "singleRow",
            localization: localize(),
            filterable: true,
            filterMode: "simple",
            columnsResize: true,
            checkboxes: true,
            ready: function () {
                $("#moTree").jqxTreeGrid('expandRow', 0);
            },
            columns: [
                {text: 'Код', dataField: 'unit_code', width: 150},
                {text: 'Наименование', dataField: 'unit_name'}
            ]
        });

    $('#Forms').on('filter',
        function (event) {
            var args = event.args;
            var filters = args.filters;
            $('#Forms').jqxTreeGrid('expandAll');
        }
    );
};
