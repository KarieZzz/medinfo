initFilterDatasources = function() {
    var formssource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'form_code' }
        ],
        id: 'id',
        localdata: forms
    };
    formsDataAdapter = new $.jqx.dataAdapter(formssource);
    tablesource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'table_code' },
            { name: 'table_name' }
        ],
        id: 'id',
        url: tablefetch_url + current_form
    };
    tablesDataAdapter = new $.jqx.dataAdapter(tablesource);
    var levelssource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'unit_name' }
        ],
        id: 'id',
        localdata: levels
    };
    levelsDataAdapter = new $.jqx.dataAdapter(levelssource);

};

initdatasources = function() {
    rowsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'excluded', map: 'excluded>0>album_id', type: 'bool' },
            { name: 'row_index', type: 'int' },
            { name: 'row_code', type: 'string' },
            { name: 'row_name', type: 'string' },
            { name: 'medstat_code', type: 'string' },
            { name: 'medinfo_id', type: 'int' }
        ],
        id: 'id',
        url: rowfetch_url + current_table,
        root: 'row'
    };
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

// Инициализация списков-фильтров форма -> таблица
initFormTableFilter = function() {
    flist.jqxDropDownList({
        theme: theme,
        source: formsDataAdapter,
        displayMember: "form_code",
        valueMember: "id",
        placeHolder: "Выберите форму:",
        //selectedIndex: 2,
        width: 250,
        height: 32
    });
    flist.on('select', function (event) {
        var args = event.args;
        current_form = args.item.value;
        updateTableDropdownList(args.item);
    });
    $("#tableListContainer").jqxDropDownButton({ width: 250, height: 32, theme: theme });
    tlist.jqxDataTable({
        theme: theme,
        source: tablesDataAdapter,
        width: 420,
        height: 400,
        columns: [{
            text: 'Код',
            dataField: 'table_code',
            width: 100
        },
            {
                text: 'Наименование',
                dataField: 'table_name',
                width: 300
            }
        ]
    });
    tlist.on('rowSelect', function (event) {
        $("#tableListContainer").jqxDropDownButton('close');
        var args = event.args;
        var r = args.row;
        current_table = args.key;
        $("#tableSelected").html('<div class="text-bold text-info" style="margin-left: -100px">Таблица: (' + r.table_code + ') ' + r.table_name + '</div>');
        updateRelated();
    });
    levellist.jqxDropDownList({
        theme: theme,
        source: levelsDataAdapter,
        displayMember: "unit_name",
        valueMember: "id",
        selectedIndex: 0,
        width: 300,
        height: 32
    });
    levellist.on('select', function (event) {
        var args = event.args;
        current_level = args.item.value;
        //console.log(args.item);
        $("#levelSelected").html('<div class="text-bold text-info" style="margin-left: -100px">Установлено ограничение по: "' + args.item.label + '"</div>');
    });

};

// Обновление списка таблиц при выборе формы
updateTableDropdownList = function(form) {
    tablesource.url = tablefetch_url + current_form;
    $("#tableListContainer").jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите таблицу из формы ' + form.label + '</div>');
    tlist.jqxDataTable('updateBoundData');
};

// Таблица строк
initRowList = function() {
    $("#rowListContainer").jqxDropDownButton({ width: 250, height: 32, theme: theme });
    rlist.jqxGrid(
        {
            width: '500px',
            height: '300px',
            theme: theme,
            localization: localize(),
            source: rowsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Код', datafield: 'row_code', width: '70px'  },
                { text: 'Имя', datafield: 'row_name' , width: '380px'}
            ]
        });
    if (groupmode == 1) {
        rlist.on('rowselect', function (event) { rowtableclick(event); });
    } else if (groupmode == 2) {
        rlist.on('rowselect', function (event) { rowtableclick(event); });
        rlist.on('rowunselect', function (event) { rowtableclick(event); });
    }
};
//Таблица граф
initColumnList = function() {
    $("#columnListContainer").jqxDropDownButton({ width: 250, height: 32, theme: theme, disabled: true });
    clist.jqxGrid(
        {
            width: '350px',
            height: '300px',
            theme: theme,
            localization: localize(),
            source: columnsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            selectionmode: 'multiplerows',
            columns: [
                { text: '№ п/п', datafield: 'column_index', width: '50px' },
                { text: 'Имя', datafield: 'column_name' , width: '300px'}
            ]
        });
    if (groupmode == 2) {
        clist.on('rowselect', function (event) { columntableclick(event); });
    } else if (groupmode == 1) {
        clist.on('rowselect', function (event) { columntableclick(event); });
        clist.on('rowunselect', function (event) { columntableclick(event); });
    }
};
// функция для обновления связанных объектов после выбора таблицы
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
// Обновление списка граф при выборе таблицы
updateColumnList = function() {
    columnsource.url = columnfetch_url + current_table;
    clist.jqxGrid('clearselection');
    clist.jqxGrid('updatebounddata');
};

setquery = function() {
    return "?form=" + current_form +
        "&table=" + current_table +
        "&rows=" + rows +
        "&columns=" + columns +
        "&level=" + current_level +
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
};

initActions = function() {
    $("#make").click(function () {
        var data = setquery();
        var url = output_url + data;
        //console.log(url);
        window.open(url);
    });
};

var getselectedrows = function () {
    var rowindexes = rlist.jqxGrid('getselectedrowindexes');
    var indexes_length =  rowindexes.length;
    rows = [];
    for (i = 0; i < indexes_length; i++) {
        rows.push(rlist.jqxGrid('getrowid', rowindexes[i]));
    }
    return rows;
};

var getselectedcolumns = function () {
    var rowindexes = clist.jqxGrid('getselectedrowindexes');
    var indexes_length =  rowindexes.length;
    columns = [];
    for (i = 0; i < indexes_length; i++) {
        columns.push(clist.jqxGrid('getrowid', rowindexes[i]));
    }
    return columns;
};

var rowtableclick = function (event) {
    if (groupmode == 1) {
        $("#rowListContainer").jqxDropDownButton('close');
        var args = event.args;
        if (args.rowindex == -1) {
            return false;
        }
        var r = args.row;
        rows = [];
        rows.push(r.id);
        //console.log(r);
        $("#rowSelected").html('<div class="text-bold text-danger" style="margin-left: -100px">Справка будет сгруппирована по строке: ' + r.row_code + ' "'+ r.row_name + '"</div>');
        $("#columnListContainer").jqxDropDownButton({ disabled: false });
    } else if (groupmode == 2) {
        var selected = getselectedrows();
        $("#rowSelected").html('<div class="text-bold text-info" style="margin-left: -100px">Выбрано строк для вывода данных: ' + selected.length + '</div>');
    }
};

var columntableclick = function (event) {
    if (groupmode == 2) {
        $("#columnListContainer").jqxDropDownButton('close');
        var args = event.args;
        if (args.rowindex == -1) {
            return false;
        }
        var r = args.row;
        columns = [];
        columns.push(r.id);
        //console.log(args);
        $("#columnSelected").html('<div class="text-bold text-danger" style="margin-left: -100px">Справка будет сгруппирована по графе: ' + r.column_index + ' "' + r.column_name + '"</div>');
        $("#rowListContainer").jqxDropDownButton({ disabled: false });
    } else if (groupmode == 1) {
        var selected = getselectedcolumns();
        $("#columnSelected").html('<div class="text-bold text-info" style="margin-left: -100px">Выбрано граф для вывода данных: ' + selected.length + '</div>');
    }
};