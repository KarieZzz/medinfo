/**
 * Created by shameev on 25.10.2016.
 */
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

// Инициализация списков-фильтров форма -> таблица
initFormTableFilter = function() {
    $("#formList").jqxDropDownList({
        theme: theme,
        source: formsDataAdapter,
        displayMember: "form_code",
        valueMember: "id",
        placeHolder: "Выберите форму:",
        //selectedIndex: 2,
        width: 200,
        height: 32
    });
    $('#formList').on('select', function (event) {
        var args = event.args;
        current_form = args.item.value;
        updateTableDropdownList(args.item);
    });
    $("#tableListContainer").jqxDropDownButton({ width: 250, height: 32, theme: theme });
    $("#tableList").jqxDataTable({
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
    $('#tableList').on('rowSelect', function (event) {
        $("#tableListContainer").jqxDropDownButton('close');
        var args = event.args;
        var r = args.row;
        current_table = args.key;
        $("#tableProperties").html('<div class="text-bold text-info" style="margin-left: -100px">Таблица: (' + r.table_code + ') ' + r.table_name + '</div>');
        updateRelated();
    });
};

// Обновление списка таблиц при выборе формы
updateTableDropdownList = function(form) {
    tablesource.url = tablefetch_url + current_form;
    $("#tableListContainer").jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите таблицу из формы ' + form.label + '</div>');
    $("#tableList").jqxDataTable('updateBoundData');
};