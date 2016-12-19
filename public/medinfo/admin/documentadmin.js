/**
 * Created by shameev on 28.06.2016.
 */
// Инициализация источников данных для таблиц
var docroute = function () {
    var route = '&filter_mode=' + filter_mode + '&ou=' + current_top_level_node +  '&dtypes=' + checkeddtypes.join();
    route += '&states='+ checkedstates.join() +'&forms=' + checkedforms.join() + '&periods=' + checkedperiods.join()
    return route;
};
var datasources = function() {
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
        url: 'fetch_mo_tree/0'
    };

    ugroup_source =
    {
        dataType: "json",
        dataFields: [
            { name: 'id', type: 'int' },
            { name: 'parent_id', type: 'int' },
            { name: 'group_code', type: 'string' },
            { name: 'group_name', type: 'string' }
        ],
        hierarchy:
        {
            keyDataField: { name: 'id' },
            parentDataField: { name: 'parent_id' }
        },
        id: 'id',
        root: '',
        url: group_tree_url
    };

    docsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'doctype', type: 'string' },
            { name: 'unit_name', type: 'string' },
            { name: 'period', type: 'string' },
            { name: 'form_code', type: 'string' },
            { name: 'state', type: 'string' },
            { name: 'protected', type: 'bool' },
            { name: 'filled', type: 'bool' }
        ],
        id: 'id',
        url: docsource_url + docroute(),
        root: null
    };
    mo_dataAdapter = new $.jqx.dataAdapter(mo_source);
    ugroup_dataAdapter = new $.jqx.dataAdapter(ugroup_source);
    dataAdapter = new $.jqx.dataAdapter(docsource, {
        loadError: function(jqXHR, status, error) {
            if (jqXHR.status == 401) {
                raiseError('Пользователь не авторизован');
            }
        }
    });
};
// инициализация источников данных для предустановленных фильтров
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
    var states_source =
    {
        datatype: "array",
        datafields: [
            { name: 'code' },
            { name: 'name' }
        ],
        id: 'code',
        localdata: states
    };
    var periods_source =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'name' }
        ],
        id: 'id',
        localdata: periods
    };
    var dtypes_source =
    {
        datatype: "array",
        datafields: [
            { name: 'code' },
            { name: 'name' }
        ],
        id: 'code',
        localdata: dtypes
    };
    formsDataAdapter = new $.jqx.dataAdapter(forms_source);
    statesDataAdapter = new $.jqx.dataAdapter(states_source);
    changestateDA =  new $.jqx.dataAdapter(states_source);
    periodsDataAdapter = new $.jqx.dataAdapter(periods_source);
    dtypesDataAdapter = new $.jqx.dataAdapter(dtypes_source);
};
var checkformfilter = function() {
    checkedforms = [];
    var checkedItems = $("#formsListbox").jqxListBox('getCheckedItems');
    var formcount = checkedItems.length;
    for (i=0; i < formcount; i++) {
        checkedforms.push(checkedItems[i].value);
    }
};
var checkstatefilter = function() {
    checkedstates = [];
    var checkedItems = $("#statesListbox").jqxListBox('getCheckedItems');
    var statecount = checkedItems.length;
    for (i=0; i < statecount; i++) {
        checkedstates.push(checkedItems[i].value);
    }
};
var checkperiodfilter = function() {
    checkedperiods = [];
    var checkedItems = $("#periodsListbox").jqxListBox('getCheckedItems');
    var periodcount = checkedItems.length;
    for (i=0; i < periodcount; i++) {
        checkedperiods.push(checkedItems[i].value);
    }
};
var checkdtypefilter = function() {
    checkeddtypes = [];
    var checkedItems = $("#dtypesListbox").jqxListBox('getCheckedItems');
    var typecount = checkedItems.length;
    for (i=0; i < typecount; i++) {
        checkeddtypes.push(checkedItems[i].value);
    }
};
// Возвращает массив с идентификаторами выделенных документов
var getselecteddocuments = function () {
    var rowindexes = dlist.jqxGrid('getselectedrowindexes');
    indexes_length =  rowindexes.length;
    var row_ids = [];
    for (i = 0; i < indexes_length; i++) {
        row_ids.push(dlist.jqxGrid('getrowid', rowindexes[i]));
    }
    return row_ids;
};
var getcheckedunits = function() {
    var ids = [];
    var checkedRows;
    var i;
    if (filter_mode == 1) {
        checkedRows = motree.jqxTreeGrid('getCheckedRows');
        for (i = 0; i < checkedRows.length; i++) {
            ids.push(checkedRows[i].uid);
        }
    } else if (filter_mode == 2) {
        checkedRows = grouptree.jqxTreeGrid('getCheckedRows');
        for (i = 0; i < checkedRows.length; i++) {
            ids.push(checkedRows[i].uid);
        }
    }
    return ids;
};
// обновление таблиц первичных и сводных документов в зависимости от выделенных форм, периодов, статусов документов
var updatedocumenttable = function() {
    var old_doc_url = docsource.url;
    var new_doc_url = docsource_url + docroute();
    if (new_doc_url != old_doc_url) {
        docsource.url = new_doc_url;
        dlist.jqxGrid('clearselection');
        dlist.jqxGrid('updatebounddata');
    }
};

