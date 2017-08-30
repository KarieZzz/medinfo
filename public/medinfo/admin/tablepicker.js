/**
 * Created by shameev on 25.10.2016.
 */
let tablefetch_url = '/fetchtables/';

let initFilterDatasources = function() {
    let formssource =
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
let initFormTableFilter = function() {
    let flist = $("#formList");
    let tlist = $("#tableList");
    flist.jqxDropDownList({
        theme: theme,
        source: formsDataAdapter,
        displayMember: "form_code",
        valueMember: "id",
        placeHolder: "Выберите форму:",
        //selectedIndex: 2,
        width: 200,
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
        let args = event.args;
        let r = args.row;
        current_table = args.key;
        $("#tableProperties").html('<div class="text-bold text-info" style="margin-left: -100px">Таблица: (' + r.table_code + ') ' + r.table_name + '</div>');
        updateRelated();
    });
};

// Обновление списка таблиц при выборе формы
let updateTableDropdownList = function(form) {
    tablesource.url = tablefetch_url + current_form;
    $("#tableListContainer").jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите таблицу из формы ' + form.label + '</div>');
    $("#tableList").jqxDataTable('updateBoundData');
};