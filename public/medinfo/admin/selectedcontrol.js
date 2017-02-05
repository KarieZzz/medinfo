initdatasources = function() {
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
    functionsource = {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'table_id', type: 'int' },
            { name: 'table_code', map: 'table>table_code', type: 'string' },
            { name: 'level', type: 'int' },
            { name: 'script', type: 'string' },
            { name: 'comment', type: 'string' },
            { name: 'blocked', type: 'bool' }
        ],
        id: 'id',
        url: functionfetch_url + current_form,
        root: 'f'
    };
    functionsDataAdapter = new $.jqx.dataAdapter(functionsource);
};

initFormFilter = function() {
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
        updateFunctionList();
    });
};
// Таблица функций
initFunctionList = function() {
    fgrid.jqxGrid(
        {
            width: '98%',
            height: '500px',
            theme: theme,
            localization: localize(),
            source: functionsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            selectionmode: 'checkbox',
            columns: [
                { text: 'Id', datafield: 'id', width: '50px' },
                { text: 'Код таблицы', datafield: 'table_code', width: '70px'  },
                { text: 'Уровень', datafield: 'level', width: '70px'  },
                { text: 'Функция контроля', datafield: 'script' , width: '50%'},
                { text: 'Комментарий', datafield: 'comment', width: '30%' },
                { text: 'Отключена', datafield: 'blocked', columntype: 'checkbox', width: '70px' }
            ]
        });
};
// Обновление списка строк при выборе таблицы
updateFunctionList = function() {
    functionsource.url = functionfetch_url + current_form;
    fgrid.jqxGrid('clearselection');
    fgrid.jqxGrid('updatebounddata');
};
// Операции с функциями контроля

setquery = function() {
    return "?form=" + current_form +
        "&cfunctions=" + getselectedfunctions();
};

initAction = function() {
    $("#performControl").click(function () {
        var data = setquery();
        var url = output_url + data;
        //console.log(url);
        window.open(url);
    });
};

var getselectedfunctions = function () {
    var rowindexes = fgrid.jqxGrid('getselectedrowindexes');
    var indexes_length =  rowindexes.length;
    var row_ids = [];
    for (i = 0; i < indexes_length; i++) {
        row_ids.push(fgrid.jqxGrid('getrowid', rowindexes[i]));
    }
    return row_ids;
};