// Инициализация панели инструкментов
var initnewdocumentwindow = function () {
    var savebutton = $('#saveButton');
    var newdoc_form = $('#newForm').jqxWindow({
        width: 500,
        height: 320,
        resizable: false,
        autoOpen: false,
        isModal: true,
        cancelButton: $('#cancelButton'),
        position: { x: 310, y: 125 },
        initContent: function () {
            savebutton.jqxButton({ width: '80px', disabled: false });
            $('#cancelButton').jqxButton({ width: '80px', disabled: false });
        }
    });
    $('#selectPrimary').jqxCheckBox({ width: '150px' });
    $('#selectAggregate').jqxCheckBox({ width: '150px' });
    $("#selectForm").jqxDropDownList({
        theme: theme,
        checkboxes: true,
        source: formsDataAdapter,
        displayMember: "form_code",
        valueMember: "id",
        placeHolder: "Выберите форму:",
        //selectedIndex: 2,
        width: 250,
        height: 25
    });
    $("#selectPeriod").jqxDropDownList({
        theme: theme,
        source: periodsDataAdapter,
        displayMember: "name",
        valueMember: "id",
        placeHolder: "Выберите период:",
        selectedIndex: 0,
        width: 250,
        height: 25
    });
    $("#selectState").jqxDropDownList({
        theme: theme,
        source: statesDataAdapter,
        displayMember: "name",
        valueMember: "code",
        placeHolder: "Выберите статус:",
        selectedIndex: 0,
        width: 250,
        height: 25
    });
    savebutton.click(function() {
        var data;
        var primary;
        var aggregate;
        var selectedunits = getcheckedunits();
        var selectedforms = [];
        var checked = $("#selectForm").jqxDropDownList('getCheckedItems');
        for (var i = 0; i < checked.length; i++) {
            selectedforms.push(checked[i].value);
        }
        if (selectedforms.length == 0) {
            raiseError("Не выбрано ни одной формы для создания документов");
            return false;
        }
        var selectedstate = $("#selectState").jqxDropDownList('getSelectedItem').value;
        var selectedperiod = $("#selectPeriod").jqxDropDownList('getSelectedItem').value;
        //console.log(selectedstate);
        primary = $('#selectPrimary').jqxCheckBox('checked') ? 1 : 0 ;
        aggregate = $('#selectAggregate').jqxCheckBox('checked') ? 1 : 0;
        if (primary == 0 && aggregate == 0) {
            raiseError("Не выбрано ни одного типа создаваемых документов (первичные, сводные");
            return false;
        }
        data = "&filter_mode=" + filter_mode + "&units=" + selectedunits + "&forms=" + selectedforms + "&period=" + selectedperiod;
        data += "&state=" + selectedstate + "&primary=" + primary + "&aggregate=" + aggregate;
        $.ajax({
            dataType: 'json',
            url: createdocuments_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.count_of_created > 0) {
                    raiseInfo("Создано документов " + data.count_of_created);
                    dlist.jqxGrid('clearselection');
                    dlist.jqxGrid('updatebounddata');
                }
                else {
                    raiseError("Документы не созданы")
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
            }
        });
        updatedocumenttable();
        $("#newForm").jqxWindow('hide');
        //console.log(primary);
    });
};

var initmotabs = function() {
    $("#motabs").jqxTabs({  height: '100%', width: '100%', theme: theme });
};

