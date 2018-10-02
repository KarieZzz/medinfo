let mon_tree_url = '/admin/fetch_mon_tree/';
let docsource_url = '/admin/fetchdocuments?';
let createdocuments_url = '/admin/createdocuments';
let deletedocuments_url = '/admin/deletedocuments';
let erasedocuments_url = '/admin/erasedocuments';
let clonedocuments_url = '/admin/clonedocuments';
let changestate_url = '/admin/documentstatechange';
let group_tree_url = '/admin/fetchugroups';
let protectaggregate_url = '/admin/protectaggregates';
let calculate_url = '/admin/consolidate/';
let log_form_url = '/admin/documents/valuechanginglog/';
let current_top_level_node = 0;
let filter_mode = 1; // 1 - по территориям; 2 - по группам
let terr = $("#moSelectorByTerritories");
let groups = $('#moSelectorByGroups');
let grouptree = $("#groupTree");
let motree = $("#moTree");
let montree = $("#monTree");
let dlist = $('#documentList');
let checkedmonitorings = [];
let checkedforms = [];

// Инициализация источников данных для таблиц
docroute = function () {
    let route = '&filter_mode=' + filter_mode + '&ou=' + current_top_level_node +  '&dtypes=' + checkeddtypes.join();
    route += '&states='+ checkedstates.join() +'&monitorings=' + checkedmonitorings.join() +'&forms=' + checkedforms.join() + '&periods=' + checkedperiods.join()
    return route;
};
datasources = function() {
    mo_source =
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
            { name: 'monitoring', type: 'string' },
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
            if (jqXHR.status === 401) {
                raiseError('Пользователь не авторизован');
            }
        }
    });
};
// инициализация источников данных для предустановленных фильтров
initfilterdatasources = function() {
    let monitorings_source =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'name' }
        ],
        id: 'id',
        localdata: monitorings
    };
    let albums_source =
        {
            datatype: "json",
            datafields: [
                { name: 'id' },
                { name: 'album_name' }
            ],
            id: 'id',
            localdata: albums
        };
    let forms_source =
        {
            datatype: "json",
            datafields: [
                { name: 'id' },
                { name: 'form_code' }
            ],
            id: 'id',
            localdata: forms
        };
    let states_source =
    {
        datatype: "array",
        datafields: [
            { name: 'code' },
            { name: 'name' }
        ],
        id: 'code',
        localdata: states
    };
    let periods_source =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'name' }
        ],
        id: 'id',
        localdata: periods
    };
    let dtypes_source =
    {
        datatype: "array",
        datafields: [
            { name: 'code' },
            { name: 'name' }
        ],
        id: 'code',
        localdata: dtypes
    };
    monitoringssDataAdapter = new $.jqx.dataAdapter(monitorings_source);
    albumsDataAdapter = new $.jqx.dataAdapter(albums_source);
    formsDataAdapter = new $.jqx.dataAdapter(forms_source);
    statesDataAdapter = new $.jqx.dataAdapter(states_source);
    changestateDA =  new $.jqx.dataAdapter(states_source);
    periodsDataAdapter = new $.jqx.dataAdapter(periods_source);
    dtypesDataAdapter = new $.jqx.dataAdapter(dtypes_source);
};

initDropdowns = function () {
    terr.jqxDropDownButton({width: 350, height: 32, theme: theme});
    terr.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по территориям)</div>');
    groups.jqxDropDownButton({width: 350, height: 32, theme: theme});
    groups.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по группам)</div>');
/*    mondropdown.jqxDropDownButton({width: 350, height: 32, theme: theme});
    mondropdown.jqxDropDownButton('setContent', '<div style="margin: 9px">Мониторинги</div>');
    mondropdown.on('close', function () {
        updatedocumenttable()
    });
    periodDropDown.jqxDropDownButton({width: 350, height: 32, theme: theme});
    periodDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Отчетные периоды</div>');
    periodDropDown.on('close', function () {
        updatedocumenttable()
    });
    statusDropDown.jqxDropDownButton({width: 350, height: 32, theme: theme});
    statusDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Статусы отчетов</div>');
    statusDropDown.on('close', function () {
        updatedocumenttable()
    });
    $("#clearAllFilters").click( clearAllFilters );*/
};

