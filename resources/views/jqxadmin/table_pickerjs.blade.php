<script type="text/javascript">
    let formspick = {!! $forms  !!};
    let tablepickfetch_url = '/fetchtables/';
    let picked_form = 0;
    let picked_table = 0;
    let initTablePickerDatasources = function() {
        let formssource =
            {
                datatype: "json",
                datafields: [
                    { name: 'id' },
                    { name: 'form_code' }
                ],
                id: 'id',
                localdata: formspick
            };
        formspickDataAdapter = new $.jqx.dataAdapter(formssource);
        tablesource =
            {
                datatype: "json",
                datafields: [
                    { name: 'id' },
                    { name: 'table_code' },
                    { name: 'table_name' }
                ],
                id: 'id',
                url: tablepickfetch_url + picked_form
            };
        tablepickDataAdapter = new $.jqx.dataAdapter(tablesource);
    };

    // Инициализация списков-фильтров форма -> таблица
    let initFormTableFilter = function() {
        let flist = $("#formList");
        let tlist = $("#tableList");
        flist.jqxDropDownList({
            theme: theme,
            source: formspickDataAdapter,
            displayMember: "form_code",
            valueMember: "id",
            placeHolder: "Выберите форму:",
            //selectedIndex: 2,
            width: 200,
            height: 32
        });
        flist.on('select', function (event) {
            let args = event.args;
            picked_form = args.item.value;
            updateTableDropdownList(args.item);
        });
        $("#tableListContainer").jqxDropDownButton({ width: 250, height: 32, theme: theme });
        tlist.jqxDataTable({
            theme: theme,
            source: tablepickDataAdapter,
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
            picked_table = args.key;
            $("#tableProperties").html('<div class="text-bold text-info" style="margin-left: -100px">Таблица: (' + r.table_code + ') ' + r.table_name + '</div>');
            updateRelated();
        });
    };

    // Обновление списка таблиц при выборе формы
    let updateTableDropdownList = function(form) {
        tablesource.url = tablepickfetch_url + picked_form;
        $("#tableListContainer").jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите таблицу из формы ' + form.label + '</div>');
        $("#tableList").jqxDataTable('updateBoundData');
    };
    initTablePickerDatasources();
    initFormTableFilter();
</script>
