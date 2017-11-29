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

// Инициализация списков-фильтров форма -> таблица
initFormTableFilter = function() {
    plist.jqxDropDownList({
        theme: theme,
        source: periodsDataAdapter,
        displayMember: "name",
        valueMember: "id",
        placeHolder: "Выберите период:",
        //selectedIndex: 2,
        width: 250,
        height: 32
    });
    plist.on('select', function (event) {
        let args = event.args;
        //console.log(args.item);
        current_period = args.item.value;
        $("#periodSelected").html('<div class="text-bold text-info" style="margin-left: -100px">Выбран период: "'+ args.item.label +'"</div>');
    });
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
        let args = event.args;
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
    $("#levelListContainer").jqxDropDownButton({ width: 250, height: 32, theme: theme });
    levellist.jqxGrid(
        {
            width: '540px',
            height: '340px',
            theme: theme,
            localization: localize(),
            source: levelsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Код', datafield: 'code', width: '70px'  },
                { text: 'Тип', datafield: 'type', width: '70px'  },
                { text: 'Имя', datafield: 'name' , width: '380px'}
            ]
        });

/*    levellist.jqxDropDownList({
        theme: theme,
        source: levelsDataAdapter,
        displayMember: "name",
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
    });*/
    levellist.on('rowselect', function (event) {
        $("#levelListContainer").jqxDropDownButton('close');
        var args = event.args;
        if (args.rowindex == -1) {
            return false;
        }
        var r = args.row;
        current_level = r.id;
        current_type = r.type;
        //console.log(current_level);
        $("#levelSelected").html('<div class="text-bold text-info" style="margin-left: -100px">Установлено ограничение по: "' + r.code + ' "'+ r.name + '"</div>');
        if (current_level == 0) {
            $( "#legacy" ).prop( "disabled", false );
            $( "#territory" ).prop( "disabled", false );
        } else if (current_level !== 0) {
            $( "#primary" ).prop( "checked", true );
            $( "#legacy" ).prop( "disabled", true );
            $( "#territory" ).prop( "disabled", true );
        }
    });
};

// Обновление списка таблиц при выборе формы
updateTableDropdownList = function(form) {
    tablesource.url = tablefetch_url + current_form;
    $("#tableListContainer").jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите таблицу из формы ' + form.label + '</div>');
    tlist.jqxDataTable('updateBoundData');
};

// Таблица списков
let initList = function() {
    $("#ListContainer").jqxDropDownButton({ width: 250, height: 32, theme: theme });
    let listsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'slug', type: 'string' },
            { name: 'name', type: 'string' }
        ],
        id: 'id',
        localdata: lists,
    };
    let dadapter =  new $.jqx.dataAdapter(listsource);
    list.jqxGrid(
        {
            width: '500px',
            height: '300px',
            theme: theme,
            localization: localize(),
            source: dadapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Пседоним', datafield: 'slug', width: '70px'  },
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
        membersource.url = member_url + currentlist;
        unitsource.url = units_url + currentlist;
        listterms.jqxGrid('updatebounddata');
        units.jqxGrid('updatebounddata');
    });
};

let initListMembers = function () {
    membersource =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'int' },
                { name: 'list_id', type: 'int' },
                { name: 'uname', map: 'unit>unit_name', type: 'string' },
                { name: 'ou_id', type: 'int' }
            ],
            id: 'id',
            url: member_url + currentlist,
            root: 'member'
        };
    let memberDataAdapter =  new $.jqx.dataAdapter(membersource);
    listterms.jqxGrid(
        {
            width: '100%',
            theme: theme,
            localization: localize(),
            source: memberDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            selectionmode: 'checkbox',
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
            theme: theme,
            localization: localize(),
            source: unitDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            selectionmode: 'checkbox',
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

initActions = function() {
    $("#make").click(function () {
        var data = setquery();
        var url = output_url + data;
        //console.log(url);
        //window.open(url);
        location.replace(url);
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
