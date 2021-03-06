// Таблица списков
let initList = function() {
    listbutton.jqxDropDownButton({ width: 250, height: 32, theme: theme });
    listbutton.jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите список</div>');
    let listsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'slug', type: 'string' },
            { name: 'name', type: 'string' },
            { name: 'on_frontend', type: 'bool' }
        ],
        id: 'id',
        url: lists
    };
    let dataadapter =  new $.jqx.dataAdapter(listsource);
    list.jqxGrid(
        {
            width: '600px',
            height: '500px',
            theme: theme,
            localization: localize(),
            source: dataadapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Пседоним', datafield: 'slug', width: '100px'  },
                { text: 'Наименование', datafield: 'name' , width: '380px'},
                { text: 'Отображаемый', columntype: 'checkbox', datafield: 'on_frontend' , width: '100px'}
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
        $("#ListSlug").html(r.slug);
        $("#ListName").html('<strong>"' + r.name + '"</strong>');
        $("#name").val(r.name);
        $("#slug").val(r.slug);
        $("#onFrontend").val(r.on_frontend);
        unitsource.url = units_url + currentlist;
        membersource.url = member_url + currentlist;
        units.jqxGrid('clearselection');
        listterms.jqxGrid('clearselection');
        units.jqxGrid('updatebounddata');
        listterms.jqxGrid('updatebounddata');

    });
};
// Состав выбранного списка
let initListMembers = function () {
    membersource =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'int' },
                { name: 'unit_name', type: 'string' },
                { name: 'unit_code', type: 'string' },
                { name: 'parent', map: 'parent>unit_name', type: 'string' },
                { name: 'node_type', type: 'int' }
            ],
            id: 'id',
            url: member_url + currentlist
        };
    memberDataAdapter =  new $.jqx.dataAdapter(membersource);
    listterms.jqxGrid(
        {
            width: '100%',
            height: '95%',
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
                //{ text: 'id', datafield: 'id' , width: '50px'},
                { text: 'код', datafield: 'unit_code' , width: '50px'},
                { text: 'МО', datafield: 'unit_name' , width: '550px'},
                { text: 'Входит в', datafield: 'parent', width: '200px' },
                { text: 'Тип', datafield: 'node_type' , width: '60px'}
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
            height: '95%',
            autoheight: false,
            theme: theme,
            localization: localize(),
            source: unitDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            selectionmode: 'multiplerowsextended',
            columns: [
                //{ text: 'Id', datafield: 'id', width: '30px' },
                { text: 'Код', datafield: 'unit_code', width: '50px'  },
                { text: 'Имя', datafield: 'unit_name' , width: '550px'},
                { text: 'Входит в', datafield: 'parent', width: '220px' },
                { text: 'Тип', datafield: 'node_type' , width: '60px'}
                //{ text: 'Блок', datafield: 'blocked', width: '50px' }
            ]
        });
    units.on("bindingcomplete", function (event) {
        let checkedvalue = $("input[name=filterNonMember]:checked")[0].value;
        applyfilter(units, checkedvalue);
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
    let createcopybutton = $('#createCopy');
    listeditform.jqxWindow({
        width: 700,
        height: 320,
        resizable: false,
        autoOpen: false,
        isModal: true,
        cancelButton: $('#cancel'),
        position: { x: 310, y: 125 }
    });
    $('#onFrontend').jqxSwitchButton({
        height: 31,
        width: 110,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false });
    updbutton.click(function() {
        let data = setquerystring();
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

    createcopybutton.click(function() {
        if (currentlist === 0) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: createcopy_url + currentlist,
            method: "POST",
            success: function (data, status, xhr) {
                if (data.copystored) {
                    //console.log(data.id);
                    currentlist = data.id;
                    raiseInfo("Копия списка учреждений создана");
                    //list.jqxGrid('clearselection');
                    list.jqxGrid('updatebounddata', 'data');
                    list.on("bindingcomplete", function (event) {
                        let newindex = list.jqxGrid('getrowboundindexbyid', data.id);
                        //console.log(newindex);
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
        data = setquerystring();
        $.ajax({
            dataType: 'json',
            url: list_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.stored) {
                    //currentlist = data.id;
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

let setquerystring = function () {
    let onfront = $("#onFrontend").val() ? 1 : 0;
    return "&name=" + $("#name").val() + "&slug=" + $("#slug").val() + "&onfrontend=" + onfront;
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
        let includesublegals = 0;
        if (currentlist === 0) {
            raiseError('Не выбран список МО для редактирования');
            return false;
        }
        let added_units = getselectednonmembers();
        if (added_units.length === 0) {
            raiseError('Не выбраны МО для добавления в список');
            return false;
        }
        //console.log($("#includeSubLegals").prop("checked"));
        if ($("#includeSubLegals").prop("checked") === true) {
            includesublegals = 1;
        }
        let data = "&units=" + added_units + "&inclusive=" + includesublegals;
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
    $("#ApplyFilter").click(function () {
        let checkedvalue = $("input[name=filterNonMember]:checked")[0].value;
        applyfilter(units, checkedvalue);
    });
    $("#ApplyMemberFilter").click(function () {
        let checkedvalue = $("input[name=filterMember]:checked")[0].value;
        applyfilter(listterms, checkedvalue);
    });
};

applyfilter = function (u, option) {
    u.jqxGrid('clearfilters');
    let filtergroup = new $.jqx.filter();
    let filter_or_operator = 1;
    let filtervalue = 0;
    switch (option) {
        case '1' :
            return;
        case '2' :
            filtervalue = 3;
            break;
        case '3' :
            filtervalue = 4;
            break;
        case '4' :
            filtervalue = 6;
            break;
    }
    //let filtercondition = 'contains';
    let filtercondition = 'equal';
    //let filter = filtergroup.createfilter('stringfilter', filtervalue, filtercondition);
    let filter = filtergroup.createfilter('numericfilter', filtervalue, filtercondition);
    filtergroup.addfilter(filter_or_operator, filter);
    u.jqxGrid('addfilter', 'node_type', filtergroup );
    u.jqxGrid('applyfilters');
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