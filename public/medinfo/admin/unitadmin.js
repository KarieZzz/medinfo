initsplitter = function() {
    $("#mainSplitter").jqxSplitter(
        {
            width: '100%',
            height: '100%',
            theme: theme,
            panels:
                [
                    { size: '55%', min: '10%'},
                    { size: '45%', min: '10%'}
                ]
        }
    );
};
initdropdowns = function() {
    let unittypessource =
    {
        datatype: "json",
        datafields: [
            { name: 'code' },
            { name: 'name' }
        ],
        id: 'code',
        localdata: unitTypes
    };
    let aggregatablesource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'unit_name' }
        ],
        id: 'id',
        localdata: aggregatables
    };
    unittypesDataAdapter = new $.jqx.dataAdapter(unittypessource);
    aggregatableDataAdapter = new $.jqx.dataAdapter(aggregatablesource);
    $("#node_type").jqxDropDownList({
        theme: theme,
        source: unittypesDataAdapter,
        displayMember: "name",
        valueMember: "code",
        placeHolder: "Выберите тип ОЕ:",
        width: 300,
        height: 34
    });
    $("#parent_id").jqxDropDownList({
        theme: theme,
        source: aggregatableDataAdapter,
        filterable: true,
        filterPlaceHolder: "Поиск",
        displayMember: "unit_name",
        valueMember: "id",
        placeHolder: "Выберите ОЕ:",
        width: 500,
        height: 34
    });
};
initdatasources = function() {
    let unitsource =
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
            { name: 'adress', type: 'string' },
            { name: 'node_type', type: 'int' },
            { name: 'report', type: 'int' },
            { name: 'aggregate', type: 'int' },
            { name: 'blocked', type: 'int' },
            { name: 'countryside', type: 'bool' }
        ],
        id: 'id',
        url: unitfetch_url,
        root: 'unit'
    };
    unitDataAdapter = new $.jqx.dataAdapter(unitsource);
};
inittablelist = function() {
    unitlist.jqxGrid(
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
                { text: 'Входит в', datafield: 'parent', width: '110px' },
                { text: 'Код', datafield: 'unit_code', width: '50px'  },
                { text: 'ИНН', datafield: 'inn', width: '90px'  },
                { text: 'Имя', datafield: 'unit_name' , width: '440px'},
                { text: 'Тип', datafield: 'node_type' , width: '40px'},
                { text: 'Адрес', datafield: 'adress' , width: '40px'},
                { text: 'Перв', datafield: 'report' , width: '50px'},
                { text: 'Свод', datafield: 'aggregate' , width: '50px'},
                { text: 'Блок', datafield: 'blocked', width: '50px' },
                { text: 'Село', datafield: 'countryside', columntype: 'checkbox', width: '60px' }
            ]
        });
    unitlist.on('rowselect', function (event) {
        $("#parent_id").jqxDropDownList('clearFilter');
        $("#parent_id").jqxDropDownList('clearSelection');
        let row = event.args.row;
        $("#unit_name").val(row.unit_name);
        $("#parent_id").val(row.parent_id);
        $("#unit_code").val(row.unit_code);
        $("#territory_type").val(row.territory_type);
        $("#inn").val(row.inn);
        $("#node_type").val(row.node_type);
        $("#adress").val(row.adress);
        $("#report").val( row.report === 1 );
        $("#aggregate").val(row.aggregate === 1);
        $("#blocked").val(row.blocked === 1);
        $("#countryside").val(row.countryside === true);
    });
};
setquerystring = function() {
    return "&unit_name=" + $("#unit_name").val() +
        "&parent_id=" + $("#parent_id").val() +
        "&unit_code=" + $("#unit_code").val() +
        "&territory_type=" + $("#territory_type").val() +
        "&inn=" + $("#inn").val() +
        "&node_type=" + $("#node_type").val() +
        "&adress=" + $("#adress").val() +
        "&report=" + ($("#report").val() ? 1 : 0) +
        "&aggregate=" + ($("#aggregate").val() ? 1 : 0) +
        "&blocked=" + ($("#blocked").val() ? 1 : 0) +
        "&countryside=" + ($("#countryside").val() ? 1 : 0);
};
initunitactions = function() {

    $('#report').jqxSwitchButton({
        height: 31,
        width: 110,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    $('#aggregate').jqxSwitchButton({
        height: 31,
        width: 110,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    $('#blocked').jqxSwitchButton({
        height: 31,
        width: 110,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    $('#countryside').jqxSwitchButton({
        height: 31,
        width: 110,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    $("#insert").click(function () {
        let data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: unitcreate_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                $("#unitList").jqxGrid('updatebounddata', 'data');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        let row = unitlist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let rowid = unitlist.jqxGrid('getrowid', row);
        let data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: unitupdate_url + rowid,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                unitlist.jqxGrid('updatebounddata', 'data');
                unitlist.on("bindingcomplete", function (event) {
                    let newindex = $('#unitList').jqxGrid('getrowboundindexbyid', rowid);
                    unitlist.jqxGrid('selectrow', newindex);

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
        let row = unitlist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        let rowid = $("#unitList").jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: unitdelete_url + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    $("#form")[0].reset();
                    unitlist.jqxGrid('updatebounddata', 'data');
                    unitlist.jqxGrid('clearselection');
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка удаления отчетного периода', xhr);
            }
        });
    });
};