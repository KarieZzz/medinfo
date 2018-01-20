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
    let unitgroupsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'parent_id', type: 'int' },
            { name: 'parent', map: 'parent>group_name', type: 'string' },
            { name: 'group_code', type: 'string' },
            { name: 'group_name', type: 'string' },
            { name: 'slug', type: 'string' }
        ],
        id: 'id',
        url: unitgroup_url,
        root: 'unit'
    };
    membersource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'group_id', type: 'int' },
            { name: 'ucode', map: 'unit>unit_code', type: 'string' },
            { name: 'uname', map: 'unit>unit_name', type: 'string' },
            { name: 'ou_id', type: 'int' }
        ],
        id: 'ou_id',
        url: member_url + currentgroup,
        root: 'member'
    };
    unitGroupDataAdapter = new $.jqx.dataAdapter(unitgroupsource);
    memberDataAdapter = new $.jqx.dataAdapter(membersource);
};
inittablelist = function() {
    grouplist.jqxGrid(
        {
            width: '98%',
            height: '30%',
            theme: theme,
            localization: localize(),
            source: unitGroupDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id Группы', datafield: 'id', width: '70px' },
                { text: 'Входит в', datafield: 'parent', width: '90px' },
                { text: 'Код', datafield: 'group_code' , width: '70px'},
                { text: 'Наименование', datafield: 'group_name' , width: '400px'},
                { text: 'Псевдоним', datafield: 'slug' , width: '200px'}
            ]
        });
    grouplist.on('rowselect', function (event) {
        parentid.jqxDropDownList('clearFilter');
        let row = event.args.row;
        currentgroup = row.id;
        //console.log(row.id);
        membersource.url = member_url + currentgroup;
        unitsource.url = units_url + currentgroup;
        memberlist.jqxGrid('updatebounddata');
        units.jqxGrid('updatebounddata');
        $("#group_code").val(row.group_code);
        $("#group_name").val(row.group_name);
        $("#slug").val(row.slug);
        parentid.val(row.parent_id);
        $("#medinfo_id").val(row.medinfo_id);
    });
    memberlist.jqxGrid(
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
            selectionmode: 'multiplerowsextended',
            columns: [
                {
                text: '№ п/п', sortable: false, filterable: false, editable: false,
                groupable: false, draggable: false, resizable: false,
                datafield: '', columntype: 'number', width: 50,
                cellsrenderer: function (row, column, value) {
                        return "<div style='margin:4px;'>" + (value + 1) + "</div>";
                    }
                },
                { text: 'id', datafield: 'ou_id' , width: '50px'},
                { text: 'код', datafield: 'ucode' , width: '50px'},
                { text: 'МО', datafield: 'uname' , width: '580px'}
            ]
        });
};
setquerystring = function() {
    return "&group_name=" + $("#group_name").val() +
        "&group_code=" + $("#group_code").val() +
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
        let row = grouplist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        currentgroup = grouplist.jqxGrid('getrowid', row);
        raiseConfirm("<strong>Внимание!</strong> Выбранная группа будет удалена вместе со всеми входящими в состав элементами и созданными документами.", event);
    });
};

let getselectednonmembers = function () {
    let rowindexes = units.jqxGrid('getselectedrowindexes');
    let indexes_length =  rowindexes.length;
    let selectedunits = [];
    for (i = 0; i < indexes_length; i++) {
        selectedunits.push(units.jqxGrid('getrowid', rowindexes[i]));
    }
    return selectedunits;
};

let getselectedmembers = function () {
    let rowindexes = memberlist.jqxGrid('getselectedrowindexes');
    let indexes_length =  rowindexes.length;
    let selectedunits = [];
    for (i = 0; i < indexes_length; i++) {
        selectedunits.push(memberlist.jqxGrid('getrowid', rowindexes[i]));
    }
    return selectedunits;
};