// Инициализация разбивки рабочего стола на области
var initsplitters = function() {
    $("#mainSplitter").jqxSplitter(
        {
            width: '100%',
            height: '95%',
            theme: theme,
            panels:
                [
                    { size: '40%', min: '10%'},
                    { size: '60%', min: '10%'}
                ]
        }
    );
    $('#leftPanel').jqxSplitter({
        width: '100%',
        height: '95%',
        theme: theme,
        orientation: 'horizontal',
        panels: [{ size: '50%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
    });
};
// Рендеринг панели инструментов для выделенных территорий/учреждений
//var rendermotreetoolbar = function(toolbar) {
var rendermotreetoolbar = function() {
    var toolbar = $("#filtermoTree");
    var container = $("<div style='display: none' id='buttonplus'></div>");
    var newdoc_form = $('#newForm');
    var newdocument = $("<i style='height: 14px' class='fa fa-wpforms fa-lg' title='Новый документ'></i>");
    newdocument.jqxButton({ theme: theme });
    container.append(newdocument);
    //var newdocument = $("#newdocument");
    newdocument.on('click', function() {
        $('#selectPrimary').jqxCheckBox('enable');
        $('#selectAggregate').jqxCheckBox('uncheck');
        newdoc_form.jqxWindow('open');
    });
    toolbar.append(container);
};

var rendergrouptreetoolbar = function() {
    var toolbar = $("#filtergroupTree");
    var container = $("<div style='display: none' id='groupbuttonplus'></div>");
    var newdoc_form = $('#newForm');
    var newdocument = $("<i style='height: 14px' class='fa fa-wpforms fa-lg' title='Новый документ'></i>");
    newdocument.jqxButton({ theme: theme });
    container.append(newdocument);
    //var newdocument = $("#newdocument");
    newdocument.on('click', function() {
        $("#selectForm").jqxDropDownList('uncheckAll');
        $('#selectPrimary').jqxCheckBox('disable');
        $('#selectAggregate').jqxCheckBox('check');
        newdoc_form.jqxWindow('open');

    });
    toolbar.append(container);
};

// инициализация дерева медицинских организаций/территорий
var initmotree = function() {
    motree.jqxTreeGrid(
        {
            width: '98%',
            height: '99%',
            theme: theme,
            source: mo_dataAdapter,
            selectionMode: "singleRow",
            localization: localize(),
            //rendertoolbar: rendermotreetoolbar,
            filterable: true,
            filterMode: "simple",
            columnsResize: true,
            checkboxes: true,
            hierarchicalCheckboxes: false,
            //showToolbar: true,
            ready: function () {
                //$("#moTree").jqxTreeGrid({ showToolbar: false });
                rendermotreetoolbar();
                motree.jqxTreeGrid('expandRow', 0);
            },
            columns: [
                {text: 'Код', dataField: 'unit_code', width: 150},
                {text: 'Наименование', dataField: 'unit_name'}
            ]
        });

    motree.on('filter',
        function (event) {
            var args = event.args;
            var filters = args.filters;
            motree.jqxTreeGrid('expandAll');
        }
    );
    motree.on('rowSelect', function (event) {
        var args = event.args;
        var new_top_level_node = args.key;
        if (new_top_level_node == current_top_level_node) {
            return false;
        }
        current_top_level_node = new_top_level_node;
        filter_mode = 1; // режим отбора документов по территориям
        updatedocumenttable();
        return true;
    });
    motree.on('rowCheck', function (event) {
        //$(this).jqxTreeGrid({ showToolbar: true });
        $("#buttonplus").show();
    });
    motree.on('rowUncheck', function (event) {
        var checked = $(this).jqxTreeGrid('getCheckedRows');
        if (checked.length > 0) {
            //$(this).jqxTreeGrid({ showToolbar: true });
            $("#buttonplus").show();
        } else {
            //$(this).jqxTreeGrid({ showToolbar: false });
            $("#buttonplus").hide();
        }

    });
};

var initgrouptree = function() {
    grouptree.jqxTreeGrid(
        {
            width: '100%',
            height: '100%',
            theme: theme,
            source: ugroup_dataAdapter,
            selectionMode: "singleRow",
            filterable: true,
            filterMode: "simple",
            localization: localize(),
            checkboxes: true,
            columnsResize: true,
            ready: function()
            {
                rendergrouptreetoolbar();
                grouptree.jqxTreeGrid('expandRow', 0);
            },
            columns: [
                { text: 'Код', dataField: 'group_code', width: 120 },
                { text: 'Наименование', dataField: 'group_name', width: 545 }
            ]
        });
    grouptree.on('filter',
        function (event)
        {
            var args = event.args;
            var filters = args.filters;
            grouptree.jqxTreeGrid('expandAll');
        }
    );
    grouptree.on('rowSelect',
        function (event)
        {
            var args = event.args;
            var new_top_level_node = args.key;
            if (new_top_level_node == current_top_level_node && filter_mode == 2) {
                return false;
            }
            filter_mode = 2; // режим отбора документов по группам
            current_top_level_node =  new_top_level_node;
            updatedocumenttable();
            return true;
        }
    );
    grouptree.on('rowCheck', function (event) {
        //$(this).jqxTreeGrid({ showToolbar: true });
        $("#groupbuttonplus").show();
    });
    grouptree.on('rowUncheck', function (event) {
        var checked = $(this).jqxTreeGrid('getCheckedRows');
        if (checked.length > 0) {
            //$(this).jqxTreeGrid({ showToolbar: true });
            $("#groupbuttonplus").show();
        } else {
            //$(this).jqxTreeGrid({ showToolbar: false });
            $("#groupbuttonplus").hide();
        }

    });

};
// инициализация вкладок-фильтров с элементами управления
var initfiltertabs = function() {
    $("#filtertabs").jqxTabs({  height: '100%', width: '100%', theme: theme });
    $("#formsListbox").jqxListBox({
        theme: theme,
        source: formsDataAdapter,
        displayMember: 'form_code',
        valueMember: 'id',
        checkboxes: true,
        filterable:true,
        filterPlaceHolder: 'Фильтр',
        width: 150,
        height: 290
    });
    $("#formsListbox").jqxListBox('checkAll');
    $("#formsListbox").on('click', function () {
        checkformfilter();
        updatedocumenttable();
    });
    $("#checkAllForms").jqxCheckBox({ width: 170, height: 20, theme: theme, checked: true});
    $('#checkAllForms').on('checked', function (event) {
        $("#formsListbox").jqxListBox('checkAll');
        checkformfilter();
        updatedocumenttable();
    });
    $('#checkAllForms').on('unchecked', function (event) {
        $("#formsListbox").jqxListBox('uncheckAll');
        checkformfilter();
        updatedocumenttable();
    });

    $("#statesListbox").jqxListBox({
        theme: theme,
        source: statesDataAdapter,
        displayMember: 'name',
        valueMember: 'code',
        checkboxes: true,
        width: 230,
        height: 200
    });
    $("#statesListbox").jqxListBox('checkAll');
    $("#statesListbox").on('click', function () {
        checkstatefilter();
        updatedocumenttable();
    });
    $("#checkAllStates").jqxCheckBox({ width: 170, height: 20, theme: theme, checked: true});
    $('#checkAllStates').on('checked', function (event) {
        $("#statesListbox").jqxListBox('checkAll');
        checkstatefilter();
        updatedocumenttable();
    });
    $('#checkAllStates').on('unchecked', function (event) {
        $("#statesListbox").jqxListBox('uncheckAll');
        checkstatefilter();
        updatedocumenttable();
    });

    $("#periodsListbox").jqxListBox({
        theme: theme,
        source: periodsDataAdapter,
        displayMember: 'name',
        valueMember: 'id',
        checkboxes: true,
        width: 230,
        height: 200
    });
    var item = $("#periodsListbox").jqxListBox('getItem', 0) ;
    $("#periodsListbox").jqxListBox('checkItem', item );
    $("#periodsListbox").on('click', function () {
        checkperiodfilter();
        updatedocumenttable();
    });

    $("#dtypesListbox").jqxListBox({
        theme: theme,
        source: dtypesDataAdapter,
        displayMember: 'name',
        valueMember: 'code',
        checkboxes: true,
        width: 230,
        height: 200
    });
    $("#dtypesListbox").jqxListBox('checkAll');
    $("#dtypesListbox").on('click', function () {
        checkdtypefilter();
        updatedocumenttable();
    });
};
//var pitem = $("#periodsListbox").jqxListBox('getItemByValue', "Годовой. 2015.");

// инициализация таблицы-перечня отчетных документов
var initdocumentslist = function() {
    dlist.jqxGrid(
        {
            width: '98%',
            height: '95%',
            theme: theme,
            localization: localize(),
            source: dataAdapter,
            columnsresize: true,
            selectionmode: 'checkbox',
            columns: [
                { text: '№', datafield: 'id', width: '60px', cellsrenderer: linkrenderer },
                { text: 'Тип', datafield: 'doctype' , width: '100px'},
                { text: 'МО', datafield: 'unit_name', width: '300px' },
                { text: 'Период', datafield: 'period', width: '100px' },
                { text: 'Форма', datafield: 'form_code', width: '100px'  },
                { text: 'Статус', datafield: 'state' },
                { text: 'Защищен', datafield: 'protected', columntype: 'checkbox', width: 100 },
                { text: 'Данные', datafield: 'filled', columntype: 'checkbox', width: 100 }
            ]
        });
};
// рендеринг панели инструментов для выделенных документов
var initdocumentactions = function() {
    $("#statesDropdownList").jqxDropDownList({
        theme: theme,
        source: changestateDA,
        displayMember: "name",
        valueMember: "code",
        placeHolder: "Статус документов:",
        //selectedIndex: 2,
        width: 150,
        height: 23
    });
    $('#statesDropdownList').on('select', function (event)
    {
        var args = event.args;
        var selectedstate = args.item.value;
        var row_ids = noselected_error("Не выбрано ни одного документа для смены статуса");
        if (!row_ids) {
            $(this).jqxDropDownList('clearSelection');
            return false;
        }
        var data = "documents=" + row_ids + '&state=' + selectedstate;
        var confirm_text = 'Подтвердите смену статуса у документов №№ ' + row_ids + '. \n';
        confirm_text += 'Выбранный статус "' + selectedstate.label + '". \n';
        if (!confirm(confirm_text)) {
            $(this).jqxDropDownList('clearSelection');
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: changestate_url,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (data.state_changed == 1) {
                    raiseInfo(data.comment + ' Количество измененных документов ' + data.affected_documents + '.');
                }
                dlist.jqxGrid('clearselection');
                dlist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                var error_text = "Ошибка сохранения данных на сервере. Обратитесь к администратору";
                raiseError(error_text, xhr);
            }
        });
        $(this).jqxDropDownList('clearSelection');
    });
    $("#deleteDocuments").jqxButton({ theme: theme });
    $("#deleteDocuments").click(function () {
        var row_ids = noselected_error("Не выбрано ни одного документа для удаления");
        if (!row_ids) {
            return false;
        }
        var data = "documents=" + row_ids;
        var confirm_text = 'Подтвердите удаление документов №№ ' + row_ids + '. \n';
        confirm_text += 'Документы будут удалены вместе со всеми введенными в них статданными без возможности восстановления!';
        if (!confirm(confirm_text)) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: deletedocuments_url,
            method: "DELETE",
            data: data,
            success: function (data, status, xhr) {
                if (data.documents_deleted == 1) {
                    raiseInfo(data.comment);
                }
                dlist.jqxGrid('clearselection');
                dlist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                var error_text = "Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.";
                raiseError(error_text);
            }
        });
    });
    $("#eraseData").jqxButton ({ theme: theme});
    $("#eraseData").click(function () {
        var row_ids = noselected_error("Не выбрано ни одного документа для удаления статданных");
        if (!row_ids) {
            return false;
        }
        var data = "documents=" + row_ids;
        var confirm_text = 'Подтвердите удаление статданных из документов №№ ' + row_ids + '. \n';
        confirm_text += 'Данные будут потеряны без возможности дальнейшего восстановления!';
        if (!confirm(confirm_text)) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: erasedocuments_url,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (data.statdata_erased == 1) {
                    raiseInfo(data.comment);
                }
                dlist.jqxGrid('clearselection');
                dlist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                var error_text = "Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.";
                raiseError(error_text);
            }
        });
    });
    $("#protectAggregates").jqxButton ({ theme: theme});
    $("#protectAggregates").click(function () {
        var row_ids = noselected_error("Не выбрано ни одного документа защиты от повторного свода");
        if (!row_ids) {
            return false;
        }
        var data = "documents=" + row_ids;
        var confirm_text = 'Подтвердите установку защиты от повторного свода для документов №№ ' + row_ids + '. \n';
        if (!confirm(confirm_text)) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: protectaggregate_url,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (data.statdata_erased == 1) {
                    raiseInfo(data.comment);
                }
                dlist.jqxGrid('clearselection');
                dlist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                var error_text = "Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.";
                raiseError(error_text);
            }
        });
    });

};
var linkrenderer = function (row, column, value) {
    var html = "<div class='jqx-grid-cell-left-align' style='margin-top: 6px'>";
    html += "<a href='/datainput/formdashboard/" + value + "' target='_blank' title='Открыть для редактирования'>" + value + "</a></div>";
    return html;
};
var noselected_error = function(message) {
    var row_ids = getselecteddocuments();
    if (row_ids.length == 0) {
        raiseError(message);
        return false;
    }
    return row_ids;
};