getCheckedMonsForms = function() {
    let monitorings = [];
    let forms = [];
    let mf = [];
    let checkedRows;
    let uniquemonitorings;
    let uniqueforms;
    checkedRows = montree.jqxTreeGrid('getCheckedRows');
    //console.log(checkedRows);
    if (typeof checkedRows !== 'undefined') {
        for (let i = 0; i < checkedRows.length; i++) {
            mf.push(checkedRows[i].id);
            let r = checkedRows[i].id.toString();
            monitorings.push(r.substr(0,6));
            let form_id = r.substr(6);
            if (form_id) {
                forms.push(form_id);
            }
        }
    }
    //console.log(forms);
    uniquemonitorings = Array.from(new Set(monitorings));
    uniqueforms = Array.from(new Set(forms));

    return {f: uniqueforms, m: uniquemonitorings, mf: mf};
};

/*checkformfilter = function() {
    checkedforms = [];
    var checkedItems = $("#formsListbox").jqxListBox('getCheckedItems');
    var formcount = checkedItems.length;
    for (i=0; i < formcount; i++) {
        checkedforms.push(checkedItems[i].value);
    }
};*/
checkstatefilter = function() {
    checkedstates = [];
    var checkedItems = $("#statesListbox").jqxListBox('getCheckedItems');
    var statecount = checkedItems.length;
    for (i=0; i < statecount; i++) {
        checkedstates.push(checkedItems[i].value);
    }
};
checkperiodfilter = function() {
    checkedperiods = [];
    var checkedItems = $("#periodsListbox").jqxListBox('getCheckedItems');
    var periodcount = checkedItems.length;
    for (i=0; i < periodcount; i++) {
        checkedperiods.push(checkedItems[i].value);
    }
};
checkdtypefilter = function() {
    checkeddtypes = [];
    var checkedItems = $("#dtypesListbox").jqxListBox('getCheckedItems');
    var typecount = checkedItems.length;
    for (i=0; i < typecount; i++) {
        checkeddtypes.push(checkedItems[i].value);
    }
};
// Возвращает массив с идентификаторами выделенных документов
getselecteddocuments = function () {
    let rowindexes = dlist.jqxGrid('getselectedrowindexes');
    let indexes_length =  rowindexes.length;
    let row_ids = [];
    for (i = 0; i < indexes_length; i++) {
        row_ids.push(dlist.jqxGrid('getrowid', rowindexes[i]));
    }
    return row_ids;
};
getcheckedunits = function() {
    let ids = [];
    let checkedRows;
    let i;
    if (filter_mode === 1) {
        checkedRows = motree.jqxTreeGrid('getCheckedRows');
        for (i = 0; i < checkedRows.length; i++) {
            ids.push(checkedRows[i].uid);
        }
    } else if (filter_mode === 2) {
        checkedRows = grouptree.jqxTreeGrid('getCheckedRows');
        for (i = 0; i < checkedRows.length; i++) {
            ids.push(checkedRows[i].uid);
        }
    }
    return ids;
};
// обновление таблиц первичных и сводных документов в зависимости от выделенных форм, периодов, статусов документов
updatedocumenttable = function() {
    var old_doc_url = docsource.url;
    var new_doc_url = docsource_url + docroute();
    if (new_doc_url !== old_doc_url) {
        docsource.url = new_doc_url;
        dlist.jqxGrid('clearselection');
        dlist.jqxGrid('updatebounddata');
    }
};

