/**
 * Created by shameev on 28.06.2016.
 */
// Инициализация источников данных для таблиц
var docroute = function () {
    var route = '&ou=' + current_top_level_node +  '&dtypes=' + checkeddtypes.join();
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
            { name: 'filled', type: 'bool' }
        ],
        id: 'id',
        url: docsource_url + docroute(),
        root: null
    };
    mo_dataAdapter = new $.jqx.dataAdapter(mo_source);
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
    var rowindexes = $('#documentList').jqxGrid('getselectedrowindexes');
    indexes_length =  rowindexes.length;
    var row_ids = [];
    for (i = 0; i < indexes_length; i++) {
        row_ids.push($('#documentList').jqxGrid('getrowid', rowindexes[i]));
    }
    return row_ids;
};
var getcheckedunits = function() {
    var ids = [];
    var checkedRows = $('#moTree').jqxTreeGrid('getCheckedRows');
    for (var i = 0; i < checkedRows.length; i++) {
        // get a row.
        ids.push(checkedRows[i].uid);
    }
    return ids;
};
// обновление таблиц первичных и сводных документов в зависимости от выделенных форм, периодов, статусов документов
var updatedocumenttable = function() {
    var old_doc_url = docsource.url;
    var new_doc_url = docsource_url + docroute();
    if (new_doc_url != old_doc_url) {
        docsource.url = new_doc_url;
        $('#documentList').jqxGrid('clearselection');
        $('#documentList').jqxGrid('updatebounddata');
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
            $('#selectPrimary').jqxCheckBox({ width: '150px' });
            $('#selectAggregate').jqxCheckBox({ width: '150px' });
            savebutton.jqxButton({ width: '80px', disabled: false });
            $('#cancelButton').jqxButton({ width: '80px', disabled: false });
        }
    });
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
        data = "&units=" + selectedunits + "&forms=" + selectedforms + "&period=" + selectedperiod + "&state=" + selectedstate;
        data += "&primary=" + primary + "&aggregate=" + aggregate;
        $.ajax({
            dataType: 'json',
            url: createdocuments_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.count_of_created > 0) {
                    raiseInfo("Создано документов " + data.count_of_created);
                    $('#documentList').jqxGrid('clearselection');
                    $('#documentList').jqxGrid('updatebounddata');
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
        newdoc_form.jqxWindow('open');
    });
    toolbar.append(container);
};
// инициализация дерева медицинских организаций/территорий
var initmotree = function() {
    $("#moTreeContainer").jqxPanel({width: '100%', height: '100%'});
    $("#moTree").jqxTreeGrid(
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
    $('#moTree').on('rowSelect', function (event) {
        var args = event.args;
        var key = args.key;
        var new_top_level_node = key;
        if (new_top_level_node == current_top_level_node) {
            return false;
        }
        current_top_level_node = key;
        updatedocumenttable();
        return true;
    });
    $('#moTree').on('rowCheck', function (event) {
        //$(this).jqxTreeGrid({ showToolbar: true });
        $("#buttonplus").show();
    });
    $('#moTree').on('rowUncheck', function (event) {
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
    $("#documentList").jqxGrid(
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
                { text: 'МО', datafield: 'unit_name', width: '400px' },
                { text: 'Период', datafield: 'period', width: '100px' },
                { text: 'Форма', datafield: 'form_code', width: '100px'  },
                { text: 'Статус', datafield: 'state' },
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
                $('#documentList').jqxGrid('clearselection');
                $('#documentList').jqxGrid('updatebounddata');
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
                $('#documentList').jqxGrid('clearselection');
                $('#documentList').jqxGrid('updatebounddata');
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
                $('#documentList').jqxGrid('clearselection');
                $('#documentList').jqxGrid('updatebounddata');
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
/*var initpopupwindows = function() {
    $("#changeStateWindow").jqxWindow({
        width: 430,
        height: 360,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: $("#CancelStateChanging"),
        theme: theme,
        modalOpacity: 0.01
    });
    $("#performed").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#prepared").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#accepted").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#declined").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#approved").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $('#statusChangeMessage').jqxTextArea({ placeHolder: 'Оставьте свой комментарий к смене статуса документа', height: 90, width: 400, minLength: 1 });
    $("#CancelStateChanging").jqxButton({ theme: theme });
    $("#SaveState").jqxButton({ theme: theme });
    $("#SaveState").click(function () {
        var rowindex = $('#Documents').jqxGrid('getselectedrowindex');
        var rowdata = $('#Documents').jqxGrid('getrowdata', rowindex);
        var row_id = $('#Documents').jqxGrid('getrowid', rowindex);
        var message = $("#statusChangeMessage").val();
        var radiostates = $('.stateradio');
        var selected_state;
        radiostates.each(function() {
            if ($(this).jqxRadioButton('checked')) {
                selected_state = $(this).attr('id');
            }
        });
        if (statelabels[selected_state] == current_document_state ) {
            return false;
        }
        var data = "&document=" + row_id + "&state=" + selected_state + "&message=" + message;
        $.ajax({
            dataType: 'json',
            url: changestate_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.status_changed == 1) {
                    $("#currentInfoMessage").html("Статус документа изменен. <br /> Новый статус: \"" + statelabels[data.new_status] + '"');
                    $("#infoNotification").jqxNotification("open");
                    rowdata.state = statelabels[data.new_status];
                    $('#Documents').jqxGrid('updaterow', row_id, rowdata);
                    $('#Documents').jqxGrid('selectrow', rowindex);
                }
                else {
                    $("#currentError").text("Статус не изменен!");
                    $("#serverErrorNotification").jqxNotification("open");
                    // TODO: Обработать ошибку изменения статуса
                }
            },
            error: function (xhr, status, errorThrown) {
                $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                    + status + ". Обратитесь к администратору.");
                $("#serverErrorNotification").jqxNotification("open");
            }
        });
        $("#changeStateWindow").jqxWindow('hide');
    });
    $("#changeAuditStateWindow").jqxWindow({
        width: 430,
        height: 300,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: $("#CancelAuditStateChanging"),
        theme: theme,
        modalOpacity: 0.01
    });
    $("#noaudit").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#audit_incorrect").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#audit_correct").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $('#auditChangeMessage').jqxTextArea({
        placeHolder: 'Оставьте свои замечания по заполнению отчетного документа',
        height: 100,
        width: 400,
        minLength: 1
    });
    $("#SaveAuditState").jqxButton({ theme: theme });
    $("#CancelAuditStateChanging").jqxButton({ theme: theme });
    $("#SaveAuditState").click(function () {
        var rowindex = $('#Documents').jqxGrid('getselectedrowindex');
        var row_id = $('#Documents').jqxGrid('getrowid', rowindex);
        var radiostates = $('.auditstateradio');
        var message = $("#auditChangeMessage").val();
        var old_state;
        $.each(current_document_audits, function(key, value) {
            if (value.auditor_id == current_user_id) {
                old_state = value.state_id;
            }
        });
        var selected_state;
        radiostates.each(function() {
            if ($(this).jqxRadioButton('checked')) {
                selected_state = $(this).attr('id');
            }
        });
        var data = "&document=" + row_id + "&auditstate=" + selected_state + "&message=" + message;
        if (audit_state_ids[selected_state] != old_state) {
            $.ajax({
                dataType: 'json',
                url: changeaudition_url,
                method: 'POST',
                data: data,
                success: function (data, status, xhr) {
                    if (data.audit_status_changed == 1) {
                        $("#currentInfoMessage").text("Статус проверки документа изменен");
                        $("#infoNotification").jqxNotification("open");
                        $('#Documents').jqxGrid('selectrow', rowindex);
                    }
                    else {
                        $("#currentError").text("Статус проверки документа не изменен!");
                        $("#serverErrorNotification").jqxNotification("open");
                    }
                },
                error: function (xhr, status, errorThrown) {
                    $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                        + status + ". Обратитесь к администратору.");
                    $("#serverErrorNotification").jqxNotification("open");
                }
            });
        }
        $("#changeAuditStateWindow").jqxWindow('hide');
    });
    $("#BatchChangeAuditStateWindow").jqxWindow({
        width: 450,
        height: 330,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: $("#CancelBatchAuditStateChanging"),
        theme: theme,
        modalOpacity: 0.01
    });
    $("#nobatchaudit").jqxRadioButton({ width: 250, height: 25, checked: true, theme: theme });
    $("#batch_audit_incorrect").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#batch_audit_correct").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $('#AuditBatchChangeMessage').jqxTextArea({ placeHolder: 'Оставьте свой комментарий к смене статуса документов', height: 100, width: 400, minLength: 1 });
    $("#SaveBatchAuditState").jqxButton({ theme: theme });
    $("#CancelBatchAuditStateChanging").jqxButton({ theme: theme });
    $("#SaveBatchAuditState").click(function () {
        var rowindex = $('#Aggregates').jqxGrid('getselectedrowindex');
        var row_id = $('#Aggregates').jqxGrid('getrowid', rowindex);
        var radiostates = $('.batchauditstateradio');
        var message = $("#AuditBatchChangeMessage").val();
        var selected_state;
        radiostates.each(function() {
            if ($(this).jqxRadioButton('checked')) {
                selected_state = $(this).attr('id');
            }
        });
        var data = "aggregate=" + row_id + "&batchauditstate=" + selected_state + "&message=" + message;
        $.ajax({
            dataType: 'json',
            url: 'change_batch_audit_state.php',
            data: data,
            success: function (data, status, xhr) {
                var comment = data.responce.comment;
                if (data.responce.audit_status_changed > 0) {
                    var m = "Статус проверки для всех входящих в сводный отчет документов ("+ data.responce.audit_status_changed + ") изменен"
                    $("#currentInfoMessage").text(m);
                    $("#infoNotification").jqxNotification("open");
                    $('#Documents').jqxGrid('selectrow', rowindex);
                }
                else if (data.responce.audit_status_changed == 0) {
                    $("#currentError").text("Статус проверки документов не изменен! " + comment);
                    $("#serverErrorNotification").jqxNotification("open");
                } else if (data.responce.audit_status_changed == -1) {
                    $("#currentError").text("Ошибка при сохранении данных на сервере. Обратитесь к администратору");
                    $("#serverErrorNotification").jqxNotification("open");
                }
            },
            error: function (xhr, status, errorThrown) {
                $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                    + status + ". Обратитесь к администратору.");
                $("#serverErrorNotification").jqxNotification("open");
            }
        });
        $("#BatchChangeAuditStateWindow").jqxWindow('hide');
    });
// Комментирование документа/отправка сообщения к документу
    $('#message').jqxTextArea({ placeHolder: 'Оставьте свой комментарий к выбранному документу', height: 150, width: 400, minLength: 1 });
    $("#CancelMessage").jqxButton({ theme: theme });
    $("#SendMessage").jqxButton({ theme: theme });
    $("#SendMessage").click(function () {
        var rowindex = $('#Documents').jqxGrid('getselectedrowindex');
        var rowdata = $('#Documents').jqxGrid('getrowdata', rowindex);
        var row_id = $('#Documents').jqxGrid('getrowid', rowindex);
        var message = $("#message").val();
        message = message.trim();
        if (message.length == 0) {
            return false;
        }
        var data = "&document=" + row_id + "&message=" + message;
        $.ajax({
            dataType: 'json',
            url: docmessagesend_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                var m = '';
                if (data.message_sent == 1) {
                    $("#currentInfoMessage").text("Сообщение сохранено");
                    $("#infoNotification").jqxNotification("open");
                    $('#Documents').jqxGrid('selectrow', rowindex);
                }
            },
            error: function (xhr, status, errorThrown) {
                $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                    + status + ". Обратитесь к администратору.");
                $("#serverErrorNotification").jqxNotification("open");
            }
        });
        $("#sendMessageWindow").jqxWindow('hide');
    });
    $("#sendMessageWindow").jqxWindow({
        width: 430,
        height: 260,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: $("#CancelMessage"),
        theme: theme,
        modalOpacity: 0.01
    });
};*/