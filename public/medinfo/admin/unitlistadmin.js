initdatasources = function() {

    columnsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'excluded', map: 'excluded>0>album_id', type: 'bool' },
            { name: 'column_index', type: 'int' },
            { name: 'column_name', type: 'string' },
            { name: 'content_type', type: 'int' },
            { name: 'size', type: 'int' },
            { name: 'decimal_count', type: 'int' },
            { name: 'medstat_code', type: 'string' },
            { name: 'medinfo_id', type: 'int' }
        ],
        id: 'id',
        url: columnfetch_url + current_table,
        root: 'column'
    };
    rowsDataAdapter = new $.jqx.dataAdapter(rowsource);
    columnsDataAdapter = new $.jqx.dataAdapter(columnsource);
};

// Таблица списков
let initList = function() {
    listbutton.jqxDropDownButton({ width: 250, height: 32, theme: theme });
    listbutton.jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите список</div>');
    let listsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'slug', type: 'string' },
            { name: 'name', type: 'string' }
        ],
        id: 'id',
        url: lists
    };
    let dadapter =  new $.jqx.dataAdapter(listsource);
    list.jqxGrid(
        {
            width: '500px',
            height: '500px',
            theme: theme,
            localization: localize(),
            source: dadapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Пседоним', datafield: 'slug', width: '100px'  },
                { text: 'Наименование', datafield: 'name' , width: '380px'}
            ]
        });

    list.on('rowselect', function (event) {
        $("#ListContainer").jqxDropDownButton('close');
        let args = event.args;
        if (args.rowindex === -1) {
            return false;
        }
        let r = args.row;
        currentlist = r.id;
        $("#ListName").html('<strong>"' + r.name + '"</strong>');
        $("#name").val(r.name);
        $("#slug").val(r.slug);
        membersource.url = member_url + currentlist;
        unitsource.url = units_url + currentlist;
        listterms.jqxGrid('updatebounddata');
        units.jqxGrid('updatebounddata');
    });
};
// Состав выбранного списка
let initListMembers = function () {
    membersource =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'int' },
                //{ name: 'list_id', type: 'int' },
                //{ name: 'uname', map: 'unit>unit_name', type: 'string' },
                { name: 'unit_name', type: 'string' },
                //{ name: 'ucode', map: 'unit>unit_code', type: 'string' },
                { name: 'unit_code', type: 'string' }
                //{ name: 'ou_id', type: 'int' }
            ],
            id: 'id',
            url: member_url + currentlist
        };
    let memberDataAdapter =  new $.jqx.dataAdapter(membersource);
    listterms.jqxGrid(
        {
            width: '100%',
            height: '100%',
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
                { text: 'id', datafield: 'id' , width: '50px'},
                //{ text: 'код', datafield: 'ucode' , width: '50px'},
                { text: 'код', datafield: 'unit_code' , width: '50px'},
                //{ text: 'МО', datafield: 'uname' , width: '580px'}
                { text: 'МО', datafield: 'unit_name' , width: '580px'}
            ]
        });
};
// Список юнитов не включенных в выбранный список
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
            url: units_url + currentlist,
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
    let rowindexes = listterms.jqxGrid('getselectedrowindexes');
    let indexes_length =  rowindexes.length;
    let selectedunits = [];
    for (i = 0; i < indexes_length; i++) {
        selectedunits.push(listterms.jqxGrid('getrowid', rowindexes[i]));
    }
    return selectedunits;
};