// Инициализация окна ввода нового документа
initnewdocumentwindow = function () {
    let savebutton = $('#saveButton');
    let newdoc_form = $('#newForm').jqxWindow({
        width: 600,
        height: 520,
        resizable: false,
        autoOpen: false,
        isModal: true,
        cancelButton: $('#cancelButton'),
        position: { x: 310, y: 125 },
        initContent: function () {
            //savebutton.jqxButton({ width: '80px', disabled: false });
            //$('#cancelButton').jqxButton({ width: '80px', disabled: false });
        }
    });
    $('#selectPrimary').jqxCheckBox({ width: '150px' });
    $('#selectAggregate').jqxCheckBox({ width: '150px' });
    $("#selectMonitoring").jqxDropDownList({
        theme: theme,
        source: monitoringssDataAdapter,
        displayMember: "name",
        valueMember: "id",
        placeHolder: "Выберите мониторинг:",
        width: 350,
        height: 25
    });

    $("#selectAlbum").jqxDropDownList({
        theme: theme,
        source: albumsDataAdapter,
        displayMember: "album_name",
        valueMember: "id",
        placeHolder: "Выберите альбом форм:",
        width: 350,
        height: 25
    });

    $("#selectForm").jqxDropDownList({
        theme: theme,
        checkboxes: true,
        source: formsDataAdapter,
        displayMember: "form_code",
        valueMember: "id",
        placeHolder: "Выберите форму:",
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
        let data;
        let primary;
        let aggregate;
        let selectedunits = getcheckedunits();
        let selectedmonitoring = $("#selectMonitoring").jqxDropDownList('getSelectedItem').value;
        let selectedalbum = $("#selectAlbum").jqxDropDownList('getSelectedItem').value;
        let selectedforms = [];
        let checked = $("#selectForm").jqxDropDownList('getCheckedItems');
        for (let i = 0; i < checked.length; i++) {
            selectedforms.push(checked[i].value);
        }
        if (selectedforms.length === 0) {
            raiseError("Не выбрано ни одной формы для создания документов");
            return false;
        }
        let selectedstate = $("#selectState").jqxDropDownList('getSelectedItem').value;
        let selectedperiod = $("#selectPeriod").jqxDropDownList('getSelectedItem').value;
        //console.log(selectedstate);
        primary = $('#selectPrimary').jqxCheckBox('checked') ? 1 : 0 ;
        aggregate = $('#selectAggregate').jqxCheckBox('checked') ? 1 : 0;
        if (primary === 0 && aggregate === 0) {
            raiseError("Не выбрано ни одного типа создаваемых документов (первичные, сводные");
            return false;
        }
        data = "&filter_mode=" + filter_mode + "&units=" + selectedunits + "&monitoring=" + selectedmonitoring + "&album=" + selectedalbum;
        data += "&forms=" + selectedforms + "&period=" + selectedperiod + "&state=" + selectedstate + "&primary=" + primary + "&aggregate=" + aggregate;
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

/*initmotabs = function() {
    $("#motabs").jqxTabs({  height: '100%', width: '100%', theme: theme });
};*/

// Инициализация разбивки рабочего стола на области
initsplitters = function() {
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
rendermotreetoolbar = function() {
    let toolbar = $("#filtermoTree");
    let container = $("<div style='display: none' id='buttonplus'></div>");
    let newdoc_form = $('#newForm');
    let newdocument = $("<i style='height: 14px' class='fa fa-wpforms fa-lg' title='Новый документ'></i>");
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

rendergrouptreetoolbar = function() {
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
initmotree = function() {
    motree.jqxTreeGrid(
        {
            width: 770,
            height: 600,
            theme: theme,
            source: mo_dataAdapter,
            selectionMode: "singleRow",
            showToolbar: true,
            renderToolbar: rendermotreetoolbar,
            hierarchicalCheckboxes: false,
            checkboxes: true,
            filterable: true,
            filterMode: "simple",
            localization: localize(),
            columnsResize: true,
            ready: function()
            {
                // expand row with 'EmployeeKey = 32'
                motree.jqxTreeGrid('expandRow', 0);
            },
            columns: [
                { text: 'Код', dataField: 'unit_code', width: 170 },
                { text: 'Наименование', dataField: 'unit_name', width: 585 }
            ]
        });
    motree.on('filter',
        function (event)
        {
            let args = event.args;
            let filters = args.filters;
            motree.jqxTreeGrid('expandAll');
        }
    );


/*    motree.jqxTreeGrid(
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
    );*/

    motree.on('rowSelect',
        function (event)
        {
            let args = event.args;
            let new_top_level_node = args.key;
            if (new_top_level_node === current_top_level_node && filter_mode === 1) {
                return false;
            }
            current_top_level_node =  new_top_level_node;
            filter_mode = 1; // режим отбора документов по территориям
            updatedocumenttable();
            //terr.jqxDropDownButton('close');
            if (current_top_level_node === 0) {
                terr.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по территориям)</div>');
            } else {
                terr.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Медицинские организации (по территориям)</div>');
            }
            groups.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по группам)</div>');
            return true;
        }
    );

/*    motree.on('rowSelect', function (event) {
        var args = event.args;
        var new_top_level_node = args.key;
        if (new_top_level_node == current_top_level_node) {
            return false;
        }
        current_top_level_node = new_top_level_node;
        filter_mode = 1; // режим отбора документов по территориям
        updatedocumenttable();
        return true;
    });*/

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

initgrouptree = function() {
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
initfiltertabs = function() {
    $("#filtertabs").jqxTabs({  height: '100%', width: '100%', theme: theme });

    $("#statesListbox").jqxListBox({
        theme: theme,
        source: statesDataAdapter,
        displayMember: 'name',
        valueMember: 'code',
        checkboxes: true,
        width: 300,
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
        width: 400,
        height: 200
    });
    //var item = $("#periodsListbox").jqxListBox('getItem', 0) ;
    //$("#periodsListbox").jqxListBox('checkItem', item );
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
initdocumentslist = function() {
    dlist.jqxGrid(
        {
            width: '98%',
            height: '95%',
            theme: theme,
            localization: localize(),
            source: dataAdapter,
            columnsresize: true,
            selectionmode: 'checkbox',
            showfilterrow: true,
            filterable: true,
            columns: [
                { text: '№', datafield: 'id', width: '60px', cellsrenderer: linkrenderer },
                { text: 'Тип', datafield: 'doctype' , width: '80px'},
                { text: 'МО', datafield: 'unit_name', width: '250px' },
                { text: 'Мониторинг', datafield: 'monitoring', width: '250px' },
                { text: 'Период', datafield: 'period', width: '100px' },
                { text: 'Форма', datafield: 'form_code', width: '80px'  },
                { text: 'Статус', datafield: 'state' },
                { text: 'Защищен', datafield: 'protected', columntype: 'checkbox', width: '80px' },
                { text: 'Данные', datafield: 'filled', columntype: 'checkbox', width: '80px' }
            ]
        });
};
// рендеринг панели инструментов для выделенных документов
initdocumentactions = function() {
    let clone = $("#CloneDocuments");
    let state = $("#statesDropdownList");
    let del = $("#deleteDocuments");
    state.jqxDropDownList({
        theme: theme,
        source: changestateDA,
        displayMember: "name",
        valueMember: "code",
        placeHolder: "Статус документов:",
        //selectedIndex: 2,
        width: 150,
        height: 23
    });
    state.on('select', function (event)
    {
        let args = event.args;
        let selectedstate = args.item.value;
        let row_ids = noselected_error("Не выбрано ни одного документа для смены статуса");
        if (!row_ids) {
            $(this).jqxDropDownList('clearSelection');
            return false;
        }
        let data = "documents=" + row_ids + '&state=' + selectedstate;
        let confirm_text = 'Подтвердите смену статуса у документов №№ ' + row_ids + '. \n';
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
                if (data.state_changed === 1) {
                    raiseInfo(data.comment + ' Количество измененных документов ' + data.affected_documents + '.');
                }
                dlist.jqxGrid('clearselection');
                dlist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                let error_text = "Ошибка сохранения данных на сервере. Обратитесь к администратору";
                raiseError(error_text, xhr);
            }
        });
        $(this).jqxDropDownList('clearSelection');
    });
    del.jqxButton({ theme: theme });
    del.click(function () {
        let row_ids = noselected_error("Не выбрано ни одного документа для удаления");
        if (!row_ids) {
            return false;
        }
        let data = "documents=" + row_ids;
        let confirm_text = 'Подтвердите удаление документов №№ ' + row_ids + '. \n';
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
                if (data.documents_deleted === 1) {
                    raiseInfo(data.comment);
                }
                dlist.jqxGrid('clearselection');
                dlist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                let error_text = "Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.";
                raiseError(error_text);
            }
        });
    });
    $("#eraseData").jqxButton ({ theme: theme});
    $("#eraseData").click(function () {
        let row_ids = noselected_error("Не выбрано ни одного документа для удаления статданных");
        if (!row_ids) {
            return false;
        }
        let data = "documents=" + row_ids;
        let confirm_text = 'Подтвердите удаление статданных из документов №№ ' + row_ids + '. \n';
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
                let error_text = "Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.";
                raiseError(error_text);
            }
        });
    });
    $("#protectAggregates").jqxButton ({ theme: theme});
    $("#protectAggregates").click(function () {
        let row_ids = noselected_error("Не выбрано ни одного документа защиты от повторного свода");
        if (!row_ids) {
            return false;
        }
        let data = "documents=" + row_ids;
        let confirm_text = 'Подтвердите установку защиты от повторного свода для документов №№ ' + row_ids + '. \n';
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
                let error_text = "Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.";
                raiseError(error_text);
            }
        });
    });
    $("#Сalculate").jqxButton ({ theme: theme});
    $("#Сalculate").click(function () {
        let row_ids = noselected_error("Не выбрано ни одного документа для расчета (консолидации)");
        if (row_ids.length > 1) {
            raiseError("Необходимо выбрать только один документ для выполнения консолидации");
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: calculate_url + row_ids[0],
            method: "GET",
            success: function (data, status, xhr) {
                if (data.consolidated === true) {
                    raiseInfo("Консолидация данных выполнена. Заполнено ячеек " + data.cell_affected + ".");
                }
                dlist.jqxGrid('clearselection');
                dlist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                let error_text = "Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.";
                raiseError(error_text);
            }
        });
    });
    $("#ValueEditingLog").jqxButton ({ theme: theme});
    $("#ValueEditingLog").click(function () {
        let row_ids = noselected_error("Не выбрано ни одного документа");
        if (row_ids.length > 1) {
            raiseError("Необходимо выбрать только один документ");
            return false;
        }
        let editWindow = window.open(log_form_url + row_ids[0]);
    });


    let newdoc_form = $('#cloneDocuments').jqxWindow({
        width: 600,
        height: 520,
        resizable: false,
        autoOpen: false,
        isModal: true,
        cancelButton: $('#cancelClone'),
        position: { x: 410, y: 225 },
    });
    $("#selectClonePeriod").jqxDropDownList({
        theme: theme,
        source: periodsDataAdapter,
        displayMember: "name",
        valueMember: "id",
        placeHolder: "Выберите период:",
        selectedIndex: 0,
        width: 250,
        height: 25
    });
    $("#selectCloneMonitoring").jqxDropDownList({
        theme: theme,
        source: monitoringssDataAdapter,
        displayMember: "name",
        valueMember: "id",
        placeHolder: "Выберите мониторинг:",
        width: 350,
        height: 25
    });
    $("#selectCloneAlbum").jqxDropDownList({
        theme: theme,
        source: albumsDataAdapter,
        displayMember: "album_name",
        valueMember: "id",
        placeHolder: "Выберите альбом форм:",
        width: 350,
        height: 25
    });
    $("#selectCloneState").jqxDropDownList({
        theme: theme,
        source: statesDataAdapter,
        displayMember: "name",
        valueMember: "code",
        placeHolder: "Выберите статус:",
        selectedIndex: 0,
        width: 250,
        height: 25
    });
    $("#doClone").click(function () {
        let row_ids = noselected_error("Не выбрано ни одного документа");
        let selectedperiod = $("#selectClonePeriod").jqxDropDownList('getSelectedItem').value;
        let selectedmon = $("#selectCloneMonitoring").jqxDropDownList('getSelectedItem').value;
        let selectedalbum = $("#selectCloneAlbum").jqxDropDownList('getSelectedItem').value;
        let selectedstate = $("#selectCloneState").jqxDropDownList('getSelectedItem').value;
        let data = "&documents=" + row_ids + "&period=" + selectedperiod + "&monitoring=" + selectedmon + "&album=" + selectedalbum + "&state=" + selectedstate;
        $.ajax({
            dataType: 'json',
            url: clonedocuments_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.documents_deleted === 1) {
                    raiseInfo(data.comment);
                }
                dlist.jqxGrid('clearselection');
                dlist.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                let error_text = "Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.";
                raiseError(error_text);
            }
        });
    });

    clone.jqxButton({ theme: theme });
    clone.click(function () {
        let row_ids = noselected_error("Не выбрано ни одного документа для клонирования");
        if (!row_ids) {
            return false;
        }
        newdoc_form.jqxWindow('open');
    });
};
linkrenderer = function (row, column, value) {
    let html = "<div class='jqx-grid-cell-left-align' style='margin-top: 6px'>";
    html += "<a href='/datainput/formdashboard/" + value + "' target='_blank' title='Открыть для редактирования'>" + value + "</a></div>";
    return html;
};
noselected_error = function(message) {
    let row_ids = getselecteddocuments();
    if (row_ids.length === 0) {
        raiseError(message);
        return false;
    }
    return row_ids;
};

