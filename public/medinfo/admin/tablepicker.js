/**
 * Created by shameev on 25.10.2016.
 */
let tablefetch_url = '/fetchtables/';

let initFilterDatasources = function() {
    let formssource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'form_code', type: 'string' },
            { name: 'form_name', type: 'string' }
        ],
        id: 'id',
        localdata: forms
    };
    formsDataAdapter = new $.jqx.dataAdapter(formssource);
    tablesource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'table_code', type: 'string' },
            { name: 'table_name', type: 'string' }
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
    let fc = $("#formListContainer");
    let tc = $("#tableListContainer");
    fc.jqxDropDownButton({ width: 300, height: 32, theme: theme });
    fc.jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите форму</div>');
/*    flist.jqxDropDownList({
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
    });*/
    flist.jqxGrid(
        {
            width: '450px',
            height: '500px',
            theme: theme,
            localization: localize(),
            source: formsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            selectionmode: 'singlerow',
            columns: [
                { text: 'Код', datafield: 'form_code', width: '50px' },
                { text: 'Имя', datafield: 'form_name' , width: '380px'}
            ]
        });
    flist.on('rowselect', function (event) {
        fc.jqxDropDownButton('close');
        let args = event.args;
        if (args.rowindex === -1) {
            return false;
        }
        updateTableDropdownList(args.row);
    });

    tc.jqxDropDownButton({ width: 250, height: 32, theme: theme });
/*    tlist.jqxDataTable({
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
        $("#tableProperties").html('<div class="text-bold text-info" style="margin-left: -30px">Таблица: (' + r.table_code + ') ' + r.table_name + '</div>');
        updateRelated();
    });*/

    tlist.jqxGrid(
        {
            width: '450px',
            height: '500px',
            theme: theme,
            localization: localize(),
            source: tablesDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            selectionmode: 'singlerow',
            columns: [
                { text: 'Код', datafield: 'table_code', width: '50px' },
                { text: 'Имя', datafield: 'table_name' , width: '380px'}
            ]
        });
    tlist.on('rowselect', function (event) {
        tc.jqxDropDownButton('close');
        let args = event.args;
        if (args.rowindex === -1) {
            return false;
        }
        let r = args.row;
        current_table = r.id;
        $("#tableProperties").html('<div class="text-bold text-info" style="margin-left: -30px">Таблица: (' + r.table_code + ') ' + r.table_name + '</div>');
        updateRelated();
    });
};

// Обновление списка таблиц при выборе формы
let updateTableDropdownList = function(form) {
    tablesource.url = tablefetch_url + form.id;
    $("#tableListContainer").jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите таблицу из формы ' + form.form_code + '</div>');
    $("#tableList").jqxGrid('updateBoundData');
};