let initeditlistwindow = function () {
    let updbutton = $('#update');
    let createbutton = $('#create');
    listeditform.jqxWindow({
        width: 700,
        height: 320,
        resizable: false,
        autoOpen: false,
        isModal: true,
        cancelButton: $('#cancel'),
        position: { x: 310, y: 125 }
    });

    updbutton.click(function() {
        data = "&name=" + $("#name").val() + "&slug=" + $("#slug").val();
        $.ajax({
            dataType: 'json',
            url: list_url + '/' + currentlist,
            method: "PUT",
            data: data,
            success: function (data, status, xhr) {
                if (data.updated) {
                    raiseInfo("Изменения сохранены");
                    //list.jqxGrid('clearselection');
                    list.jqxGrid('updatebounddata', 'data');
                    list.on("bindingcomplete", function (event) {
                        let newindex = list.jqxGrid('getrowboundindexbyid', currentlist);
                        list.jqxGrid('selectrow', newindex);
                    });
                }
                else {
                    raiseError("Изменения не сохранены");
                }
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
        listeditform.jqxWindow('hide');
    });

    createbutton.click(function() {
        data = "&name=" + $("#name").val() + "&slug=" + $("#slug").val();
        $.ajax({
            dataType: 'json',
            url: list_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.stored) {
                    raiseInfo("Новый список учреждений создан");
                    //list.jqxGrid('clearselection');
                    list.jqxGrid('updatebounddata', 'data');
                    list.on("bindingcomplete", function (event) {
                        let newindex = list.jqxGrid('getrowboundindexbyid', data.id);
                        list.jqxGrid('selectrow', newindex);
                    });
                }
                else {
                    raiseError("Изменения не сохранены");
                }
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
        listeditform.jqxWindow('hide');
    });

};

let initActions = function() {
    $("#edit").click(function () {
        if (currentlist === 0) {
            raiseInfo('Не выбран список МО для редактирования');
            $("#update").addClass('disabled');
        } else {
            $("#update").removeClass('disabled');
        }

        listeditform.jqxWindow('open');
    });

    $("#delete").click(function () {
        if (currentlist === 0) {
            raiseError('Не выбран список МО для удаления');
            return false;
        }
        if (!confirm('Удалить текущий список учреждений?')) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: list_url + '/' + currentlist,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (data.removed) {
                    raiseInfo("Удален список Id " + data.id);
                    listterms.jqxGrid('clearselection');
                    listterms.jqxGrid('updatebounddata');
                    units.jqxGrid('clearselection');
                    units.jqxGrid('updatebounddata');
                    list.jqxGrid('clearselection');
                    list.jqxGrid('updatebounddata');
                }
                else {
                    raiseError("Списрк учреждений не удален");
                }
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });

    $("#AddSelected").click(function () {
        if (currentlist === 0) {
            raiseError('Не выбран список МО для редактирования');
            return false;
        }
        let added_units = getselectednonmembers();
        if (added_units.length === 0) {
            raiseError('Не выбраны МО для добавления в список');
            return false;
        }
        let data = "&units=" + added_units;
        $.ajax({
            dataType: 'json',
            url: addmembers_url + currentlist,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.count_of_inserted > 0) {
                    raiseInfo("Добавлено учреждений в список " + data.count_of_inserted);
                    listterms.jqxGrid('clearselection');
                    listterms.jqxGrid('updatebounddata');
                    units.jqxGrid('clearselection');
                    units.jqxGrid('updatebounddata');
                }
                else {
                    raiseError("Учреждения не добавлены");
                }
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#RemoveSelected").click(function () {
        if (currentlist === 0) {
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
            url: removemembers_url + currentlist + '/' + removed_units,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (data.count_of_removed > 0) {
                    raiseInfo("Удалено учреждений из списка " + data.count_of_removed);
                    listterms.jqxGrid('clearselection');
                    listterms.jqxGrid('updatebounddata');
                    units.jqxGrid('clearselection');
                    units.jqxGrid('updatebounddata');
                }
                else {
                    raiseError("Учреждения не удалены");
                }
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });

    $("#RemoveAll").click(function () {
        if (currentlist === 0) {
            raiseError('Не выбран список МО для редактирования');
            return false;
        }
        if (!confirm('Очистить текущий список учреждений?')) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: removeall_url + currentlist,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (data.count_of_removed > 0) {
                    raiseInfo("Удалено учреждений из списка " + data.count_of_removed);
                    listterms.jqxGrid('clearselection');
                    listterms.jqxGrid('updatebounddata');
                    units.jqxGrid('clearselection');
                    units.jqxGrid('updatebounddata');
                }
                else {
                    raiseError("Учреждения не удалены");
                }
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
};


updateRelated = function() {
    updateRowList();
    updateColumnList();
    $("#rowSelected").html('');
    $("#columnSelected").html('');
};
// Обновление списка строк при выборе таблицы
updateRowList = function() {
    rowsource.url = rowfetch_url + current_table;
    rlist.jqxGrid('clearselection');
    rlist.jqxGrid('updatebounddata');
};

setquery = function() {
    return "?period=" + current_period +
        "&form=" + current_form +
        "&table=" + current_table +
        "&rows=" + rows +
        "&columns=" + columns +
        "&level=" + current_level +
        "&type=" + current_type +
        "&aggregate=" + aggregate +
        "&output=" + output +
        "&mode=" + groupmode;
};

initButtons = function() {
    modebutton.jqxSwitchButton({
        height: 31,
        width: 250,
        onLabel: 'Строке',
        offLabel: 'Графе',
        checked: true });
    modebutton.on( 'unchecked', function (event) {
        $("#rowListContainer").jqxDropDownButton({ disabled: false });
        $("#columnListContainer").jqxDropDownButton({ disabled: true });
        groupmode = 1;
        rlist.jqxGrid('clearselection');
        clist.jqxGrid('clearselection');
        rlist.jqxGrid('selectionmode', 'singlerow');
        clist.jqxGrid('selectionmode', 'multiplerows');
        $("#modeSelected").html('<div class="text-bold text-info" style="margin-left: -100px">Текущий режим группировки "по строке"</div>');
        $("#rowSelected").html('');
        $("#columnSelected").html('');
    });
    modebutton.on( 'checked', function (event) {
        $("#rowListContainer").jqxDropDownButton({ disabled: true });
        $("#columnListContainer").jqxDropDownButton({ disabled: false });
        groupmode = 2;
        rlist.jqxGrid('clearselection');
        clist.jqxGrid('clearselection');
        rlist.jqxGrid('selectionmode', 'multiplerows');
        clist.jqxGrid('selectionmode', 'singlerow');
        $("#modeSelected").html('<div class="text-bold text-info" style="margin-left: -100px">Текущий режим группировки "по графе"</div>');
        $("#rowSelected").html('');
        $("#columnSelected").html('');
    });
    $("#html").on('click', function() {
        output = 1;
    });
    $("#excel").on('click', function() {
        output = 2;
    });
    $("#primary").on('click', function() {
        aggregate = 1;
    });
    $("#legacy").on('click', function() {
        aggregate = 2;
    });
    $("#territory").on('click', function() {
        aggregate = 3;
    });
};