initMonitoringTree = function () {
    let mon_source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'parent_id', type: 'int' },
                { name: 'name', type: 'string' }
            ],
            hierarchy:
                {
                    keyDataField: { name: 'id' },
                    parentDataField: { name: 'parent_id' }
                },
            id: 'id',
            root: '',
            url: mon_tree_url
        };
    mon_dataAdapter = new $.jqx.dataAdapter(mon_source);
    montree.jqxTreeGrid(
        {
            width: 900,
            height: 600,
            theme: theme,
            source: mon_dataAdapter,
            selectionMode: "singleRow",
            showToolbar: true,
            renderToolbar: montreeToolbar,
            filterable: true,
            filterMode: "simple",
            localization: localize(),
            checkboxes: true,
            hierarchicalCheckboxes: true,
            columnsResize: true,
            autoRowHeight: false,
            ready: function()
            {
                montree.jqxTreeGrid('expandRow', 100000);
/*                for (let i = 0; i < checkedmf.length; i++) {
                    montree.jqxTreeGrid('checkRow', checkedmf[i]);
                }*/
            },
            columns: [
                { text: 'Наименование мониторинга/отчетной формы', dataField: 'name', width: 900 }
            ]
        });
    montree.on('filter',
        function (event)
        {
            let args = event.args;
            let filters = args.filters;
            montree.jqxTreeGrid('expandAll');
        }
    );

    /*    montree.on('rowCheck', function (event) {
     console.log(getCheckedMonsForms());
     });*/
};
montreeToolbar = function (toolbar) {
    toolbar.append("<button type='button' id='moncollapseAll' class='btn btn-default btn-sm'>Свернуть все</button>");
    toolbar.append("<button type='button' id='monexpandAll' class='btn btn-default btn-sm'>Развернуть все</button>");
    toolbar.append("<button type='button' id='monfilterApply' class='btn btn-primary btn-sm'>Применить фильтр</button>");
    $('#monexpandAll').click(function (event) {
        montree.jqxTreeGrid('expandAll');
    });
    $('#moncollapseAll').click(function (event) {
        montree.jqxTreeGrid('collapseAll');
        montree.jqxTreeGrid('expandRow', 0);
    });
    $('#monfilterApply').click(function (event) {
        let checked = getCheckedMonsForms();
        checkedmonitorings = checked.m;
        checkedforms = checked.f;
        updatedocumenttable();
    });
};