initmemberactions = function() {
    $("#insertmembers").click(function() {
        let selectedunits = getselectednonmembers();
        let data = "&units=" + selectedunits;
        $.ajax({
            dataType: 'json',
            url: addmembers_url + currentgroup,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.count_of_inserted > 0) {
                    raiseInfo("Добавлено учреждений в группу " + data.count_of_inserted);
                    memberlist.jqxGrid('clearselection');
                    memberlist.jqxGrid('updatebounddata');
                    units.jqxGrid('clearselection');
                    units.jqxGrid('updatebounddata');
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
        let selectedunits = getselectedmembers();
        if (currentgroup === 0) {
            raiseError('Не выбран список МО для редактирования');
            return false;
        }
        let removed_units = getselectedmembers();
        if (removed_units.length === 0) {
            raiseError('Не выбраны МО для удаления из списка');
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: removemembers_url + currentgroup + '/' + removed_units,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (data.count_of_removed > 0) {
                    raiseInfo("Удалено учреждений из группы " + data.count_of_removed);
                    memberlist.jqxGrid('clearselection');
                    memberlist.jqxGrid('updatebounddata');
                    units.jqxGrid('clearselection');
                    units.jqxGrid('updatebounddata');
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

/*initmotree = function() {
    var mo_source =
    {
        dataType: "json",
        dataFields: [
            { name: 'id', type: 'int' },
            { name: 'parent_id', type: 'int' },
            { name: 'unit_code', type: 'string' },
            { name: 'unit_name', type: 'string' }
        ],
        hierarchy:
        {
            keyDataField: { name: 'id' },
            parentDataField: { name: 'parent_id' }
        },
        id: 'id',
        root: '',
        url: motree_url +'0'
    };
    mo_dataAdapter = new $.jqx.dataAdapter(mo_source);
    $("#moTreeContainer").jqxPanel({width: '100%', height: '350px'});
    $("#moTree").jqxTreeGrid(
        {
            width: '98%',
            height: '99%',
            theme: theme,
            source: mo_dataAdapter,
            selectionMode: "singleRow",
            localization: localize(),
            filterable: true,
            filterMode: "simple",
            columnsResize: true,
            checkboxes: true,
            hierarchicalCheckboxes: false,
            ready: function () {
                $("#moTree").jqxTreeGrid('expandRow', 0);
            },
            columns: [
                {text: 'Код', dataField: 'unit_code', width: 150},
                {text: 'Наименование', dataField: 'unit_name'}
            ]
        });

    $('#moTree').on('filter',
        function (event) {
            var args = event.args;
            var filters = args.filters;
            $('#moTree').jqxTreeGrid('expandAll');
        }
    );
};*/

let initUnitsNonmembers = function () {
    unitsource =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'int' },
                { name: 'parent_id', type: 'int' },
                { name: 'parent', map: 'parent>unit_name', type: 'string' },
                { name: 'unit_code', type: 'string' },
                { name: 'territory_type', type: 'int' },
                { name: 'inn', type: 'string' },
                { name: 'unit_name', type: 'string' },
                { name: 'node_type', type: 'int' },
                { name: 'report', type: 'int' },
                { name: 'aggregate', type: 'int' },
                { name: 'blocked', type: 'int' },
                { name: 'medinfo_id', type: 'int' }
            ],
            id: 'id',
            url: units_url + currentgroup,
            root: 'unit'
        };
    unitDataAdapter = new $.jqx.dataAdapter(unitsource);
    units.jqxGrid(
        {
            width: '100%',
            height: '100%',
            theme: theme,
            localization: localize(),
            source: unitDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            selectionmode: 'multiplerowsextended',
            columns: [
                { text: 'Id', datafield: 'id', width: '30px' },
                { text: 'Входит в', datafield: 'parent', width: '120px' },
                { text: 'Код', datafield: 'unit_code', width: '50px'  },
                { text: 'Имя', datafield: 'unit_name' , width: '420px'},
                { text: 'Тип', datafield: 'node_type' , width: '40px'},
                { text: 'Блок', datafield: 'blocked', width: '50px' }
            ]
        });
};

initdropdowns = function() {
    let groupesource =
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
    parentid.jqxDropDownList({
        theme: theme,
        source: groupeDataAdapter,
        filterable: true,
        filterPlaceHolder: "Поиск",
        displayMember: "group_name",
        valueMember: "id",
        placeHolder: "Выберите группу:",
        width: 500,
        height: 34
    });
};

performAction = function() {
    $.ajax({
        dataType: 'json',
        url: groupdelete_url + currentgroup,
        method: "DELETE",
        success: function (data, status, xhr) {
            if (typeof data.error !== 'undefined') {
                raiseError(data.message);
            } else {
                raiseInfo(data.message);
                $("#form")[0].reset();
                grouplist.jqxGrid('updatebounddata', 'data');
                grouplist.jqxGrid('clearselection');
            }
        },
        error: function (xhr, status, errorThrown) {
            raiseError('Ошибка удаления группы', xhr);
        }
    });
};