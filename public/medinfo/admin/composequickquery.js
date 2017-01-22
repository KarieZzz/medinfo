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
                { text: '№ п/п', datafield: 'row_index', width: '50px' },
                { text: 'Код', datafield: 'row_code', width: '70px'  },
                { text: 'Имя', datafield: 'row_name' , width: '380px'}
            ]
        });
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
            columns: [
                { text: '№ п/п', datafield: 'column_index', width: '50px' },
                { text: 'Имя', datafield: 'column_name' , width: '300px'}
            ]
        });
};
// функция для обновления связанных объектов после выбора таблицы
updateRelated = function() {
    updateRowList();
    updateColumnList();
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
    return "&table_id=" + current_table +
        "&row_index=" + $("#row_index").val() +
        "&row_code=" + $("#row_code").val() +
        "&row_name=" + $("#row_name").val() +
        "&medstat_code=" + $("#row_medstat_code").val() +
        "&medinfo_id=" + $("#row_medinfo_id").val() +
        "&excluded=" + ($("#excludedRow").val() ? 1 : 0);
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
    });
    modebutton.on( 'checked', function (event) {
        $("#rowListContainer").jqxDropDownButton({ disabled: true });
        $("#columnListContainer").jqxDropDownButton({ disabled: false });
    });
};

// Операции со строками
initRowActions = function() {
    $("#insertrow").click(function () {
        var data = setrowquery();
        $.ajax({
            dataType: 'json',
            url: '/admin/rc/rowcreate',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error != 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                rlist.jqxGrid('updatebounddata', 'data');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });

};